<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

use Cake\Database\Driver;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\ORM\Table;
use Crustum\JsonField\Database\JsonFieldEngineInterface;
use Crustum\JsonField\Database\JsonTypeMap;

/**
 * Base class for JsonField associations declared over JSON columns.
 *
 * Modeled on the Mongo ODM `Association`: an association describes a
 * relationship between a root document entity and a target, and carries the
 * configuration (property, target class, JSON path, strategy) used by the
 * hydration and persistence layers. Two families extend this base:
 *
 * - `JsonFieldEmbed` (nested value stored inside the parent's JSON column, no
 *   SQL join) — the analog of Mongo `Embedded`.
 * - `JsonFieldReference` (a foreign key stored inside the JSON column, resolved
 *   by a real SQL join) — the analog of Mongo `DBRef`.
 */
abstract class JsonFieldAssociation
{
    /**
     * Cardinality: a single nested document.
     *
     * @var string
     */
    public const TYPE_ONE_TO_ONE = 'oneToOne';

    /**
     * Cardinality: a list of nested documents.
     *
     * @var string
     */
    public const TYPE_ONE_TO_MANY = 'oneToMany';

    /**
     * Cardinality: a single foreign key to another table.
     *
     * @var string
     */
    public const TYPE_MANY_TO_ONE = 'manyToOne';

    /**
     * Cardinality: a list of foreign keys to another table.
     *
     * @var string
     */
    public const TYPE_MANY_TO_MANY = 'manyToMany';

    /**
     * Strategy: the value is a nested document inside the parent's JSON column.
     *
     * @var string
     */
    public const STRATEGY_EMBED = 'embed';

    /**
     * Strategy: the value is a foreign key resolved by a SQL join.
     *
     * @var string
     */
    public const STRATEGY_REFERENCE = 'reference';

    /**
     * The repository (Table) that owns the root document entity.
     *
     * @var \Cake\Datasource\RepositoryInterface|null
     */
    protected ?RepositoryInterface $source = null;

    /**
     * The association name (used as the root entity property by default).
     *
     * @var string
     */
    protected string $name;

    /**
     * The root entity property that holds the hydrated association target.
     *
     * @var string
     */
    protected string $property;

    /**
     * The target class (embedded entity class or related Table class).
     *
     * @var string
     */
    protected string $className;

    /**
     * The JSON path (in `column->node` notation) where the association's data lives.
     *
     * @var string
     */
    protected string $path;

    /**
     * Optional override for the parent field holding the data (defaults to property).
     *
     * @var string|null
     */
    protected ?string $localKey = null;

    /**
     * Optional property used as a stable identity for list elements.
     *
     * When set, list embeds (HasMany / BelongsToMany) are matched for update,
     * append, and delete by this field instead of array position — mirroring
     * Laravel's auto `_id` on embedded insert. `null` means positional identity.
     *
     * @var string|null
     */
    protected ?string $idField = null;

    /**
     * Save strategy for list embeds: `replace` (default) rewrites the whole list
     * from the in-memory entities, `append` keeps the persisted rows and merges the
     * in-memory entities into them (by `idField` when configured, otherwise by append).
     *
     * @var string
     */
    protected string $saveStrategy = 'replace';

    /**
     * The loading/persistence strategy for this association.
     *
     * @var string
     */
    protected string $strategy;

    /**
     * Raw options passed to the constructor.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * @param string $name The association name.
     * @param array<string, mixed> $options Association options.
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;

        if (isset($options['source'])) {
            $this->setSource($options['source']);
        }

        if (isset($options['property'])) {
            $this->property = $options['property'];
        }

        if (isset($options['className'])) {
            $this->className = $options['className'];
        } elseif (isset($options['documentClass'])) {
            $this->className = $options['documentClass'];
        }

        if (isset($options['path'])) {
            $this->path = $options['path'];
        }

        if (isset($options['localKey'])) {
            $this->localKey = $options['localKey'];
        }

        $this->strategy = $options['strategy'] ?? $this->defaultStrategy();
        $this->initialize($options);
    }

    /**
     * Hook for subclasses to apply extra option handling.
     *
     * @param array<string, mixed> $options The constructor options.
     * @return void
     */
    protected function initialize(array $options): void
    {
        if (isset($options['idField'])) {
            $this->idField = $options['idField'];
        }

        if (isset($options['saveStrategy'])) {
            $this->saveStrategy = $options['saveStrategy'] === 'append' ? 'append' : 'replace';
        }
    }

    /**
     * Returns the cardinality constant for this association.
     *
     * @return string One of the TYPE_* constants.
     */
    abstract public function type(): string;

    /**
     * Returns the default strategy for this association family.
     *
     * @return string One of the STRATEGY_* constants.
     */
    protected function defaultStrategy(): string
    {
        return self::STRATEGY_EMBED;
    }

    /**
     * Returns the association name.
     *
     * @return string The association name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the owning repository (Table) for the root document entity.
     *
     * @param \Cake\Datasource\RepositoryInterface $source The owning repository.
     * @return $this
     */
    public function setSource(RepositoryInterface $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Returns the owning repository (Table) for the root document entity.
     *
     * @return \Cake\Datasource\RepositoryInterface|null The owning repository.
     */
    public function getSource(): ?RepositoryInterface
    {
        return $this->source;
    }

    /**
     * Returns the target repository. For embeds the target is the source itself
     * (the data lives inside the parent); references override this.
     *
     * @return \Cake\Datasource\RepositoryInterface|null The target repository.
     */
    public function getTarget(): ?RepositoryInterface
    {
        return $this->source;
    }

    /**
     * Returns the root entity property that holds the hydrated target.
     *
     * @return string The property name.
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * Returns the target class name (embedded entity or related Table).
     *
     * @return string The fully-qualified class name.
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Returns the JSON path where the association's data is stored.
     *
     * @return string The path in `column->node` notation.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the parent field holding the association's data.
     *
     * @return string The local key (defaults to the property name).
     */
    public function getLocalKey(): string
    {
        return $this->localKey ?? $this->property;
    }

    /**
     * Returns the stable identity field for list elements, if configured.
     *
     * @return string|null The id field name, or null for positional identity.
     */
    public function getIdField(): ?string
    {
        return $this->idField;
    }

    /**
     * Returns the save strategy for list embeds (`replace` or `append`).
     *
     * @return string The save strategy name.
     */
    public function getSaveStrategy(): string
    {
        return $this->saveStrategy;
    }

    /**
     * Returns the loading/persistence strategy.
     *
     * @return string One of the STRATEGY_* constants.
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * Returns the raw constructor options.
     *
     * @return array<string, mixed> The association options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Cascade delete handling for this association.
     *
     * Embeds are removed structurally with the parent, so this is a no-op that
     * returns true. References may override this in a later phase.
     *
     * @param \Cake\Datasource\EntityInterface $document The parent document.
     * @param array<string, mixed> $options Delete options.
     * @return bool True when the cascade is handled.
     */
    public function cascadeDelete(EntityInterface $document, array $options = []): bool
    {
        return true;
    }

    /**
     * Persists this association's data for the given parent entity.
     *
     * Base implementation is a no-op; embedded associations override it to export
     * the nested value into the owning JSON column (whole-column or atomic), and
     * reference associations will override it in a later phase to write the stored
     * foreign key. The behavior dispatches this method for every dirty association.
     *
     * @param \Cake\Datasource\EntityInterface $parent The parent root entity.
     * @param \Crustum\JsonField\Database\JsonTypeMap $typeMap The per-path type map.
     * @param \Cake\Database\Driver|null $driver The active database driver.
     * @param \Crustum\JsonField\Database\JsonFieldEngineInterface|null $engine The engine adapter (nullable for whole-column writes).
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
    }
}
