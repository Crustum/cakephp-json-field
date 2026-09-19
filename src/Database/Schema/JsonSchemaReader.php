<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Schema;

use Cake\Datasource\EntityInterface;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Database\Schema\Attribute\JsonEmbed;
use Crustum\JsonField\Database\Schema\Attribute\JsonPathType;
use Crustum\JsonField\Path\JsonPath;
use Crustum\JsonField\Path\JsonPathParser;
use ReflectionClass;

/**
 * Reflects `#[JsonColumn]` / `#[JsonEmbed]` / `#[JsonPathType]` attributes on a
 * root document Entity class into a `JsonTypeMap`.
 *
 * This is the declarative, Mongo-attribute-style entry point. The root entity
 * declares its JSON storage columns (`#[JsonColumn]`) and the embeds living
 * inside them (`#[JsonEmbed]`, linking a JSON path to an embedded entity
 * class). Each embedded entity declares its own relative per-field types via
 * `#[JsonPathType]`; the reader prefixes those relative paths with the embed's
 * path to produce the full, engine-neutral type map (cached per class).
 */
class JsonSchemaReader
{
    /**
     * Cache of reflected type maps keyed by root entity class name.
     *
     * @var array<class-string, \Crustum\JsonField\Database\JsonTypeMap>
     */
    protected static array $cache = [];

    /**
     * Programmatically registered per-path casters keyed by root entity class then path.
     *
     * Attributes cannot carry closures, so callback `toPHP` / `toDatabase` casters
     * (the reference's `attributes->callback` style) are registered here and merged
     * into the reflected map. This keeps custom scalar marshalling in the database
     * layer without polluting the attribute declarations.
     *
     * @var array<class-string, array<string, array{toPHP?: callable|null, toDatabase?: callable|null}>>
     */
    protected static array $casters = [];

    /**
     * Reads a root entity class into a `JsonTypeMap` derived from its attributes.
     *
     * Root-level absolute `#[JsonPathType]` entries are added as-is. Each
     * `#[JsonEmbed]` is followed to its embedded entity class; that class's
     * relative `#[JsonPathType]` entries are prefixed with the embed path and
     * added to the map. The result is cached per class.
     *
     * @param class-string $rootEntityClass The root entity class to read.
     * @return \Crustum\JsonField\Database\JsonTypeMap The derived type map.
     */
    public static function read(string $rootEntityClass): JsonTypeMap
    {
        if (isset(self::$cache[$rootEntityClass])) {
            return self::$cache[$rootEntityClass];
        }

        $map = new JsonTypeMap();
        $reflection = new ReflectionClass($rootEntityClass);

        foreach ($reflection->getAttributes(JsonPathType::class) as $attribute) {
            /** @var \Crustum\JsonField\Database\Schema\Attribute\JsonPathType $pathType */
            $pathType = $attribute->newInstance();
            $type = $pathType->type();
            if ($type === null) {
                continue;
            }

            if (JsonPathParser::detect($pathType->path()) === 0) {
                continue;
            }

            $map->addJsonType($pathType->path(), $type);
        }

        foreach ($reflection->getAttributes(JsonEmbed::class) as $attribute) {
            /** @var \Crustum\JsonField\Database\Schema\Attribute\JsonEmbed $embed */
            $embed = $attribute->newInstance();
            $documentClass = $embed->documentClass();
            if (!is_a($documentClass, EntityInterface::class, true)) {
                continue;
            }

            $embedPath = JsonPathParser::parse($embed->path());
            $embedReflection = new ReflectionClass($documentClass);

            foreach ($embedReflection->getAttributes(JsonPathType::class) as $typeAttribute) {
                /** @var \Crustum\JsonField\Database\Schema\Attribute\JsonPathType $pathType */
                $pathType = $typeAttribute->newInstance();
                $type = $pathType->type();
                if ($type === null) {
                    continue;
                }

                $relativeNodes = JsonPathParser::parseNodes($pathType->path());
                $full = new JsonPath($embedPath->column, array_merge($embedPath->nodes, $relativeNodes));
                $map->addJsonType($full->toFieldPath(), $type);
            }
        }

        self::applyRegisteredCasters($rootEntityClass, $map);

        return self::$cache[$rootEntityClass] = $map;
    }

    /**
     * Merges any programmatically registered per-path casters into a reflected map.
     *
     * Registered callbacks (`toPHP` / `toDatabase`) override or complement an
     * attribute-declared type for the same path, enabling custom scalar
     * marshalling that attributes cannot encode.
     *
     * @param class-string $rootEntityClass The root entity class.
     * @param \Crustum\JsonField\Database\JsonTypeMap $map The reflected map to enrich.
     * @return void
     */
    private static function applyRegisteredCasters(string $rootEntityClass, JsonTypeMap $map): void
    {
        if (!isset(self::$casters[$rootEntityClass])) {
            return;
        }

        foreach (self::$casters[$rootEntityClass] as $path => $caster) {
            $definition = $map->type($path) ?? [];
            foreach ($caster as $operation => $callable) {
                if ($callable !== null) {
                    $definition[$operation] = $callable;
                }
            }

            $map->addJsonType($path, $definition);
        }
    }

    /**
     * Returns the JSON column names declared via `#[JsonColumn]`.
     *
     * @param class-string $rootEntityClass The root entity class to read.
     * @return list<string> The declared JSON column names.
     */
    public static function columns(string $rootEntityClass): array
    {
        $reflection = new ReflectionClass($rootEntityClass);
        $columns = [];

        foreach ($reflection->getAttributes(JsonColumn::class) as $attribute) {
            /** @var \Crustum\JsonField\Database\Schema\Attribute\JsonColumn $column */
            $column = $attribute->newInstance();
            $columns[] = $column->name();
        }

        return $columns;
    }

    /**
     * Registers programmatic per-path casters for a root entity class.
     *
     * Use this for custom `toPHP` / `toDatabase` marshalling that cannot be
     * expressed as a named type in an attribute (for example the reference
     * `attributes->callback` style). The casters merge into the reflected
     * `JsonTypeMap` on the next {@see read()} for the class.
     *
     * @param class-string $rootEntityClass The root entity class the casters apply to.
     * @param string $path Path in `column->node` notation.
     * @param callable|null $toPHP Database-to-PHP caster.
     * @param callable|null $toDatabase PHP-to-database caster.
     * @return void
     */
    public static function registerCaster(
        string $rootEntityClass,
        string $path,
        ?callable $toPHP = null,
        ?callable $toDatabase = null,
    ): void {
        $key = JsonPathParser::parse($path)->toFieldPath();
        self::$casters[$rootEntityClass][$key] = ['toPHP' => $toPHP, 'toDatabase' => $toDatabase];
    }

    /**
     * Clears the reflection cache and any registered casters.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = [];
        self::$casters = [];
    }
}
