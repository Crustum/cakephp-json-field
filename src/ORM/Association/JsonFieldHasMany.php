<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

/**
 * Embedded one-to-many association over a JSON column (Mongo `EmbedMany` analog).
 */
class JsonFieldHasMany extends JsonFieldEmbed
{
    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_ONE_TO_MANY;
    }
}
