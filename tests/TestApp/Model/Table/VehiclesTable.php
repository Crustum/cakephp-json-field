<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use JfApp\Model\Entity\Vehicle;

/**
 * TestApp table for the geolocation real-case (vehicles with a JSON-FK to a location).
 */
class VehiclesTable extends JsonFieldTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('jf_vehicles');
        $this->setEntityClass(Vehicle::class);
    }

    /**
     * Declares the JSON storage column so every driver serialises it correctly.
     *
     * @return \Cake\Database\Schema\TableSchemaInterface
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('data', 'json');

        return $schema;
    }
}
