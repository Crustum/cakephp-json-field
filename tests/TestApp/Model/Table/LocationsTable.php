<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use JfApp\Model\Entity\Location;

/**
 * TestApp target table for the geolocation real-case (locations with `point`).
 */
class LocationsTable extends JsonFieldTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('jf_locations');
        $this->setEntityClass(Location::class);
    }

    /**
     * Declares the JSON storage column so every driver serialises it correctly.
     *
     * @return \Cake\Database\Schema\TableSchemaInterface
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('point', 'json');

        return $schema;
    }
}
