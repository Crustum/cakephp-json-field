<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Driver;

use Cake\Core\Exception\CakeException;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\ValueBinder;
use Closure;
use Crustum\JsonField\Database\Dialect\JsonFieldDialectTrait;
use Crustum\JsonField\Path\JsonPath;

/**
 * SQLite driver with JSON-field dialect support.
 *
 * Intercepts query compilation to rewrite JSON-path expressions into
 * engine-native SQL. SQLite's `JSON_VALUE` is rewritten by CakePHP core
 * to `json_extract`, which returns TEXT for scalar values.
 */
class JsonFieldSqliteDriver extends Sqlite
{
    use JsonFieldDialectTrait;

    /**
     * Translates a JSON path to a scalar-comparable extract expression.
     *
     * Uses `JSON_VALUE` which CakePHP core rewrites to `json_extract`.
     * SQLite's `json_extract` returns TEXT for scalar values, making it
     * suitable for comparisons, ordering, and LIKE operations.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The extract expression.
     */
    protected function translateJsonPath(string $column, JsonPath $path, bool $unquote = true): ExpressionInterface
    {
        return new FunctionExpression('JSON_VALUE', [
            $column => 'identifier',
            "'" . $path->toJsonPath() . "'" => 'literal',
        ]);
    }

    /**
     * Translates a JSON path to a JSON-typed extract expression.
     *
     * SQLite's `json_extract` already returns JSON text, so this is
     * identical to the scalar form.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The JSON-typed extract expression.
     */
    protected function translateJsonPathTyped(string $column, JsonPath $path): ExpressionInterface
    {
        return new FunctionExpression('JSON_VALUE', [
            $column => 'identifier',
            "'" . $path->toJsonPath() . "'" => 'literal',
        ]);
    }

    /**
     * Casts a PHP value to a JSON literal using SQLite's `json()` function.
     *
     * @param mixed $value The PHP value to cast.
     * @return \Cake\Database\ExpressionInterface The JSON literal expression.
     */
    protected function castToJson(mixed $value): ExpressionInterface
    {
        if ($value instanceof ExpressionInterface) {
            // For a JOIN against another column, SQLite compares the extracted
            // JSON scalar directly to the referenced column (`json_extract(...) = fk.id`);
            // wrapping the FK in `json()` makes the comparison fail. Return it as-is.
            return $value;
        }

        return new FunctionExpression('json', [json_encode($value)]);
    }

    /**
     * SQLite has no native JSON-containment operator (`@>`); containment must be
     * emulated via a registered `json_contains` function, which is out of scope.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param \Cake\Database\ExpressionInterface $valueExpr The JSON-cast containment value.
     * @param bool $negate Whether to negate the containment.
     * @return \Cake\Database\ExpressionInterface Never returned.
     * @throws \Cake\Core\Exception\CakeException Always, as SQLite lacks `@>`.
     */
    protected function translateJsonContains(
        string $column,
        JsonPath $path,
        ExpressionInterface $valueExpr,
        bool $negate,
    ): ExpressionInterface {
        throw new CakeException('JSON containment operator @> is not supported on SQLite.');
    }

    /**
     * SQLite's json_extract returns native scalars — plain value comparison works.
     *
     * @return bool False — no JSON-literal comparison needed.
     */
    protected function comparesWithJsonLiteral(): bool
    {
        return false;
    }

    /**
     * Casts a JSON-path extract to a SQL numeric type for numeric ordering.
     *
     * @param \Cake\Database\ExpressionInterface $field The extract expression.
     * @return \Cake\Database\ExpressionInterface The numeric-cast expression.
     */
    protected function castJsonExtractToNumeric(ExpressionInterface $field): ExpressionInterface
    {
        return new FunctionExpression('CAST', [$field])
            ->setConjunction(' AS')
            ->add(['REAL' => 'literal']);
    }

    /**
     * Renders a PHP scalar as a native value for a JSON-path comparison.
     *
     * SQLite's `json_extract` returns native scalars (booleans as `1`/`0`,
     * numbers as int/real, strings as text), so booleans are mapped to
     * `1`/`0` and other scalars are kept unchanged.
     *
     * @param mixed $value The PHP value to render.
     * @return mixed The native form used in the comparison.
     */
    protected function jsonScalarText(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }

    /**
     * Wraps the scalar extract in a CAST to a SQL type matching the value.
     *
     * SQLite's `json_extract` returns JSON-subtyped values, and comparing
     * those to a bound parameter fails for numeric scalars (the JSON subtype
     * forces a textual comparison). Casting the extract to the matching SQL
     * type makes the bound comparison correct for integers, reals and text.
     *
     * @param \Cake\Database\ExpressionInterface $field The extract expression.
     * @param mixed $value The original PHP scalar value (pre-cast).
     * @return \Cake\Database\ExpressionInterface The cast expression.
     */
    protected function wrapJsonScalarField(ExpressionInterface $field, mixed $value): ExpressionInterface
    {
        $type = $this->castSqlType($value);

        return new class ($field, $type) implements ExpressionInterface {
            /**
             * @param \Cake\Database\ExpressionInterface $field The inner extract expression.
             * @param string $type The SQLite CAST target type.
             */
            public function __construct(
                private ExpressionInterface $field,
                private string $type,
            ) {
            }

            /**
             * Compiles the CAST wrapper around the inner extract expression.
             *
             * @param \Cake\Database\ValueBinder $binder The value binder.
             * @return string The compiled SQL.
             */
            public function sql(ValueBinder $binder): string
            {
                return 'CAST(' . $this->field->sql($binder) . ' AS ' . $this->type . ')';
            }

            /**
             * Traverses the inner extract expression.
             *
             * @param \Closure $visitor The visitor callback.
             * @return static
             */
            public function traverse(Closure $visitor): static
            {
                $visitor($this->field);
                $this->field->traverse($visitor);

                return $this;
            }
        };
    }

    /**
     * Derives the SQLite CAST target type from a PHP value.
     *
     * @param mixed $value A scalar or an array of scalars (for IN clauses).
     * @return string One of `INTEGER`, `REAL`, `TEXT`.
     */
    private function castSqlType(mixed $value): string
    {
        $sample = is_array($value) ? ($value[0] ?? null) : $value;

        return match (true) {
            is_bool($sample) => 'INTEGER',
            is_int($sample) => 'INTEGER',
            is_float($sample) => 'REAL',
            default => 'TEXT',
        };
    }

    /**
     * Expression for "JSON path IS NULL" (missing path or explicit JSON null).
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The IS NULL expression.
     */
    protected function jsonPathIsNull(string $column, JsonPath $path): ExpressionInterface
    {
        $extract = $this->translateJsonPath($column, $path);
        $jsonExtract = $this->translateJsonPathTyped($column, $path);

        return new QueryExpression()->or([
            new QueryExpression()->isNull($extract),
            new QueryExpression()->eq($jsonExtract, $this->castToJson(null)),
        ]);
    }

    /**
     * Expression for "JSON path IS NOT NULL" (present and not JSON null).
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The IS NOT NULL expression.
     */
    protected function jsonPathIsNotNull(string $column, JsonPath $path): ExpressionInterface
    {
        $extract = $this->translateJsonPath($column, $path);
        $jsonExtract = $this->translateJsonPathTyped($column, $path);

        return new QueryExpression()->and([
            new QueryExpression()->isNotNull($extract),
            new QueryExpression()->notEq($jsonExtract, $this->castToJson(null)),
        ]);
    }

    /**
     * SQLite array-length: `json_array_length(col, '$.path')`.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The array-length expression.
     */
    protected function jsonPathArrayLength(string $column, JsonPath $path): ExpressionInterface
    {
        return new FunctionExpression('json_array_length', [
            $column => 'identifier',
            "'" . $path->toJsonPath() . "'" => 'literal',
        ]);
    }

    /**
     * Translates a JSON SET for UPDATE queries.
     *
     * Returns `json_set(col, '$.path', json(value))` which atomically sets
     * the value at a path in a JSON column.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param mixed $value The value to write at the path.
     * @return \Cake\Database\ExpressionInterface The atomic setter expression.
     */
    protected function translateJsonSet(string $column, JsonPath $path, mixed $value): ExpressionInterface
    {
        return new FunctionExpression('json_set', [
            $column => 'identifier',
            $path->toJsonPath(),
            $this->castToJson($value),
        ]);
    }
}
