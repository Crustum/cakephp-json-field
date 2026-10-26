<?php
declare(strict_types=1);

namespace Crustum\JsonField\Model\Entity;

use Cake\Datasource\EntityInterface;
use Crustum\JsonField\ORM\Association\JsonFieldEmbed;
use Crustum\JsonField\Path\JsonPathParser;

/**
 * Thin path-string sugar layered over the standard CakePHP Entity API.
 *
 * Adds `column->node` / `node@column` access on top of the normal Entity
 * behaviour. Calls that are not paths (`get('name')`, `set('id', 1)`,
 * `isDirty('id')`) are passed straight through to the underlying Entity
 * implementation, so ordinary CakePHP usage is unchanged. Path calls resolve in
 * pure PHP against already-fetched JSON (no SQL), exactly like the Mongo ODM's
 * in-place embedded-document access.
 */
trait JsonFieldTrait
{
    /**
     * Reads a property or a `column->node` path against the nested tree.
     *
     * @param string $property A field name or a `column->node` / `node@column` path.
     * @param mixed $default Value returned when the path cannot be resolved.
     * @return mixed The resolved value or the default.
     */
    public function &get(string $property, mixed $default = null): mixed
    {
        if ((str_contains($property, '->') || str_contains($property, '@')) && JsonPathParser::detect($property) !== 0) {
            $found = false;
            $ref = &$this->getByPathRef($property, $found);
            if ($found) {
                return $ref;
            }

            $result = $this->getByPath($property, $default);

            return $result;
        }

        $value = &parent::get($property);
        if ($value === null && $default !== null && !$this->has($property)) {
            return $default;
        }

        return $value;
    }

    /**
     * Checks whether a property or a `column->node` path exists in the nested tree.
     *
     * @param array<string>|string $property A field name or a `column->node` / `node@column` path.
     * @return bool True when the target exists.
     */
    public function has(array|string $property): bool
    {
        if (!is_string($property) || ((!str_contains($property, '->') && !str_contains($property, '@')) || JsonPathParser::detect($property) === 0)) {
            return parent::has($property);
        }

        $parsed = JsonPathParser::parse($property);
        $current = $this->get($parsed->column);
        foreach ($parsed->nodes as $node) {
            if ($current instanceof JsonEntity) {
                if (!$current->has($node)) {
                    return false;
                }

                $current = $current->get($node);
            } elseif (is_array($current)) {
                if (!array_key_exists($node, $current)) {
                    return false;
                }

                $current = $current[$node];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes a property or a `column->node` path from the nested tree.
     *
     * Mirrors the Mongo ODM `EmbedOne` unset / `EmbedMany` pull: the targeted leaf
     * is removed from its owning container, and the owning parent is marked dirty
     * so the change routes to an atomic JSON-column write.
     *
     * @param array<string>|string $field A field name or a `column->node` / `node@column` path.
     * @return $this
     */
    public function unset(array|string $field): static
    {
        if (!is_string($field) || ((!str_contains($field, '->') && !str_contains($field, '@')) || JsonPathParser::detect($field) === 0)) {
            parent::unset($field);

            return $this;
        }

        $parsed = JsonPathParser::parse($field);
        $nodes = $parsed->nodes;
        $lastNode = array_pop($nodes);
        if ($lastNode === null) {
            return $this;
        }

        $columnValue = $this->get($parsed->column);
        if ($columnValue instanceof JsonEntity) {
            $columnValue->unset(implode('->', array_merge($nodes, [$lastNode])));

            return $this;
        }

        $root = is_array($columnValue) ? $columnValue : [];
        $ref = &$root;
        foreach ($nodes as $index => $node) {
            if (isset($ref[$node]) && $ref[$node] instanceof JsonEntity) {
                $remaining = array_slice($nodes, $index + 1);
                $remaining[] = $lastNode;
                $ref[$node]->unset(implode('->', $remaining));

                return $this;
            }

            if (!isset($ref[$node]) || !is_array($ref[$node])) {
                $ref[$node] = [];
            }

            $ref = &$ref[$node];
        }

        unset($ref[$lastNode]);
        $this->set($parsed->column, $root);

        return $this;
    }

    /**
     * Sets a property or a `column->node` path against the nested tree.
     *
     * CakePHP 6 narrowed `EntityInterface::set()` to a single field; mass
     * assignment lives on {@see patch()}. Path keys in bulk data are handled
     * by the {@see patch()} override below.
     *
     * @param string $field A field name or a `column->node` / `node@column` path.
     * @param mixed $value The value to set.
     * @param array<string, mixed> $options Options forwarded to the Entity setter.
     * @return static
     */
    public function set(string $field, mixed $value, array $options = []): static
    {
        if (!str_contains($field, '->') && !str_contains($field, '@')) {
            $result = parent::set($field, $value, $options);
            $this->markParentDirty();

            return $result;
        }

        if (JsonPathParser::detect($field) !== 0) {
            return $this->setByPath($field, $value, $options);
        }

        return parent::set($field, $value, $options);
    }

    /**
     * Mass-assigns fields, resolving `column->node` / `node@column` path keys.
     *
     * @param array<string, mixed> $values Map of fields or paths to values.
     * @param array<string, mixed> $options Options forwarded to the Entity setter.
     * @return static
     */
    public function patch(array $values, array $options = []): static
    {
        $paths = [];
        $plain = [];
        foreach ($values as $field => $value) {
            $field = (string)$field;
            if ((str_contains($field, '->') || str_contains($field, '@')) && JsonPathParser::detect($field) !== 0) {
                $paths[$field] = $value;
            } else {
                $plain[$field] = $value;
            }
        }

        if ($plain !== []) {
            parent::patch($plain, $options);
            $this->markParentDirty();
        }

        foreach ($paths as $path => $value) {
            $this->setByPath($path, $value, $options);
        }

        return $this;
    }

    /**
     * Checks the dirty status of the whole entity or a `column->node` path.
     *
     * @param string|null $field A field name or a `column->node` / `node@column` path. Null for the whole entity.
     * @return bool Whether the target is dirty.
     */
    public function isDirty(?string $field = null): bool
    {
        if ($field === null) {
            return parent::isDirty($field);
        }

        if (!str_contains($field, '->') && !str_contains($field, '@')) {
            return parent::isDirty($field);
        }

        if (JsonPathParser::detect($field) !== 0) {
            return $this->isDirtyByPath($field);
        }

        return parent::isDirty($field);
    }

    /**
     * Marks the owning parent's embed property dirty when this entity is embedded.
     *
     * Mirrors the Mongo ODM `embeddedParent` dirty propagation: a change inside a
     * nested `JsonEntity` must make the root document appear dirty so that
     * `Table::save()` proceeds to dispatch `Model.beforeSave` (where the change is
     * routed down to an atomic JSON-column write). The parent property marked is
     * the embed property (for example `profile`), never the JSON storage column,
     * so the SAVE path stays atomic rather than rewriting the whole column.
     *
     * @return void
     */
    protected function markParentDirty(): void
    {
        if (!isset($this->embeddedParent)) {
            return;
        }

        $parent = $this->embeddedParent['parent'];
        $association = $this->embeddedParent['association'];
        $parent->setDirty($association->getProperty(), true);
    }

    /**
     * Resolves a read path, descending through nested `JsonEntity` / array values.
     *
     * @param string $path A `column->node` / `node@column` path.
     * @param mixed $default Value returned when the path cannot be resolved.
     * @return mixed The resolved value or the default.
     */
    protected function getByPath(string $path, mixed $default = null): mixed
    {
        $parsed = JsonPathParser::parse($path);
        $current = $this->get($parsed->column);

        foreach ($parsed->nodes as $node) {
            if ($current instanceof JsonEntity) {
                $current = $current->get($node);
            } elseif (is_array($current)) {
                if (!array_key_exists($node, $current)) {
                    return $default;
                }

                $current = $current[$node];
            } else {
                return $default;
            }
        }

        return $current;
    }

    /**
     * Resolves a write path and assigns the value on the owning container.
     *
     * @param string $path A `column->node` / `node@column` path.
     * @param mixed $value The value to set.
     * @param array<string, mixed> $options Options forwarded to the Entity setter.
     * @return $this
     */
    protected function setByPath(string $path, mixed $value, array $options = []): static
    {
        $parsed = JsonPathParser::parse($path);
        $nodes = $parsed->nodes;
        $lastNode = array_pop($nodes);
        if ($lastNode === null) {
            return $this;
        }

        $columnValue = $this->get($parsed->column);

        if ($columnValue instanceof JsonEntity) {
            $columnValue->set(implode('->', array_merge($nodes, [$lastNode])), $value, $options);

            return $this;
        }

        $root = is_array($columnValue) ? $columnValue : [];
        $ref = &$root;
        foreach ($nodes as $index => $node) {
            if (isset($ref[$node]) && $ref[$node] instanceof JsonEntity) {
                $remaining = array_slice($nodes, $index + 1);
                $remaining[] = $lastNode;
                $ref[$node]->set(implode('->', $remaining), $value, $options);

                return $this;
            }

            if (!isset($ref[$node]) || !is_array($ref[$node])) {
                $ref[$node] = [];
            }

            $ref = &$ref[$node];
        }

        $ref[$lastNode] = $value;
        unset($ref);

        $this->set($parsed->column, $root, $options);

        return $this;
    }

    /**
     * Resolves a dirty-check path to its owning container.
     *
     * @param string $path A `column->node` / `node@column` path.
     * @return bool Whether the targeted leaf is dirty.
     */
    protected function isDirtyByPath(string $path): bool
    {
        $parsed = JsonPathParser::parse($path);
        $nodes = $parsed->nodes;
        $lastNode = array_pop($nodes);
        if ($lastNode === null) {
            return false;
        }

        $container = $this->get($parsed->column);
        foreach ($nodes as $node) {
            if ($container instanceof JsonEntity) {
                $container = $container->get($node);
            } elseif (is_array($container)) {
                $container = $container[$node] ?? null;
            } else {
                return false;
            }
        }

        return $container instanceof JsonEntity && $container->isDirty((string)$lastNode);
    }

    /**
     * Resolves a read path by reference, descending through nested `JsonEntity` /
     * array values so callers can mutate the resolved leaf in place.
     *
     * @param string $path A `column->node` / `node@column` path.
     * @param bool $found Set to false when the path cannot be resolved (caller should fall back).
     * @return mixed Reference to the resolved value, or a null reference when not found.
     */
    protected function &getByPathRef(string $path, bool &$found): mixed
    {
        $found = true;
        $parsed = JsonPathParser::parse($path);

        if (!$this->has($parsed->column)) {
            $found = false;
            $fallback = null;

            return $fallback;
        }

        $current = &$this->get($parsed->column);
        foreach ($parsed->nodes as $node) {
            if ($current instanceof JsonEntity) {
                $current = &$current->get($node);
            } elseif (is_array($current)) {
                if (!array_key_exists($node, $current)) {
                    $found = false;
                    $fallback = null;

                    return $fallback;
                }

                $current = &$current[$node];
            } else {
                $found = false;
                $fallback = null;

                return $fallback;
            }
        }

        return $current;
    }

    /**
     * Back-pointer to the owning parent entity and the association that produced it.
     *
     * @var array{parent: \Cake\Datasource\EntityInterface, association: \Crustum\JsonField\ORM\Association\JsonFieldEmbed}|null
     */
    protected ?array $embeddedParent = null;

    /**
     * Whether this embedded entity has been marked for removal via {@see delete()}.
     *
     * @var bool
     */
    protected bool $markedForDeletion = false;

    /**
     * Sets the embedded parent back-pointer on this entity.
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent entity that owns this embed.
     * @param \Crustum\JsonField\ORM\Association\JsonFieldEmbed $association The association that produced this embed.
     * @return $this
     */
    public function setEmbeddedParent(EntityInterface $parent, JsonFieldEmbed $association): static
    {
        $this->embeddedParent = ['parent' => $parent, 'association' => $association];

        return $this;
    }

    /**
     * Returns the embedded parent back-pointer, if this entity is embedded.
     *
     * @return array{parent: \Cake\Datasource\EntityInterface, association: \Crustum\JsonField\ORM\Association\JsonFieldEmbed}|null
     */
    public function getEmbeddedParent(): ?array
    {
        return $this->embeddedParent;
    }

    /**
     * Returns whether this entity is embedded inside another entity.
     *
     * @return bool True when an embedded parent back-pointer is set.
     */
    public function isEmbedded(): bool
    {
        return $this->embeddedParent !== null;
    }

    /**
     * Marks this embedded entity for removal from its owning parent.
     *
     * Routes the removal down to the owning `JsonFieldEmbed` association so the
     * element is dropped from the parent's in-memory list immediately and the
     * parent's embed property is marked dirty (triggering an atomic rewrite of the
     * JSON column on save). Mirrors the Mongo ODM `Embedded::deleteChild` trigger.
     *
     * @return $this
     */
    public function delete(): static
    {
        if (isset($this->embeddedParent)) {
            $parent = $this->embeddedParent['parent'];
            $association = $this->embeddedParent['association'];
            $association->deleteChild($parent, $this);
        }

        $this->markedForDeletion = true;
        $this->markParentDirty();

        return $this;
    }

    /**
     * Returns whether this entity has been marked for deletion via {@see delete()}.
     *
     * @return bool True when deletion is pending.
     */
    public function isMarkedForDeletion(): bool
    {
        return $this->markedForDeletion;
    }
}
