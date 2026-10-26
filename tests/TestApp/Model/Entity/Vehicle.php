<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Database\Schema\Attribute\JsonReference;
use Crustum\JsonField\Model\Entity\JsonFieldTrait;
use JfApp\Model\Table\LocationsTable;

/**
 * Vehicle document carrying a JSON-FK (`data->location_id`) to a {@see Location}.
 */
#[JsonColumn('data')]
#[JsonReference(property: 'location', target: LocationsTable::class, path: 'data->location_id', type: 'one')]
class Vehicle extends Entity
{
    use JsonFieldTrait;

    /**
     * @var array<string, bool>
     */
    protected array $patchable = ['*' => true];
}
