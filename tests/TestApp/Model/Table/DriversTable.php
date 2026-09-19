<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use JfApp\Model\Entity\Driver;

/**
 * TestApp table for the geolocation real-case (drivers with a JSON-FK to a vehicle).
 */
class DriversTable extends JsonFieldTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('jf_drivers');
        $this->setEntityClass(Driver::class);
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
