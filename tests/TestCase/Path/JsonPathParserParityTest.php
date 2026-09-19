<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Path;

use Cake\TestSuite\TestCase;
use Crustum\JsonField\Path\Exception\UnparsableJsonPathException;
use Crustum\JsonField\Path\JsonPathParser;

/**
 * Parity with the reference plugin's path/notation parsing coverage
 * (DatFieldParserTraitTest) for our engine-neutral `JsonPathParser` / `JsonPath`.
 *
 * @uses \Crustum\JsonField\Path\JsonPathParser
 * @uses \Crustum\JsonField\Path\JsonPath
 */
class JsonPathParserParityTest extends TestCase
{
    public function testDetectNotation(): void
    {
        $this->assertSame(2, JsonPathParser::detect('data->a'));
        $this->assertSame(1, JsonPathParser::detect('a@data'));
        $this->assertSame(0, JsonPathParser::detect('plain'));
        $this->assertSame(0, JsonPathParser::detect('Model.field'));
        $this->assertSame(2, JsonPathParser::detect('data->items[*].sku'));
    }

    public function testParseV2JokerAndIndex(): void
    {
        $path = JsonPathParser::parse('data->items[*].sku');
        $this->assertSame('data', $path->column);
        $this->assertSame(['items', '*', 'sku'], $path->nodes);
        $this->assertTrue($path->hasJoker());
        $this->assertSame('$.items[*].sku', $path->toJsonPath());
        $this->assertSame('data->items[*].sku', $path->toFieldPath());
        $this->assertSame('data_items_*_sku', $path->toAlias());
    }

    public function testParseNumericIndex(): void
    {
        $path = JsonPathParser::parse('data->items[0]');
        $this->assertSame(['items', '0'], $path->nodes);
        $this->assertFalse($path->hasJoker());
        $this->assertSame('$.items[0]', $path->toJsonPath());
    }

    public function testParseNestedJoker(): void
    {
        $path = JsonPathParser::parse('data->arr2[*][*]');
        $this->assertSame(['arr2', '*', '*'], $path->nodes);
        $this->assertTrue($path->hasJoker());
    }

    public function testParseV1Equivalence(): void
    {
        $v2 = JsonPathParser::parse('data->a.b');
        $v1 = JsonPathParser::parse('a.b@data');
        $this->assertEquals($v2, $v1);
        $this->assertSame('data', $v1->column);
        $this->assertSame(['a', 'b'], $v1->nodes);
    }

    public function testParseWithModelPrefix(): void
    {
        $path = JsonPathParser::parse('Model.data->a.b');
        $this->assertSame('Model.data', $path->column);
        $this->assertSame(['a', 'b'], $path->nodes);
    }

    public function testParseInvalidThrows(): void
    {
        $this->expectException(UnparsableJsonPathException::class);
        JsonPathParser::parse('plain');
    }

    public function testParseNodesHelper(): void
    {
        $this->assertSame(['a', 'b', 'c'], JsonPathParser::parseNodes('a.b.c'));
        $this->assertSame(['a', '0', 'b'], JsonPathParser::parseNodes('a[0].b'));
    }

    public function testValueObjectIsCacheable(): void
    {
        $first = JsonPathParser::parse('data->x.y');
        $second = JsonPathParser::parse('data->x.y');
        $this->assertSame($first, $second);
    }

    public function testToPathArrayForEngineWrites(): void
    {
        $path = JsonPathParser::parse('data->a.b');
        $this->assertSame('{a,b}', $path->toPathArray());
    }
}
