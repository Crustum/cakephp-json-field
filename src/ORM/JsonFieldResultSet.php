<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM;

use Cake\Database\Driver;
use Cake\Datasource\EntityInterface;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder;
use Crustum\JsonField\Path\JsonPath;

/**
 * ResultSet that post-processes already-hydrated root document entities.
 *
 * Cake's `ResultSetFactory::createResultSet()` hydrates each row before the
 * `ResultSet` is built, so `current()` sees fully-hydrated entities. This class
 * overrides `current()` to deserialize every `#[JsonEmbed]` decoded array into a
 * nested `JsonEntity` sub-entity carrying an `embeddedParent` back-pointer — the
 * exact analog of Mongo ODM's `ODM\ResultSet::current()` → `groupResult()` →
 * `Embedded::hydrateEmbedded()`. Entities without `#[JsonEmbed]` are returned
 * untouched, making per-query install safe for any table.
 *
 * @extends \Cake\ORM\ResultSet<array-key, mixed>
 */
class JsonFieldResultSet extends ResultSet
{
    /**
     * Per-entity-class cache of embed associations discovered via attributes.
     *
     * @var array<class-string, array<string, \Crustum\JsonField\ORM\Association\JsonFieldEmbed>>
     */
    private array $embedCache = [];

    /**
     * Tracks which result rows have already been hydrated to avoid re-work.
     *
     * @var array<int|string, true>
     */
    private array $hydrated = [];

    /**
     * Returns the current entity, hydrating its embedded associations first.
     *
     * @return mixed The (possibly post-processed) current result row.
     */
    public function current(): mixed
    {
        $entity = parent::current();

        if ($entity instanceof EntityInterface) {
            $key = $this->key();
            if (!isset($this->hydrated[$key])) {
                $this->hydrateEmbeds($entity);
                $this->castEmbeds($entity);
                $this->hydrated[$key] = true;
            }
        }

        return $entity;
    }

    /**
     * Hydrates every declared embed on the root entity into a nested JsonEntity.
     *
     * @param \Cake\Datasource\EntityInterface $entity The root document entity.
     * @return void
     */
    private function hydrateEmbeds(EntityInterface $entity): void
    {
        $class = $entity::class;
        $this->embedCache[$class] ??= new JsonFieldAssociationBuilder()->buildEmbeds($class);

        foreach ($this->embedCache[$class] as $association) {
            $raw = $this->extractRaw($entity, $association->resolvePath());
            $hydrated = $association->hydrateEmbedded($raw, $entity);
            $entity->set($association->getProperty(), $hydrated, ['setter' => false]);
            $entity->setDirty($association->getProperty(), false);
        }
    }

    /**
     * Applies per-path type casts to the hydrated embedded entities.
     *
     * @param \Cake\Datasource\EntityInterface $entity The root document entity.
     * @return void
     */
    private function castEmbeds(EntityInterface $entity): void
    {
        $map = JsonSchemaReader::read($entity::class);
        if ($map->getTypes() === []) {
            return;
        }

        $driver = $this->resolveDriver($entity);
        if (!$driver instanceof Driver) {
            return;
        }

        new JsonFieldTypeCaster()->castEmbeds($entity, $map, $driver);
    }

    /**
     * Resolves the database driver for the root entity's source table, if reachable.
     *
     * @param \Cake\Datasource\EntityInterface $entity The root document entity.
     * @return \Cake\Database\Driver|null The driver, or null when it cannot be resolved.
     */
    private function resolveDriver(EntityInterface $entity): ?Driver
    {
        $source = $entity->getSource();
        if ($source === '') {
            return null;
        }

        $locator = TableRegistry::getTableLocator();
        if (!$locator->exists($source)) {
            return null;
        }

        return $locator->get($source)->getConnection()->getDriver();
    }

    /**
     * Navigates the decoded JSON column array to the raw value at the embed path.
     *
     * @param \Cake\Datasource\EntityInterface $entity The root document entity.
     * @param \Crustum\JsonField\Path\JsonPath $path The embed JSON path.
     * @return mixed The raw value at the path, or null when any node is missing.
     */
    private function extractRaw(EntityInterface $entity, JsonPath $path): mixed
    {
        $value = $entity->get($path->column);
        if (!is_array($value)) {
            return null;
        }

        foreach ($path->nodes as $node) {
            if (is_array($value) && array_key_exists($node, $value)) {
                $value = $value[$node];

                continue;
            }

            return null;
        }

        return $value;
    }
}
