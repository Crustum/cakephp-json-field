<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database;

use Cake\Database\Driver;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\Path\JsonPathParser;
use InvalidArgumentException;

/**
 * @uses \Crustum\JsonField\Database\JsonTypeMap
 */
class JsonTypeMapTest extends TestCase
{
    /**
     * Returns the active test driver for casting to/from the database.
     */
    private function driver(): Driver
    {
        return ConnectionManager::get('test')->getDriver();
    }

    public function testAddJsonTypeAndHasType(): void
    {
        $map = new JsonTypeMap();
        $this->assertFalse($map->hasType('data->created'));

        $map->addJsonType('data->created', 'datetime');
        $this->assertTrue($map->hasType('data->created'));
    }

    public function testAddJsonTypes(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonTypes([
            'data->created' => 'datetime',
            'data->price' => 'decimal',
        ]);

        $this->assertTrue($map->hasType('data->created'));
        $this->assertTrue($map->hasType('data->price'));
        $this->assertSame('datetime', $map->type('data->created')['type']);
    }

    public function testTypeReturnsDefinition(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('data->created', 'datetime');

        $definition = $map->type('data->created');
        $this->assertSame('datetime', $definition['type']);
        $this->assertNull($definition['marshal']);
        $this->assertNull($definition['toPHP']);
        $this->assertNull($definition['toDatabase']);
    }

    public function testMissingTypeReturnsNull(): void
    {
        $map = new JsonTypeMap();
        $this->assertNull($map->type('data->missing'));
    }

    public function testLookupNormalizesNotation(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('data->created', 'datetime');

        $expected = JsonPathParser::parse('created@data')->toFieldPath();
        $this->assertSame('data->created', $expected);
        $this->assertTrue($map->hasType('created@data'));
    }

    public function testGetTypesReturnsCanonicalKeys(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('created@data', 'datetime');

        $this->assertArrayHasKey('data->created', $map->getTypes());
    }

    public function testGetCasterReturnsNullWhenMissing(): void
    {
        $map = new JsonTypeMap();
        $this->assertNull($map->getCaster('data->missing', JsonTypeMap::OPERATION_TO_PHP));
    }

    public function testMarshalCasterFromTypeName(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('data->created', 'datetime');

        $value = $map->castValue('data->created', '2020-01-01', JsonTypeMap::OPERATION_MARSHAL);
        $this->assertInstanceOf(DateTime::class, $value);
    }

    public function testToDatabaseCasterRequiresDriver(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('data->created', 'datetime');

        $this->expectException(InvalidArgumentException::class);
        $map->castValue('data->created', '2020-01-01', JsonTypeMap::OPERATION_TO_DATABASE);
    }

    public function testToDatabaseAndToPhpCastWithDriver(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('data->created', 'datetime');

        $driver = $this->driver();

        $marshalled = $map->castValue('data->created', '2020-01-01', JsonTypeMap::OPERATION_MARSHAL);
        $db = $map->castValue('data->created', $marshalled, JsonTypeMap::OPERATION_TO_DATABASE, $driver);
        $this->assertIsString($db);

        $php = $map->castValue('data->created', $db, JsonTypeMap::OPERATION_TO_PHP, $driver);
        $this->assertInstanceOf(DateTime::class, $php);
    }

    public function testCustomCallableCaster(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('data->code', [
            'toPHP' => static fn(mixed $value): string => strtoupper((string)$value),
        ]);

        $this->assertSame('ABC', $map->castValue('data->code', 'abc', JsonTypeMap::OPERATION_TO_PHP));
    }

    public function testCastValueReturnsValueWhenNoCaster(): void
    {
        $map = new JsonTypeMap();
        $this->assertSame(42, $map->castValue('data->nope', 42, JsonTypeMap::OPERATION_TO_PHP));
    }

    public function testClearRemovesAllTypes(): void
    {
        $map = new JsonTypeMap();
        $map->addJsonType('data->created', 'datetime');
        $map->clear();

        $this->assertFalse($map->hasType('data->created'));
        $this->assertSame([], $map->getTypes());
    }
}
