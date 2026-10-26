<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Driver;

use Cake\Database\Driver\Mysql;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Crustum\JsonField\Database\Dialect\JsonFieldDialectTrait;
use Crustum\JsonField\Path\JsonPath;

/**
 * MySQL driver with JSON-field dialect support.
 *
 * Intercepts query compilation to rewrite JSON-path expressions into
 * engine-native SQL. MySQL's `JSON_EXTRACT` returns a JSON-typed value,
 * so `JSON_UNQUOTE` is used for scalar comparisons.
 */
class JsonFieldMysqlDriver extends Mysql
{
    use JsonFieldDialectTrait;

    /**
     * Translates a JSON path to a scalar-comparable extract expression.
     *
     * Uses `JSON_UNQUOTE(JSON_EXTRACT(col, '$.path'))` to extract the
     * text value. `JSON_EXTRACT` returns JSON type; `JSON_UNQUOTE` yields
     * the bare scalar so comparisons behave correctly.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The extract expression.
     */
    protected function translateJsonPath(string $column, JsonPath $path, bool $unquote = true): ExpressionInterface
    {
        $extract = new FunctionExpression('JSON_EXTRACT', [
            $column => 'identifier',
            "'" . $path->toJsonPath() . "'" => 'literal',
        ]);

        if ($unquote) {
            return new FunctionExpression('JSON_UNQUOTE', [$extract]);
        }

        return $extract;
    }

    /**
     * Translates a JSON path to a JSON-typed extract expression.
     *
     * Uses `JSON_EXTRACT(col, '$.path')` which returns the value in
     * MySQL's native JSON type, suitable for JSON equality and IN checks.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The JSON-typed extract expression.
     */
    protected function translateJsonPathTyped(string $column, JsonPath $path): ExpressionInterface
    {
        return new FunctionExpression('JSON_EXTRACT', [
            $column => 'identifier',
            "'" . $path->toJsonPath() . "'" => 'literal',
        ]);
    }

    /**
     * Casts a PHP value to a JSON literal using MySQL's `CAST(v AS JSON)`.
     *
     * @param mixed $value The PHP value to cast.
     * @return \Cake\Database\ExpressionInterface The JSON literal expression.
     */
    protected function castToJson(mixed $value): ExpressionInterface
    {
        if ($value instanceof ExpressionInterface) {
            return new FunctionExpression('CAST', [$value])
                ->setConjunction(' AS')
                ->add(['JSON' => 'literal']);
        }

        $encoded = json_encode($value);
        if ($encoded === false) {
            $encoded = 'null';
        }

        return new FunctionExpression('CAST', ["'" . str_replace("'", "''", $encoded) . "'" => 'literal'])
            ->setConjunction(' AS')
            ->add(['JSON' => 'literal']);
    }

    /**
     * MySQL JSON containment: `JSON_CONTAINS(col, json, '$.path')`.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param \Cake\Database\ExpressionInterface $valueExpr The JSON-cast containment value.
     * @param bool $negate When true, wraps the call in `NOT`.
     * @return \Cake\Database\ExpressionInterface The containment expression.
     */
    protected function translateJsonContains(
        string $column,
        JsonPath $path,
        ExpressionInterface $valueExpr,
        bool $negate,
    ): ExpressionInterface {
        $contains = new FunctionExpression('JSON_CONTAINS', [
            $column => 'identifier',
            $valueExpr,
            "'" . $path->toJsonPath() . "'" => 'literal',
        ]);

        return $negate ? new QueryExpression()->not($contains) : $contains;
    }

    /**
     * MySQL's JSON_EXTRACT returns JSON type — values must be JSON-literals.
     *
     * @return bool True — JSON-literal comparison needed.
     */
    protected function comparesWithJsonLiteral(): bool
    {
        return true;
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
            ->add(['DECIMAL(65,10)' => 'literal']);
    }

    /**
     * Renders a PHP scalar as JSON text for a textual JSON-path comparison.
     *
     * The text extract (`JSON_EXTRACT`) returns text, so booleans become
     * `true`/`false` and other scalars are stringified; strings are kept
     * verbatim so `'2'` matches a stored numeric `2`.
     *
     * @param mixed $value The PHP value to render.
     * @return mixed The text form used in the comparison.
     */
    protected function jsonScalarText(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value)) {
            return $value;
        }

        return (string)$value;
    }

    /**
     * Expression for "JSON path IS NULL" (missing path or explicit JSON null).
     *
     * MySQL: `JSON_EXTRACT(col, '$.path') IS NULL OR JSON_EXTRACT(col, '$.path') = CAST('null' AS JSON)`.
     * Handles both missing paths (IS NULL) and explicit JSON null values.
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
     * MySQL: `JSON_EXTRACT(col, '$.path') IS NOT NULL AND JSON_EXTRACT(col, '$.path') != CAST('null' AS JSON)`.
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
     * MySQL array-length: `JSON_LENGTH(col, '$.path')`.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The array-length expression.
     */
    protected function jsonPathArrayLength(string $column, JsonPath $path): ExpressionInterface
    {
        return new FunctionExpression('JSON_LENGTH', [
            $column => 'identifier',
            "'" . $path->toJsonPath() . "'" => 'literal',
        ]);
    }

    /**
     * Translates a JSON SET for UPDATE queries.
     *
     * Returns `JSON_SET(col, '$.path', CAST(v AS JSON))` which atomically
     * sets the value at a path in a JSON column.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param mixed $value The value to write at the path.
     * @return \Cake\Database\ExpressionInterface The atomic setter expression.
     */
    protected function translateJsonSet(string $column, JsonPath $path, mixed $value): ExpressionInterface
    {
        return new FunctionExpression('JSON_SET', [
            $column => 'identifier',
            "'" . $path->toJsonPath() . "'" => 'literal',
            $this->castToJson($value),
        ]);
    }
}
