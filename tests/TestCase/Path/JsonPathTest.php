<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Path;

use Cake\TestSuite\TestCase;
use Crustum\JsonField\Path\JsonPath;

/**
 * @uses \Crustum\JsonField\Path\JsonPath
 */
class JsonPathTest extends TestCase
{
    public function testToJsonPathNested(): void
    {
        $path = new JsonPath('data', ['deep', 'nested', 'key']);
        $this->assertSame('$.deep.nested.key', $path->toJsonPath());
    }

    public function testToJsonPathJoker(): void
    {
        $path = new JsonPath('data', ['items', '*', 'sku']);
        $this->assertSame('$.items[*].sku', $path->toJsonPath());
    }

    public function testToJsonPathNumericIndex(): void
    {
        $path = new JsonPath('data', ['items', '0']);
        $this->assertSame('$.items[0]', $path->toJsonPath());
    }

    public function testToPathArray(): void
    {
        $path = new JsonPath('data', ['deep', 'nested', 'key']);
        $this->assertSame('{deep,nested,key}', $path->toPathArray());
    }

    public function testToAlias(): void
    {
        $path = new JsonPath('data', ['deep', 'nested', 'key']);
        $this->assertSame('data_deep_nested_key', $path->toAlias());
    }

    public function testHasJoker(): void
    {
        $this->assertTrue((new JsonPath('data', ['*']))->hasJoker());
        $this->assertFalse((new JsonPath('data', ['a']))->hasJoker());
    }

    public function testToFieldPath(): void
    {
        $this->assertSame(
            'data->deep.nested.key',
            (new JsonPath('data', ['deep', 'nested', 'key']))->toFieldPath(),
        );
        $this->assertSame(
            'data->items[*].sku',
            (new JsonPath('data', ['items', '*', 'sku']))->toFieldPath(),
        );
        $this->assertSame(
            'data->items[0]',
            (new JsonPath('data', ['items', '0']))->toFieldPath(),
        );
    }
}
