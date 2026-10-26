<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association\Loader;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Crustum\JsonField\ORM\Association\JsonFieldBelongsTo;
use Crustum\JsonField\ORM\Association\JsonFieldBelongsToMany;
use Crustum\JsonField\ORM\Association\JsonFieldReference;
use Crustum\JsonField\Path\JsonPath;
use InvalidArgumentException;

/**
 * Loader for JSON-FK reference associations (BelongsTo / BelongsToMany).
 *
 * Resolves a foreign key (scalar for `JsonFieldBelongsTo`, list for
 * `JsonFieldBelongsToMany`) stored inside the parent's JSON column by querying the
 * target Table for the matching rows and assigning them back onto the root entity.
 * This is the JsonField analog of Mongo's `DBRef` reference resolution, and is the
 * consumer counterpart to the SQL-join produced by the engine's `extract()` helper.
 */
class JsonFieldReferenceLoader implements JsonFieldReferenceLoaderInterface
{
    /**
     * Resolves and assigns the reference target onto each provided root entity.
     *
     * @param array<int, \Cake\Datasource\EntityInterface> $entities The root document entities to populate.
     * @param \Crustum\JsonField\ORM\Association\JsonFieldReference $association The reference association.
     * @return void
     * @throws \InvalidArgumentException When the association is not a supported reference type.
     */
    public function load(array $entities, JsonFieldReference $association): void
    {
        if ($association instanceof JsonFieldBelongsToMany) {
            $this->loadMany($entities, $association);

            return;
        }

        if ($association instanceof JsonFieldBelongsTo) {
            $this->loadOne($entities, $association);

            return;
        }

        throw new InvalidArgumentException('JsonFieldReferenceLoader only supports BelongsTo and BelongsToMany references.');
    }

    /**
     * Resolves a single-foreign-key reference (BelongsTo) onto each root entity.
     *
     * @param array<int, \Cake\Datasource\EntityInterface> $entities The root entities to populate.
     * @param \Crustum\JsonField\ORM\Association\JsonFieldBelongsTo $association The BelongsTo association.
     * @return void
     */
    private function loadOne(array $entities, JsonFieldBelongsTo $association): void
    {
        $fkPath = $association->getForeignKeyPath();
        $keys = $this->collectKeys($entities, $fkPath, false);
        if ($keys === []) {
            foreach ($entities as $entity) {
                $entity->set($association->getProperty(), null);
            }

            return;
        }

        $map = $this->fetchTargets($association, $keys);
        foreach ($entities as $entity) {
            $key = $this->readForeignKey($entity, $fkPath);
            $entity->set($association->getProperty(), $key !== null ? ($map[(string)$key] ?? null) : null);
        }
    }

    /**
     * Resolves a list-of-foreign-keys reference (BelongsToMany) onto each root entity.
     *
     * @param array<int, \Cake\Datasource\EntityInterface> $entities The root entities to populate.
     * @param \Crustum\JsonField\ORM\Association\JsonFieldBelongsToMany $association The BelongsToMany association.
     * @return void
     */
    private function loadMany(array $entities, JsonFieldBelongsToMany $association): void
    {
        $fkPath = $association->getForeignKeyPath();
        $keys = $this->collectKeys($entities, $fkPath, true);
        $map = $keys === [] ? [] : $this->fetchTargets($association, $keys);

        foreach ($entities as $entity) {
            $raw = $this->readForeignKey($entity, $fkPath);
            if (!is_iterable($raw)) {
                $entity->set($association->getProperty(), []);

                continue;
            }

            $related = [];
            foreach ($raw as $item) {
                if (isset($map[(string)$item])) {
                    $related[] = $map[(string)$item];
                }
            }

            $entity->set($association->getProperty(), $related);
        }
    }

    /**
     * Collects the distinct foreign-key values across the provided entities.
     *
     * @param array<int, \Cake\Datasource\EntityInterface> $entities The root entities.
     * @param \Crustum\JsonField\Path\JsonPath $fkPath The JSON path storing the foreign key.
     * @param bool $flatten When true, each foreign key is treated as a list and flattened.
     * @return array<int, int|string> The distinct key values.
     */
    private function collectKeys(array $entities, JsonPath $fkPath, bool $flatten): array
    {
        $keys = [];
        foreach ($entities as $entity) {
            $raw = $this->readForeignKey($entity, $fkPath);
            if ($raw === null) {
                continue;
            }

            if ($flatten && is_iterable($raw)) {
                foreach ($raw as $item) {
                    $keys[] = $item;
                }

                continue;
            }

            $keys[] = $raw;
        }

        return array_values(array_unique($keys, SORT_REGULAR));
    }

    /**
     * Fetches the target rows for the given foreign keys, keyed by primary key.
     *
     * @param \Crustum\JsonField\ORM\Association\JsonFieldReference $association The reference association.
     * @param array<int, int|string> $keys The foreign-key values to fetch.
     * @return array<string, \Cake\Datasource\EntityInterface> Targets keyed by string primary key.
     */
    private function fetchTargets(JsonFieldReference $association, array $keys): array
    {
        $target = TableRegistry::getTableLocator()->get($association->getTargetClass());
        $primaryKey = (array)$target->getPrimaryKey();
        $primaryField = $primaryKey[0] ?? 'id';

        $rows = $target
            ->find()
            ->where(fn($exp) => $exp->in($target->aliasField($primaryField), $keys))
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $map[(string)$row->get($primaryField)] = $row;
        }

        return $map;
    }

    /**
     * Reads the foreign-key value from a root entity's decoded JSON column.
     *
     * @param \Cake\Datasource\EntityInterface $entity The root entity.
     * @param \Crustum\JsonField\Path\JsonPath $fkPath The JSON path storing the foreign key.
     * @return mixed The foreign-key value, or null when the path is missing.
     */
    private function readForeignKey(EntityInterface $entity, JsonPath $fkPath): mixed
    {
        $value = $entity->get($fkPath->column);
        if (!is_array($value)) {
            return null;
        }

        foreach ($fkPath->nodes as $node) {
            if (is_array($value) && array_key_exists($node, $value)) {
                $value = $value[$node];

                continue;
            }

            return null;
        }

        return $value;
    }
}
