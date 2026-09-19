<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM\Association;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use JfApp\Model\Table\ArticlesTable;
use PHPUnit\Framework\Attributes\Group;

/**
 * Parity with the reference plugin's JSON-FK association tests (BelongsToTest /
 * BelongsToManyTest), using our shipped `loadJsonReferences` (contain-equivalent)
 * and `joinJsonReference` (matching / innerJoinWith / filtered contain).
 *
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldReferenceLoader
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder
 */
#[Group('orm-deferred')]
class JsonFieldReferenceAssociationTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Crustum/JsonField.Articles',
        'plugin.Crustum/JsonField.Users',
        'plugin.Crustum/JsonField.Tags',
    ];

    private ArticlesTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \JfApp\Model\Table\ArticlesTable $table */
        $table = TableRegistry::getTableLocator()->get('Articles', [
            'className' => ArticlesTable::class,
        ]);
        $this->table = $table;
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->clear();

        parent::tearDown();
    }

    public function testContainEquivalentLoadsReferences(): void
    {
        $article = $this->table->find()->first();
        $this->table->loadJsonReferences([$article]);

        $this->assertSame(2, $article->get('author')->get('id'));
        $this->assertCount(2, $article->get('tags'));
    }

    public function testMatchingByJoinedTarget(): void
    {
        $matchedQuery = $this->table->find();
        $this->table->joinJsonReference($matchedQuery, 'author');
        $matched = $matchedQuery->where(['jf_author.username' => 'bob'])->count();

        $this->assertSame(1, $matched);

        $noMatchQuery = $this->table->find();
        $this->table->joinJsonReference($noMatchQuery, 'author');
        $noMatch = $noMatchQuery->where(['jf_author.username' => 'alice'])->count();

        $this->assertSame(0, $noMatch);
    }

    public function testInnerJoinWithByTarget(): void
    {
        $query = $this->table->find();
        $this->table->joinJsonReference($query, 'author', 'INNER');
        $rows = $query
            ->where(fn($exp) => $exp->eq('jf_author.username', 'bob'))
            ->all()
            ->toList();

        $this->assertCount(1, $rows);
        $this->table->loadJsonReferences($rows);
        $this->assertSame('bob', $rows[0]->get('author')->get('username'));
    }

    public function testFilteredContainViaSourceFilter(): void
    {
        $articles = $this->table
            ->whereJsonPathIn('data->author_id', [2])
            ->all()
            ->toList();

        $this->assertCount(1, $articles);
        $this->table->loadJsonReferences($articles);
        $names = array_map(static fn($t) => $t->get('name'), $articles[0]->get('tags'));
        $this->assertContains('php', $names);
    }

    public function testLinkWritesForeignKeyIntoJson(): void
    {
        $article = $this->table->find()->first();
        $article->set('data->author_id', 3);
        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->table->loadJsonReferences([$reloaded]);
        $this->assertSame(3, $reloaded->get('author')->get('id'));
        $this->assertSame('carol', $reloaded->get('author')->get('username'));
    }

    public function testUnlinkClearsForeignKey(): void
    {
        $article = $this->table->find()->first();
        $article->set('data->author_id', null);
        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->table->loadJsonReferences([$reloaded]);
        $this->assertNull($reloaded->get('author'));
    }

    public function testHasOneReferenceScenario(): void
    {
        $article = $this->table->find()->first();
        $this->table->loadJsonReferences([$article]);

        $author = $article->get('author');
        $this->assertNotNull($author);
        $this->assertSame(2, $author->get('id'));
        $this->assertIsArray($article->get('tags'));
    }
}
