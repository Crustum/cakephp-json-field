<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

/**
 * Reference association: a list of foreign keys inside a JSON column points to many
 * rows of another table (Mongo `BelongsToMany` / `_ids` reference analog).
 */
class JsonFieldBelongsToMany extends JsonFieldReference
{
    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_MANY_TO_MANY;
    }
}
