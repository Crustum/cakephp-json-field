<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Schema\Attribute;

use Attribute;

/**
 * Declares a JSON-FK reference from a root document entity to another table.
 *
 * Place on a **root document entity** class, repeatable per reference (Mongo ODM
 * `DBRef` analog). The attribute links a JSON path (where the foreign key is
 * stored inside the parent's JSON column) to a target Table class, mirroring the
 * `#[JsonEmbed]` shape but for foreign keys instead of nested documents. The
 * `JsonFieldAssociationBuilder` reads this attribute and constructs the matching
 * `JsonFieldReference` association (`JsonFieldBelongsTo` for `type: 'one'`,
 * `JsonFieldBelongsToMany` for `type: 'many'`).
 *
     * ```php
     * #[JsonReference(property: 'author', target: UsersTable::class, path: 'data->author_id', type: 'one')]
     * #[JsonReference(property: 'tags', target: TagsTable::class, path: 'data->tag_ids', type: 'many', idField: 'uid')]
     * class Article extends Entity
     * {
     * }
     * ```
     */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class JsonReference
{
    /**
     * Stores the reference link for this attribute.
     *
     * @param string $property The root entity property that holds the hydrated target entity/entities.
     * @param string $target The fully-qualified target Table class (or registry alias).
     * @param string $path The JSON path (in `column->node` notation) where the foreign key is stored.
     * @param string $type Cardinality: `one` (default) for BelongsTo or `many` for BelongsToMany.
     * @param string|null $idField Optional property used as stable identity for list elements (D2).
     */
    public function __construct(
        public string $property,
        public string $target,
        public string $path,
        public string $type = 'one',
        public ?string $idField = null,
    ) {
    }

    /**
     * Returns the root entity property that holds the hydrated target.
     *
     * @return string The property name.
     */
    public function property(): string
    {
        return $this->property;
    }

    /**
     * Returns the fully-qualified target Table class or registry alias.
     *
     * @return string The target Table class name.
     */
    public function target(): string
    {
        return $this->target;
    }

    /**
     * Returns the JSON path where the foreign key is stored.
     *
     * @return string The path in `column->node` notation.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Returns the cardinality declared for this reference.
     *
     * @return string `one` for a single foreign key or `many` for a list of foreign keys.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Returns the stable identity field for list elements, if configured.
     *
     * @return string|null The id field name, or null for positional identity.
     */
    public function idField(): ?string
    {
        return $this->idField;
    }
}
