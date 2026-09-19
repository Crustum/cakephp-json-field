<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM\Association;

use Cake\Database\Driver\Sqlite;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder;
use Crustum\JsonField\ORM\Association\JsonFieldBelongsTo;
use Crustum\JsonField\ORM\Association\JsonFieldBelongsToMany;
use Crustum\JsonField\ORM\Association\Loader\JsonFieldReferenceLoader;
use JfApp\Model\Entity\JsonFieldArticle;
use JfApp\Model\Entity\JsonFieldProfile;
use JfApp\Model\Table\ArticlesTable;
use JfApp\Model\Table\TagsTable;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldReference
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldBelongsTo
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldBelongsToMany
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder
 * @uses \Crustum\JsonField\ORM\Association\Loader\JsonFieldReferenceLoader
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait
 */
#[Group('orm-deferred')]
class JsonFieldReferenceTest extends TestCase
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

    public function testBuilderProducesBelongsToAndBelongsToMany(): void
    {
        $references = (new JsonFieldAssociationBuilder())->buildReferences(JsonFieldArticle::class);

        $this->assertArrayHasKey('author', $references);
        $this->assertArrayHasKey('tags', $references);
        $this->assertInstanceOf(JsonFieldBelongsTo::class, $references['author']);
        $this->assertInstanceOf(JsonFieldBelongsToMany::class, $references['tags']);
        $this->assertSame('data', $references['author']->getForeignKeyPath()->column);
        $this->assertSame(['author_id'], $references['author']->getForeignKeyPath()->nodes);
        $this->assertSame(TagsTable::class, $references['tags']->getTargetClass());
    }

    public function testLoaderResolvesBelongsTo(): void
    {
        $article = $this->table->find()->first();
        $this->assertInstanceOf(JsonFieldArticle::class, $article);

        $this->table->loadJsonReferences([$article]);

        $author = $article->get('author');
        $this->assertNotNull($author);
        $this->assertSame(2, $author->get('id'));
        $this->assertSame('bob', $author->get('username'));
    }

    public function testLoaderResolvesBelongsToMany(): void
    {
        $article = $this->table->find()->first();
        $this->assertInstanceOf(JsonFieldArticle::class, $article);

        $this->table->loadJsonReferences([$article]);

        $tags = $article->get('tags');
        $this->assertIsArray($tags);
        $this->assertCount(2, $tags);
        $this->assertSame([1, 3], array_map(static fn($tag) => $tag->get('id'), $tags));
        $this->assertSame(['php', 'json'], array_map(static fn($tag) => $tag->get('name'), $tags));
    }

    public function testMissingForeignKeyResolvesToNull(): void
    {
        $article = $this->table->newEntity([
            'title' => 'No keys',
            'data' => ['note' => 'x'],
        ]);
        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->table->loadJsonReferences([$reloaded]);

        $this->assertNull($reloaded->get('author'));
        $this->assertSame([], $reloaded->get('tags'));
    }

    public function testJoinEmitsEngineExtractExpression(): void
    {
        $query = $this->table->find();
        $this->table->joinJsonReference($query, 'author');

        $sql = $query->sql();

        $this->assertStringContainsString('jf_author', $sql);
        $this->assertThat(
            $sql,
            self::logicalOr(
                self::stringContains('JSON_EXTRACT'),
                self::stringContains('json_extract'),
                self::stringContains('jsonb_extract_path'),
            ),
        );
    }

    public function testLoaderViaTraitHelper(): void
    {
        $references = $this->table->jsonFieldReferences();
        $this->assertArrayHasKey('author', $references);
        $this->assertArrayHasKey('tags', $references);

        $article = $this->table->find()->first();
        (new JsonFieldReferenceLoader())->load([$article], $references['author']);
        $this->assertSame(2, $article->get('author')->get('id'));
    }

    public function testEagerLoadJsonReferencesPopulatesFromHydratedJson(): void
    {
        $query = $this->table->find();
        $this->table->eagerLoadJsonReferences($query, ['author', 'tags']);

        $articles = $query->all()->toList();
        $this->assertNotEmpty($articles);

        $article = $articles[0];
        $this->assertInstanceOf(JsonFieldArticle::class, $article);
        $this->assertSame(2, $article->get('author')->get('id'));
        $this->assertSame('bob', $article->get('author')->get('username'));

        $tags = $article->get('tags');
        $this->assertIsArray($tags);
        $this->assertSame([1, 3], array_map(static fn($tag) => $tag->get('id'), $tags));
    }

    public function testEagerLoadJsonReferencesHandlesMissingKey(): void
    {
        $article = $this->table->newEntity([
            'title' => 'No keys',
            'data' => ['note' => 'x'],
        ]);
        $this->assertNotFalse($this->table->save($article));

        $query = $this->table->find()->where(['id' => $article->id]);
        $this->table->eagerLoadJsonReferences($query);

        $reloaded = $query->first();
        $this->assertNull($reloaded->get('author'));
        $this->assertSame([], $reloaded->get('tags'));
    }

    public function testWhereJsonPathElemMatchFindsArrayElement(): void
    {
        $driver = $this->table->getConnection()->getDriver();
        if ($driver instanceof Sqlite) {
            $this->markTestSkipped('elemMatch uses JSON containment, unsupported on SQLite');
        }

        $article = $this->table->newEntity(['title' => 'ElemMatch']);
        $article->set('chapters', [
            new JsonFieldProfile(['name' => 'a', 'uid' => 'u1']),
            new JsonFieldProfile(['name' => 'b', 'uid' => 'u2']),
        ]);
        $this->table->saveOrFail($article);

        $found = $this->table->whereJsonPathElemMatch('data->chapters', ['name' => 'b'])->all()->toList();
        $this->assertCount(1, $found);
        $this->assertSame($article->get('id'), $found[0]->get('id'));
    }
}
