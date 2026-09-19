<?php
declare(strict_types=1);

namespace Crustum\JsonField\Path;

use Crustum\JsonField\Path\Exception\UnparsableJsonPathException;

/**
 * Parses path notation into a {@see JsonPath} value object.
 *
 * Supports V2 (`column->node1.node2`), V1 (`node1.node2@column`) and the
 * joker / index notations (`column->items[*].sku`, `column->items[0]`).
 * Results are cached per input string (pure, engine-neutral).
 */
final class JsonPathParser
{
    /**
     * Cache of parsed paths keyed by the original input string.
     *
     * @var array<string, \Crustum\JsonField\Path\JsonPath>
     */
    private static array $cache = [];

    /**
     * Detects whether a field is a path and which notation it uses.
     *
     * @param string $field The field to inspect
     * @return int 0 for non-path strings, 1 for `path@column`, 2 for `column->path`
     */
    public static function detect(string $field): int
    {
        if (preg_match('/^[\w\.\*\[\]]+(@|->)[\w\.\*\[\]]+$/i', $field) === 1) {
            return str_contains($field, '@') ? 1 : 2;
        }

        return 0;
    }

    /**
     * Parses a path string into a {@see JsonPath}.
     *
     * @param string $path The path string to parse
     * @return \Crustum\JsonField\Path\JsonPath The parsed path value object
     * @throws \Crustum\JsonField\Path\Exception\UnparsableJsonPathException when the string is not a valid path
     */
    public static function parse(string $path): JsonPath
    {
        if (array_key_exists($path, self::$cache)) {
            return self::$cache[$path];
        }

        return self::$cache[$path] = self::parseUncached($path);
    }

    /**
     * Splits a dotted / indexed / joker path into its node list.
     *
     * @param string $path The path to split
     * @return list<array-key> The extracted nodes
     */
    public static function parseNodes(string $path): array
    {
        $nodes = preg_split('/[\.\[\]]/', $path, 0, PREG_SPLIT_NO_EMPTY);

        return $nodes === false ? [] : $nodes;
    }

    /**
     * Parses a path string without consulting the cache.
     *
     * @param string $path The path string to parse
     * @return \Crustum\JsonField\Path\JsonPath The parsed path value object
     * @throws \Crustum\JsonField\Path\Exception\UnparsableJsonPathException when the string is not a valid path
     */
    private static function parseUncached(string $path): JsonPath
    {
        $type = self::detect($path);
        if ($type === 0) {
            throw new UnparsableJsonPathException($path);
        }

        if ($type === 2) {
            [$fieldPart, $pathPart] = explode('->', $path, 2);
        } else {
            [$pathPart, $fieldPart] = explode('@', $path, 2);
        }

        $column = $fieldPart;

        return new JsonPath($column, self::parseNodes($pathPart));
    }
}
