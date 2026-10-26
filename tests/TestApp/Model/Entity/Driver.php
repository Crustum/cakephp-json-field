<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Database\Schema\Attribute\JsonReference;
use Crustum\JsonField\Model\Entity\JsonFieldTrait;
use JfApp\Model\Table\VehiclesTable;

/**
 * Driver document carrying a JSON-FK (`data->vehicle_id`) to a {@see Vehicle},
 * demonstrating two-level reference resolution in the geolocation scenario.
 */
#[JsonColumn('data')]
#[JsonReference(property: 'vehicle', target: VehiclesTable::class, path: 'data->vehicle_id', type: 'one')]
class Driver extends Entity
{
    use JsonFieldTrait;

    /**
     * @var array<string, bool>
     */
    protected array $patchable = ['*' => true];
}
