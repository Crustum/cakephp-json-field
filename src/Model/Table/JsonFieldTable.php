<?php
declare(strict_types=1);

namespace Crustum\JsonField\Model\Table;

use Cake\ORM\Table;
use Crustum\JsonField\ORM\JsonFieldAwareTrait;

/**
 * Base table class for JSON-field enabled tables.
 *
 * Extend this instead of `Cake\ORM\Table` to gain the JSON-path query builders
 * (`whereJsonPath`, `selectJsonPath`, `groupByJsonPath`, `havingJsonPath`,
 * `aggregateJsonPath`, `joinJsonReference`, `eagerLoadJsonReferences`, …) without
 * manually applying {@see \Crustum\JsonField\ORM\JsonFieldAwareTrait}.
 */
class JsonFieldTable extends Table
{
    use JsonFieldAwareTrait;
}
