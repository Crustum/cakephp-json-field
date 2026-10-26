<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Cake\Collection\Collection;
use Crustum\JsonField\Database\Driver\JsonFieldPostgresDriver;

/**
 * Port of the reference `TableFunctionsTest` (aggregate functions over JSON
 * paths), adapted to our `ObjectsTable` + `attributes->path` API.
 */
class TableFunctionsTest extends TestCase
{
    public function testCount(): void
    {
        $data = $this->generator
          ->clear()
          ->static('attributes.item', 'test')
          ->generate(10);

        $this->table->saveMany($this->table->newEntities($data));

        $q = $this->table->find();
        $q->select(['count' => $q->func()->count('attributes->item')]);

        $this->assertEquals(10, $q->first()->count);
    }

    public function testSum(): void
    {
        $this->skipIf(
            $this->connection->getDriver()::class === JsonFieldPostgresDriver::class,
            'SUM over a JSON extract returns text on PostgreSQL without jsonTypeMap casting',
        );

        $data = $this->generator
          ->clear()
          ->faker('attributes.n', 'randomElement', [1, '1'])
          ->generate(10);

        $this->table->saveMany($this->table->newEntities($data));

        $q = $this->table->find();
        $q->select(['total' => $q->func()->sum('attributes->n')]);

        $expected = new Collection($data)->sumOf('attributes.n');

        $this->assertEquals($expected, $q->first()->total);
    }
}
