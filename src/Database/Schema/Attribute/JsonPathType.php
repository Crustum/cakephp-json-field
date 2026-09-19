<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Schema\Attribute;

use Attribute;

/**
 * Declares a per-path type for a JSON embed.
 *
 * Place on an **embedded entity** class (the sub-document hydrated from a JSON
 * column), repeatable per field, with a **relative** path. The declared type
 * drives marshalling and per-path casting of the value found at the path; the
 * full path is computed by prefixing the embed's path (see `#[JsonEmbed]`):
 *
 * ```php
 * #[JsonPathType('created', type: 'datetime')]
 * #[JsonPathType('price', type: 'decimal')]
 * class ArticleProfile extends Entity
 * {
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class JsonPathType
{
    /**
     * Stores the path and its declared type for this attribute.
     *
     * @param string      $path Path in `column->node` notation (any supported variant)
     * @param string|null $type TypeFactory type name (for example `datetime`, `decimal`)
     */
    public function __construct(
        public string $path,
        public ?string $type = null,
    ) {
    }

    /**
     * Returns the path addressed by this type declaration.
     *
     * @return string The path in `column->node` notation.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Returns the TypeFactory type name declared for this path.
     *
     * @return string|null The type name, or null when no transformation is declared.
     */
    public function type(): ?string
    {
        return $this->type;
    }
}
