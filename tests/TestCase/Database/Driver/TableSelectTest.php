<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Cake\I18n\DateTime;
use Cake\Utility\Text;

/**
 * Faithful port of the reference `TableSelectTest`.
 *
 * Exercises whole-column JSON decoding plus per-path `toPHP` casting
 * (`datetime`, the registered `callback`) and path-alias projection through the
 * JSON-field database layer. The reference's per-request `jsonTypeMap` /
 * schema `setJsonTypes` options are replaced by our `#[JsonPathType]` attributes
 * and the registered callback caster (declared once in the base case).
 *
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait
 */
class TableSelectTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    protected function buildRow(): array
    {
        $datetime = new DateTime('2021-01-31 22:11:30');

        return $this->generator
            ->clear()
            ->static('id', Text::uuid())
            ->static('attributes.null', null)
            ->static('attributes.boolean', true)
            ->static('attributes.int', 10)
            ->static('attributes.double', 125.25)
            ->static('attributes.scientific', 2.1E-5)
            ->static('attributes.string', 'test')
            ->static('attributes.datetime', $datetime)
            ->static('attributes.callback', $datetime)
            ->static('attributes.array', ['a'])
            ->static('attributes.object', ['a' => 'a'])
            ->static('attributes.arrayobject', [['a' => 'a'], ['a' => 'b']])
            ->generate(1);
    }

    /**
     * @return void
     */
    public function testFetchWithJsonTypeMapOption(): void
    {
        $row = $this->buildRow();
        $this->assertNotFalse($this->table->save($this->table->newEntity($row)));

        $object = $this->table->find()->where(['id' => $row['id']])->first();

        $this->assertNull($object->get('attributes->null'));
        $this->assertTrue($object->get('attributes->boolean'));
        $this->assertSame(10, $object->get('attributes->int'));
        $this->assertSame(125.25, $object->get('attributes->double'));
        $this->assertEquals(2.1E-5, $object->get('attributes->scientific'));
        $this->assertInstanceOf(DateTime::class, $object->get('attributes->datetime'));
        $this->assertSame('2021-01-31 22:11:30', $object->get('attributes->datetime')->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertInstanceOf(DateTime::class, $object->get('attributes->callback'));
        $this->assertSame(2019, $object->get('attributes->callback')->year);
        $this->assertSame(['a'], $object->get('attributes->array'));
        $this->assertSame(['a' => 'a'], $object->get('attributes->object'));
        $this->assertSame([['a' => 'a'], ['a' => 'b']], $object->get('attributes->arrayobject'));
    }

    /**
     * @return void
     */
    public function testFetchWithJsonTypeMapOnTable(): void
    {
        // Our schema already declares the typed paths; this is the same round-trip.
        $this->testFetchWithJsonTypeMapOption();
    }

    /**
     * Path-alias projection: selecting a JSON path under an explicit alias.
     *
     * @return void
     */
    public function testFetchWithAliases(): void
    {
        $row = $this->buildRow();
        $this->assertNotFalse($this->table->save($this->table->newEntity($row)));

        $query = $this->table
            ->find()
            ->enableHydration(false)
            ->select(['id'])
            ->select(['attributes_boolean' => $this->table->extractJsonPath('attributes->boolean')])
            ->select(['datetime' => $this->table->extractJsonPath('attributes->datetime')]);

        $results = $query->toArray();

        $this->assertSame($row['id'], $results[0]['id']);
        $this->assertTrue((bool)$results[0]['attributes_boolean']);
        // Path-alias projection returns the raw JSON value; toPHP casting is applied
        // on full-column hydration, not on a projected scalar extract.
        $this->assertSame('2021-01-31 22:11:30', $results[0]['datetime']);
    }
}
