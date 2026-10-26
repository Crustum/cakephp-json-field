<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Cake\I18n\DateTime;

/**
 * Faithful port of the reference `BasicInsertAndUpdateTest`.
 *
 * The reference inserts through a raw connection with a per-request type map and
 * a *computed* `attributes->relativeYear` caster that reads its sibling
 * `attributes->datetime`. We reproduce the same scenario through the JSON-field
 * database layer: the `JsonObject` entity declares `#[JsonPathType]` for
 * `year`/`relativeYear` and the base case registers `toPHP`/`toDatabase` casters
 * (the `relativeYear` `toDatabase` receives the full column array as context).
 *
 * @uses \Crustum\JsonField\Model\Behavior\JsonFieldBehavior
 * @uses \Crustum\JsonField\Database\Schema\JsonSchemaReader
 */
class BasicInsertAndUpdateTest extends TestCase
{
    /**
     * @return array<string, mixed> The generated row.
     */
    protected function buildRow(): array
    {
        $datetime = new DateTime('2021-01-31 22:11:30');

        return $this->generator
            ->clear()
            ->static('id', 'b8b04734-5ea1-4220-9798-58edef3ffcd7')
            ->static('attributes.null', null)
            ->static('attributes.boolean', true)
            ->static('attributes.int', 10)
            ->static('attributes.double', 125.25)
            ->static('attributes.scientific', 2.1E-5)
            ->static('attributes.string', 'test')
            ->static('attributes.datetime', $datetime)
            ->static('attributes.year', $datetime)
            ->static('attributes.relativeYear', null)
            ->static('attributes.array', ['a'])
            ->static('attributes.object', ['a' => 'a'])
            ->static('attributes.arrayobject', [['a' => 'a'], ['a' => 'b']])
            ->generate(1);
    }

    /**
     * Round-trips an insert then an atomic-style whole-column update.
     *
     * @return void
     */
    public function testInsertAndUpdate(): void
    {
        $row = $this->buildRow();
        $entity = $this->table->newEntity($row);
        $this->assertNotFalse($this->table->save($entity));

        $attributes = $this->readAttributes($row['id']);
        $at2 = $this->readAt2($row['id']);

        $this->assertNull($attributes['null']);
        $this->assertTrue($attributes['boolean']);
        $this->assertSame(10, $attributes['int']);
        $this->assertSame(125.25, $attributes['double']);
        $this->assertEquals(2.1E-5, $attributes['scientific']);
        $this->assertSame('2021-01-31 22:11:30', $attributes['datetime']);
        $this->assertSame(2021, $attributes['year']);
        $this->assertSame(2021, $attributes['relativeYear']);
        $this->assertSame(['a'], $attributes['array']);
        $this->assertSame(['a' => 'a'], $attributes['object']);
        $this->assertSame([['a' => 'a'], ['a' => 'b']], $attributes['arrayobject']);

        /** @var \JfApp\Model\Entity\JsonObject $object */
        $object = $this->table->find()->where(['id' => $row['id']])->first();
        $attrs = $object->get('attributes');
        $attrs['datetime'] = new DateTime('2022-01-31 22:11:30');
        $object->set('attributes', $attrs);
        $object->set('at2', 'test');
        $this->assertNotFalse($this->table->save($object));

        $attributes = $this->readAttributes($row['id']);
        $at2 = $this->readAt2($row['id']);

        $this->assertNull($attributes['null']);
        $this->assertTrue($attributes['boolean']);
        $this->assertSame(10, $attributes['int']);
        $this->assertSame(125.25, $attributes['double']);
        $this->assertEquals(2.1E-5, $attributes['scientific']);
        $this->assertSame('2022-01-31 22:11:30', $attributes['datetime']);
        $this->assertSame(2021, $attributes['year']);
        $this->assertSame(2022, $attributes['relativeYear']);
        $this->assertSame(['a'], $attributes['array']);
        $this->assertSame(['a' => 'a'], $attributes['object']);
        $this->assertSame([['a' => 'a'], ['a' => 'b']], $attributes['arrayobject']);
        $this->assertSame('test', $at2);
    }

    /**
     * @param string $id Row id.
     * @return array<string, mixed>
     */
    private function readAttributes(string $id): array
    {
        $row = $this->connection
            ->execute('SELECT attributes FROM jf_objects WHERE id = ?', [$id])
            ->fetch('assoc');

        return json_decode((string)$row['attributes'], true);
    }

    /**
     * @param string $id Row id.
     * @return mixed
     */
    private function readAt2(string $id): mixed
    {
        $row = $this->connection
            ->execute('SELECT at2 FROM jf_objects WHERE id = ?', [$id])
            ->fetch('assoc');

        return json_decode((string)$row['at2'], true);
    }
}
