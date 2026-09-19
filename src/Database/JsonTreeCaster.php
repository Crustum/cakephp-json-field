<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

use Cake\Database\Driver;
use Crustum\JsonField\Model\Entity\JsonEntity;
use Crustum\JsonField\Path\JsonPath;

/**
 * Shared helpers for casting an embed value tree to its database representation
 * and writing a value at a nested JSON path.
 *
 * Used by both the behavior (root typed-path export) and the embed associations
 * once persistence was encapsulated into the association layer (D1). Keeping the
 * tree-walk logic in one place avoids drift between the two call sites.
 */
class JsonTreeCaster
{
    /**
     * Recursively converts an embed value into a database-ready array, applying
     * per-path `toDatabase` casters on each leaf.
     *
     * @param \Crustum\JsonField\Database\JsonTypeMap $typeMap The per-path type map.
     * @param \Crustum\JsonField\Path\JsonPath $base The base path of the current value.
     * @param mixed $value The value (entity, list, array, or scalar leaf).
     * @param \Cake\Database\Driver|null $driver The active database driver.
     * @param mixed $context Optional context forwarded to casters.
     * @return mixed The cast, database-ready value.
     */
    public static function castTree(
        JsonTypeMap $typeMap,
        JsonPath $base,
        mixed $value,
        ?Driver $driver,
        mixed $context = null,
    ): mixed {
        if ($value instanceof JsonEntity) {
            return self::castTree($typeMap, $base, $value->toArray(), $driver, $context);
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $key => $item) {
                $childBase = new JsonPath($base->column, array_merge($base->nodes, [(string)$key]));
                $out[$key] = self::castTree($typeMap, $childBase, $item, $driver, $context);
            }

            return $out;
        }

        $path = $base->toFieldPath();
        if ($typeMap->hasType($path)) {
            return $typeMap->castValue($path, $value, JsonTypeMap::OPERATION_TO_DATABASE, $driver, $context);
        }

        return $value;
    }

    /**
     * Inserts a value at a nested path inside a column array.
     *
     * @param array<string, mixed> $data The column array to modify.
     * @param list<array-key> $nodes The path nodes to descend.
     * @param mixed $value The value to place at the leaf.
     * @return array<string, mixed> The modified column array.
     */
    public static function insertAtPath(array $data, array $nodes, mixed $value): array
    {
        $reference = &$data;
        foreach ($nodes as $node) {
            if (!isset($reference[$node]) || !is_array($reference[$node])) {
                $reference[$node] = [];
            }

            $reference = &$reference[$node];
        }

        $reference = $value;
        unset($reference);

        return $data;
    }
}
