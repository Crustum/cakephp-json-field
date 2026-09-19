<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Dialect;

use Cake\Database\Expression\BetweenExpression;
use Cake\Database\Expression\ComparisonExpression;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Expression\OrderByExpression;
use Cake\Database\Expression\OrderClauseExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Expression\TupleComparison;
use Cake\Database\Expression\UnaryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\Query;
use Cake\Database\Query\DeleteQuery;
use Cake\Database\Query\InsertQuery;
use Cake\Database\Query\SelectQuery;
use Cake\Database\Query\UpdateQuery;
use Crustum\JsonField\Path\JsonPath;
use Crustum\JsonField\Path\JsonPathParser;
use ReflectionClass;

/**
 * Dialect trait that intercepts CakePHP query compilation and rewrites
 * JSON-path expressions (`column->node1.node2`) into engine-native SQL.
 *
 * Use this trait in driver subclasses that extend a CakePHP driver.
 * The driver must call {@see queryTranslator()} from its
 * {@see \Cake\Database\Driver::transformQuery()} override so that every
 * query is processed before the parent driver's own transformations.
 *
 * ## Usage
 *
 * ```php
 * class JsonFieldSqliteDriver extends \Cake\Database\Driver\Sqlite
 * {
 *     use JsonFieldDialectTrait;
 *
 *     // Implement the abstract methods that emit engine-specific SQL.
 * }
 * ```
 */
trait JsonFieldDialectTrait
{
    /**
     * Translates JSON-path expressions in the query before compilation.
     *
     * CakePHP invokes this during query compilation. We dispatch to the
     * appropriate query-type translator, then delegate to the parent driver
     * so its own transformations (auto-quoting, expression translators) apply.
     *
     * @param \Cake\Database\Query $query The query to translate.
     * @return \Cake\Database\Query The translated query.
     */
    public function transformQuery(Query $query): Query
    {
        match (true) {
            $query instanceof SelectQuery => $this->_selectJsonFieldQueryTranslator($query),
            $query instanceof InsertQuery => $this->_insertJsonFieldQueryTranslator($query),
            $query instanceof UpdateQuery => $this->_updateJsonFieldQueryTranslator($query),
            $query instanceof DeleteQuery => $this->_deleteJsonFieldQueryTranslator($query),
            default => null,
        };

        /** @var \Cake\Database\Query $result */
        $result = parent::transformQuery($query); // @phpstan-ignore-line trait parent

        return $result;
    }

    /**
     * Recursive expression dispatcher that translates JSON-path expression objects.
     *
     * Inspects each expression type and delegates to the matching handler.
     * Unknown expression types are traversed recursively so nested paths
     * are also translated.
     *
     * @param \Cake\Database\ExpressionInterface $expression The expression to translate.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\ExpressionInterface The translated expression.
     */
    public function translateExpression(ExpressionInterface $expression, Query $query): ExpressionInterface
    {
        if ($expression instanceof ComparisonExpression) {
            return $this->_translateComparisonExpression($expression, $query);
        }

        if ($expression instanceof BetweenExpression) {
            return $this->_translateBetweenExpression($expression, $query);
        }

        if ($expression instanceof OrderByExpression) {
            return $this->_translateOrderByExpression($expression, $query);
        }

        if ($expression instanceof UnaryExpression) {
            return $this->_translateUnaryExpression($expression, $query);
        }

        if ($expression instanceof IdentifierExpression) {
            return $this->_translateIdentifierExpression($expression, $query);
        }

        if ($expression instanceof QueryExpression) {
            $expression->iterateParts(function ($expr) use ($query) {
                if (is_string($expr)) {
                    return $this->_translateRawSQL($expr, $query);
                }

                if (is_array($expr)) {
                    // FunctionExpression arguments are stored as
                    // `['value' => mixed, 'type' => string|null]`. Translate any
                    // JSON-path string value, or recurse into nested expressions.
                    if (
                        isset($expr['value'])
                        && is_string($expr['value'])
                        && str_contains($expr['value'], '->')
                    ) {
                        $expr['value'] = $this->_translateRawSQL($expr['value'], $query);
                    } elseif (isset($expr['value']) && $expr['value'] instanceof ExpressionInterface) {
                        $expr['value'] = $this->translateExpression($expr['value'], $query);
                    }

                    return $expr;
                }

                return $this->translateExpression($expr, $query);
            });

            return $expression;
        }

        $expression->traverse(function ($expr) use ($query): mixed {
            if ($expr === null) {
                return null;
            }

            if (is_string($expr)) {
                return $this->_translateRawSQL($expr, $query);
            }

            if ($expr instanceof ExpressionInterface) {
                return $this->translateExpression($expr, $query);
            }

            return $expr;
        });

        return $expression;
    }

    /**
     * Translates a ComparisonExpression whose field is a JSON path.
     *
     * Rewrites the field to an engine-native extract expression and casts
     * the comparison value to a JSON literal so the comparison is correct.
     *
     * @param \Cake\Database\Expression\ComparisonExpression $expression The comparison to translate.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\ExpressionInterface The translated expression.
     */
    protected function _translateComparisonExpression(
        ComparisonExpression $expression,
        Query $query,
    ): ExpressionInterface {
        $field = $expression->getField();

        if ($expression instanceof TupleComparison) {
            return $this->_translateTupleComparisonExpression($expression, $query);
        }

        if (is_string($field) && JsonPathParser::detect($field) === 0) {
            return $expression;
        }

        $operator = strtolower($expression->getOperator());
        $isLikeOperator = in_array($operator, ['like', 'not like', 'ilike', 'not ilike'], true);
        $isNullOperator = in_array($operator, ['is', 'is not'], true);

        $value = $expression->getValue();
        $isScalarValue = is_scalar($value);

        if ($operator === '@>' && is_string($field) && JsonPathParser::detect($field) > 0) {
            $path = JsonPathParser::parse($field);
            $valueExpr = $value;
            if (!$valueExpr instanceof ExpressionInterface) {
                $valueExpr = $this->castToJson($valueExpr);
            }

            return $this->translateJsonContains($path->column, $path, $valueExpr, false);
        }

        if (is_string($field) && JsonPathParser::detect($field) > 0) {
            $path = JsonPathParser::parse($field);

            if ($isLikeOperator) {
                // LIKE must use text extract — jsonb LIKE jsonb is not supported.
                $expression->setField($this->translateJsonPath($path->column, $path));
            } elseif ($operator === 'in' || $operator === 'not in') {
                // IN / NOT IN over scalar elements: text comparison.
                $expression->setField($this->wrapJsonScalarField($this->translateJsonPath($path->column, $path), $value));
            } elseif ($value instanceof IdentifierExpression) {
                // JOIN against another column: compare the JSON-typed extract to a
                // JSON-cast of the referenced column so the engine compares
                // jsonb = jsonb (the FK is stored as a JSON scalar, not a SQL column).
                $expression->setField($this->translateJsonPathTyped($path->column, $path));
                $value = $this->castToJson($value);
            } elseif ($value instanceof ExpressionInterface) {
                // Engine-cast JSON value (whereJsonPathJson): JSONB comparison.
                $expression->setField($this->translateJsonPathTyped($path->column, $path));
            } elseif (!$isScalarValue) {
                // Array/object literal: JSONB equality.
                $expression->setField($this->translateJsonPathTyped($path->column, $path));
            } else {
                // Scalar ordering operators (>, >=, <, <=) on a numeric value must
                // compare the stored JSON number numerically. Casting the extract to
                // a SQL numeric type (not jsonb) avoids JSONB type-ordering quirks
                // where a stored JSON string sorts before a JSON number, keeping the
                // result consistent with PHP's numeric comparison. Non-numeric scalars
                // (strings, dates) keep the text form so comparisons remain lexical.
                $extract = $this->translateJsonPath($path->column, $path);
                if (in_array($operator, ['>', '>=', '<', '<='], true) && is_numeric($value)) {
                    $expression->setField($this->castJsonExtractToNumeric($extract));
                } else {
                    $expression->setField($this->wrapJsonScalarField($extract, $value));
                }
            }
        }

        if ($isNullOperator) {
            // IS NULL / IS NOT NULL — never cast the null value.
        } elseif ($value instanceof ExpressionInterface) {
            // ExpressionInterface values (e.g. engine-cast JSON, IdentifierExpression for JOINs) are not cast.
        } elseif ($isLikeOperator) {
            // LIKE pattern is compared as text — no JSON casting.
        } elseif ($operator === 'in' || $operator === 'not in') {
            // IN / NOT IN over scalar elements: bind each as its JSON scalar text form.
            $value = array_map(fn($v) => $this->jsonScalarText($v), $value);
        } elseif (!$isScalarValue) {
            // Array/object literal: cast the whole value to a JSON literal.
            $value = $this->castToJson($value);
        } else {
            // Scalar: bind as its JSON scalar text form
            // (string stays raw, bool -> 'true'/'false', else stringified).
            $value = $this->jsonScalarText($value);
        }

        // SQLite: extract returns TEXT, plain value comparison works.

        $expression->setValue($value);

        return $expression;
    }

    /**
     * Translates a TupleComparison whose fields may be JSON paths.
     *
     * Only the field tuple is rewritten; the value is a (possibly multi-) tuple
     * and must be left intact for the binder, so it is never JSON-cast.
     *
     * @param \Cake\Database\Expression\TupleComparison $expression The tuple comparison.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\Expression\TupleComparison The translated expression.
     */
    protected function _translateTupleComparisonExpression(
        TupleComparison $expression,
        Query $query,
    ): TupleComparison {
        $fields = $expression->getField();

        if (is_string($fields) && JsonPathParser::detect($fields) > 0) {
            $path = JsonPathParser::parse($fields);
            $expression->setField(
                $this->comparesWithJsonLiteral()
                    ? $this->translateJsonPathTyped($path->column, $path)
                    : $this->translateJsonPath($path->column, $path),
            );

            return $expression;
        }

        if (is_array($fields)) {
            $translated = [];
            foreach ($fields as $key => $field) {
                if (is_string($field) && JsonPathParser::detect($field) > 0) {
                    $path = JsonPathParser::parse($field);
                    $translated[$key] = $this->comparesWithJsonLiteral()
                        ? $this->translateJsonPathTyped($path->column, $path)
                        : $this->translateJsonPath($path->column, $path);
                } else {
                    $translated[$key] = $field;
                }
            }

            $expression->setField($translated);
        }

        return $expression;
    }

    /**
     * Translates a BetweenExpression whose field is a JSON path.
     *
     * Rewrites the field to an engine-native extract expression and casts
     * both bounds to JSON literals.
     *
     * @param \Cake\Database\Expression\BetweenExpression $expression The between to translate.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\Expression\BetweenExpression The translated expression.
     */
    protected function _translateBetweenExpression(
        BetweenExpression $expression,
        Query $query,
    ): BetweenExpression {
        $reflection = new ReflectionClass($expression);

        $fromProp = $reflection->getProperty('_from');

        $from = $fromProp->getValue($expression);

        $field = $expression->getField();

        if (is_string($field) && JsonPathParser::detect($field) > 0) {
            $path = JsonPathParser::parse($field);
            $toProp = $reflection->getProperty('_to');
            $to = $toProp->getValue($expression);
            $numeric = is_numeric($from) && is_numeric($to);

            if ($numeric) {
                // Numeric BETWEEN: cast the extract to a SQL numeric type and bind
                // the bounds as plain scalars so the range compares numerically
                // (consistent with PHP), not by JSONB/JSON type ordering.
                $expression->setField(
                    $this->castJsonExtractToNumeric($this->translateJsonPath($path->column, $path)),
                );
            } else {
                $extract = $this->comparesWithJsonLiteral()
                    ? $this->translateJsonPathTyped($path->column, $path)
                    : $this->translateJsonPath($path->column, $path);
                $expression->setField($this->wrapJsonScalarField($extract, $from));

                if ($this->comparesWithJsonLiteral()) {
                    $fromProp->setValue($expression, $this->castToJson($from));
                    $toProp->setValue($expression, $this->castToJson($to));
                }
            }
        }

        return $expression;
    }

    /**
     * Translates an OrderByExpression, rewriting any JSON-path fields.
     *
     * @param \Cake\Database\Expression\OrderByExpression $expression The order expression.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\Expression\OrderByExpression The translated expression.
     */
    protected function _translateOrderByExpression(
        OrderByExpression $expression,
        Query $query,
    ): OrderByExpression {
        $expression->iterateParts(function ($fieldOrOrder, &$key) {
            if ($fieldOrOrder instanceof OrderClauseExpression) {
                $field = $fieldOrOrder->getField();
                if (is_string($field) && JsonPathParser::detect($field) > 0) {
                    $path = JsonPathParser::parse($field);
                    $fieldOrOrder->setField($this->translateJsonPath($path->column, $path, false));
                }

                return $fieldOrOrder;
            }

            if (is_string($fieldOrOrder) && JsonPathParser::detect($fieldOrOrder) > 0) {
                $path = JsonPathParser::parse($fieldOrOrder);

                return $this->translateJsonPath($path->column, $path, false);
            }

            if (is_string($key) && JsonPathParser::detect($key) > 0) {
                $path = JsonPathParser::parse($key);
                $field = $this->translateJsonPath($path->column, $path, false);

                // OrderByExpression stores the field as an array key, which cannot
                // be an ExpressionInterface. Wrap it in an OrderClauseExpression and
                // reindex the key to a numeric value so the compiled SQL renders the
                // whole `field DIRECTION` clause (the driver still rewrites the inner
                // JSON_VALUE function during transformQuery).
                static $i = 0;
                $key = 1000 + $i;
                $i++;

                return new OrderClauseExpression($field, (string)$fieldOrOrder);
            }

            return $fieldOrOrder;
        });

        return $expression;
    }

    /**
     * Translates a UnaryExpression (IS NULL / IS NOT NULL) for JSON paths.
     *
     * Uses reflection to access the private `_operator` and `_value` properties
     * because CakePHP's UnaryExpression does not expose public getters.
     *
     * @param \Cake\Database\Expression\UnaryExpression $expression The unary expression.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\ExpressionInterface The translated expression.
     */
    protected function _translateUnaryExpression(
        UnaryExpression $expression,
        Query $query,
    ): ExpressionInterface {
        $reflection = new ReflectionClass($expression);

        $operatorProp = $reflection->getProperty('_operator');

        $op = $operatorProp->getValue($expression);

        $valueProp = $reflection->getProperty('_value');

        $value = $valueProp->getValue($expression);

        if ($op === 'IS NULL' && $value instanceof IdentifierExpression) {
            $field = $value->getIdentifier();
            if (JsonPathParser::detect($field) > 0) {
                $path = JsonPathParser::parse($field);

                return $this->jsonPathIsNull($path->column, $path);
            }
        }

        if ($op === 'IS NOT NULL' && $value instanceof IdentifierExpression) {
            $field = $value->getIdentifier();
            if (JsonPathParser::detect($field) > 0) {
                $path = JsonPathParser::parse($field);

                return $this->jsonPathIsNotNull($path->column, $path);
            }
        }

        if ($value instanceof ExpressionInterface && !$value instanceof IdentifierExpression) {
            $valueProp->setValue($expression, $this->translateExpression($value, $query));

            return $expression;
        }

        return $expression;
    }

    /**
     * Translates an IdentifierExpression whose identifier is a JSON path.
     *
     * @param \Cake\Database\Expression\IdentifierExpression $expression The identifier.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\Expression\IdentifierExpression The translated expression.
     */
    protected function _translateIdentifierExpression(
        IdentifierExpression $expression,
        Query $query,
    ): IdentifierExpression {
        $field = $expression->getIdentifier();

        if (JsonPathParser::detect($field) > 0) {
            $path = JsonPathParser::parse($field);
            $expr = $this->translateJsonPath($path->column, $path);
            $expression->setIdentifier(
                $expr instanceof IdentifierExpression ? $expr->getIdentifier() : (string)$expr, // @phpstan-ignore-line cast.string
            );
        }

        return $expression;
    }

    /**
     * Translates a raw SQL string, rewriting any inline JSON-path patterns.
     *
     * If the entire string is a JSON path it is translated directly.
     * Otherwise a regex scan replaces embedded `column->path` patterns.
     *
     * @param string $fragment The raw SQL fragment.
     * @param \Cake\Database\Query $query The query being compiled.
     * @return \Cake\Database\ExpressionInterface|string The translated fragment.
     */
    protected function _translateRawSQL(string $fragment, Query $query): string|ExpressionInterface
    {
        if (!str_contains($fragment, '->')) {
            return $fragment;
        }

        $trimmed = trim($fragment);

        // Aggregate functions over a JSON path (Batch F). Numeric aggregates
        // (SUM/AVG/MIN/MAX) cast the extract to a SQL numeric type so the math
        // is correct; COUNT returns the JSON array element count via the engine's
        // array-length function rather than a row count.
        if (preg_match('/^(SUM|AVG|MIN|MAX|COUNT)\s*\(\s*([\w\.\[\]]+->[\w\.\[\]]+)\s*\)$/i', $trimmed, $m)) {
            $fn = strtoupper($m[1]);
            $path = JsonPathParser::parse($m[2]);

            if ($fn === 'COUNT') {
                return $this->jsonPathArrayLength($path->column, $path);
            }

            return new FunctionExpression($fn, [
                $this->castJsonExtractToNumeric($this->translateJsonPath($path->column, $path)),
            ]);
        }

        // Handle "path IS NULL" / "path IS NOT NULL"
        if (preg_match('/^(.+?)\s+(IS\s+(?:NOT\s+)?)NULL$/i', $trimmed, $m)) {
            $pathPart = trim($m[1]);
            if (JsonPathParser::detect($pathPart) > 0) {
                $path = JsonPathParser::parse($pathPart);
                $isNot = stripos($m[2], 'NOT') !== false;

                return $isNot
                    ? $this->jsonPathIsNotNull($path->column, $path)
                    : $this->jsonPathIsNull($path->column, $path);
            }
        }

        // A fragment without whitespace that is entirely a JSON path is a
        // pure-path reference; translate it directly.
        if (!str_contains($fragment, ' ') && JsonPathParser::detect($fragment) > 0) {
            $path = JsonPathParser::parse($fragment);

            return $this->translateJsonPath($path->column, $path);
        }

        // Embedded path(s) — replace every `column->path` occurrence, keeping
        // the surrounding literal SQL, and return a QueryExpression so
        // FunctionExpression parameter bindings are preserved.
        $pathPattern = '/[\w\.\*\[\]]+->[\w\.\*\[\]]+/';
        if (preg_match_all($pathPattern, $fragment, $matches, PREG_OFFSET_CAPTURE)) {
            $parts = [];
            $last = 0;
            foreach ($matches[0] as [$match, $offset]) {
                if ($offset > $last) {
                    $parts[] = substr($fragment, $last, $offset - $last);
                }

                $path = JsonPathParser::parse($match);
                $parts[] = $this->translateJsonPath($path->column, $path);
                $last = $offset + strlen($match);
            }

            if ($last < strlen($fragment)) {
                $parts[] = substr($fragment, $last);
            }

            return new QueryExpression($parts, [], '');
        }

        return $fragment;
    }

    /**
     * Checks whether a field string uses JSON-path notation.
     *
     * @param string $field The field to inspect.
     * @return bool True when the field matches `column->path` notation.
     */
    protected function isJsonFieldPath(string $field): bool
    {
        return JsonPathParser::detect($field) > 0;
    }

    /**
     * Quotes a JSON-path-aware identifier.
     *
     * When the identifier uses the `column->path` JSON-path notation the column
     * part (which may be `Model.column`) is quoted through the parent driver,
     * while the `->path` suffix is preserved verbatim so the engine's JSON
     * extract operator/function keeps working. Plain identifiers (no `->`) are
     * delegated to the parent driver unchanged.
     *
     * @param string $identifier The identifier to quote.
     * @return string The quoted identifier.
     */
    public function quoteIdentifier(string $identifier): string
    {
        $pos = strpos($identifier, '->');

        if ($pos === false) {
            return parent::quoteIdentifier($identifier);
        }

        $column = trim(substr($identifier, 0, $pos));
        $suffix = substr($identifier, $pos);

        return parent::quoteIdentifier($column) . $suffix;
    }

    /**
     * Renders a PHP scalar for use as a JSON-path comparison value.
     *
     * Each driver implements this to match how its extract function returns
     * scalar JSON values: PostgreSQL/MySQL return text (`jsonb_extract_path_text`
     * / `JSON_EXTRACT`), so booleans become `true`/`false` and numbers are
     * stringified; SQLite's `json_extract` returns native scalars (booleans as
     * `1`/`0`), so the value is kept native.
     *
     * @param mixed $value The PHP value to render.
     * @return mixed The form used in the comparison.
     */
    abstract protected function jsonScalarText(mixed $value): mixed;

    /**
     * Wraps a scalar-extract field expression before a scalar comparison.
     *
     * Most engines compare a text extract directly against the bound value,
     * so this is a no-op. SQLite returns JSON-subtyped values from
     * `json_extract`, and comparing those to a bound parameter fails for
     * numeric scalars; subclasses override this to `CAST` the extract to a
     * SQL type that matches the PHP value.
     *
     * @param \Cake\Database\ExpressionInterface $field The extract expression.
     * @param mixed $value The original PHP scalar value (pre-cast).
     * @return \Cake\Database\ExpressionInterface The field to use in the comparison.
     */
    protected function wrapJsonScalarField(ExpressionInterface $field, mixed $value): ExpressionInterface
    {
        return $field;
    }

    // ------------------------------------------------------------------
    // Query-type translators
    // ------------------------------------------------------------------

    /**
     * Translates SELECT clauses, translating JSON-path select fields.
     *
     * @param \Cake\Database\Query $query The query to translate.
     * @return void
     */
    protected function _selectJsonFieldQueryTranslator(Query $query): void
    {
        if (!$query instanceof SelectQuery) {
            return;
        }

        $query->traverse(function ($part, $clause) use ($query): void {
            if ($clause === 'select' && !empty($part)) {
                $fields = [];
                foreach ($part as $alias => $field) {
                    if ($field instanceof ExpressionInterface) {
                        $fields[$alias] = $this->translateExpression($field, $query);
                    } elseif (is_string($field) && str_contains($field, '->')) {
                        $fields[$alias] = $this->_translateRawSQL($field, $query);
                    } else {
                        $fields[$alias] = $field;
                    }
                }

                $query->select($fields, true);
            }

            if ($clause === 'group' && !empty($part)) {
                $query->groupBy(array_map(function ($field) {
                    if (is_string($field) && JsonPathParser::detect($field) > 0) {
                        $path = JsonPathParser::parse($field);

                        return $this->translateJsonPath($path->column, $path);
                    }

                    return $field;
                }, $part), true);
            }

            if ($clause === 'join' && !empty($part)) {
                foreach ($part as $joint) {
                    if (isset($joint['conditions']) && $joint['conditions'] instanceof ExpressionInterface) {
                        $this->translateExpression($joint['conditions'], $query);
                    }
                }
            }

            if ($clause === 'order' && $part instanceof OrderByExpression) {
                $this->_translateOrderByExpression($part, $query);
            }

            if ($part instanceof ExpressionInterface) {
                $this->translateExpression($part, $query);
            }
        });
    }

    /**
     * Translates INSERT clauses, casting JSON-typed values.
     *
     * INSERT queries do not have WHERE clauses, so this is a no-op.
     *
     * @param \Cake\Database\Query\InsertQuery $query The query to translate.
     * @return void
     */
    protected function _insertJsonFieldQueryTranslator(InsertQuery $query): void
    {
        // INSERT queries have no WHERE clause — nothing to translate.
    }

    /**
     * Translates UPDATE clauses, rewriting SET assignments and WHERE conditions.
     *
     * @param \Cake\Database\Query $query The query to translate.
     * @return void
     */
    protected function _updateJsonFieldQueryTranslator(Query $query): void
    {
        $set = $query->clause('set');

        if ($set instanceof QueryExpression) {
            $set->iterateParts(function ($expr) {
                if ($expr instanceof ComparisonExpression) {
                    $field = $expr->getField();
                    if (is_string($field) && JsonPathParser::detect($field) > 0) {
                        $path = JsonPathParser::parse($field);
                        $expr->setField($this->translateJsonPath($path->column, $path));
                        $expr->setValue($this->translateJsonSet($path->column, $path, $expr->getValue()));
                    }
                }

                return $expr;
            });
        }

        $where = $query->clause('where');

        if ($where instanceof ExpressionInterface) {
            $this->translateExpression($where, $query);
        }
    }

    /**
     * Translates DELETE clauses, rewriting WHERE conditions.
     *
     * @param \Cake\Database\Query $query The query to translate.
     * @return void
     */
    protected function _deleteJsonFieldQueryTranslator(Query $query): void
    {
        $where = $query->clause('where');

        if ($where instanceof ExpressionInterface) {
            $this->translateExpression($where, $query);
        }
    }

    // ------------------------------------------------------------------
    // Query option helper
    // ------------------------------------------------------------------

    /**
     * Reads a single option from the query's options array.
     *
     * CakePHP 5.4 ORM queries (which extend {@see \Cake\Database\Query\SelectQuery})
     * store extra options via `setOption()` / `getOptions()`. Database-level
     * queries do not carry options, so this method returns the fallback when
     * the query does not support options.
     *
     * @param \Cake\Database\Query $query The query.
     * @param string $option The option key.
     * @param mixed $fallback Default value when the option is absent.
     * @return mixed The option value.
     */
    protected function _getQueryOption(Query $query, string $option, mixed $fallback = null): mixed
    {
        if (method_exists($query, 'getOptions')) {
            /** @var array<string, mixed> $options */
            $options = $query->getOptions();

            return $options[$option] ?? $fallback;
        }

        return $fallback;
    }

    // ------------------------------------------------------------------
    // Abstract methods — implemented by each driver subclass
    // ------------------------------------------------------------------

    /**
     * Whether comparisons against a JSON path should bind the value as a JSON
     * literal (MySQL/PostgreSQL) rather than a plain scalar (SQLite).
     *
     * @return bool True when the engine needs JSON-literal comparison.
     */
    abstract protected function comparesWithJsonLiteral(): bool;

    /**
     * Casts a JSON-path extract expression to a SQL numeric type so ordering
     * operators (`>`, `>=`, `<`, `<=`) compare stored JSON numbers numerically,
     * matching PHP's numeric semantics (and avoiding JSONB type ordering).
     *
     * @param \Cake\Database\ExpressionInterface $field The extract expression.
     * @return \Cake\Database\ExpressionInterface The numeric-cast expression.
     */
    abstract protected function castJsonExtractToNumeric(ExpressionInterface $field): ExpressionInterface;

    /**
     * Translates a JSON path to the engine-native extract expression.
     *
     * Returns a scalar-comparable form suitable for comparisons, ordering,
     * LIKE, and NULL checks.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The extract expression.
     */
    abstract protected function translateJsonPath(string $column, JsonPath $path): ExpressionInterface;

    /**
     * Translates a JSON path to the engine-native extract expression.
     *
     * Returns the JSON-typed form for JSON equality, IN with JSON literals,
     * and BETWEEN comparisons against JSON values.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The JSON-typed extract expression.
     */
    abstract protected function translateJsonPathTyped(string $column, JsonPath $path): ExpressionInterface;

    /**
     * Casts a PHP value to a JSON literal for the engine.
     *
     * MySQL: `CAST(v AS JSON)`, PostgreSQL: `to_jsonb(v)`, SQLite: `json(v)`.
     *
     * @param mixed $value The PHP value to cast.
     * @return \Cake\Database\ExpressionInterface The JSON literal expression.
     */
    abstract protected function castToJson(mixed $value): ExpressionInterface;

    /**
     * Builds a JSON-containment expression for the `@>` operator at a path.
     *
     * The dialect trait rewrites a `column->path @> value` comparison into this
     * engine-native form. Postgres uses the native `jsonb @> jsonb` operator;
     * MySQL uses `JSON_CONTAINS`; SQLite has no native containment operator and
     * raises an exception.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param \Cake\Database\ExpressionInterface $valueExpr The containment value (already JSON-cast).
     * @param bool $negate When true, negates the containment (NOT contains).
     * @return \Cake\Database\ExpressionInterface The containment expression.
     */
    abstract protected function translateJsonContains(
        string $column,
        JsonPath $path,
        ExpressionInterface $valueExpr,
        bool $negate,
    ): ExpressionInterface;

    /**
     * Expression for "JSON path IS NULL" (missing or explicit JSON null).
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The IS NULL expression.
     */
    abstract protected function jsonPathIsNull(string $column, JsonPath $path): ExpressionInterface;

    /**
     * Expression for "JSON path IS NOT NULL" (present and not JSON null).
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The IS NOT NULL expression.
     */
    abstract protected function jsonPathIsNotNull(string $column, JsonPath $path): ExpressionInterface;

    /**
     * Translates a JSON SET for UPDATE queries.
     *
     * Returns an expression that atomically sets the value at a path in a
     * JSON column, suitable for SET clauses in UPDATE statements.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @param mixed $value The value to write at the path.
     * @return \Cake\Database\ExpressionInterface The atomic setter expression.
     */
    abstract protected function translateJsonSet(string $column, JsonPath $path, mixed $value): ExpressionInterface;

    /**
     * Expression returning the number of elements in a JSON array at a path.
     *
     * Used by `COUNT(path)` aggregation, which counts array elements rather than
     * rows. Each engine maps this to its native array-length function.
     *
     * @param string $column The JSON column name.
     * @param \Crustum\JsonField\Path\JsonPath $path The parsed path.
     * @return \Cake\Database\ExpressionInterface The array-length expression.
     */
    abstract protected function jsonPathArrayLength(string $column, JsonPath $path): ExpressionInterface;
}
