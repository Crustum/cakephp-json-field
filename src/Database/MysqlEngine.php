<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

use Cake\Database\Expression\FunctionExpression;
use Cake\Database\ExpressionInterface;
use Crustum\JsonField\Path\JsonPath;

/**
 * MySQL implementation of the JSON field engine adapter.
 *
 * Uses `JSON_SET` / `CAST(value AS JSON)` for atomic writes. READ-path
 * queries are handled by {@see \Crustum\JsonField\Database\Driver\JsonFieldMysqlDriver}.
 */
class MysqlEngine extends AbstractJsonFieldEngine
{
    /**
     * Builds a `JSON_SET(column, '$.path', CAST(value AS JSON))` expression.
     *
     * @param \Crustum\JsonField\Path\JsonPath $path Path addressing the value
     * @param mixed $value The value to write at the path
     * @return \Cake\Database\ExpressionInterface The atomic setter expression.
     */
    public function set(JsonPath $path, mixed $value): ExpressionInterface
    {
        return new FunctionExpression('JSON_SET', [
            $path->column => 'identifier',
            $path->toJsonPath(),
            $this->cast($value),
        ]);
    }

    /**
     * Builds a `CAST(value AS JSON)` expression.
     *
     * @param mixed $value The value to cast
     * @return \Cake\Database\ExpressionInterface The JSON literal expression.
     */
    public function cast(mixed $value): ExpressionInterface
    {
        return (new FunctionExpression('CAST', [json_encode($value)]))
            ->setConjunction(' AS')
            ->add(['JSON' => 'literal']);
    }
}
