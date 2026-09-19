<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use JfApp\Model\Table\ArticlesTable;
use PHPUnit\Framework\Attributes\Group;

/**
 * Parity with the reference plugin's WRITE coverage (DatFieldBehaviorTest):
 * whole-column + atomic writes exercised through real SAVE scenarios.
 *
 * Covers scientific-notation floats, nested array/object/array-of-objects
 * round-trips, and undeclared-key preservation at scale. The reference
 * plugin's custom `toDatabase`/`toPHP` callbacks and `patchEntity` merge
 * versus replace map to `NEEDS FEATURE` items and are tracked in the
 * coverage-parity plan rather than asserted here.
 *
 * @uses \Crustum\JsonField\Model\Behavior\JsonFieldBehavior
 */
#[Group('orm-deferred')]
class JsonFieldBehaviorSaveParityTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = ['plugin.Crustum/JsonField.Articles'];

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

    public function testScientificNotationFloatRoundTrips(): void
    {
        $article = $this->table->newEntity(['title' => 'Sci article']);
        $article->set('profile', ['name' => 'sci', 'score' => 1.0e20]);

        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertSame(1.0e20, (float)$reloaded->profile->get('score'));
    }

    public function testNestedArrayAndObjectRoundTrips(): void
    {
        $article = $this->table->newEntity(['title' => 'Nested article']);
        $article->set('profile', [
            'name' => 'nested',
            'config' => ['timeout' => 30, 'flags' => ['x', 'y']],
            'items' => [['id' => 1, 'label' => 'a'], ['id' => 2, 'label' => 'b']],
        ]);

        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertEquals(['timeout' => 30, 'flags' => ['x', 'y']], $reloaded->profile->get('config'));
        $this->assertSame(
            [['id' => 1, 'label' => 'a'], ['id' => 2, 'label' => 'b']],
            $reloaded->profile->get('items'),
        );
    }

    public function testArrayOfObjectsRoundTripsThroughMetaColumn(): void
    {
        $article = $this->table->newEntity(['title' => 'Meta article']);
        $article->set('meta', [
            'seo' => [
                'items' => [['rank' => 1, 'term' => 'cake'], ['rank' => 2, 'term' => 'json']],
            ],
        ]);

        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertSame(
            [['rank' => 1, 'term' => 'cake'], ['rank' => 2, 'term' => 'json']],
            $reloaded->get('meta')['seo']['items'],
        );
    }

    public function testUndeclaredKeysPreservedAtScale(): void
    {
        $article = $this->table->newEntity(['title' => 'Scale article']);
        $profile = ['name' => 'scale'];
        for ($i = 0; $i < 25; $i++) {
            $profile['field_' . $i] = $i;
        }

        $article->set('profile', $profile);
        $this->assertNotFalse($this->table->save($article));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        for ($i = 0; $i < 25; $i++) {
            $this->assertSame($i, $reloaded->profile->get('field_' . $i));
        }

        $this->assertSame('scale', $reloaded->profile->get('name'));
    }

    public function testAtomicUpdateOnNestedScalarPreservesSiblings(): void
    {
        $article = $this->table->newEntity(['title' => 'Sibling article']);
        $article->set('profile', [
            'name' => 'keep-me',
            'level' => 1,
            'deep' => ['a' => 1, 'b' => 2],
        ]);
        $this->assertNotFalse($this->table->save($article));

        $loaded = $this->table->find()->where(['id' => $article->id])->first();
        $loaded->profile->set('level', 99);
        $loaded->profile->set('deep', ['a' => 10, 'b' => 20]);
        $this->assertNotFalse($this->table->save($loaded));

        $reloaded = $this->table->find()->where(['id' => $article->id])->first();
        $this->assertSame('keep-me', $reloaded->profile->get('name'));
        $this->assertSame(99, $reloaded->profile->get('level'));
        $this->assertSame(['a' => 10, 'b' => 20], $reloaded->profile->get('deep'));
    }
}
