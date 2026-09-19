<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

/**
 * Embedded one-to-one association over a JSON column (Mongo `EmbedOne` analog).
 */
class JsonFieldHasOne extends JsonFieldEmbed
{
    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_ONE_TO_ONE;
    }
}
