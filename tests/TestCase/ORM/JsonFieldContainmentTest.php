<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use JfApp\Model\Table\ArticlesTable;

/**
 * Verifies JSON-containment (`@>`) support across engines.
 *
 * PostgreSQL uses the native `jsonb @> jsonb` operator; MySQL uses
 * `JSON_CONTAINS`; SQLite has no native containment operator and is skipped.
 *
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait::whereJsonPathContains()
 */
class JsonFieldContainmentTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Crustum/JsonField.Articles',
    ];

    protected function setUp(): void
    {
        if (ConnectionManager::get('test')->getDriver() instanceof Sqlite) {
            $this->markTestSkipped('SQLite has no native @> containment operator');
        }

        parent::setUp();
    }

    private function table(): ArticlesTable
    {
        /** @var \JfApp\Model\Table\ArticlesTable $table */
        $table = TableRegistry::getTableLocator()->get('JfArticles', [
            'className' => ArticlesTable::class,
            'table' => 'jf_articles',
        ]);

        return $table;
    }

    public function testWhereJsonPathContainsCompilesToNativeContainment(): void
    {
        $driver = $this->table()->getConnection()->getDriver();

        $sql = $this->table()->whereJsonPathContains('data->tag_ids', [1, 3])->sql();

        if ($driver instanceof Postgres) {
            $this->assertStringContainsString('jsonb_extract_path', $sql);
            $this->assertStringContainsString('@>', $sql);
        } elseif ($driver instanceof Mysql) {
            $this->assertStringContainsString('JSON_CONTAINS', $sql);
        }
    }

    public function testWhereJsonPathContainsExecutes(): void
    {
        $table = $this->table();

        // data->tag_ids = [1, 3]
        $this->assertSame(1, $table->whereJsonPathContains('data->tag_ids', [1, 3])->count());
        $this->assertSame(1, $table->whereJsonPathContains('data->tag_ids', [1])->count());
        $this->assertSame(0, $table->whereJsonPathContains('data->tag_ids', [9])->count());

        // Negated containment: NOT (tag_ids @> [9]).
        $this->assertSame(1, $table->whereJsonPathContains('data->tag_ids', [9], true)->count());

        // meta->seo.keywords = ['a', 'b']
        $this->assertSame(1, $table->whereJsonPathContains('meta->seo.keywords', ['a', 'b'])->count());
        $this->assertSame(1, $table->whereJsonPathContains('meta->seo.keywords', ['a'])->count());
        $this->assertSame(0, $table->whereJsonPathContains('meta->seo.keywords', ['z'])->count());
    }
}
