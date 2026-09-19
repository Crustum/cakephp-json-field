<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Crustum\JsonField\Database\Schema\Attribute\JsonPathType;
use Crustum\JsonField\Model\Entity\JsonEntity;

/**
 * Embedded entity fixture (one-to-many) for association tests.
 */
#[JsonPathType('name')]
class AssociationChapter extends JsonEntity
{
}
