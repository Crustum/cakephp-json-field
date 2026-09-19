<?php
declare(strict_types=1);

/**
 * Runs a curated subset of CakePHP's core `Database\Query` test suite on the
 * Crustum/JsonField JSON driver, so we can prove the driver does not break
 * CakePHP core SQL generation (select/order/group/having/join/union/insert...).
 *
 * The CakePHP core fixtures (`core.Articles`, `core.Authors`, `core.Comments`,
 * `core.MenuLinkTrees`) are used UNTOUCHED. They resolve against the unprefixed
 * core tables merged into `tests/schema.php`; our own plugin tables are `jf_*`
 * prefixed and never collide.
 *
 * Oracle-specific tests (toChar/datePart/now/concat-oracle/DUAL/distinctON/
 * collation) are skipped — they are not part of standard SQL compatibility.
 */

namespace Crustum\JsonField\Test\TestCase\Database;

use Cake\Database\Exception\DatabaseException;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Datasource\ConnectionManager;
use Cake\Test\TestCase\Database\QueryAssertsTrait;
use Cake\TestSuite\TestCase;
use DateTime;

/**
 * Tests Query class on the JSON driver.
 */
class QueryTestTmp extends TestCase
{
    use QueryAssertsTrait;

    public const ARTICLE_COUNT = 3;

    public const AUTHOR_COUNT = 4;

    public const COMMENT_COUNT = 6;

    protected array $fixtures = [
        'core.Authors',
        'core.Articles',
        'core.Comments',
        'core.MenuLinkTrees',
    ];

    /**
     * @var \Cake\Database\Connection
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $autoQuote;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = ConnectionManager::get('test');
        $this->autoQuote = $this->connection->getDriver()->isAutoQuotingEnabled();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->getDriver()->enableAutoQuoting($this->autoQuote);
        unset($this->connection);
    }

    /**
     * @inheritDoc
     */
    public function testSelectAliasedTables(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query->select(['text' => new IdentifierExpression('a.body'), 'a.author_id'])
            ->from(['a' => 'articles'])->execute();

        $this->assertEquals(['text' => 'First Article Body', 'author_id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['text' => 'Second Article Body', 'author_id' => 3], $result->fetch('assoc'));

        $result = $query->select(['name' => 'b.name'])->from(['b' => 'authors'])
            ->orderBy(['text' => 'desc', 'name' => 'desc'])
            ->execute();
        $this->assertEquals(
            ['text' => 'Third Article Body', 'author_id' => 1, 'name' => 'nate'],
            $result->fetch('assoc'),
        );
        $this->assertEquals(
            ['text' => 'Third Article Body', 'author_id' => 1, 'name' => 'mariano'],
            $result->fetch('assoc'),
        );
    }

    /**
     * @inheritDoc
     */
    public function testSelectOrderBy(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['id'])
            ->from('articles')
            ->orderBy(['id' => 'desc'])
            ->execute();
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));

        $result = $query->orderBy(['id' => 'asc'])->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $result = $query->orderBy(['title' => 'asc'])->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $result = $query->orderBy(['title' => 'asc'], true)->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $result = $query->orderBy(['title' => 'asc', 'published' => 'asc'], true)
            ->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $idField = 'id';
        $expression = $query->expr(["({$idField} + :offset) % 2"]);
        $result = $query
            ->orderBy([$expression, 'id' => 'desc'], true)
            ->bind(':offset', 1)
            ->execute();
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));

        $result = $query
            ->orderBy($expression, true)
            ->orderBy(['id' => 'asc'])
            ->bind(':offset', 1)
            ->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
    }

    /**
     * @inheritDoc
     */
    public function testSelectGroup(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join([
                'table' => 'authors',
                'alias' => 'a',
                'conditions' => $query
                    ->expr()
                    ->eq(new IdentifierExpression('author_id'), new IdentifierExpression('a.id')),
            ])
            ->groupBy('author_id')
            ->execute();

        $expected = [['total' => 2, 'author_id' => 1], ['total' => '1', 'author_id' => 3]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $result = $query
            ->select(['total' => 'count(title)', 'name'], true)
            ->groupBy(['name'], true)
            ->orderBy(['total' => 'asc'])
            ->execute();
        $expected = [['total' => 1, 'name' => 'larry'], ['total' => 2, 'name' => 'mariano']];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $result = $query
            ->select(['articles.id'])
            ->groupBy(['articles.id'])
            ->execute();
        $this->assertCount(3, $result->fetchAll());
    }

    /**
     * Tests that expression objects can be used as the field in a comparison
     * and the values will be bound correctly to the query
     *
     * @return void
     */
    public function testSelectWhereUsingExpressionInField(): void
    {
        $query = $this->connection->selectQuery();
        $subquery = clone $query;
        $result = $query
            ->select(['id'])
            ->from('comments')
            ->where(function ($exp) use ($subquery) {
                $field = clone $exp;
                $field
                    ->add($subquery
                        ->select([
                            $subquery
                                ->func()
                                ->min(new IdentifierExpression('id')),
                        ])
                        ->from('comments'));

                return $exp->eq($field, 100, 'integer');
            })
            ->execute();
        $this->assertCount(0, $result->fetchAll());
    }

    /**
     * Test that order() being a string works.
     *
     * @return void
     */
    public function testSelectOrderByString(): void
    {
        $query = $this->connection->selectQuery();
        $query->select(['id'])
            ->from('articles')
            ->orderBy(['id' => 'asc']);
        $result = $query->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
    }

    /**
     * Tests that having() behaves pretty much the same as the where() method
     *
     * @return void
     */
    public function testSelectHaving(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join(['table' => 'authors', 'alias' => 'a', 'conditions' => $query->expr()->equalFields('author_id', 'a.id')])
            ->groupBy('author_id')
            ->having(['count(author_id) <' => 2], ['count(author_id)' => 'integer'])
            ->execute();
        $expected = [['total' => 1, 'author_id' => 3]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $result = $query->having(['count(author_id)' => 2], ['count(author_id)' => 'integer'], true)
            ->execute();
        $expected = [['total' => 2, 'author_id' => 1]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));
    }

    /**
     * Tests that Query::andHaving() can be used to concatenate conditions with AND
     * in the having clause
     *
     * @return void
     */
    public function testSelectAndHaving(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join(['table' => 'authors', 'alias' => 'a', 'conditions' => $query->expr()->equalFields('author_id', 'a.id')])
            ->groupBy('author_id')
            ->having(['count(author_id) >' => 2], ['count(author_id)' => 'integer'])
            ->andHaving(['count(author_id) <' => 2], ['count(author_id)' => 'integer'])
            ->execute();
        $this->assertCount(0, $result->fetchAll());

        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join(['table' => 'authors', 'alias' => 'a', 'conditions' => $query->expr()->equalFields('author_id', 'a.id')])
            ->groupBy('author_id')
            ->having(['count(author_id)' => 2], ['count(author_id)' => 'integer'])
            ->andHaving(['count(author_id) >' => 1], ['count(author_id)' => 'integer'])
            ->execute();
        $expected = [['total' => 2, 'author_id' => 1]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));
    }

    /**
     * Tests parameter binding
     *
     * @return void
     */
    public function testBind(): void
    {
        $query = $this->connection->selectQuery();
        $createdField = 'created';
        $results = $query->select(['id', 'comment'])
            ->from('comments')
            ->where(["{$createdField} BETWEEN :foo AND :bar"])
            ->bind(':foo', new DateTime('2007-03-18 10:50:00'), 'datetime')
            ->bind(':bar', new DateTime('2007-03-18 10:52:00'), 'datetime')
            ->execute();
        $expected = [['id' => '4', 'comment' => 'Fourth Comment for First Article']];
        $this->assertEquals($expected, $results->fetchAll('assoc'));

        $query = $this->connection->selectQuery();
        $results = $query->select(['id', 'comment'])
            ->from('comments')
            ->where(["{$createdField} BETWEEN :foo AND :bar"])
            ->bind(':foo', '2007-03-18 10:50:00')
            ->bind(':bar', '2007-03-18 10:52:00')
            ->execute();
        $this->assertEquals($expected, $results->fetchAll('assoc'));
    }

    /**
     * Test that insert can use expression objects as values.
     *
     * @return void
     */
    public function testInsertExpressionValues(): void
    {
        $query = $this->connection->insertQuery();
        $query->insert(['title', 'author_id'])
            ->into('articles')
            ->values(['title' => $query->expr("SELECT 'jose'"), 'author_id' => 99]);

        $result = $query->execute();
        $this->assertSame(1, $result->rowCount());
        $result->closeCursor();

        $rows = $this->connection->selectQuery()->select('*')
            ->from('articles')
            ->where(['author_id' => 99])
            ->execute()->fetchAll('assoc');
        $this->assertCount(1, $rows);
        $expected = [
            'id' => 4,
            'title' => 'jose',
            'body' => null,
            'author_id' => '99',
            'published' => 'N',
        ];
        $this->assertEquals($expected, $rows[0]);

        $subquery = $this->connection->selectQuery();
        $subquery->select(['name'])
            ->from('authors')
            ->where(['id' => 1]);

        $query = $this->connection->insertQuery();
        $query->insert(['title', 'author_id'])
            ->into('articles')
            ->values(['title' => $subquery, 'author_id' => 100]);
        $result = $query->execute();
        $this->assertSame(1, $result->rowCount());
        $result->closeCursor();

        $rows = $this->connection->selectQuery()->select('*')
            ->from('articles')
            ->where(['author_id' => 100])
            ->execute()->fetchAll('assoc');
        $this->assertCount(1, $rows);
        $expected = [
            'id' => 5,
            'title' => 'mariano',
            'body' => null,
            'author_id' => '100',
            'published' => 'N',
        ];
        $this->assertEquals($expected, $rows[0]);
    }

    /**
     * Tests that it is possible to one or multiple UNION statements in a query
     *
     * @return void
     */
    public function testUnion(): void
    {
        $union = $this->connection->selectQuery()->select(['id', 'title'])->from(['a' => 'articles']);
        $query = $this->connection->selectQuery();
        $result = $query->select(['id', new IdentifierExpression('comment')])
            ->from(['c' => 'comments'])
            ->union($union)
            ->execute();
        $rows = $result->fetchAll();
        $this->assertCount(self::COMMENT_COUNT + self::ARTICLE_COUNT, $rows);

        $union->select(['foo' => 'id', 'bar' => 'title']);
        $union = $this->connection->selectQuery()
            ->select(['id', 'name', 'other' => 'id', 'nameish' => 'name'])
            ->from(['b' => 'authors'])
            ->where(['id ' => 1]);

        $query->select(['foo' => 'id', 'bar' => new IdentifierExpression('comment')])->union($union);
        $result = $query->execute();
        $rows2 = $result->fetchAll();
        $this->assertCount(self::COMMENT_COUNT + self::AUTHOR_COUNT, $rows2);
        $this->assertNotEquals($rows, $rows2);

        $union = $this->connection->selectQuery()
            ->select(['id', 'title'])
            ->from(['c' => 'articles']);
        $query->select(['id', new IdentifierExpression('comment')], true)->union($union, true);
        $result = $query->execute();
        $rows3 = $result->fetchAll();
        $this->assertCount(self::COMMENT_COUNT + self::ARTICLE_COUNT, $rows3);
        $this->assertEquals($rows, $rows3);
    }

    /**
     * Tests that UNION ALL can be built by setting the second param of union() to true
     *
     * @return void
     */
    public function testUnionAll(): void
    {
        $union = $this->connection->selectQuery()->select(['id', 'title'])->from(['a' => 'articles']);
        $query = $this->connection->selectQuery();
        $result = $query->select(['id', new IdentifierExpression('comment')])
            ->from(['c' => 'comments'])
            ->union($union)
            ->execute();
        $rows = $result->fetchAll();
        $this->assertCount(self::ARTICLE_COUNT + self::COMMENT_COUNT, $rows);

        $union->select(['foo' => 'id', 'bar' => 'title']);
        $union = $this->connection->selectQuery()
            ->select(['id', 'name', 'other' => 'id', 'nameish' => 'name'])
            ->from(['b' => 'authors'])
            ->where(['id ' => 1]);

        $query->select(['foo' => 'id', 'bar' => new IdentifierExpression('comment')])->unionAll($union);
        $result = $query->execute();
        $rows2 = $result->fetchAll();
        $this->assertCount(1 + self::COMMENT_COUNT + self::ARTICLE_COUNT, $rows2);
        $this->assertNotEquals($rows, $rows2);
    }

    /**
     * Tests that joins can be aliased using array keys
     *
     * @return void
     */
    public function testSelectAliasedJoins(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['title', 'name'])
            ->from('articles')
            ->join(['a' => 'authors'])
            ->orderBy(['name' => 'desc', 'articles.id' => 'asc'])
            ->execute();
        $this->assertEquals(['title' => 'First Article', 'name' => 'nate'], $result->fetch('assoc'));
        $this->assertEquals(['title' => 'Second Article', 'name' => 'nate'], $result->fetch('assoc'));
        $result->closeCursor();

        $query = $this->connection->selectQuery();
        $conditions = $query->expr()->equalFields('author_id', 'a.id');
        $result = $query
            ->select(['title', 'name'])
            ->from('articles')
            ->join(['a' => ['table' => 'authors', 'conditions' => $conditions]])
            ->orderBy(['title' => 'asc'])
            ->execute();
        $this->assertEquals(['title' => 'First Article', 'name' => 'mariano'], $result->fetch('assoc'));
        $this->assertEquals(['title' => 'Second Article', 'name' => 'larry'], $result->fetch('assoc'));
        $result->closeCursor();

        $query = $this->connection->selectQuery();
        $time = new DateTime('2007-03-18 10:45:23');
        $types = ['created' => 'datetime'];
        $result = $query
            ->select(['title', 'name' => 'c.comment'])
            ->from('articles')
            ->join(['c' => ['table' => 'comments', 'conditions' => ['created' => $time]]], $types)
            ->execute();
        $this->assertEquals(['title' => 'First Article', 'name' => 'First Comment for First Article'], $result->fetch('assoc'));
        $result->closeCursor();
    }

    /**
     * Test selecting rows using the page() method and ordering the results
     * by a calculated column.
     *
     * @return void
     */
    public function testSelectPageWithOrder(): void
    {
        $query = $this->connection->selectQuery();

        $userIdField = 'user_id';
        $articleIdField = 'article_id';
        $addExpression = $query->expr(["{$userIdField} + {$articleIdField}"]);
        $result = $query
            ->select([
                'id',
                'ids_added' => $query->expr()->add($addExpression),
            ])
            ->from('comments')
            ->orderBy(['ids_added' => 'asc'])
            ->limit(2)
            ->page(3)
            ->execute();
        $rows = $result->fetchAll('assoc');
        $this->assertCount(2, $rows);
        $this->assertEquals(
            [
                ['id' => '6', 'ids_added' => '4'],
                ['id' => '2', 'ids_added' => '5'],
            ],
            $rows,
        );
    }

    /**
     * Tests Query::whereNull()
     *
     * @return void
     */
    public function testSelectWhereNull(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['id', 'parent_id'])
            ->from('menu_link_trees')
            ->whereNull(['parent_id'])
            ->execute();
        $this->assertCount(5, $result->fetchAll());
        $result->closeCursor();

        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['id'])
            ->from('menu_link_trees')
            ->whereNull('parent_id')
            ->execute();
        $this->assertCount(5, $result->fetchAll());
        $result->closeCursor();
    }

    /**
     * Tests Query::whereNotNull()
     *
     * @return void
     */
    public function testSelectWhereNotNull(): void
    {
        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['id', 'parent_id'])
            ->from('menu_link_trees')
            ->whereNotNull(['parent_id'])
            ->execute();
        $this->assertCount(13, $result->fetchAll());
        $result->closeCursor();

        $query = $this->connection->selectQuery();
        $result = $query
            ->select(['id'])
            ->from('menu_link_trees')
            ->whereNotNull('parent_id')
            ->execute();
        $this->assertCount(13, $result->fetchAll());
        $result->closeCursor();
    }

    /**
     * Test orderAsc() and its input types.
     *
     * @return void
     */
    public function testSelectOrderAsc(): void
    {
        $query = $this->connection->selectQuery();
        $query->select(['id'])
            ->from('articles')
            ->orderByAsc('id');

        $sql = $query->sql();
        $result = $query->execute()->fetchAll('assoc');
        $expected = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];
        $this->assertEquals($expected, $result);
        $this->assertQuotedQuery(
            'SELECT <id> FROM <articles> ORDER BY <id> ASC',
            $sql,
            !$this->autoQuote,
        );

        $query = $this->connection->selectQuery();
        $query->select(['id'])
            ->from('articles')
            ->orderByAsc($query->func()->concat(['id' => 'identifier', '3']));

        $result = $query->execute()->fetchAll('assoc');
        $expected = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test orderDesc() and its input types.
     *
     * @return void
     */
    public function testSelectOrderDesc(): void
    {
        $query = $this->connection->selectQuery();
        $query->select(['id'])
            ->from('articles')
            ->orderByDesc('id');
        $sql = $query->sql();
        $result = $query->execute()->fetchAll('assoc');
        $expected = [
            ['id' => 3],
            ['id' => 2],
            ['id' => 1],
        ];
        $this->assertEquals($expected, $result);
        $this->assertQuotedQuery(
            'SELECT <id> FROM <articles> ORDER BY <id> DESC',
            $sql,
            !$this->autoQuote,
        );

        $query = $this->connection->selectQuery();
        $query->select(['id'])
            ->from('articles')
            ->orderByDesc($query->func()->concat(['id' => 'identifier', '3']));

        $result = $query->execute()->fetchAll('assoc');
        $expected = [
            ['id' => 3],
            ['id' => 2],
            ['id' => 1],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test insert() with no into()
     *
     * @return void
     */
    public function testInsertNoInto(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Could not compile insert query. No table was specified');
        $query = $this->connection->insertQuery();
        $query->insert(['title', 'body'])->sql();
    }
}
