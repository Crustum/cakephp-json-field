<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Crustum\JsonField\Database\Schema\Attribute\JsonPathType;
use Crustum\JsonField\Model\Entity\JsonEntity;

/**
 * A dummy embedded entity used to verify relative attribute reflection in tests.
 */
#[JsonPathType('created', type: 'datetime')]
#[JsonPathType('price', type: 'decimal')]
#[JsonPathType('label')]
class ReaderProfile extends JsonEntity
{
}
