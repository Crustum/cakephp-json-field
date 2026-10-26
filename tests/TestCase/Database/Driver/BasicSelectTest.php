<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Cake\I18n\DateTime;
use Cake\Utility\Text;

/**
 * Faithful port of the reference `BasicSelectTest`: insert a single `objects`
 * row through the JSON-field database layer and assert the stored document is
 * decoded and typed JSON paths are cast (datetime + a registered callback caster)
 * on read.
 *
 * @uses \Crustum\JsonField\Model\Behavior\JsonFieldBehavior
 * @uses \Crustum\JsonField\Database\Schema\JsonSchemaReader
 */
class BasicSelectTest extends TestCase
{
    /**
     * Builds a single `objects` row from the fluent DataGenerator, mirroring the
     * reference scenario (long / string / double / boolean / datetime / callback
     * inside `attributes`, a nested `at2` document).
     *
     * @return array<string, mixed>
     */
    protected function buildRow(): array
    {
        $datetime = new DateTime('2021-01-31 22:11:30');

        return $this->generator
            ->clear()
            ->seed(1)
            ->static('id', Text::uuid())
            ->static('attributes.long', 1234567890)
            ->static('attributes.string', 'a string')
            ->static('attributes.double', 12.3)
            ->static('attributes.boolean', true)
            ->static('attributes.datetime', $datetime)
            ->static('attributes.callback', $datetime)
            ->static('at2.string', 'another string')
            ->static('at2.array', [1, 2, 3])
            ->generate();
    }

    /**
     * Insert + fetch round-trips the JSON document and applies per-path casts.
     *
     * @return void
     */
    public function testFetch(): void
    {
        $row = $this->buildRow();
        $entity = $this->table->newEntity($row);
        $this->assertNotFalse($this->table->save($entity));

        /** @var \JfApp\Model\Entity\JsonObject $object */
        $object = $this->table->find()->where(['id' => $row['id']])->first();

        $this->assertSame(1234567890, $object->get('attributes->long'));
        $this->assertSame('a string', $object->get('attributes->string'));
        $this->assertSame(12.3, $object->get('attributes->double'));
        $this->assertTrue($object->get('attributes->boolean'));
        $this->assertSame('another string', $object->get('at2->string'));
        $this->assertSame([1, 2, 3], $object->get('at2->array'));

        $this->assertInstanceOf(DateTime::class, $object->get('attributes->datetime'));
        $this->assertSame('2021-01-31 22:11:30', $object->get('attributes->datetime')->format('Y-m-d H:i:s'));

        $this->assertInstanceOf(DateTime::class, $object->get('attributes->callback'));
        $this->assertSame(2019, $object->get('attributes->callback')->year);
    }

    /**
     * A second fetch confirms the casts are stable and the raw JSON string is
     * internally stored (the callback round-trips to its `year - 2` form).
     *
     * @return void
     */
    public function testFetchWithAliases(): void
    {
        $row = $this->buildRow();
        $entity = $this->table->newEntity($row);
        $this->assertNotFalse($this->table->save($entity));

        $object = $this->table->find()->where(['id' => $row['id']])->first();

        $this->assertTrue($object->get('attributes->boolean'));
        $this->assertInstanceOf(DateTime::class, $object->get('attributes->datetime'));
        $this->assertInstanceOf(DateTime::class, $object->get('attributes->callback'));
        $this->assertSame(2019, $object->get('attributes->callback')->year);
    }
}
