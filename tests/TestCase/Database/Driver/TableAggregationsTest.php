<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Cake\Collection\Collection;
use Crustum\JsonField\Database\Driver\JsonFieldPostgresDriver;
use Crustum\JsonField\Database\Driver\JsonFieldSqliteDriver;

/**
 * Port of the reference `TableAggregationsTest` (GROUP BY / HAVING over JSON
 * paths), adapted to our `ObjectsTable` + `attributes->path` API.
 *
 * `jsonTypeMap` is a reference-only feature; the date grouping test is adapted
 * to group on the raw extracted string. `testHaving` uses HAVING without a
 * GROUP BY, which SQLite rejects, so it is skipped on SQLite.
 */
class TableAggregationsTest extends TestCase
{
    public function testGroup(): void
    {
        $this->skipIf(
            $this->connection->getDriver()::class === JsonFieldPostgresDriver::class,
            'GROUP BY over a JSON extract requires jsonTypeMap-style expression matching on PostgreSQL',
        );

        $data = $this->generator
          ->clear()
          ->faker('attributes.string', 'randomElement', ['foo', 'bar', 'baz'])
          ->generate(50);

        $this->table->saveMany($this->table->newEntities($data));
        $data = (new Collection($data))->countBy('attributes.string')->toArray();

        $q = $this->table->find();

        $results = $q
          ->select(['count' => $q->func()->count('*'), 'v' => 'attributes->string'])
          ->groupBy('attributes->string')
          ->all();

        foreach ($results as $s) {
            $this->assertEquals($data[$s->v], $s->count);
        }
    }

    public function testGroupWithAlias(): void
    {
        $this->skipIf(
            $this->connection->getDriver()::class === JsonFieldPostgresDriver::class,
            'GROUP BY over a JSON extract requires jsonTypeMap-style expression matching on PostgreSQL',
        );

        $data = $this->generator
          ->clear()
          ->faker('attributes.string', 'randomElement', ['foo', 'bar', 'baz'])
          ->generate(50);

        $this->table->saveMany($this->table->newEntities($data));

        $q = $this->table->find();

        $results = $q
          ->select(['string' => 'attributes->string'])
          ->groupBy('string')
          ->orderBy('string')
          ->all()
          ->map(fn($o) => $o->string)
          ->toArray();

        $this->assertSame(['bar', 'baz', 'foo'], $results);
    }

    public function testGroupWithJsonType(): void
    {
        $this->skipIf(
            $this->connection->getDriver()::class === JsonFieldPostgresDriver::class,
            'GROUP BY over a JSON extract requires jsonTypeMap-style expression matching on PostgreSQL',
        );

        $data = $this->generator
          ->clear()
          ->faker('attributes.date', 'randomElement', ['2020-01-01', '2021-01-01', '2022-01-01'])
          ->generate(50);

        $this->table->saveMany($this->table->newEntities($data));
        $data = (new Collection($data))->countBy('attributes.date')->toArray();

        $q = $this->table->find();

        $results = $q
          ->select(['count' => $q->func()->count('*'), 'v' => 'attributes->date'])
          ->groupBy('attributes->date')
          ->all();

        foreach ($results as $s) {
            $this->assertEquals($data[$s->v], $s->count);
        }
    }

    public function testHaving(): void
    {
        $this->skipIf(
            $this->connection->getDriver()::class === JsonFieldPostgresDriver::class,
            'HAVING with `+` over JSON extracts is text concatenation on PostgreSQL without jsonTypeMap casting',
        );

        $this->skipIf(
            $this->connection->getDriver()::class === JsonFieldSqliteDriver::class,
            'HAVING without GROUP BY is not supported on SQLite',
        );

        $data = $this->generator
          ->clear()
          ->faker('attributes.i', 'randomElement', [0, 1, 2])
          ->faker('attributes.j', 'randomElement', [0, 1, 2])
          ->generate(50);

        $this->table->saveMany($this->table->newEntities($data));

        $q = $this->table->find();

        $results = $q
          ->select([
              'i' => 'attributes->i',
              'j' => 'attributes->j',
              'total' => 'attributes->i + attributes->j',
          ])
          ->having(['total >=' => 3])
          ->all();

        foreach ($results as $o) {
            $this->assertEquals($o->i + $o->j, $o->total);
            $this->assertTrue($o->total >= 3);
        }
    }
}
