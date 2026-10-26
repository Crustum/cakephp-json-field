<?php
declare(strict_types=1);

namespace Crustum\JsonField\Model\Behavior;

use ArrayObject;
use Cake\Database\Driver;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query\SelectQuery;
use Crustum\JsonField\Database\JsonFieldEngineResolver;
use Crustum\JsonField\Database\JsonTreeCaster;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder;
use Crustum\JsonField\ORM\JsonFieldResultSet;
use Crustum\JsonField\Path\JsonPath;

/**
 * Installs the JsonField hydration ResultSet and routes JSON-embed changes to
 * the database on save.
 *
 * On `Model.beforeFind` the behavior registers `JsonFieldResultSet` as the
 * query's ResultSet class so `#[JsonEmbed]` decoded arrays become nested
 * `JsonEntity` sub-entities. On `Model.beforeSave` it persists changes to those
 * nested entities:
 *
 * - A new entity (or one whose JSON storage column is dirty) is written as a
 *   whole column, with each embed exported and per-path `toDatabase` casters
 *   applied РІР‚вЂќ core `JsonType` encodes the result.
 * - An existing entity with dirty nested-embed fields is persisted with atomic,
 *   engine-native `set()` expressions (`jsonb_set` / `JSON_SET` / `json_set`),
 *   scoped to the single changed path, exactly like the Mongo ODM
 *   `Embedded::saveChild` analog. Other JSON keys in the column are preserved.
 */
class JsonFieldBehavior extends Behavior
{
    /**
     * Behavior configuration defaults.
     *
     * - `mergeJsonEmbeds`: when true, a plain-array value assigned to an embed
     *   property is deep-merged into the already-hydrated embed entity instead of
     *   replacing it whole. This makes `patchEntity` with partial embed data keep
     *   sibling fields. Default false (replace).
     *
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [
        'mergeJsonEmbeds' => false,
    ];

    /**
     * Installs the JsonField ResultSet class for the upcoming query.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event The beforeFind event.
     * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query The query about to be executed.
     * @param \ArrayObject<int|string, mixed> $options The find options.
     * @param bool $primary Whether this is the primary query.
     * @return void
     */
    public function beforeFind(EventInterface $event, SelectQuery $query, ArrayObject $options, bool $primary): void
    {
        $query->resultSetFactory()->setResultSetClass(JsonFieldResultSet::class);
    }

    /**
     * Persists JSON-embed changes before the row is written.
     *
     * Embeds declared via `#[JsonEmbed]` on the entity class are discovered and
     * either exported into the JSON storage column (new / whole-column write) or
     * written with atomic per-path `set()` expressions (existing row).
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event The beforeSave event.
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved.
     * @param \ArrayObject<int|string, mixed> $options The save options.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $entityClass = $entity::class;

        $embeds = new JsonFieldAssociationBuilder()->buildEmbeds($entityClass, $this->table());
        $references = new JsonFieldAssociationBuilder()->buildReferences($entityClass, $this->table());
        $columns = JsonSchemaReader::columns($entityClass);
        if ($columns === []) {
            return;
        }

        $typeMap = JsonSchemaReader::read($entityClass);
        $driver = $this->table()->getConnection()->getDriver();

        foreach ($references as $assoc) {
            $assoc->saveAssociated($entity, $typeMap, $driver, null, $this->table());
        }

        if ($entity->isNew() || $this->anyColumnDirty($entity, $columns)) {
            $this->exportTypedPathsToColumns($entity, $columns, $typeMap, $driver);
            $merge = (bool)$this->getConfig('mergeJsonEmbeds');
            foreach ($embeds as $assoc) {
                $assoc->exportToColumns($entity, $typeMap, $driver, $merge, $this->table());
            }

            return;
        }

        if ($embeds === []) {
            return;
        }

        $engine = new JsonFieldEngineResolver()->engine($driver);
        $merge = (bool)$this->getConfig('mergeJsonEmbeds');
        foreach ($embeds as $assoc) {
            $assoc->applyAtomicUpdates($entity, $typeMap, $engine, $driver, $this->table(), $merge);
        }
    }

    /**
     * Returns whether any of the JSON storage columns on the entity is dirty.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to inspect.
     * @param list<string> $columns The declared JSON column names.
     * @return bool True when at least one JSON column is dirty.
     */
    private function anyColumnDirty(EntityInterface $entity, array $columns): bool
    {
        return array_any($columns, fn(string $column): bool => $entity->isDirty($column));
    }

    /**
     * Applies per-path `toDatabase` casters to typed leaves living directly in
     * the root JSON columns, outside any `#[JsonEmbed]`.
     *
     * For example `attributes->callback` (a custom callback caster registered via
     * {@see \Crustum\JsonField\Database\Schema\JsonSchemaReader::registerCaster})
     * is cast here on a whole-column write. The column array is walked recursively
     * and every value whose full path has a type in the map is run through its
     * `toDatabase` caster; other JSON keys are preserved untouched.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved.
     * @param list<string> $columns The declared JSON column names.
     * @param \Crustum\JsonField\Database\JsonTypeMap $typeMap The per-path type map.
     * @param \Cake\Database\Driver|null $driver The active database driver.
     * @return void
     */
    private function exportTypedPathsToColumns(
        EntityInterface $entity,
        array $columns,
        JsonTypeMap $typeMap,
        ?Driver $driver,
    ): void {
        if ($typeMap->getTypes() === []) {
            return;
        }

        foreach ($columns as $column) {
            $value = $entity->get($column);
            if (!is_array($value)) {
                continue;
            }

            $cast = JsonTreeCaster::castTree($typeMap, new JsonPath($column, []), $value, $driver, $value);
            $entity->set($column, $cast);
        }
    }
}
