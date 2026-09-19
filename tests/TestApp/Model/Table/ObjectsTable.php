<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use Crustum\JsonField\Model\Behavior\JsonFieldBehavior;
use JfApp\Model\Entity\JsonObject;

/**
 * TestApp table for the ported reference `Database/Driver` tests.
 *
 * Owns the `objects` table with two JSON columns and the `JsonField` behavior
 * so per-path `toDatabase` / `toPHP` casters apply on save / read.
 */
class ObjectsTable extends JsonFieldTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('jf_objects');
        $this->setEntityClass(JsonObject::class);
        $this->addBehavior(JsonFieldBehavior::class);
    }

    /**
     * Declares the JSON storage columns so every driver serialises them correctly.
     *
     * @return \Cake\Database\Schema\TableSchemaInterface
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('attributes', 'json');
        $schema->setColumnType('at2', 'json');

        return $schema;
    }
}
