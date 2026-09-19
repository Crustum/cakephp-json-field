<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

use Cake\Database\Driver;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Crustum\JsonField\Database\JsonFieldEngineInterface;
use Crustum\JsonField\Database\JsonTreeCaster;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\ORM\Association\Loader\JsonFieldReferenceLoader;
use Crustum\JsonField\Path\JsonPath;
use Crustum\JsonField\Path\JsonPathParser;
use InvalidArgumentException;

/**
 * Base class for associations whose JSON path stores a foreign key to another table.
 *
 * References resolve through a real SQL join (the foreign key is emitted as the
 * engine's `extract()` expression). This is the JsonField analog of Mongo's
 * `DBRef` / `BelongsTo` / `BelongsToMany` reference semantics. The join itself is
 * wired in a later phase; this class fixes the reference strategy and configuration.
 */
abstract class JsonFieldReference extends JsonFieldAssociation
{
    /**
     * References only support the reference strategy.
     *
     * @var array<string>
     */
    protected array $validStrategies = [self::STRATEGY_REFERENCE];

    /**
     * The related repository (Table) class or alias.
     *
     * @var string|null
     */
    protected ?string $targetClass = null;

    /**
     * @inheritDoc
     */
    protected function defaultStrategy(): string
    {
        return self::STRATEGY_REFERENCE;
    }

    /**
     * @inheritDoc
     */
    protected function initialize(array $options): void
    {
        parent::initialize($options);

        if (isset($options['foreignKey'])) {
            $this->path = $options['foreignKey'];
        }

        if (isset($options['target'])) {
            $this->targetClass = $options['target'];
        } elseif (isset($options['className'])) {
            $this->targetClass = $options['className'];
        }
    }

    /**
     * References always use the reference strategy.
     *
     * @return string The reference strategy constant.
     */
    public function getStrategy(): string
    {
        return self::STRATEGY_REFERENCE;
    }

    /**
     * Rejects any strategy other than `reference`.
     *
     * @param string $strategy The requested strategy.
     * @return $this
     * @throws \InvalidArgumentException When the strategy is not `reference`.
     */
    public function setStrategy(string $strategy): static
    {
        if ($strategy !== self::STRATEGY_REFERENCE) {
            throw new InvalidArgumentException('Reference associations only support the reference strategy.');
        }

        return $this;
    }

    /**
     * Returns the JSON path that stores the foreign key.
     *
     * @return \Crustum\JsonField\Path\JsonPath The parsed foreign-key path.
     */
    public function getForeignKeyPath(): JsonPath
    {
        return JsonPathParser::parse($this->path);
    }

    /**
     * Returns the related repository (Table) class or alias.
     *
     * @return string|null The related target class, or null when unresolved.
     */
    public function getTargetClass(): ?string
    {
        return $this->targetClass;
    }

    /**
     * Persists the foreign key for this reference into the parent's JSON column.
     *
     * The parent entity's property holds the hydrated target entity/entities (from
     * a `loadJsonReferences` join). On save the target's primary key value(s) are
     * written into the declared foreign-key JSON path, optionally saving a new
     * target first. The change is folded into the parent JSON column so the
     * behavior's normal column write persists it. Only dirty reference properties
     * are processed, so a read-only resolution never clobbers the stored key.
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param \Crustum\JsonField\Database\JsonTypeMap $typeMap The per-path type map.
     * @param \Cake\Database\Driver|null $driver The active database driver.
     * @param \Crustum\JsonField\Database\JsonFieldEngineInterface|null $engine The engine adapter (unused; FK writes use whole-column merge).
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
        $property = $this->getProperty();
        if (!$parent->isDirty($property)) {
            return;
        }

        $value = $parent->get($property);
        if ($value === null) {
            $this->writeForeignKey($parent, null);
            $parent->setDirty($property, false);

            return;
        }

        $targetTable = $this->resolveTargetTable();
        $primaryKey = (array)$targetTable->getPrimaryKey();

        if ($this->type() === self::TYPE_MANY_TO_MANY) {
            $fkValue = [];
            foreach ((array)$value as $item) {
                $fkValue[] = $this->extractForeignKey($item, $targetTable, $primaryKey);
            }
        } else {
            $fkValue = $this->extractForeignKey($value, $targetTable, $primaryKey);
        }

        $this->writeForeignKey($parent, $fkValue);
        $parent->setDirty($property, false);
    }

    /**
     * Returns a result-formatter loader for this reference, suitable for eager loading
     * via a query's `formatResults` hook.
     *
     * The returned callable resolves the foreign key(s) from each entity's already
     * hydrated JSON column (read through the dotted JSON property, never a real SQL
     * column) and assigns the matched target row(s) onto the reference property. This
     * is the READ counterpart of `saveAssociated`: it populates references without a
     * SQL join, mirroring ODM `EagerLoader::loadExternal` reading the DBRef key from
     * the decoded document.
     *
     * @return callable(array<int, \Cake\Datasource\EntityInterface>): void The eager loader.
     */
    public function eagerLoader(): callable
    {
        return function (array $entities): void {
            (new JsonFieldReferenceLoader())->load($entities, $this);
        };
    }

    /**
     * Resolves the target Table from the configured target class/alias.
     *
     * @return \Cake\ORM\Table The related table.
     */
    protected function resolveTargetTable(): Table
    {
        return TableRegistry::getTableLocator()->get($this->getTargetClass());
    }

    /**
     * Returns the foreign-key value for a single target, saving a new/dirty target first.
     *
     * @param mixed $item The hydrated target entity or a raw key value.
     * @param \Cake\ORM\Table $targetTable The related table.
     * @param array<string> $primaryKey The target primary key field(s).
     * @return mixed A scalar key (single-column PK) or a list of key values.
     */
    protected function extractForeignKey(mixed $item, Table $targetTable, array $primaryKey): mixed
    {
        if ($item instanceof EntityInterface) {
            if ($item->isNew() || $item->isDirty()) {
                $targetTable->saveOrFail($item);
            }

            $keys = [];
            foreach ($primaryKey as $field) {
                $keys[] = $item->get($field);
            }

            return count($keys) === 1 ? $keys[0] : $keys;
        }

        return $item;
    }

    /**
     * Writes the foreign-key value into the parent JSON column at the FK path, marking
     * the column dirty so the behavior's normal write persists it.
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param mixed $fkValue The foreign-key value (scalar, array, or null).
     * @return void
     */
    protected function writeForeignKey(EntityInterface $parent, mixed $fkValue): void
    {
        $fkPath = $this->getForeignKeyPath();
        $column = $fkPath->column;
        $data = $parent->has($column) && is_array($parent->get($column))
            ? $parent->get($column)
            : [];
        $data = JsonTreeCaster::insertAtPath($data, $fkPath->nodes, $fkValue);

        $parent->set($column, $data);
        $parent->setDirty($column, true);
    }
}
