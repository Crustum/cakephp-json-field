<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\ORM\Table;
use Crustum\JsonField\ORM\JsonFieldAwareTrait;

/**
 * Test-suite base table fixture composing the JSON-path query helpers.
 *
 * Exists only as a convenience base for the test app; it is intentionally NOT
 * part of the public `src` API. Production integration is `JsonFieldAwareTrait`
 * (use it on any table) plus attaching the `JsonField` behavior.
 */
class JsonFieldTable extends Table
{
    use JsonFieldAwareTrait;
}
