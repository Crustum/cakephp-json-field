<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

use Cake\Database\ExpressionInterface;
use Crustum\JsonField\Path\JsonPath;

/**
 * Engine adapter that emits native JSON path SQL for save operations.
 *
 * READ-path queries (WHERE, ORDER BY, GROUP BY, HAVING) are handled by the
 * dialect trait ({@see \Crustum\JsonField\Database\Dialect\JsonFieldDialectTrait}),
 * which intercepts query compilation at the driver level. This interface
 * covers only the SAVE-path primitives: atomic column writes and value casting.
 *
 * Each supported engine (PostgreSQL, MySQL, SQLite) implements this interface
 * with two small operations, so the rest of the plugin stays engine-neutral
 * and testable. Adding a new engine means adding one adapter class.
 */
interface JsonFieldEngineInterface
{
    /**
     * Builds an expression that atomically sets the value at a path in a JSON column.
     *
     * @param \Crustum\JsonField\Path\JsonPath $path Path addressing the value
     * @param mixed $value The value to write at the path
     * @return \Cake\Database\ExpressionInterface The engine-native atomic setter
     */
    public function set(JsonPath $path, mixed $value): ExpressionInterface;

    /**
     * Builds an expression that casts a PHP value into a JSON literal for this engine.
     *
     * @param mixed $value The value to cast
     * @return \Cake\Database\ExpressionInterface The engine-native JSON literal expression
     */
    public function cast(mixed $value): ExpressionInterface;
}
