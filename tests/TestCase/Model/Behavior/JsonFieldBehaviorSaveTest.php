<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Model\Behavior;

use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use Crustum\JsonField\Model\Behavior\JsonFieldBehavior;
use JfApp\Model\Dto\ArticleDto;
use JfApp\Model\Dto\ArticleNestedDto;
use JfApp\Model\Dto\ChapterDto;
use JfApp\Model\Entity\JsonFieldArticle;
use JfApp\Model\Entity\JsonFieldProfile;
use JfApp\Model\Table\ArticlesTable;
use JfApp\Model\Table\TagsTable;
use JfApp\Model\Table\UsersTable;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\Model\Behavior\JsonFieldBehavior
 * @uses \JfApp\Model\Table\JsonFieldTable
 */
#[Group('orm-deferred')]
class JsonFieldBehaviorSaveTest extends TestCase
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
        JsonSchemaReader::clearCache();

        TableRegistry::getTableLocator()->clear();

        parent::tearDown();
    }

    public function testAtomicUpdatePreservesOtherKeys(): void
    {
        $article = $this->table->newEntity(['title' => 'Test article']);
        $article->set('profile', ['name' => 'old', 'level' => 1]);
        $article->set('data->note', 'keep');
        $this->assertNotFalse($this->table->save($article));

        $loaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertInstanceOf(JsonFieldArticle::class, $loaded);
        $this->assertSame('old', $loaded->profile->get('name'));

        $loaded->profile->set('name', 'new');
        $this->assertNotFalse($this->table->save($loaded));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertSame('new', $reloaded->profile->get('name'));
        $this->assertSame(1, $reloaded->profile->get('level'));
        $this->assertSame('keep', $reloaded->get('data->note'));
    }

    public function testNewEntityExportsEmbedsIntoColumn(): void
    {
        $article = $this->table->newEntity(['title' => 'Test article']);
        $article->set('profile', ['name' => 'fresh']);

        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertSame('fresh', $reloaded->profile->get('name'));
    }

    public function testToDatabaseCastAppliedOnSave(): void
    {
        $created = new DateTime('2024-03-15 12:00:00');
        $article = $this->table->newEntity(['title' => 'Test article']);
        $article->set('profile', ['created' => $created]);

        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertInstanceOf(DateTime::class, $reloaded->profile->get('created'));
        $this->assertSame($created->toAtomString(), $reloaded->profile->get('created')->toAtomString());
    }

    public function testUndeclaredJsonKeysArePreserved(): void
    {
        $article = $this->table->newEntity(['title' => 'Test article']);
        $article->set('profile', ['name' => 'neo', 'nickname' => 'the-one']);
        $article->set('data->note', 'top-level-undeclared');

        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertSame('the-one', $reloaded->profile->get('nickname'));
        $this->assertSame('top-level-undeclared', $reloaded->get('data->note'));
    }

    public function testMultipleJsonColumnsRoundTrip(): void
    {
        $article = $this->table->newEntity(['title' => 'Test article']);
        $article->set('profile', ['name' => 'multi']);
        $article->set('meta', ['theme' => 'dark', 'count' => 3]);

        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertSame('multi', $reloaded->profile->get('name'));
        $this->assertSame('dark', $reloaded->get('meta')['theme']);
        $this->assertSame(3, $reloaded->get('meta')['count']);
    }

    public function testEmbedIdFieldIsGeneratedAndPersisted(): void
    {
        $article = $this->table->newEntity(['title' => 'WithChapters']);
        $article->set('chapters', [
            new JsonFieldProfile(['name' => 'One']),
            new JsonFieldProfile(['name' => 'Two']),
        ]);

        $this->assertNotFalse($this->table->save($article));
        $this->assertNotNull($article->get('id'));

        $reloaded = $this->table->get($article->get('id'));
        /** @var list<\JfApp\Model\Entity\JsonFieldProfile> $chapters */
        $chapters = $reloaded->get('chapters');
        $this->assertCount(2, $chapters);

        $uids = [];
        foreach ($chapters as $chapter) {
            $uid = $chapter->get('uid');
            $this->assertNotNull($uid);
            $uids[] = $uid;
        }

        $this->assertCount(2, array_unique($uids));
    }

    public function testEntityTraitHasUnsetAndReferenceGet(): void
    {
        $article = $this->table->newEntity(['title' => 'TraitTest']);
        $article->set('meta', ['theme' => 'dark', 'count' => 3]);

        $this->table->saveOrFail($article);

        $reloaded = $this->table->get($article->get('id'));

        $this->assertTrue($reloaded->has('meta->theme'));
        $this->assertFalse($reloaded->has('meta->missing'));

        $value = &$reloaded->get('meta->theme');
        $value = 'light';
        $this->assertSame('light', $reloaded->get('meta->theme'));

        $reloaded->unset('meta->theme');
        $this->assertFalse($reloaded->has('meta->theme'));

        $this->table->saveOrFail($reloaded);
        $after = $this->table->get($article->get('id'));
        $this->assertFalse($after->has('meta->theme'));
    }

    public function testEmbedChildDeleteRemovesElement(): void
    {
        $article = $this->table->newEntity(['title' => 'DeleteTest']);
        $article->set('chapters', [
            new JsonFieldProfile(['name' => 'a']),
            new JsonFieldProfile(['name' => 'b']),
        ]);
        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->get($article->get('id'));
        /** @var list<\JfApp\Model\Entity\JsonFieldProfile> $chapters */
        $chapters = $reloaded->get('chapters');
        $this->assertCount(2, $chapters);

        $chapters[0]->delete();
        $this->assertNotFalse($this->table->save($reloaded));

        $after = $this->table->get($article->get('id'));
        /** @var list<\JfApp\Model\Entity\JsonFieldProfile> $remaining */
        $remaining = $after->get('chapters');
        $this->assertCount(1, $remaining);
        $this->assertSame('b', $remaining[0]->get('name'));
    }

    public function testRegisterCasterAppliesOnSaveAndRead(): void
    {
        JsonSchemaReader::registerCaster(
            JsonFieldArticle::class,
            'data->secret',
            toPHP: static fn($v): ?string => $v === null ? null : substr((string)$v, 4),
            toDatabase: static fn($v): ?string => $v === null ? null : 'enc:' . $v,
        );

        $article = $this->table->newEntity(['title' => 'CasterTest']);
        $article->set('data->secret', 'hello');

        $this->table->saveOrFail($article);

        $raw = $this->table->getConnection()
            ->execute('SELECT data FROM jf_articles WHERE id = :id', ['id' => $article->get('id')])
            ->fetch('assoc');
        $this->assertStringContainsString('enc:hello', (string)json_encode($raw['data']));

        $reloaded = $this->table->get($article->get('id'));
        $this->assertSame('hello', $reloaded->get('data->secret'));
    }

    public function testMergeJsonEmbedsKeepsSiblingsOnPatch(): void
    {
        $this->table->removeBehavior(JsonFieldBehavior::class);
        $this->table->addBehavior(JsonFieldBehavior::class, ['mergeJsonEmbeds' => true]);

        $article = $this->table->newEntity(['title' => 'MergeTest']);
        $article->set('profile', ['name' => 'a', 'score' => 2.5]);

        $this->table->saveOrFail($article);

        $reloaded = $this->table->get($article->get('id'));
        $patched = $this->table->patchEntity($reloaded, ['profile' => ['name' => 'b']]);
        $this->table->saveOrFail($patched);

        $after = $this->table->get($article->get('id'));
        $this->assertSame('b', $after->get('profile')->get('name'));
        $this->assertEquals(2.5, $after->get('profile')->get('score'));
    }

    public function testAppendSaveStrategyKeepsExistingRows(): void
    {
        $article = $this->table->newEntity(['title' => 'AppendTest']);
        $article->set('notes', [
            new JsonFieldProfile(['name' => 'a', 'uid' => 'u1']),
            new JsonFieldProfile(['name' => 'b', 'uid' => 'u2']),
        ]);
        $this->table->saveOrFail($article);

        $reloaded = $this->table->get($article->get('id'));
        /** @var list<\JfApp\Model\Entity\JsonFieldProfile> $notes */
        $notes = $reloaded->get('notes');
        $this->assertCount(2, $notes);

        $notes[] = new JsonFieldProfile(['name' => 'c', 'uid' => 'u3']);
        $reloaded->set('notes', $notes);
        $this->table->saveOrFail($reloaded);

        $after = $this->table->get($article->get('id'));
        /** @var list<\JfApp\Model\Entity\JsonFieldProfile> $remaining */
        $remaining = $after->get('notes');
        $this->assertCount(3, $remaining);
        $this->assertSame(
            ['u1', 'u2', 'u3'],
            array_map(static fn(JsonFieldProfile $n): mixed => $n->get('uid'), $remaining),
        );
    }

    public function testReferenceSaveWritesForeignKeyForBelongsTo(): void
    {
        /** @var \JfApp\Model\Table\UsersTable $usersTable */
        $usersTable = TableRegistry::getTableLocator()->get('Users', ['className' => UsersTable::class]);
        $user = $usersTable->get(1);

        $article = $this->table->newEntity(['title' => 'FKTest']);
        $article->set('author', $user);

        $this->table->saveOrFail($article);

        $raw = $this->table->getConnection()
            ->execute('SELECT data FROM jf_articles WHERE id = :id', ['id' => $article->get('id')])
            ->fetch('assoc');
        $data = is_array($raw['data']) ? $raw['data'] : json_decode((string)$raw['data'], true);
        $this->assertSame(1, $data['author_id']);

        $reloaded = $this->table->get($article->get('id'));
        $this->table->loadJsonReferences([$reloaded]);
        $this->assertSame(1, $reloaded->get('author')->get('id'));
    }

    public function testReferenceSaveWritesForeignKeyListForBelongsToMany(): void
    {
        /** @var \JfApp\Model\Table\TagsTable $tagsTable */
        $tagsTable = TableRegistry::getTableLocator()->get('Tags', ['className' => TagsTable::class]);
        $tag1 = $tagsTable->get(1);
        $tag2 = $tagsTable->get(2);

        $article = $this->table->newEntity(['title' => 'FKListTest']);
        $article->set('tags', [$tag1, $tag2]);

        $this->table->saveOrFail($article);

        $raw = $this->table->getConnection()
            ->execute('SELECT data FROM jf_articles WHERE id = :id', ['id' => $article->get('id')])
            ->fetch('assoc');
        $data = is_array($raw['data']) ? $raw['data'] : json_decode((string)$raw['data'], true);
        $this->assertSame([1, 2], $data['tag_ids']);
    }

    public function testUpdateReplacesEmbeddedList(): void
    {
        $article = $this->table->newEntity(['title' => 'Replace']);
        $article->set('chapters', [
            new JsonFieldProfile(['name' => 'a', 'uid' => 'u1']),
            new JsonFieldProfile(['name' => 'b', 'uid' => 'u2']),
        ]);
        $this->table->saveOrFail($article);

        $reloaded = $this->table->get($article->get('id'));
        $reloaded->set('chapters', [new JsonFieldProfile(['name' => 'c', 'uid' => 'u3'])]);

        $this->table->saveOrFail($reloaded);

        $after = $this->table->get($article->get('id'));
        /** @var list<\JfApp\Model\Entity\JsonFieldProfile> $chapters */
        $chapters = $after->get('chapters');
        $this->assertCount(1, $chapters);
        $this->assertSame('c', $chapters[0]->get('name'));
    }

    public function testClearingEmbeddedFieldPersistsEmptyAndNull(): void
    {
        $article = $this->table->newEntity(['title' => 'Clear']);
        $article->set('chapters', [new JsonFieldProfile(['name' => 'a', 'uid' => 'u1'])]);
        $article->set('profile', ['name' => 'p']);

        $this->table->saveOrFail($article);

        $reloaded = $this->table->get($article->get('id'));
        $reloaded->set('chapters', []);
        $reloaded->set('profile', null);

        $this->table->saveOrFail($reloaded);

        $after = $this->table->get($article->get('id'));
        $this->assertSame([], $after->get('chapters'));
        $this->assertNull($after->get('profile'));
    }

    public function testSaveWithPlainArrayEmbed(): void
    {
        // An embed supplied as a plain array (the unhydrated path) must still be
        // persisted and re-hydrated on the next read.
        $article = $this->table->newEntity(['title' => 'Raw']);
        $article->set('chapters', [['name' => 'a', 'uid' => 'u1']]);

        $this->table->saveOrFail($article);

        $reloaded = $this->table->get($article->get('id'));
        /** @var list<\JfApp\Model\Entity\JsonFieldProfile> $chapters */
        $chapters = $reloaded->get('chapters');
        $this->assertCount(1, $chapters);
        $this->assertSame('a', $chapters[0]->get('name'));
    }

    public function testProjectAsLeavesEmbedsAsRawArrays(): void
    {
        // projectAs (CakePHP 5.3+) builds the DTO from the raw row array and
        // bypasses JsonFieldResultSet, so JSON embeds arrive as plain arrays.
        $article = $this->table->newEntity(['title' => 'DTO']);
        $article->set('chapters', [new JsonFieldProfile(['name' => 'a', 'uid' => 'u1'])]);

        $this->table->saveOrFail($article);

        $dtos = $this->table->find()
            ->where(['id' => $article->get('id')])
            ->projectAs(ArticleDto::class)
            ->all()
            ->toList();

        $this->assertCount(1, $dtos);
        $this->assertIsArray($dtos[0]->data['chapters']);
        $this->assertSame('a', $dtos[0]->data['chapters'][0]['name']);
    }

    public function testProjectAsNestedDtoUsesCollectionOfAttribute(): void
    {
        // The `data` embed column maps into a nested DTO; the core
        // #[CollectionOf] attribute converts the chapters array into ChapterDto
        // instances. Confirms nested/hasMany embeds work under projectAs.
        $article = $this->table->newEntity(['title' => 'Nested']);
        $article->set('chapters', [new JsonFieldProfile(['name' => 'a', 'uid' => 'u1'])]);

        $this->table->saveOrFail($article);

        $dtos = $this->table->find()
            ->where(['id' => $article->get('id')])
            ->projectAs(ArticleNestedDto::class)
            ->all()
            ->toList();

        $this->assertCount(1, $dtos);
        $this->assertInstanceOf(ChapterDto::class, $dtos[0]->data->chapters[0]);
        $this->assertSame('a', $dtos[0]->data->chapters[0]->name);
    }

    public function testAggregateJsonPathSumsNumericJsonValue(): void
    {
        // Numeric aggregates must cast the extracted JSON scalar to a SQL numeric
        // type, otherwise the dialect would string-concatenate instead of summing.
        foreach ([10, 20, 30] as $score) {
            $article = $this->table->newEntity(['title' => 'agg']);
            $article->set('data->score', $score);
            $this->table->saveOrFail($article);
        }

        $row = $this->table->aggregateJsonPath('SUM', 'data->score', 'total')
            ->where(['title' => 'agg'])
            ->first();

        $this->assertSame(60.0, (float)$row->get('total'));
    }

    public function testAggregateJsonPathCountsJsonArrayElements(): void
    {
        // COUNT over a JSON path counts array elements via the engine's
        // array-length function, not rows.
        $article = $this->table->newEntity(['title' => 'agg']);
        $article->set('chapters', [
            new JsonFieldProfile(['name' => 'a', 'uid' => 'u1']),
            new JsonFieldProfile(['name' => 'b', 'uid' => 'u2']),
        ]);
        $this->table->saveOrFail($article);

        $row = $this->table->aggregateJsonPath('COUNT', 'data->chapters', 'cnt')
            ->where(['title' => 'agg'])
            ->first();

        $this->assertSame(2, (int)$row->get('cnt'));
    }
}
