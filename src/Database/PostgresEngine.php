<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

use Cake\Database\Expression\FunctionExpression;
use Cake\Database\ExpressionInterface;
use Crustum\JsonField\Path\JsonPath;

/**
 * PostgreSQL implementation of the JSON field engine adapter.
 *
 * Uses `jsonb_set` / `CAST(value AS JSONB)` for atomic writes. READ-path
 * queries are handled by {@see \Crustum\JsonField\Database\Driver\JsonFieldPostgresDriver}.
 */
class PostgresEngine extends AbstractJsonFieldEngine
{
    /**
     * Builds a `jsonb_set(column, '{path}', CAST(value AS JSONB), true)` expression.
     *
     * @param \Crustum\JsonField\Path\JsonPath $path Path addressing the value
     * @param mixed $value The value to write at the path
     * @return \Cake\Database\ExpressionInterface The atomic setter expression.
     */
    public function set(JsonPath $path, mixed $value): ExpressionInterface
    {
        return new FunctionExpression('jsonb_set', [
            $path->column => 'identifier',
            $path->toPathArray(),
            $this->cast($value),
            'true' => 'literal',
        ]);
    }

    /**
     * Builds a `CAST(value AS JSONB)` expression.
     *
     * @param mixed $value The value to cast
     * @return \Cake\Database\ExpressionInterface The JSON literal expression.
     */
    public function cast(mixed $value): ExpressionInterface
    {
        return (new FunctionExpression('CAST', [json_encode($value)]))
            ->setConjunction(' AS')
            ->add(['JSONB' => 'literal']);
    }
}
