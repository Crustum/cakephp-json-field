<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use JfApp\Model\Table\ArticlesTable;
use PHPUnit\Framework\Attributes\Group;

/**
 * Parity with the reference plugin's full WHERE operator matrix (BasicFilterTest /
 * TableFilterTest), scoped to the operators our engine-neutral design supports.
 *
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait
 */
#[Group('orm-deferred')]
class JsonFieldAwareTraitParityTest extends TestCase
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

    public function testEqualityAndInequality(): void
    {
        $this->assertSame(1, $this->table->whereJsonPath('data->author_id', 2)->count());
        $this->assertSame(0, $this->table->whereJsonPath('data->author_id', 99)->count());
        $this->assertSame(1, $this->table->whereJsonPath('data->author_id', 99, '!=')->count());
        $this->assertSame(1, $this->table->whereJsonPath('data->author_id', 2, '=')->count());
    }

    public function testGreaterAndLessThan(): void
    {
        $this->assertSame(1, $this->table->whereJsonPath('data->author_id', 1, '>')->count());
        $this->assertSame(1, $this->table->whereJsonPath('data->score', 9.0, '>=')->count());
        $this->assertSame(1, $this->table->whereJsonPath('data->score', 10.0, '<')->count());
        $this->assertSame(1, $this->table->whereJsonPath('data->score', 9.5, '<=')->count());
    }

    public function testInAndNotIn(): void
    {
        $this->assertSame(1, $this->table->whereJsonPathIn('data->author_id', [2])->count());
        $this->assertSame(0, $this->table->whereJsonPathIn('data->author_id', [1, 3])->count());
        $this->assertSame(1, $this->table->whereJsonPathNotIn('data->author_id', [1, 3])->count());
    }

    public function testLikeAndNotLike(): void
    {
        $this->assertSame(1, $this->table->whereJsonPathLike('data->note', 'ref%')->count());
        $this->assertSame(0, $this->table->whereJsonPathLike('data->note', 'zzz%')->count());
        $this->assertSame(1, $this->table->whereJsonPathLike('data->note', 'zzz%', true)->count());
    }

    public function testIsNullAndIsNotNull(): void
    {
        $this->assertSame(1, $this->table->whereJsonPathNull('data->missing')->count());
        $this->assertSame(0, $this->table->whereJsonPathNotNull('data->missing')->count());
        $this->assertSame(1, $this->table->whereJsonPathNotNull('data->note')->count());
        $this->assertSame(0, $this->table->whereJsonPathNull('data->note')->count());
    }

    public function testBetweenOnIntegerAndDate(): void
    {
        $this->assertSame(1, $this->table->whereJsonPathBetween('data->author_id', 1, 3)->count());
        $this->assertSame(0, $this->table->whereJsonPathBetween('data->author_id', 10, 20)->count());
        $this->assertSame(1, $this->table->whereJsonPathBetween('data->published', '2024-01-01', '2024-12-31')->count());
    }

    public function testMultipleConditionsCompose(): void
    {
        $count = $this->table
            ->find()
            ->where(fn($exp) => $exp
                ->eq($this->table->extractJsonPath('data->author_id'), 2, 'integer')
                ->like($this->table->extractJsonPath('data->note'), 'ref%'))
            ->count();

        $this->assertSame(1, $count);
    }

    public function testOrderedByJsonPathAscAndDesc(): void
    {
        $asc = $this->table->orderByJsonPath('data->author_id', 'ASC')->all()->toList();
        $desc = $this->table->orderByJsonPath('data->author_id', 'DESC')->all()->toList();

        $this->assertCount(1, $asc);
        $this->assertCount(1, $desc);
        $this->assertSame(2, $asc[0]->get('data->author_id'));
        $this->assertSame(2, $desc[0]->get('data->author_id'));
    }

    public function testMultipleOrderByJsonPath(): void
    {
        $rows = $this->table
            ->find()
            ->orderByDesc($this->table->extractJsonPath('data->score'))
            ->orderByAsc($this->table->extractJsonPath('data->author_id'))
            ->all()
            ->toList();

        $this->assertNotEmpty($rows);
    }
}
