<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database;

use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\ValueBinder;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Database\JsonFieldEngineInterface;
use Crustum\JsonField\Database\MysqlEngine;
use Crustum\JsonField\Database\PostgresEngine;
use Crustum\JsonField\Database\SqliteEngine;
use Crustum\JsonField\Path\JsonPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\Database\PostgresEngine
 * @uses \Crustum\JsonField\Database\MysqlEngine
 * @uses \Crustum\JsonField\Database\SqliteEngine
 */
#[Group('orm-deferred')]
class JsonFieldEngineTest extends TestCase
{
    /**
     * Collects the names of all FunctionExpression nodes within an expression tree.
     *
     * @param array<string> $names Accumulated function names
     * @return array<string> The collected names.
     */
    private function collectFunctionNames(ExpressionInterface $expr, array &$names = []): array
    {
        if ($expr instanceof FunctionExpression) {
            $names[] = $expr->getName();
        }

        if ($expr instanceof QueryExpression) {
            $expr->iterateParts(function ($part) use (&$names) {
                if ($part instanceof ExpressionInterface) {
                    $this->collectFunctionNames($part, $names);
                }

                return $part;
            });
        }

        return $names;
    }

    /**
     * @return array<string, array{0: \Crustum\JsonField\Database\JsonFieldEngineInterface, 1: string, 2: string, 3: string}>
     */
    public static function engineProvider(): array
    {
        return [
            'postgres' => [new PostgresEngine(), 'jsonb_extract_path', 'jsonb_set', 'CAST'],
            'mysql' => [new MysqlEngine(), 'JSON_EXTRACT', 'JSON_SET', 'CAST'],
            'sqlite' => [new SqliteEngine(), 'JSON_VALUE', 'json_set', 'json'],
        ];
    }

    /**
     * @param \Crustum\JsonField\Database\JsonFieldEngineInterface $engine The engine under test
     * @param string $extractName Expected extract function name
     * @param string $setName Expected set function name
     * @param string $castName Expected cast function name
     */
    #[DataProvider('engineProvider')]
    public function testSetWrapsCastInAtomicSetter(
        JsonFieldEngineInterface $engine,
        string $extractName,
        string $setName,
        string $castName,
    ): void {
        $path = new JsonPath('data', ['deep', 'nested', 'key']);
        $expr = $engine->set($path, 'value');

        $this->assertInstanceOf(FunctionExpression::class, $expr);
        $this->assertSame($setName, $expr->getName());

        $names = $this->collectFunctionNames($expr);
        $this->assertContains($castName, $names);
    }

    /**
     * @param \Crustum\JsonField\Database\JsonFieldEngineInterface $engine The engine under test
     * @param string $extractName Expected extract function name
     * @param string $setName Expected set function name
     * @param string $castName Expected cast function name
     */
    #[DataProvider('engineProvider')]
    public function testCastReturnsEngineJsonLiteral(
        JsonFieldEngineInterface $engine,
        string $extractName,
        string $setName,
        string $castName,
    ): void {
        $expr = $engine->cast('value');

        $this->assertInstanceOf(FunctionExpression::class, $expr);
        $this->assertSame($castName, $expr->getName());
    }

    public function testSqliteEngineCompilesNativeFunctions(): void
    {
        $this->skipIf(
            !(ConnectionManager::get('test')->getDriver() instanceof Sqlite),
            'SQLite-specific native function compilation is only asserted on SQLite.',
        );

        $engine = new SqliteEngine();
        $path = new JsonPath('data', ['deep', 'nested', 'key']);
        $connection = ConnectionManager::get('test');

        $castSql = $connection
            ->selectQuery()
            ->select(['v' => $engine->cast('value')])
            ->sql(new ValueBinder());
        $this->assertStringContainsString('json(', $castSql);

        $setSql = $connection
            ->selectQuery()
            ->select(['v' => $engine->set($path, 'value')])
            ->sql(new ValueBinder());
        $this->assertStringContainsString('json_set(', $setSql);
        $this->assertStringContainsString('json(', $setSql);
    }

    public function testEnginesAreDistinctInstances(): void
    {
        $this->assertInstanceOf(JsonFieldEngineInterface::class, new PostgresEngine());
        $this->assertInstanceOf(JsonFieldEngineInterface::class, new MysqlEngine());
        $this->assertInstanceOf(JsonFieldEngineInterface::class, new SqliteEngine());
    }
}
