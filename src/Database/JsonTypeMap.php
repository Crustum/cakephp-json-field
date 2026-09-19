<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

use Cake\Database\Driver;
use Cake\Database\TypeFactory;
use Crustum\JsonField\Path\JsonPathParser;
use InvalidArgumentException;

/**
 * Engine-neutral per-path type map for JSON columns.
 *
 * Maps a path (for example `data->created`) to a type definition that drives
 * marshalling and per-path casting (`marshal` / `toPHP` / `toDatabase`). The
 * whole-column JSON encode/decode stays with the core `JsonType`; this map
 * only handles the value addressed by a path. Definitions can be declared
 * programmatically or derived from attributes by `JsonSchemaReader`.
 */
class JsonTypeMap
{
    /**
     * Marshalling operation: converts a raw request value into its PHP representation.
     */
    public const OPERATION_MARSHAL = 'marshal';

    /**
     * Database-to-PHP operation: converts a stored database value into its PHP representation.
     */
    public const OPERATION_TO_PHP = 'toPHP';

    /**
     * PHP-to-database operation: converts a PHP value into its stored database representation.
     */
    public const OPERATION_TO_DATABASE = 'toDatabase';

    /**
     * Registered type definitions keyed by their canonical path.
     *
     * @var array<string, array{type?: string, marshal?: callable|null, toPHP?: callable|null, toDatabase?: callable|null}>
     */
    protected array $types = [];

    /**
     * Registers a per-path type definition, replacing any existing one.
     *
     * @param string $path Path in `column->node` notation (or any supported variant)
     * @param array{type?: string, marshal?: callable, toPHP?: callable, toDatabase?: callable}|string $type Type name or definition
     * @return $this
     */
    public function addJsonType(string $path, string|array $type): static
    {
        $key = $this->normalizeKey($path);

        if (is_string($type)) {
            $type = ['type' => $type];
        }

        $type += ['marshal' => null, 'toPHP' => null, 'toDatabase' => null];
        $this->types[$key] = $type;

        return $this;
    }

    /**
     * Registers several per-path type definitions at once.
     *
     * @param array<string, mixed> $types Map of path => type definition
     * @return $this
     */
    public function addJsonTypes(array $types): static
    {
        foreach ($types as $path => $type) {
            $this->addJsonType($path, $type);
        }

        return $this;
    }

    /**
     * Returns true when a type definition is registered for the given path.
     *
     * @param string $path Path in `column->node` notation (or any supported variant)
     * @return bool True when the path has a registered type.
     */
    public function hasType(string $path): bool
    {
        return isset($this->types[$this->normalizeKey($path)]);
    }

    /**
     * Returns the registered type definition for a path or null when absent.
     *
     * @param string $path Path in `column->node` notation (or any supported variant)
     * @return array{type?: string, marshal?: callable|null, toPHP?: callable|null, toDatabase?: callable|null}|null The definition or null.
     */
    public function type(string $path): ?array
    {
        return $this->types[$this->normalizeKey($path)] ?? null;
    }

    /**
     * Returns all registered type definitions keyed by canonical path.
     *
     * @return array<string, array{type?: string, marshal?: callable|null, toPHP?: callable|null, toDatabase?: callable|null}> The full map.
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Builds a caster callable for the given path and operation, or null when none exists.
     *
     * @param string $path Path in `column->node` notation (or any supported variant)
     * @param string $operation One of the OPERATION_* constants
     * @return callable|null The caster or null.
     */
    public function getCaster(string $path, string $operation): ?callable
    {
        $definition = $this->type($path);
        if ($definition === null) {
            return null;
        }

        return $this->buildCaster($definition, $operation);
    }

    /**
     * Applies the registered caster for a path, returning the value unchanged when none exists.
     *
     * @param string $path Path in `column->node` notation (or any supported variant)
     * @param string $operation One of the OPERATION_* constants
     * @param \Cake\Database\Driver|null $driver Database driver; required for `toPHP` / `toDatabase`
     * @return mixed The cast value, or the original value when no caster is registered.
     */
    public function castValue(string $path, mixed $value, string $operation, ?Driver $driver = null, mixed $context = null): mixed
    {
        $caster = $this->getCaster($path, $operation);
        if ($caster === null) {
            return $value;
        }

        return $caster($value, $driver, $context);
    }

    /**
     * Removes all registered type definitions.
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->types = [];

        return $this;
    }

    /**
     * Normalizes a path to its canonical `column->node` form.
     *
     * @param string $path Path in any supported notation
     * @return string The canonical path.
     */
    protected function normalizeKey(string $path): string
    {
        return JsonPathParser::parse($path)->toFieldPath();
    }

    /**
     * Builds a caster callable from a resolved type definition.
     *
     * @param array{type?: string, marshal?: callable|null, toPHP?: callable|null, toDatabase?: callable|null} $definition Type definition
     * @param string $operation One of the OPERATION_* constants
     * @return callable The caster.
     * @throws \InvalidArgumentException When no caster can be resolved from the definition.
     */
    protected function buildCaster(array $definition, string $operation): callable
    {
        if (isset($definition[$operation]) && is_callable($definition[$operation])) {
            return $definition[$operation];
        }

        if (isset($definition['type']) && $definition['type'] !== '') {
            $typeName = $definition['type'];
            if (TypeFactory::getMapped($typeName) !== null) {
                $type = TypeFactory::build($typeName);

                return function (mixed $value, ?Driver $driver = null) use ($type, $operation) {
                    if ($operation === self::OPERATION_MARSHAL) {
                        return $type->marshal($value);
                    }

                    if (!$driver instanceof Driver) {
                        throw new InvalidArgumentException(sprintf(
                            'A database driver is required to cast path values using operation `%s`.',
                            $operation,
                        ));
                    }

                    if ($operation === self::OPERATION_TO_DATABASE) {
                        return $type->toDatabase($value, $driver);
                    }

                    return $type->toPHP($value, $driver);
                };
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Unable to resolve a caster for operation `%s` from the JSON type definition.',
            $operation,
        ));
    }
}
