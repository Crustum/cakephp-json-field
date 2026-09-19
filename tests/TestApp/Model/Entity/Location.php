<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Model\Entity\JsonFieldTrait;

/**
 * Target entity for the geolocation real-case: a location storing a `point`
 * JSON column with `lat` / `lon` used by `BETWEEN` coverage.
 */
#[JsonColumn('point')]
class Location extends Entity
{
    use JsonFieldTrait;

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = ['*' => true];
}
