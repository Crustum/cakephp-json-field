<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM;

use Cake\Database\Expression\IdentifierExpression;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use Crustum\JsonField\Database\JsonFieldEngineInterface;
use Crustum\JsonField\Database\JsonFieldEngineResolver;
use Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder;
use Crustum\JsonField\ORM\Association\Loader\JsonFieldReferenceLoader;
use InvalidArgumentException;

/**
 * Adds JSON-path aware query building to a Table (the READ-path entry point).
 *
 * Apply this trait to a Table that stores path-addressable JSON columns. The
 * dialect trait intercepts query compilation and rewrites JSON-path strings
 * (`column->path`) into engine-native SQL, so these helpers emit plain string
 * paths rather than engine-specific expressions.
 *
 * @mixin \Cake\ORM\Table
 */
trait JsonFieldAwareTrait
{
    /**
     * Returns the engine adapter for the table's connection driver.
     *
     * @return \Crustum\JsonField\Database\JsonFieldEngineInterface The resolved engine.
     */
    public function jsonFieldEngine(): JsonFieldEngineInterface
    {
        $driver = $this->getConnection()->getDriver();

        return new JsonFieldEngineResolver()->engine($driver);
    }

    /**
     * Returns the raw JSON-path string for use in query conditions.
     *
     * The dialect trait rewrites path strings at compile time into
     * engine-native SQL, so no ExpressionInterface is needed.
     *
     * @param string $path Path in `column->node` notation (for example `data->profile.created`).
     * @return string The path string.
     */
    public function extractJsonPath(string $path): string
    {
        return $path;
    }

    /**
     * Builds a query filtered by a JSON path value via a string condition.
     *
     * @param string $path Path in `column->node` notation.
     * @param mixed $value The value to compare against.
     * @param string $operator One of `=`, `!=`, `>`, `>=`, `<`, `<=`.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPath(string $path, mixed $value, string $operator = '='): SelectQuery
    {
        return $this->find()->where(["{$path} {$operator}" => $value]);
    }

    /**
     * Builds a query filtered by a JSON path value within a set.
     *
     * @param string $path Path in `column->node` notation.
     * @param array<int, mixed> $values The set of values to match against.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathIn(string $path, array $values): SelectQuery
    {
        return $this->find()->where(["{$path} IN" => $values]);
    }

    /**
     * Builds a query filtering rows whose JSON value at the path contains the
     * given value (JSON containment, the `@>` operator).
     *
     * @param string $path Path in `column->node` notation.
     * @param mixed $value The value that must be contained at the path.
     * @param bool $negate When true, matches rows that do NOT contain the value.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathContains(string $path, mixed $value, bool $negate = false): SelectQuery
    {
        $operator = '@>';

        if ($negate) {
            return $this->find()->where(fn($exp) => $exp->not(["{$path} {$operator}" => $value]));
        }

        return $this->whereJsonPath($path, $value, $operator);
    }

    /**
     * Builds a query matching rows where at least one element of the JSON array at
     * the path satisfies the given sub-document conditions (Mongo `$elemMatch`).
     *
     * Translates to array-element containment: the engine's `@>` / `JSON_CONTAINS`
     * operator checks whether the array at the path contains an element that is a
     * superset of the supplied sub-document, so equality conditions on element fields
     * are matched across the list. Range/operator conditions on element fields are out
     * of scope for the engine-neutral design; SQLite has no native containment and
     * throws, mirroring `whereJsonPathContains`.
     *
     * @param string $path Path in `column->node` notation pointing at a JSON array.
     * @param array<string, mixed> $conditions The sub-document the array element must contain.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathElemMatch(string $path, array $conditions): SelectQuery
    {
        return $this->whereJsonPathContains($path, [$conditions]);
    }

    /**
     * Builds a query filtered by a JSON path value NOT IN a set.
     *
     * @param string $path Path in `column->node` notation.
     * @param array<int, mixed> $values The set of values the path must not match.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathNotIn(string $path, array $values): SelectQuery
    {
        return $this->find()->where(["{$path} NOT IN" => $values]);
    }

    /**
     * Builds a query filtered by a JSON path value within a range.
     *
     * @param string $path Path in `column->node` notation.
     * @param mixed $from The inclusive lower bound.
     * @param mixed $to The inclusive upper bound.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathBetween(string $path, mixed $from, mixed $to): SelectQuery
    {
        return $this->find()->where(fn($exp) => $exp->between($path, $from, $to));
    }

    /**
     * Builds a query filtering for rows where the JSON path value IS NULL.
     *
     * @param string $path Path in `column->node` notation.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathNull(string $path): SelectQuery
    {
        return $this->find()->where(["{$path} IS NULL"]);
    }

    /**
     * Builds a query filtering for rows where the JSON path value IS NOT NULL.
     *
     * @param string $path Path in `column->node` notation.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathNotNull(string $path): SelectQuery
    {
        return $this->find()->where(["{$path} IS NOT NULL"]);
    }

    /**
     * Builds a query filtering by a JSON path value with a LIKE / NOT LIKE pattern.
     *
     * @param string $path Path in `column->node` notation.
     * @param string $value The LIKE pattern (for example `ba%`).
     * @param bool $negate When true, uses `NOT LIKE` instead of `LIKE`.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathLike(string $path, string $value, bool $negate = false): SelectQuery
    {
        $extract = $this->extractJsonPath($path);

        return $this->find()->where(fn($exp) => $negate ? $exp->notLike($extract, $value) : $exp->like($extract, $value));
    }

    /**
     * Builds a query ordered by a JSON path value.
     *
     * @param string $path Path in `column->node` notation.
     * @param string $direction Sort direction, `ASC` or `DESC`.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function orderByJsonPath(string $path, string $direction = 'ASC'): SelectQuery
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this->find()->orderBy(["{$path}" => $direction]);
    }

    /**
     * Builds a query that selects a JSON path extract, optionally aliased.
     *
     * @param string $path Path in `column->node` notation.
     * @param string|null $alias Optional SQL alias for the projected column.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function selectJsonPath(string $path, ?string $alias = null): SelectQuery
    {
        $query = $this->find();

        return $alias !== null ? $query->select([$alias => $path]) : $query->select($path);
    }

    /**
     * Builds a query grouped by a JSON path.
     *
     * @param string $path Path in `column->node` notation.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function groupByJsonPath(string $path): SelectQuery
    {
        return $this->find()->groupBy($path);
    }

    /**
     * Builds a query with a HAVING clause on a JSON path.
     *
     * @param string $path Path in `column->node` notation.
     * @param mixed $value The comparison value.
     * @param string $operator One of `=`, `!=`, `>`, `>=`, `<`, `<=`.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function havingJsonPath(string $path, mixed $value, string $operator = '='): SelectQuery
    {
        return $this->find()->having(["{$path} {$operator}" => $value]);
    }

    /**
     * Builds a query that aggregates a JSON path (COUNT / SUM / AVG / MIN / MAX).
     *
     * The aggregate is expressed as a plain function over the path string; the
     * dialect rewrites it during compilation. Numeric aggregates (SUM/AVG/MIN/MAX)
     * cast the extracted JSON value to a SQL numeric type so the math is correct;
     * COUNT returns the number of elements in the JSON array at the path (via the
     * engine's array-length function) rather than a row count.
     *
     * @param string $function One of `COUNT`, `SUM`, `AVG`, `MIN`, `MAX` (case-insensitive).
     * @param string $path Path in `column->node` notation.
     * @param string|null $alias Optional SQL alias for the projected column.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function aggregateJsonPath(string $function, string $path, ?string $alias = null): SelectQuery
    {
        $query = $this->find();
        $expression = "{$function}({$path})";

        return $alias !== null
            ? $query->select([$alias => $expression])
            : $query->select($expression);
    }

    /**
     * Builds a query filtered by JSON-equality (array / object) at a path.
     *
     * The left side is a path string (rewritten by the dialect trait); the right
     * side is an engine-cast JSON literal that the dialect trait does NOT
     * double-encode.
     *
     * @param string $path Path in `column->node` notation.
     * @param mixed $value The array or object value to match.
     * @param bool $negate When true, negates the equality (not equal).
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The configured query.
     */
    public function whereJsonPathJson(string $path, mixed $value, bool $negate = false): SelectQuery
    {
        $json = $this->jsonFieldEngine()->cast($value);

        return $this->find()->where(fn($exp) => $negate ? $exp->notEq($path, $json) : $exp->eq($path, $json));
    }

    /**
     * Builds the JSON-FK reference associations declared on this table's entity class.
     *
     * @return array<string, \Crustum\JsonField\ORM\Association\JsonFieldReference> References keyed by property name.
     */
    public function jsonFieldReferences(): array
    {
        return new JsonFieldAssociationBuilder()->buildReferences($this->getEntityClass(), $this);
    }

    /**
     * Resolves and assigns every declared JSON-FK reference onto the given entities.
     *
     * After a `find()` the foreign keys from the JSON columns are already decoded;
     * this method queries each target Table once (per reference) and populates the
     * root entities' reference properties.
     *
     * @param iterable<\Cake\Datasource\EntityInterface> $entities The root entities to populate.
     * @return void
     */
    public function loadJsonReferences(iterable $entities): void
    {
        $list = $entities instanceof EntityInterface ? [$entities] : iterator_to_array($entities);
        if ($list === []) {
            return;
        }

        $references = $this->jsonFieldReferences();
        if ($references === []) {
            return;
        }

        $loader = new JsonFieldReferenceLoader();
        foreach ($references as $association) {
            $loader->load($list, $association);
        }
    }

    /**
     * Adds a SQL join from this table to a JSON-FK reference's target table.
     *
     * The join condition uses a path string on the left side (rewritten by the
     * dialect trait) compared against the target table's primary key.
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query The query to modify.
     * @param string $name The reference property/association name.
     * @param string $joinType Join type, `INNER` or `LEFT`.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The modified query.
     * @throws \InvalidArgumentException When the named reference is not declared.
     */
    public function joinJsonReference(SelectQuery $query, string $name, string $joinType = 'INNER'): SelectQuery
    {
        $references = $this->jsonFieldReferences();
        if (!isset($references[$name])) {
            throw new InvalidArgumentException(sprintf('Unknown JSON-FK reference `%s`.', $name));
        }

        $association = $references[$name];
        $target = TableRegistry::getTableLocator()->get($association->getTargetClass());
        $primaryKey = (array)$target->getPrimaryKey();
        $primaryField = $primaryKey[0] ?? 'id';

        $foreignKeyPath = $association->getForeignKeyPath();
        $pathString = $this->getAlias() . '.' . $foreignKeyPath->column . '->' . implode('.', $foreignKeyPath->nodes);
        $joinAlias = 'jf_' . $name;

        $query->join([
            $joinAlias => [
                'table' => $target->getTable(),
                'alias' => $joinAlias,
                'type' => $joinType,
                'conditions' => $query->expr()->eq($pathString, new IdentifierExpression($joinAlias . '.' . $primaryField)),
            ],
        ]);

        return $query;
    }

    /**
     * Eager-loads JSON-FK references as part of query result hydration.
     *
     * Registers a `formatResults` formatter per requested reference that resolves the
     * foreign key(s) from each entity's already-hydrated JSON column (the dotted JSON
     * property, not a real SQL column) and assigns the matched target row(s). Unlike
     * `loadJsonReferences`, the population happens automatically as the query results
     * are produced — the JsonField analog of a Cake `contain()` eager load for a
     * JSON-stored foreign key (D8 read side; see `JsonFieldReference::eagerLoader`).
     *
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query The query to modify.
     * @param list<string> $names Reference property names to eager-load; empty loads all declared references.
     * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> The modified query.
     */
    public function eagerLoadJsonReferences(SelectQuery $query, array $names = []): SelectQuery
    {
        $references = $this->jsonFieldReferences();
        if ($names !== []) {
            $references = array_intersect_key($references, array_flip($names));
        }

        foreach ($references as $association) {
            $loader = $association->eagerLoader();
            $query->formatResults(static fn($results) => $results->map(static function ($entity) use ($loader) {
                $loader([$entity]);

                return $entity;
            }));
        }

        return $query;
    }
}
