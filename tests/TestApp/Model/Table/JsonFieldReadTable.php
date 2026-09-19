<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\ORM\Table;
use Crustum\JsonField\ORM\JsonFieldAwareTrait;

/**
 * TestApp table exercising the JSON-path aware READ-path helpers.
 */
class JsonFieldReadTable extends Table
{
    use JsonFieldAwareTrait;
}
