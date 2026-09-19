<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Port of the reference `QuoteIdentifiersTest`, adapted to our JSON-path-aware
 * `quoteIdentifier()` (the reference's `field@layer` notation is replaced by
 * native `column->path`).
 *
 * Our implementation only resolves the `column->path` JSON syntax; raw SQL
 * function expressions such as `JSON_EXTRACT(...)` are intentionally out of
 * scope (the engine never receives such fragments from our query builder), so
 * those reference cases are omitted here.
 */
class QuoteIdentifiersTest extends TestCase
{
    private function quote(string $identifier): string
    {
        return $this->connection->getDriver()->quoteIdentifier($identifier);
    }

    /**
     * @return array<array{0:string}>
     */
    public static function quoteIdentifierData(): array
    {
        return [
            ['data->\'$.p\''],
            ["data -> 'p'"],
            ['data->"$.p"'],
            ['Model.data->\'$.p\''],
            ['data->>\'$.p\''],
        ];
    }

    #[DataProvider('quoteIdentifierData')]
    public function testIdentifierQuoting(string $input): void
    {
        $pos = strpos($input, '->');
        if ($pos === false) {
            $this->fail("quoteIdentifier input must contain '->': {$input}");
        }

        $column = trim(substr($input, 0, $pos));
        $suffix = substr($input, $pos);

        $expected = $this->quote($column) . $suffix;

        $this->assertSame($expected, $this->quote($input));
    }

    public function testCompatibility(): void
    {
        $qc = substr($this->quote('a'), 0, 1);
        $expect = static fn(string $backtick): string => str_replace('`', $qc, $backtick);

        $cases = [
            'name' => '`name`',
            'Model.*' => '`Model`.*',
            'Items.No_ 2' => '`Items`.`No_ 2`',
            'Items.No_ 2 thing' => '`Items`.`No_ 2 thing`',
            'Items.No_ 2 thing AS thing' => '`Items`.`No_ 2 thing` AS `thing`',
            'Items.Item Category Code = :c1' => '`Items`.`Item Category Code` = :c1',
            'MTD()' => 'MTD()',
            '(sm)' => '(sm)',
            'name AS x' => '`name` AS `x`',
            'Model.name AS x' => '`Model`.`name` AS `x`',
            'Function(Something.foo)' => 'Function(`Something`.`foo`)',
            'Function(SubFunction(Something.foo))' => 'Function(SubFunction(`Something`.`foo`))',
            'Function(Something.foo) AS x' => 'Function(`Something`.`foo`) AS `x`',
            'name-with-minus' => '`name-with-minus`',
            'my-name' => '`my-name`',
            'Foo-Model.*' => '`Foo-Model`.*',
            'Team.P%' => '`Team`.`P%`',
            'Team.G/G' => '`Team`.`G/G`',
            'Model.name as y' => '`Model`.`name` AS `y`',
            'nämé' => '`nämé`',
            'aßa.nämé' => '`aßa`.`nämé`',
            'aßa.*' => '`aßa`.*',
            'Modeß.nämé as y' => '`Modeß`.`nämé` AS `y`',
            'Model.näme Datum as y' => '`Model`.`näme Datum` AS `y`',
        ];

        foreach ($cases as $input => $backtick) {
            $this->assertSame($expect($backtick), $this->quote($input), "quoteIdentifier('{$input}')");
        }
    }
}
