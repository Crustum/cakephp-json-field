<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\Database\Driver;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Database\JsonFieldEngineInterface;
use Crustum\JsonField\Database\JsonFieldEngineResolver;
use JfApp\Model\Table\JsonFieldReadTable;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait
 * @uses \Crustum\JsonField\Database\JsonFieldEngineResolver
 */
#[Group('orm-deferred')]
class JsonFieldAwareTraitTest extends TestCase
{
    private function table(): JsonFieldReadTable
    {
        $connection = ConnectionManager::get('test');

        return new JsonFieldReadTable([
            'table' => 'json_field_read',
            'connection' => $connection,
            'schema' => ['id' => ['type' => 'integer'], 'data' => ['type' => 'json']],
        ]);
    }

    /**
     * Returns the native extract token expected for the active driver.
     *
     * @return string
     */
    private function expectedExtractToken(): string
    {
        $driver = ConnectionManager::get('test')->getDriver();

        return match (true) {
            $driver instanceof Postgres => 'jsonb_extract_path',
            $driver instanceof Mysql => 'JSON_EXTRACT',
            $driver instanceof Sqlite => 'JSON_EXTRACT',
            default => 'JSON_VALUE',
        };
    }

    /**
     * Returns the native JSON-typed extract token expected for `whereJsonPath`.
     *
     * `whereJsonPath` compares against JSON literals, so it uses the JSON-typed
     * extract (`jsonb_extract_path`) rather than the scalar form.
     *
     * @return string
     */
    private function expectedJsonExtractToken(): string
    {
        $driver = ConnectionManager::get('test')->getDriver();

        return match (true) {
            $driver instanceof Postgres => 'jsonb_extract_path',
            $driver instanceof Mysql => 'JSON_EXTRACT',
            $driver instanceof Sqlite => 'JSON_EXTRACT',
            default => 'JSON_VALUE',
        };
    }

    /**
     * Returns the native JSON array-length function expected for the active driver.
     *
     * @return string
     */
    private function expectedArrayLengthToken(): string
    {
        $driver = ConnectionManager::get('test')->getDriver();

        return match (true) {
            $driver instanceof Postgres => 'jsonb_array_length',
            $driver instanceof Mysql => 'JSON_LENGTH',
            $driver instanceof Sqlite => 'json_array_length',
            default => 'JSON_LENGTH',
        };
    }

    public function testResolverPicksEngineForDriver(): void
    {
        $driver = ConnectionManager::get('test')->getDriver();
        $this->assertInstanceOf(Driver::class, $driver);

        $engine = (new JsonFieldEngineResolver())->engine($driver);
        $this->assertInstanceOf(JsonFieldEngineInterface::class, $engine);
    }

    public function testExtractJsonPathReturnsExpression(): void
    {
        $table = $this->table();
        $expression = $table->extractJsonPath('data->profile.created');

        $this->assertSame('data->profile.created', $expression);
    }

    public function testWhereJsonPathCompilesToNativeExtract(): void
    {
        $table = $this->table();
        $query = $table->whereJsonPath('data->profile.created', '2024-01-01');

        $sql = $query->sql();
        $this->assertStringContainsString($this->expectedJsonExtractToken(), $sql);
        $this->assertStringContainsString('data', $sql);
    }

    public function testWhereJsonPathSupportsOperators(): void
    {
        $table = $this->table();
        $sql = $table->whereJsonPath('data->profile.created', '2024-01-01', '>')->sql();

        $this->assertStringContainsString($this->expectedJsonExtractToken(), $sql);
        $this->assertStringContainsString('>', $sql);
    }

    public function testWhereJsonPathInCompilesToNativeExtract(): void
    {
        $table = $this->table();
        $sql = $table->whereJsonPathIn('data->profile.created', ['2024-01-01', '2024-02-01'])->sql();

        $this->assertStringContainsString($this->expectedJsonExtractToken(), $sql);
        $this->assertStringContainsString('IN', $sql);
    }

    public function testOrderByJsonPathCompilesToNativeExtract(): void
    {
        $table = $this->table();
        $sql = $table->orderByJsonPath('data->profile.created', 'DESC')->sql();

        $this->assertStringContainsString($this->expectedExtractToken(), $sql);
        $this->assertStringContainsString('DESC', $sql);
    }

    public function testHavingJsonPathCompilesToNativeExtract(): void
    {
        $table = $this->table();
        $sql = $table->havingJsonPath('data->profile.score', 0, '>')->sql();

        $this->assertStringContainsString('HAVING', $sql);
        $this->assertStringContainsString($this->expectedJsonExtractToken(), $sql);
        $this->assertStringContainsString('>', $sql);
    }

    public function testAggregateJsonPathSumCompilesWithNumericCast(): void
    {
        $table = $this->table();
        $sql = $table->aggregateJsonPath('SUM', 'data->profile.score')->sql();

        $this->assertStringContainsString('SUM(', $sql);
        $this->assertStringContainsString($this->expectedJsonExtractToken(), $sql);
        $this->assertStringContainsString('CAST', $sql);
    }

    public function testAggregateJsonPathCountCompilesToArrayLength(): void
    {
        $table = $this->table();
        $sql = $table->aggregateJsonPath('COUNT', 'data->profile.tags')->sql();

        // The COUNT(...) over a path is rewritten to the engine's array-length
        // function; the literal `COUNT(` only survives as the (irrelevant) alias.
        $this->assertStringContainsString($this->expectedArrayLengthToken(), $sql);
    }

    public function testClauseVisitorRewritesPathConditions(): void
    {
        $table = $this->table();
        $query = $table->find()->where(['data->profile.created' => '2024-01-01']);

        $sql = $query->sql();
        $this->assertStringContainsString($this->expectedExtractToken(), $sql);
        $this->assertStringContainsString('=', $sql);
    }

    public function testFinderIsJsonFieldAware(): void
    {
        $table = $this->table();
        $query = $table->find();

        $this->assertInstanceOf(SelectQuery::class, $query);
    }

    public function testVisitorLeavesNonPathConditionsUntouched(): void
    {
        $table = $this->table();
        $query = $table->find()->where(['id' => 1]);

        $sql = $query->sql();
        $this->assertStringNotContainsString($this->expectedExtractToken(), $sql);
        $this->assertStringContainsString('id', $sql);
    }
}
