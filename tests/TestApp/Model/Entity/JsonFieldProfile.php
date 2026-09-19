<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Crustum\JsonField\Database\Schema\Attribute\JsonPathType;
use Crustum\JsonField\Model\Entity\JsonEntity;

/**
 * Embedded profile sub-document with per-path types.
 */
#[JsonPathType('created', type: 'datetime')]
#[JsonPathType('score', type: 'decimal')]
class JsonFieldProfile extends JsonEntity
{
}
