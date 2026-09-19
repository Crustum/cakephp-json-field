<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM;

use Cake\Database\Driver;
use Cake\Datasource\EntityInterface;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder;
use Crustum\JsonField\Path\JsonPath;

/**
 * Applies per-path type casts from a JsonTypeMap to hydrated embedded entities.
 *
 * After `JsonFieldResultSet` hydrates `#[JsonEmbed]` decoded arrays into nested
 * `JsonEntity` sub-entities, this caster walks each embed (and its list elements)
 * and runs the registered `toPHP` caster for every field whose full path has a
 * type in the map. The full path is the embed's JSON path joined with the relative
 * field name, mirroring how `JsonSchemaReader` prefixes relative `#[JsonPathType]`
 * entries. This is the per-path counterpart of the Mongo ODM field type casting and
 * the `decorateResults` phase described in the design.
 */
class JsonFieldTypeCaster
{
    /**
     * Casts every declared embed field on the root entity to its PHP type.
     *
     * @param \Cake\Datasource\EntityInterface $root The root document entity.
     * @param \Crustum\JsonField\Database\JsonTypeMap $map Per-path type map for the root class.
     * @param \Cake\Database\Driver|null $driver Database driver; required for `toPHP` casts.
     * @return void
     */
    public function castEmbeds(EntityInterface $root, JsonTypeMap $map, ?Driver $driver = null): void
    {
        if ($map->getTypes() === []) {
            return;
        }

        $embeds = (new JsonFieldAssociationBuilder())->buildEmbeds($root::class);
        foreach ($embeds as $association) {
            $embedded = $root->get($association->getProperty());
            if ($embedded === null) {
                continue;
            }

            $prefix = $association->resolvePath();
            $this->castNode($embedded, $map, $driver, $prefix);
        }

        $this->castTypedColumns($root, $map, $driver);
    }

    /**
     * Casts per-path typed leaves that live directly in the root JSON columns
     * (outside any `#[JsonEmbed]`), for example `attributes->callback`. The
     * decoded column array is walked recursively and every value whose full path
     * has a registered type is run through its `toPHP` caster.
     *
     * @param \Cake\Datasource\EntityInterface $root The root document entity.
     * @param \Crustum\JsonField\Database\JsonTypeMap $map Per-path type map for the root class.
     * @param \Cake\Database\Driver|null $driver Database driver; required for `toPHP` casts.
     * @return void
     */
    private function castTypedColumns(EntityInterface $root, JsonTypeMap $map, ?Driver $driver): void
    {
        foreach (JsonSchemaReader::columns($root::class) as $column) {
            $value = $root->get($column);
            if (!is_array($value)) {
                continue;
            }

            $cast = $this->castArray($value, $map, $driver, new JsonPath($column, []));
            $root->set($column, $cast, ['setter' => false]);
        }
    }

    /**
     * Recursively casts an array tree, applying `toPHP` casters at typed leaves.
     *
     * @param array<string, mixed> $tree The decoded column array.
     * @param \Crustum\JsonField\Database\JsonTypeMap $map The type map.
     * @param \Cake\Database\Driver|null $driver The database driver.
     * @param \Crustum\JsonField\Path\JsonPath $prefix Full JSON path prefix for the current node.
     * @return array<string, mixed> The cast array tree.
     */
    private function castArray(array $tree, JsonTypeMap $map, ?Driver $driver, JsonPath $prefix): array
    {
        $out = [];
        foreach ($tree as $key => $item) {
            $childPrefix = new JsonPath($prefix->column, array_merge($prefix->nodes, [(string)$key]));
            if (is_array($item) && array_is_list($item) === false) {
                $out[$key] = $this->castArray($item, $map, $driver, $childPrefix);
            } elseif (is_array($item)) {
                $castList = [];
                foreach ($item as $index => $element) {
                    $castList[$index] = is_array($element)
                        ? $this->castArray($element, $map, $driver, $childPrefix)
                        : $element;
                }

                $out[$key] = $castList;
            } else {
                $pathKey = $childPrefix->toFieldPath();
                $out[$key] = $map->hasType($pathKey)
                    ? $map->castValue($pathKey, $item, JsonTypeMap::OPERATION_TO_PHP, $driver)
                    : $item;
            }
        }

        return $out;
    }

    /**
     * Casts one node, recursing through lists and nested entities.
     *
     * @param mixed $node The value to cast (entity, list of entities, or scalar).
     * @param \Crustum\JsonField\Database\JsonTypeMap $map The type map.
     * @param \Cake\Database\Driver|null $driver The database driver.
     * @param \Crustum\JsonField\Path\JsonPath $prefix Full JSON path prefix for the current node.
     * @return void
     */
    private function castNode(mixed $node, JsonTypeMap $map, ?Driver $driver, JsonPath $prefix): void
    {
        if (is_iterable($node)) {
            foreach ($node as $item) {
                $this->castNode($item, $map, $driver, $prefix);
            }

            return;
        }

        if (!$node instanceof EntityInterface) {
            return;
        }

        foreach (array_keys($node->toArray()) as $field) {
            $path = new JsonPath($prefix->column, array_merge($prefix->nodes, [$field]));
            $key = $path->toFieldPath();
            if (!$map->hasType($key)) {
                continue;
            }

            $value = $node->get($field);
            if ($value === null) {
                continue;
            }

            $cast = $map->castValue($key, $value, JsonTypeMap::OPERATION_TO_PHP, $driver);
            $node->set($field, $cast, ['setter' => false]);
        }
    }
}
