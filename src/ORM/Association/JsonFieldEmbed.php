<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

use Cake\Database\Driver;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Query\UpdateQuery;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Crustum\JsonField\Database\JsonFieldEngineInterface;
use Crustum\JsonField\Database\JsonTreeCaster;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\Model\Entity\JsonEntity;
use Crustum\JsonField\Path\JsonPath;
use Crustum\JsonField\Path\JsonPathParser;
use InvalidArgumentException;

/**
 * Base class for associations whose data is a nested document inside the parent's
 * JSON column.
 *
 * Embedded associations never issue a secondary query and always use the `embed`
 * strategy (the analog of Mongo's `Embedded`). Hydration is in-place against the
 * already-fetched row; persistence routes child writes down to the owning JSON
 * column via the `embeddedParent` back-pointer.
 */
abstract class JsonFieldEmbed extends JsonFieldAssociation
{
    /**
     * Embedded associations only support the embed strategy.
     *
     * @var array<string>
     */
    protected array $validStrategies = [self::STRATEGY_EMBED];

    /**
     * Optional validator invoked for each embedded entity before it is persisted.
     *
     * Mirrors Mongo ODM `Embedded::embeddedValidator` (run before `saveChild`). The
     * callable receives the hydrated `JsonEntity` (or the plain array for a patch) and
     * should throw on invalid data; it may also be used for side effects such as
     * normalisation. Null when no validation is configured.
     *
     * @var callable|null
     */
    protected $embeddedValidator;

    /**
     * @inheritDoc
     */
    protected function defaultStrategy(): string
    {
        return self::STRATEGY_EMBED;
    }

    /**
     * Embedded associations always use the embed strategy.
     *
     * @return string The embed strategy constant.
     */
    public function getStrategy(): string
    {
        return self::STRATEGY_EMBED;
    }

    /**
     * Registers a validator invoked for each embedded entity before persistence.
     *
     * The callable receives the hydrated `JsonEntity` (or the plain array for a patch)
     * and should throw on invalid data. Invoked from `saveAssociated` / `exportToColumns`
     * / `applyAtomicUpdates` before the value is written.
     *
     * @param callable $validator The embedded validator callable.
     * @return $this
     */
    public function setEmbeddedValidator(callable $validator): static
    {
        $this->embeddedValidator = $validator;

        return $this;
    }

    /**
     * Returns the registered embedded validator, if any.
     *
     * @return callable|null The validator callable, or null when not configured.
     */
    public function getEmbeddedValidator(): ?callable
    {
        return $this->embeddedValidator;
    }

    /**
     * Rejects any strategy other than `embed`.
     *
     * @param string $strategy The requested strategy.
     * @return $this
     * @throws \InvalidArgumentException When the strategy is not `embed`.
     */
    public function setStrategy(string $strategy): static
    {
        if ($strategy !== self::STRATEGY_EMBED) {
            throw new InvalidArgumentException('Embedded associations only support the embed strategy.');
        }

        return $this;
    }

    /**
     * Returns the target repository, which for embeds is the source itself.
     *
     * @return \Cake\Datasource\RepositoryInterface|null The source repository.
     */
    public function getTarget(): ?RepositoryInterface
    {
        return $this->source;
    }

    /**
     * Returns the JSON path where the embed's data is stored.
     *
     * @return \Crustum\JsonField\Path\JsonPath The parsed embed path.
     */
    public function resolvePath(): JsonPath
    {
        return JsonPathParser::parse($this->path);
    }

    /**
     * Returns the write location for the whole embed (EmbedOne) or the array
     * container (EmbedMany).
     *
     * @return \Crustum\JsonField\Path\JsonPath The write path.
     */
    public function writePath(): JsonPath
    {
        return $this->resolvePath();
    }

    /**
     * Returns the write location for a specific child element.
     *
     * EmbedOne returns the embed path; EmbedMany appends the child key so writes
     * target a single array element.
     *
     * @param array-key $childKey The index or key identifying the child.
     * @return \Crustum\JsonField\Path\JsonPath The child write path.
     */
    public function writePathForChild(mixed $childKey): JsonPath
    {
        $path = $this->resolvePath();
        $nodes = $path->nodes;
        $nodes[] = $childKey;

        return new JsonPath($path->column, $nodes);
    }

    /**
     * Hydrates the raw embedded value (decoded from JSON) into one or more nested
     * entities, setting the `embeddedParent` back-pointer.
     *
     * @param mixed $raw The raw embedded value from the parent row.
     * @param \Cake\Datasource\EntityInterface $parent The parent document entity.
     * @param array<string, mixed> $options Hydration options forwarded to entities.
     * @return mixed A single entity (ONE_TO_ONE), a list of entities (ONE_TO_MANY), or null.
     */
    public function hydrateEmbedded(mixed $raw, EntityInterface $parent, array $options = []): mixed
    {
        if ($raw === null) {
            return null;
        }

        if ($this->type() === self::TYPE_ONE_TO_MANY) {
            if (!is_iterable($raw)) {
                return [];
            }

            $entities = [];
            foreach ($raw as $item) {
                $entities[] = $this->embeddedDocument((array)$item, $parent, $options);
            }

            return $entities;
        }

        return $this->embeddedDocument((array)$raw, $parent, $options);
    }

    /**
     * Builds one embedded entity with its parent back-pointer set.
     *
     * @param array<string, mixed> $data The embedded data.
     * @param \Cake\Datasource\EntityInterface $parent The parent document entity.
     * @param array<string, mixed> $options Hydration options forwarded to the entity.
     * @return \Crustum\JsonField\Model\Entity\JsonEntity The hydrated embedded entity.
     */
    protected function embeddedDocument(array $data, EntityInterface $parent, array $options): JsonEntity
    {
        $class = $this->className;
        if (!is_a($class, JsonEntity::class, true)) {
            throw new InvalidArgumentException('Embedded entity class must extend JsonEntity.');
        }

        $document = new $class($data, $options + ['markClean' => true, 'markNew' => false]);
        $document->setEmbeddedParent($parent, $this);

        return $document;
    }

    /**
     * Persists this embedded association's data for the given parent entity.
     *
     * Encapsulates the write logic that previously lived inline in
     * `JsonFieldBehavior::beforeSave` (D1). The behavior dispatches this method
     * for every dirty `#[JsonEmbed]` property, mirroring the ODM
     * `AssociationCollection::save()` dirty-gating. The actual strategy (whole
     * column vs atomic) is chosen by the caller and routed to the two helpers
     * below.
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param \Crustum\JsonField\Database\JsonTypeMap $typeMap The per-path type map.
     * @param \Cake\Database\Driver|null $driver The active database driver.
     * @param \Crustum\JsonField\Database\JsonFieldEngineInterface|null $engine The engine adapter (null for whole-column writes).
     * @param \Cake\ORM\Table $table The owning table.
     * @return void
     */
    public function saveAssociated(
        EntityInterface $parent,
        JsonTypeMap $typeMap,
        ?Driver $driver,
        ?JsonFieldEngineInterface $engine,
        Table $table,
    ): void {
        if (!$engine instanceof JsonFieldEngineInterface) {
            $this->exportToColumns($parent, $typeMap, $driver, false, $table);

            return;
        }

        $this->applyAtomicUpdates($parent, $typeMap, $engine, $driver, $table);
    }

    /**
     * Exports the embedded value into its JSON storage column as a whole-column write.
     *
     * Each embed value (a `JsonEntity`, a list of `JsonEntity`, or a plain array)
     * is recursively cast with the per-path `toDatabase` casters and merged into the
     * current column array so unrelated JSON keys are preserved. When `idField` is
     * configured, new list elements without an id are assigned one (D2, mirroring
     * Laravel's auto `_id` on embedded insert).
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param \Crustum\JsonField\Database\JsonTypeMap $typeMap The per-path type map.
     * @param \Cake\Database\Driver|null $driver The active database driver.
     * @param \Cake\ORM\Table|null $table The owning table (used for merge strategies).
     * @return void
     */
    public function exportToColumns(
        EntityInterface $parent,
        JsonTypeMap $typeMap,
        ?Driver $driver,
        bool $merge = false,
        ?Table $table = null,
    ): void {
        $value = $parent->get($this->getProperty());
        if ($value === null) {
            if (!$parent->isDirty($this->getProperty())) {
                return;
            }

            // Explicit clear: write a JSON null into the embed path so the key is removed.
            $embedPath = $this->resolvePath();
            $column = $embedPath->column;
            $data = $parent->has($column) && is_array($parent->get($column))
                ? $parent->get($column)
                : [];
            $data = JsonTreeCaster::insertAtPath($data, $embedPath->nodes, null);
            $parent->set($column, $data);
            $parent->setDirty($column, true);
            $parent->setDirty($this->getProperty(), false);

            return;
        }

        $this->validateEmbed($value);

        if ($merge && is_array($value)) {
            $existing = $parent->get($this->getProperty());
            if ($existing instanceof JsonEntity) {
                $value = $this->mergeArraysDeep($existing->toArray(), $value);
            } elseif ($table instanceof Table) {
                $base = $this->fetchExistingValue($table, $parent, $this->resolvePath());
                if (is_array($base)) {
                    $value = $this->mergeArraysDeep($base, $value);
                }
            }
        }

        if (
            $this->getSaveStrategy() === 'append'
            && $this->type() === self::TYPE_ONE_TO_MANY
            && is_array($value)
            && $table instanceof Table
        ) {
            $existing = $this->fetchExistingValue($table, $parent, $this->resolvePath());
            if (is_array($existing)) {
                $value = $this->mergeAppendLists($existing, $value);
            }
        }

        $embedPath = $this->resolvePath();
        $column = $embedPath->column;

        if ($this->type() === self::TYPE_ONE_TO_MANY) {
            $casted = [];
            foreach ((array)$value as $index => $item) {
                $this->ensureIdField($item);
                $itemBase = new JsonPath($column, array_merge($embedPath->nodes, [(string)$index]));
                $casted[] = JsonTreeCaster::castTree($typeMap, $itemBase, $item, $driver);
            }

            $valueToInsert = $casted;
        } else {
            $this->ensureIdField($value);
            $valueToInsert = JsonTreeCaster::castTree($typeMap, $embedPath, $value, $driver);
        }

        $data = $parent->has($column) && is_array($parent->get($column))
            ? $parent->get($column)
            : [];
        $data = JsonTreeCaster::insertAtPath($data, $embedPath->nodes, $valueToInsert);

        $parent->set($column, $data);
        $parent->setDirty($this->getProperty(), false);
    }

    /**
     * Writes dirty nested-embed fields as atomic engine `set()` expressions.
     *
     * Each dirty leaf of a `JsonEntity` embed is written with a per-path `set()`
     * expression; the value passes through the per-path `toDatabase` caster when
     * one is registered. All other JSON keys in the column are left untouched.
     * After writing, the embedded entities are marked clean.
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param \Crustum\JsonField\Database\JsonTypeMap $typeMap The per-path type map.
     * @param \Crustum\JsonField\Database\JsonFieldEngineInterface $engine The engine adapter.
     * @param \Cake\Database\Driver|null $driver The active database driver.
     * @param \Cake\ORM\Table $table The owning table.
     * @return void
     */
    public function applyAtomicUpdates(
        EntityInterface $parent,
        JsonTypeMap $typeMap,
        JsonFieldEngineInterface $engine,
        ?Driver $driver,
        Table $table,
        bool $merge = false,
    ): void {
        $primaryKey = (array)$table->getPrimaryKey();
        foreach ($primaryKey as $key) {
            if ($parent->get($key) === null) {
                return;
            }
        }

        $keyValues = $parent->extract($primaryKey);

        $embedPath = $this->resolvePath();
        $column = $embedPath->column;

        $value = $parent->get($this->getProperty());
        if ($value === null) {
            if ($parent->isDirty($this->getProperty())) {
                // Explicit clear: remove the embed path via an atomic engine `set(NULL)`.
                $query = new UpdateQuery($table);
                $query->set([$column => $engine->set($embedPath, null)]);
                $query->where($keyValues);
                $query->execute();
                $parent->setDirty($this->getProperty(), false);
            }

            return;
        }

        $this->validateEmbed($value);

        if ($parent->isDirty($this->getProperty()) || !$this->isEntityTree($value)) {
            // Full rewrite: structural change (e.g. a child was deleted), or a plain
            // array value (typical of a `patchEntity` partial update) that must replace
            // or merge the whole embed rather than individual dirty leaves.
            if ($merge && is_array($value)) {
                $base = $this->fetchExistingValue($table, $parent, $embedPath);
                if (is_array($base)) {
                    $value = $this->mergeArraysDeep($base, $value);
                }
            }

            if (
                $this->getSaveStrategy() === 'append'
                && $this->type() === self::TYPE_ONE_TO_MANY
                && is_array($value)
            ) {
                $existing = $this->fetchExistingValue($table, $parent, $embedPath);
                if (is_array($existing)) {
                    $value = $this->mergeAppendLists($existing, $value);
                }
            }

            $casted = $this->type() === self::TYPE_ONE_TO_MANY
                ? array_map(
                    static fn($item): mixed => JsonTreeCaster::castTree(
                        $typeMap,
                        new JsonPath($column, $embedPath->nodes),
                        $item,
                        $driver,
                    ),
                    (array)$value,
                )
                : JsonTreeCaster::castTree($typeMap, $embedPath, $value, $driver);

            $query = new UpdateQuery($table);
            $query->set([$column => $engine->set($embedPath, $casted)]);
            $query->where($keyValues);
            $query->execute();
            $parent->setDirty($this->getProperty(), false);

            return;
        }

        $items = $this->type() === self::TYPE_ONE_TO_MANY
            ? (array)$value
            : [$value];

        foreach ($items as $item) {
            if (!$item instanceof JsonEntity) {
                continue;
            }

            $dirty = $item->getDirty();
            if ($dirty === []) {
                continue;
            }

            foreach ($dirty as $field) {
                $fullPath = new JsonPath($column, array_merge($embedPath->nodes, [(string)$field]));
                $raw = $item->get($field);
                $pathKey = $fullPath->toFieldPath();
                $cast = $typeMap->hasType($pathKey)
                    ? $typeMap->castValue($pathKey, $raw, JsonTypeMap::OPERATION_TO_DATABASE, $driver)
                    : $raw;

                $query = new UpdateQuery($table);
                $query->set([$column => $engine->set($fullPath, $cast)]);
                $query->where($keyValues);
                $query->execute();
            }

            $item->clean();
        }

        $parent->setDirty($this->getProperty(), false);
    }

    /**
     * Assigns a generated identity value to a new embedded entity when `idField`
     * is configured and the value is missing (D2).
     *
     * @param mixed $item The embedded value (entity or array).
     * @return void
     */
    protected function ensureIdField(mixed $item): void
    {
        if ($this->idField === null || !$item instanceof JsonEntity) {
            return;
        }

        if ($item->has($this->idField) && $item->get($this->idField) !== null) {
            return;
        }

        $item->set($this->idField, $this->generateId());
    }

    /**
     * Generates a stable identity value for a new embedded element.
     *
     * @return string The generated identifier.
     */
    protected function generateId(): string
    {
        return Text::uuid();
    }

    /**
     * Returns whether the given embed value is a hydrated entity tree (the normal
     * atomic-edit case) as opposed to a plain array (a patch/replace value).
     *
     * @param mixed $value The embed property value.
     * @return bool True when the value is one/all `JsonEntity` instances.
     */
    protected function isEntityTree(mixed $value): bool
    {
        if ($value instanceof JsonEntity) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        if ($value === []) {
            return true;
        }

        foreach ($value as $item) {
            if (!$item instanceof JsonEntity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reads the currently persisted embed value from the database so a patch merge
     * has a base to merge into (the in-memory hydrated entity may already have been
     * replaced by a plain-array patch).
     *
     * @param \Cake\ORM\Table $table The owning table.
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param \Crustum\JsonField\Path\JsonPath $embedPath The embed's JSON path.
     * @return mixed|null The persisted embed value, or null when unavailable.
     */
    protected function fetchExistingValue(Table $table, EntityInterface $parent, JsonPath $embedPath): mixed
    {
        $primaryKey = (array)$table->getPrimaryKey();
        $where = [];
        foreach ($primaryKey as $key) {
            $value = $parent->get($key);
            if ($value === null) {
                return null;
            }

            $where[$key] = $value;
        }

        $row = $table->find()->select([$embedPath->column])->where($where)->first();
        if ($row === null) {
            return null;
        }

        $base = $row->get($embedPath->column);
        foreach ($embedPath->nodes as $node) {
            if (is_array($base) && array_key_exists($node, $base)) {
                $base = $base[$node];

                continue;
            }

            return null;
        }

        return $base;
    }

    /**
     * Deep-merges a patched array into the existing embed data so sibling fields
     * survive a partial update.
     *
     * @param array<string, mixed> $base The existing embed data.
     * @param array<string, mixed> $patch The partial patch data.
     * @return array<string, mixed> The merged array.
     */
    protected function mergeArraysDeep(array $base, array $patch): array
    {
        $result = $base;
        foreach ($patch as $key => $value) {
            if (
                isset($result[$key]) && is_array($result[$key]) && is_array($value)
                && array_is_list($result[$key]) === false && array_is_list($value) === false
            ) {
                $result[$key] = $this->mergeArraysDeep($result[$key], $value);

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Merges an in-memory list of embed entities into the persisted list for the
     * `append` save strategy: existing rows (matched by `idField`) are replaced in
     * place, new in-memory rows are appended, and rows only present in the persisted
     * list are kept. With no `idField` the in-memory list is appended wholesale.
     *
     * @param array<int|string, mixed> $existing The persisted list.
     * @param array<int|string, mixed> $incoming The in-memory list.
     * @return array<int|string, mixed> The merged list.
     */
    protected function mergeAppendLists(array $existing, array $incoming): array
    {
        $idField = $this->getIdField();
        if ($idField === null) {
            return array_values(array_merge($existing, $incoming));
        }

        $byId = [];
        foreach ($existing as $item) {
            $key = $this->itemIdentity($item, $idField);
            if ($key !== null) {
                $byId[$key] = $item;
            }
        }

        foreach ($incoming as $item) {
            $key = $this->itemIdentity($item, $idField);
            if ($key !== null && array_key_exists($key, $byId)) {
                $byId[$key] = $item;
            } else {
                $byId[] = $item;
            }
        }

        return array_values($byId);
    }

    /**
     * Reads the stable-identity value from a list element, whether it is a plain
     * array (persisted DB value) or a hydrated `JsonEntity`.
     *
     * @param mixed $item The list element.
     * @param string $idField The identity field name.
     * @return mixed|null The identity value, or null when unavailable.
     */
    protected function itemIdentity(mixed $item, string $idField): mixed
    {
        if ($item instanceof EntityInterface) {
            return $item->get($idField);
        }

        if (is_array($item) && array_key_exists($idField, $item)) {
            return $item[$idField];
        }

        return null;
    }

    /**
     * Invokes the configured embedded validator for each embedded entity before persistence.
     *
     * For ONE_TO_ONE the value is validated directly; for ONE_TO_MANY each list element is
     * validated in turn. The validator receives the hydrated `JsonEntity` (or the plain
     * array for a patch) and is expected to throw on invalid data. A null validator is a no-op.
     *
     * @param mixed $value The embed property value (entity, list of entities, or plain array).
     * @return void
     */
    protected function validateEmbed(mixed $value): void
    {
        if ($this->embeddedValidator === null) {
            return;
        }

        $items = $this->type() === self::TYPE_ONE_TO_MANY && is_array($value)
            ? $value
            : [$value];

        foreach ($items as $item) {
            ($this->embeddedValidator)($item);
        }
    }

    /**
     * Removes a child embedded entity from the parent's in-memory list.
     *
     * For ONE_TO_ONE the property is set to null; for ONE_TO_MANY the matching
     * child is removed by identity (`idField` when configured, otherwise object
     * identity, mirroring ODM `Embedded::deleteChild`). The parent's embed property
     * is marked dirty so the change persists on save (whole-column rewrite for new
     * rows, atomic array rewrite for existing rows).
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param \Crustum\JsonField\Model\Entity\JsonEntity $child The embedded child to remove.
     * @return void
     */
    public function deleteChild(EntityInterface $parent, JsonEntity $child): void
    {
        $value = $parent->get($this->getProperty());
        if ($value === null) {
            return;
        }

        if ($this->type() === self::TYPE_ONE_TO_ONE) {
            $parent->set($this->getProperty());
            $parent->setDirty($this->getProperty(), true);

            return;
        }

        $key = null;
        if ($this->idField !== null && $child->has($this->idField) && $child->get($this->idField) !== null) {
            $key = $child->get($this->idField);
        }

        $filtered = [];
        foreach ((array)$value as $item) {
            if ($item === $child) {
                continue;
            }

            if ($key !== null && $item instanceof JsonEntity && $item->get($this->idField) === $key) {
                continue;
            }

            $filtered[] = $item;
        }

        $parent->set($this->getProperty(), $filtered);
        $parent->setDirty($this->getProperty(), true);
    }
}
