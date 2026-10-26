<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Driver;

use Cake\Database\Driver\Postgres;
use Cake\Database\Expression\ComparisonExpression;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Crustum\JsonField\Database\Dialect\JsonFieldDialectTrait;
use Crustum\JsonField\Path\JsonPath;

/**
 * PostgreSQL driver with JSON-field dialect support.
 *
 * Intercepts query compilation to rewrite JSON-path expressions into
 * engine-native SQL. Uses `jsonb_extract_path_text` for scalar comparisons
 * and `jsonb_extract_path` for JSON-typed comparisons.
 */
class JsonFieldPostgresDriver extends Postgres
{
    use JsonFieldDialectTrait;

    /**
     * Translates a JSON path to a scalar-comparable extract expression.
     *
     * Uses `jsonb_extract_path_text(col, 'key1', 'key2')` which returns
     * the value as text, suitable for comparisons, ordering, and LIKE.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The extract expression.
     */
    protected function translateJsonPath(string $column, JsonPath $path, bool $unquote = true): ExpressionInterface
    {
        $params = [$column => 'identifier'];
        foreach ($path->pathNodes() as $node) {
            $params["'" . $node . "'"] = 'literal';
        }

        $function = $unquote ? 'jsonb_extract_path_text' : 'jsonb_extract_path';

        return new FunctionExpression($function, $params);
    }

    /**
     * Translates a JSON path to a JSON-typed extract expression.
     *
     * Uses `jsonb_extract_path(col, 'key1', 'key2')` which returns
     * a `jsonb` value, suitable for JSON equality and IN checks.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The JSON-typed extract expression.
     */
    protected function translateJsonPathTyped(string $column, JsonPath $path): ExpressionInterface
    {
        $params = [$column => 'identifier'];
        foreach ($path->pathNodes() as $node) {
            $params["'" . $node . "'"] = 'literal';
        }

        return new FunctionExpression('jsonb_extract_path', $params);
    }

    /**
     * Casts a PHP value to a JSON literal using PostgreSQL's `CAST(v AS JSONB)`.
     *
     * @param mixed $value The PHP value to cast.
     * @return \Cake\Database\ExpressionInterface The JSON literal expression.
     */
    protected function castToJson(mixed $value): ExpressionInterface
    {
        if ($value instanceof ExpressionInterface) {
            return new FunctionExpression('to_jsonb', [$value]);
        }

        $encoded = json_encode($value);
        if ($encoded === false) {
            $encoded = 'null';
        }

        return new FunctionExpression('CAST', ["'" . str_replace("'", "''", $encoded) . "'" => 'literal'])
            ->setConjunction(' AS')
            ->add(['JSONB' => 'literal']);
    }

    /**
     * PostgreSQL JSON containment: `jsonb_extract_path(col, ...) @> jsonb`.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param \Cake\Database\ExpressionInterface $valueExpr The JSON-cast containment value.
     * @param bool $negate When true, wraps the comparison in `NOT`.
     * @return \Cake\Database\ExpressionInterface The containment expression.
     */
    protected function translateJsonContains(
        string $column,
        JsonPath $path,
        ExpressionInterface $valueExpr,
        bool $negate,
    ): ExpressionInterface {
        $comparison = new ComparisonExpression(
            $this->translateJsonPathTyped($column, $path),
            $valueExpr,
            null,
            '@>',
        );

        return $negate ? new QueryExpression()->not($comparison) : $comparison;
    }

    /**
     * PostgreSQL's jsonb_extract_path returns jsonb - values must be JSON-literals.
     *
     * @return bool True - JSON-literal comparison needed.
     */
    public function comparesWithJsonLiteral(): bool
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
            ->add(['numeric' => 'literal']);
    }

    /**
     * Renders a PHP scalar as JSON text for a textual JSON-path comparison.
     *
     * The text extract (`jsonb_extract_path_text`) returns text, so booleans
     * become `true`/`false` and other scalars are stringified; strings are
     * kept verbatim so `'2'` matches a stored numeric `2`.
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
     * PostgreSQL: `col #> '{path}' IS NULL OR col #> '{path}' = 'null'::jsonb`.
     * Uses the JSON-typed extract to handle both missing and explicit null values.
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
     * PostgreSQL: `col #> '{path}' IS NOT NULL AND col #> '{path}' != 'null'::jsonb`.
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
     * PostgreSQL array-length: `jsonb_array_length(jsonb_extract_path(col, 'k1', ...))`.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The array-length expression.
     */
    protected function jsonPathArrayLength(string $column, JsonPath $path): ExpressionInterface
    {
        return new FunctionExpression('jsonb_array_length', [$this->translateJsonPathTyped($column, $path)]);
    }

    /**
     * Translates a JSON SET for UPDATE queries.
     *
     * Returns `jsonb_set(col, '{k1,k2}', to_jsonb(v), true)` which atomically
     * sets the value at a path in a JSON column.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param mixed $value The value to write at the path.
     * @return \Cake\Database\ExpressionInterface The atomic setter expression.
     */
    protected function translateJsonSet(string $column, JsonPath $path, mixed $value): ExpressionInterface
    {
        return new FunctionExpression('jsonb_set', [
            $column => 'identifier',
            "'" . $path->toPathArray() . "'" => 'literal',
            $this->castToJson($value),
            'true' => 'literal',
        ]);
    }
}
