<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Crustum\JsonField\Database\Schema\Attribute\JsonPathType;
use Crustum\JsonField\Model\Entity\JsonEntity;

/**
 * Embedded entity fixture (one-to-one) for association tests.
 */
#[JsonPathType('title')]
class AssociationProfile extends JsonEntity
{
}
