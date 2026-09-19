<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database\Schema\Attribute;

use Attribute;

/**
 * Marks a JSON storage column as path-addressable.
 *
 * Place on a **root document entity** class, repeatable per JSON column. The
 * marker lets the schema reader know which columns hold path-addressable JSON
 * so per-path types (declared on embedded entities via `#[JsonPathType]`) can be
 * attached to them:
 *
 * ```php
 * #[JsonColumn('data')]
 * #[JsonColumn('meta')]
 * class Article extends Entity
 * {
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class JsonColumn
{
    /**
     * Stores the JSON column name for this marker attribute.
     *
     * @param string $name JSON column name
     */
    public function __construct(public string $name)
    {
    }

    /**
     * Returns the JSON column name declared by this attribute.
     *
     * @return string The JSON column name.
     */
    public function name(): string
    {
        return $this->name;
    }
}
