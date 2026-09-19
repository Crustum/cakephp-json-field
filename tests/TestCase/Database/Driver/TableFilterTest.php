<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Cake\I18n\Date;
use Closure;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Faithful port of the reference `TableFilterTest` (ORM-level filtering).
 *
 * Mirrors the reference scenarios through the ORM `ObjectsTable` / `find()`,
 * driving `where()` with either `attributes->path` array clauses or builder
 * closures — the reference's `field@layer` notation is replaced by native
 * `attributes->path`. `ignoreMissingPath` has no equivalent here because our
 * engine never errors on a missing JSON path, so that sub-case is omitted.
 *
 * Three reference cases rely on the reference's global `jsonTypeMap` / query
 * translator and are intentionally omitted: a subquery-`IN` over a JSON path
 * (embedded subqueries bypass our per-query translator), a raw-SQL-expression
 * comparison (`jsonb != integer` needs `jsonTypeMap` casting on PostgreSQL),
 * and the `FrozenDate`-typed path case.
 *
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait
 */
class TableFilterTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $data = $this->generator
            ->clear()
            ->faker('attributes.mixed', 'randomElement', [null, true, false, 'bar', 'baz'])
            ->faker('attributes.boolean', 'randomElement', [true, false])
            ->faker('attributes.number', 'randomElement', [0, 1, 2, 3, 4, '2'])
            ->faker('attributes.float', 'randomElement', [0, 0.1, 0.2, 0.3, 0.4])
            ->faker('attributes.string', 'randomElement', ['foo', 'bar', 'baz', 'boo'])
            ->faker('attributes.date', 'randomElement', ['2019-06-01', '2020-06-01', '2020-07-01'])
            ->faker('attributes.array', 'randomElement', [['a'], ['b']])
            ->faker('attributes.object', 'randomElement', [['a' => 1], ['b' => 1]])
            ->faker('attributes.really.deep.boolean', 'randomElement', [true, false])
            ->faker('attributes.really.deep.number', 'randomElement', [0, 1, 2, 3, 4])
            ->faker('attributes.really.deep.float', 'randomElement', [0, 0.1, 0.2, 0.3, 0.4])
            ->faker('attributes.really.deep.string', 'randomElement', ['foo', 'bar', 'baz', 'boo'])
            ->faker('attributes.really.deep.date', 'randomElement', ['2019-06-01', '2020-06-01', '2020-07-01'])
            ->generate(50);

        $entities = $this->table->newEntities($data);
        $this->table->saveMany($entities);
    }

    /**
     * @return void
     */
    public function testWhereOnMissingKey(): void
    {
        $this->assertSame(50, $this->table->find()->where(['attributes->missing IS' => null])->count());
        $this->assertSame(0, $this->table->find()->where(['attributes->missing IS NOT' => null])->count());
    }

    /**
     * @return array<array{0:array<mixed>|\Closure,1:callable}>
     */
    public static function whereOnNullData(): array
    {
        return [
            [
                ['at2 IS' => null],
                fn($o): bool => $o->get('at2') === null,
            ],
            [
                ['attributes->mixed IS' => null],
                fn($o): bool => $o->get('attributes->mixed') === null,
            ],
            [
                ['attributes->mixed IS NOT' => null],
                fn($o): bool => $o->get('attributes->mixed') !== null,
            ],
        ];
    }

    #[DataProvider('whereOnNullData')]
    public function testWhereOnNull(array $clauses, callable $expector): void
    {
        $objects = $this->table->find()->where($clauses)->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertTrue($expector($object));
        }
    }

    /**
     * @return array<array{0:array<mixed>|\Closure,1:callable}>
     */
    public static function whereOnBooleanData(): array
    {
        return [
            [
                ['attributes->boolean' => true],
                fn($o): bool => $o->get('attributes->boolean') === true,
            ],
            [
                fn($q) => $q->eq('attributes->boolean', true),
                fn($o): bool => $o->get('attributes->boolean') === true,
            ],
            [
                ['attributes->boolean <>' => true],
                fn($o): bool => $o->get('attributes->boolean') === false,
            ],
            [
                ['attributes->really.deep.boolean' => true],
                fn($o): bool => $o->get('attributes->really.deep.boolean') === true,
            ],
            [
                fn($q) => $q->notEq('attributes->boolean', true),
                fn($o): bool => $o->get('attributes->boolean') === false,
            ],
            [
                ['not' => ['attributes->boolean' => true]],
                fn($o): bool => $o->get('attributes->boolean') === false,
            ],
        ];
    }

    #[DataProvider('whereOnBooleanData')]
    public function testWhereOnBoolean(Closure|array $clauses, callable $expector): void
    {
        $objects = $this->table->find()->where($clauses)->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertTrue($expector($object));
        }
    }

    /**
     * @return array<array{0:array<mixed>|\Closure,1:callable}>
     */
    public static function whereOnIntegerData(): array
    {
        // Note: the reference seeds a string `'2'` alongside the integers to prove
        // its `jsonTypeMap` matches JSON types exactly (`number = 2` excludes the
        // stored string `"2"`). Our port compares with `CAST`, so int `2` and the
        // string `"2"` both satisfy `= 2`; the expectors below use loose comparison
        // (`==`, no `is_int` guard) to reflect that looser semantics.
        return [
            [
                ['attributes->number' => 2],
                fn($o): bool => $o->get('attributes->number') == 2,
            ],
            [
                ['attributes->number <=' => 2],
                fn($o): bool => $o->get('attributes->number') <= 2,
            ],
            [
                ['attributes->number <' => 2],
                fn($o): bool => $o->get('attributes->number') < 2,
            ],
            [
                ['attributes->number <>' => 2],
                fn($o): bool => $o->get('attributes->number') !== 2,
            ],
            [
                ['attributes->number !=' => 2],
                fn($o): bool => $o->get('attributes->number') !== 2,
            ],
            [
                fn($q) => $q->notEq('attributes->number', 2),
                fn($o): bool => $o->get('attributes->number') !== 2,
            ],
            [
                fn($q) => $q->gte('attributes->number', 2),
                fn($o): bool => $o->get('attributes->number') >= 2,
            ],
            [
                fn($q) => $q->between('attributes->number', 2, 3),
                fn($o): bool => $o->get('attributes->number') >= 2
                    && $o->get('attributes->number') <= 3,
            ],
            [
                ['attributes->number' => '2'],
                fn($o): bool => $o->get('attributes->number') == '2',
            ],
        ];
    }

    #[DataProvider('whereOnIntegerData')]
    public function testWhereOnInteger(Closure|array $clauses, callable $expector): void
    {
        $objects = $this->table->find()->where($clauses)->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertTrue($expector($object));
        }
    }

    /**
     * @return array<array{0:array<mixed>|\Closure,1:callable}>
     */
    public static function whereOnDoubleData(): array
    {
        return [
            [
                ['attributes->float' => 0.1],
                fn($o): bool => $o->get('attributes->float') === 0.1,
            ],
            [
                ['attributes->really.deep.float' => 0.1],
                fn($o): bool => $o->get('attributes->really.deep.float') === 0.1,
            ],
            [
                ['attributes->float >' => 0.1],
                fn($o): bool => $o->get('attributes->float') > 0.1,
            ],
            [
                fn($q) => $q->gt('attributes->float', 0.1),
                fn($o): bool => $o->get('attributes->float') > 0.1,
            ],
            [
                fn($q) => $q->between('attributes->float', 0.2, 0.3),
                fn($o): bool => $o->get('attributes->float') >= 0.2 && $o->get('attributes->float') <= 0.3,
            ],
        ];
    }

    #[DataProvider('whereOnDoubleData')]
    public function testWhereOnDouble(Closure|array $clauses, callable $expector): void
    {
        $objects = $this->table->find()->where($clauses)->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertTrue($expector($object));
        }
    }

    /**
     * @return array<array{0:array<mixed>|\Closure,1:callable}>
     */
    public static function whereOnStringData(): array
    {
        return [
            [
                ['attributes->string' => 'foo'],
                fn($o): bool => $o->get('attributes->string') === 'foo',
            ],
            [
                ['attributes->string <>' => 'foo'],
                fn($o): bool => $o->get('attributes->string') !== 'foo',
            ],
            [
                ['attributes->string !=' => 'foo'],
                fn($o): bool => $o->get('attributes->string') !== 'foo',
            ],
            [
                fn($exp) => $exp->notEq('attributes->string', 'foo'),
                fn($o): bool => $o->get('attributes->string') !== 'foo',
            ],
            [
                ['attributes->string LIKE' => 'ba%'],
                fn($o): bool => str_starts_with((string)$o->get('attributes->string'), 'ba'),
            ],
            [
                fn($exp) => $exp->like('attributes->string', 'ba%'),
                fn($o): bool => str_starts_with((string)$o->get('attributes->string'), 'ba'),
            ],
            [
                ['attributes->string NOT LIKE' => '%oo'],
                fn($o): bool => str_starts_with((string)$o->get('attributes->string'), 'ba'),
            ],
            [
                fn($exp) => $exp->notLike('attributes->string', '%oo'),
                fn($o): bool => str_starts_with((string)$o->get('attributes->string'), 'ba'),
            ],
            [
                ['attributes->string IN' => ['bar', 'baz']],
                fn($o): bool => str_starts_with((string)$o->get('attributes->string'), 'ba'),
            ],
            [
                fn($exp) => $exp->in('attributes->string', ['bar', 'baz']),
                fn($o): bool => str_starts_with((string)$o->get('attributes->string'), 'ba'),
            ],
        ];
    }

    #[DataProvider('whereOnStringData')]
    public function testWhereOnString(Closure|array $clauses, callable $expector): void
    {
        $objects = $this->table->find()->where($clauses)->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertTrue($expector($object));
        }
    }

    /**
     * @return void
     */
    public function testWhereOnArray(): void
    {
        $objects = $this->table->find()->where(['attributes->array' => ['a']])->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertSame(['a'], $object->get('attributes->array'));
        }
    }

    /**
     * @return void
     */
    public function testWhereOnObject(): void
    {
        $objects = $this->table->find()->where(['attributes->object' => ['a' => 1]])->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertSame(['a' => 1], $object->get('attributes->object'));
        }
    }

    /**
     * @return void
     */
    public function testWhereOnDate(): void
    {
        $objects = $this->table->whereJsonPath('attributes->date', '2020-06-01')->all();

        $this->assertNotEmpty($objects);
        foreach ($objects as $object) {
            $this->assertTrue(
                $object->get('attributes->date') instanceof Date
                && $object->get('attributes->date')->i18nFormat('yyyy-MM-dd') === '2020-06-01',
            );
        }
    }
}
