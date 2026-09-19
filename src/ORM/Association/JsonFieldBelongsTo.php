<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

/**
 * Reference association: a single foreign key inside a JSON column points to one
 * row of another table (Mongo `DBRef` one-to-one reference analog).
 */
class JsonFieldBelongsTo extends JsonFieldReference
{
    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_MANY_TO_ONE;
    }
}
