<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Path;

use Cake\TestSuite\TestCase;
use Crustum\JsonField\Path\Exception\UnparsableJsonPathException;
use Crustum\JsonField\Path\JsonPathParser;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @uses \Crustum\JsonField\Path\JsonPathParser
 * @uses \Crustum\JsonField\Path\JsonPath
 * @uses \Crustum\JsonField\Path\Exception\UnparsableJsonPathException
 */
class JsonPathParserTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function detectProvider(): array
    {
        return [
            'plain' => ['data', 0],
            'dotted only' => ['data.nested', 0],
            'v2' => ['data->deep.nested.key', 2],
            'v1' => ['deep.nested.key@data', 1],
        ];
    }

    #[DataProvider('detectProvider')]
    public function testDetect(string $field, int $expected): void
    {
        $this->assertSame($expected, JsonPathParser::detect($field));
    }

    public function testParseV2(): void
    {
        $path = JsonPathParser::parse('data->deep.nested.key');
        $this->assertSame('data', $path->column);
        $this->assertSame(['deep', 'nested', 'key'], $path->nodes);
    }

    public function testParseV1MatchesV2(): void
    {
        $v1 = JsonPathParser::parse('deep.nested.key@data');
        $v2 = JsonPathParser::parse('data->deep.nested.key');
        $this->assertEquals($v1, $v2);
        $this->assertSame('data', $v1->column);
        $this->assertSame(['deep', 'nested', 'key'], $v1->nodes);
    }

    public function testParseJoker(): void
    {
        $path = JsonPathParser::parse('data->items[*].sku');
        $this->assertSame(['items', '*', 'sku'], $path->nodes);
        $this->assertTrue($path->hasJoker());
    }

    public function testParseNumericIndex(): void
    {
        $path = JsonPathParser::parse('data->items[0]');
        $this->assertSame(['items', '0'], $path->nodes);
    }

    public function testParseKeepsModelPrefixInColumn(): void
    {
        $path = JsonPathParser::parse('Some.Model.data->deep.key');
        $this->assertSame('Some.Model.data', $path->column);
        $this->assertSame(['deep', 'key'], $path->nodes);
    }

    public function testParseThrowsForPlainString(): void
    {
        $this->expectException(UnparsableJsonPathException::class);
        JsonPathParser::parse('plain');
    }

    public function testParseIsCached(): void
    {
        $a = JsonPathParser::parse('data->a.b');
        $b = JsonPathParser::parse('data->a.b');
        $this->assertSame($a, $b);
    }

    public function testRoundTripFieldPath(): void
    {
        $this->assertSame(
            'data->deep.nested.key',
            JsonPathParser::parse('data->deep.nested.key')->toFieldPath(),
        );
        $this->assertSame(
            'data->items[*].sku',
            JsonPathParser::parse('data->items[*].sku')->toFieldPath(),
        );
    }

    public function testParseNodes(): void
    {
        $this->assertSame(['items', '*', 'sku'], JsonPathParser::parseNodes('items[*].sku'));
        $this->assertSame(['a', 'b'], JsonPathParser::parseNodes('a.b'));
    }
}
