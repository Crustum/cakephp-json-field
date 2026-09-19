<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

use Cake\Database\Expression\FunctionExpression;
use Cake\Database\ExpressionInterface;
use Crustum\JsonField\Path\JsonPath;

/**
 * SQLite implementation of the JSON field engine adapter.
 *
 * Uses `json_set` / `json` for atomic writes. READ-path queries are
 * handled by {@see \Crustum\JsonField\Database\Driver\JsonFieldSqliteDriver}.
 */
class SqliteEngine extends AbstractJsonFieldEngine
{
    /**
     * Builds a `json_set(column, '$.path', json(value))` expression.
     *
     * @param \Crustum\JsonField\Path\JsonPath $path Path addressing the value
     * @param mixed $value The value to write at the path
     * @return \Cake\Database\ExpressionInterface The atomic setter expression.
     */
    public function set(JsonPath $path, mixed $value): ExpressionInterface
    {
        return new FunctionExpression('json_set', [
            $path->column => 'identifier',
            $path->toJsonPath(),
            $this->cast($value),
        ]);
    }

    /**
     * Builds a `json(value)` expression.
     *
     * @param mixed $value The value to cast
     * @return \Cake\Database\ExpressionInterface The JSON literal expression.
     */
    public function cast(mixed $value): ExpressionInterface
    {
        return new FunctionExpression('json', [json_encode($value)]);
    }
}
