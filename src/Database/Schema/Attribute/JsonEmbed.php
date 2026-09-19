<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Schema\Attribute;

use Attribute;

/**
 * Declares an embedded sub-document living inside a JSON column.
 *
 * Place on a **root document entity** class, repeatable per embed (Mongo ODM
 * `#[Embedded]` analog). The attribute links a JSON path (where the embed's
 * data is stored) to an embedded entity class whose own `#[JsonPathType]`
 * fields are relative to this embed. The schema reader prefixes each embedded
 * relative type with this path to build the full per-path type map.
 *
 * The `type` argument selects the cardinality: `one` builds a `JsonFieldHasOne`
 * (a single nested document) and `many` builds a `JsonFieldHasMany` (a list of
 * nested documents). The `JsonFieldAssociationBuilder` reads this attribute to
 * construct the matching association object.
 *
     * ```php
     * #[JsonEmbed(property: 'profile', documentClass: ArticleProfile::class, path: 'data->profile')]
     * #[JsonEmbed(property: 'tags', documentClass: ArticleTag::class, path: 'data->tags', type: 'many', idField: 'uid')]
     * class Article extends Entity
     * {
     * }
     * ```
     */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class JsonEmbed
{
    /**
     * Stores the embed link for this attribute.
     *
     * @param string $property The root entity property that holds the hydrated embed entity.
     * @param string $documentClass The fully-qualified embedded entity class.
     * @param string $path The JSON path (in `column->node` notation) where the embed's data is stored.
     * @param string $type Cardinality: `one` (default) for HasOne or `many` for HasMany.
     * @param string|null $idField Optional property used as stable identity for list elements (D2).
     * @param string $saveStrategy Save strategy for list embeds: `replace` (default) or `append` (keep existing DB rows, merge by idField).
     */
    public function __construct(
        public string $property,
        public string $documentClass,
        public string $path,
        public string $type = 'one',
        public ?string $idField = null,
        public string $saveStrategy = 'replace',
    ) {
    }

    /**
     * Returns the root entity property that holds the hydrated embed entity.
     *
     * @return string The property name.
     */
    public function property(): string
    {
        return $this->property;
    }

    /**
     * Returns the fully-qualified embedded entity class.
     *
     * @return string The embedded entity class name.
     */
    public function documentClass(): string
    {
        return $this->documentClass;
    }

    /**
     * Returns the JSON path where the embed's data is stored.
     *
     * @return string The path in `column->node` notation.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Returns the cardinality declared for this embed.
     *
     * @return string `one` for a single document or `many` for a list of documents.
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

    /**
     * Returns the save strategy for list embeds (`replace` or `append`).
     *
     * @return string The save strategy name.
     */
    public function saveStrategy(): string
    {
        return $this->saveStrategy;
    }
}
