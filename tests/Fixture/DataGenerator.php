<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\Utility\Hash;
use Faker\Factory;
use Faker\Generator;

/**
 * Fluent, Faker-backed seeder producing nested JSON documents for the
 * JSON-field database-layer (Driver) tests, plus the static factories used by
 * the other coverage-parity suites.
 *
 * This is a faithful port of the reference `Lqdt\OrmJson` `DataGenerator`: it
 * exposes `clear()` / `seed()` / `static()` / `callable()` / `faker()` /
 * `sequence()` / `generate()` so the reference `Database/Driver` scenarios can
 * be reproduced. Internally it uses `Cake\Utility\Hash` for dotted key
 * set/flatten (the reference used `adbar/dot`, which is not a dependency here).
 *
 * Every factory is seeded so produced data is reproducible across runs and
 * across drivers.
 */
class DataGenerator
{
    /**
     * Faker generator instance.
     *
     * @var \Faker\Generator
     */
    public Generator $faker;

    /**
     * Scheme storage (dotted key => value or callable).
     *
     * @var array<string, mixed>
     */
    public array $scheme = [];

    /**
     * Initializes the generator and seeds Faker for reproducible output.
     *
     * @param int $seed Seed controlling Faker output.
     */
    public function __construct(int $seed = 0)
    {
        $this->faker = Factory::create();
        $this->seed($seed);
    }

    /**
     * Clears the current scheme.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->scheme = [];

        return $this;
    }

    /**
     * Seeds the Faker generator.
     *
     * @param int $seed Seed value.
     * @return self
     */
    public function seed(int $seed): self
    {
        $this->faker->seed($seed);

        return $this;
    }

    /**
     * Adds a static value at a dotted key.
     *
     * @param string $key Target dotted key.
     * @param mixed $value Value to store.
     * @return self
     */
    public function static(string $key, mixed $value): self
    {
        $this->scheme[$key] = $value;

        return $this;
    }

    /**
     * Adds a callable generator at a dotted key.
     *
     * The callable receives the current row data (array), the key, and the row offset.
     *
     * @param string $key Target dotted key.
     * @param callable(array, string, int):mixed $callable Value generator.
     * @return self
     */
    public function callable(string $key, callable $callable): self
    {
        $this->scheme[$key] = $callable;

        return $this;
    }

    /**
     * Adds a Faker-generated value at a dotted key.
     *
     * @param string $key Target dotted key.
     * @param string $fake Faker formatter name.
     * @param mixed ...$params Additional Faker formatter parameters.
     * @return self
     */
    public function faker(string $key, string $fake, ...$params): self
    {
        return $this->callable(
            $key,
            fn() => $this->faker->{$fake}(...$params),
        );
    }

    /**
     * Adds a looping sequence at a dotted key.
     *
     * @param string $key Target dotted key.
     * @param array<int, mixed> $sequence Values looped per row offset.
     * @return self
     */
    public function sequence(string $key, array $sequence): self
    {
        return $this->callable(
            $key,
            function ($data, $key, $offset) use ($sequence) {
                $count = count($sequence);

                return $sequence[$offset % $count];
            },
        );
    }

    /**
     * Generates data from the scheme for one entity or a collection.
     *
     * @param int $n Number of rows to generate.
     * @param int $offset Starting row offset.
     * @return array<string, mixed>|list<array<string, mixed>> A single row when `$n` is 1, else a list.
     */
    public function generate(int $n = 1, int $offset = 0): array
    {
        if ($n === 1) {
            $data = [];
            foreach ($this->scheme as $key => $value) {
                if (is_callable($value)) {
                    $data = Hash::insert($data, $key, $value($data, $key, $offset));
                } else {
                    $data = Hash::insert($data, $key, $value);
                }
            }

            return $data;
        }

        $collection = [];
        for ($i = 0; $i < $n; $i++) {
            $collection[] = $this->generate(1, $i);
        }

        return $collection;
    }

    /**
     * Builds a profile sub-document with mixed scalar and nested shapes.
     *
     * @param int $seed Stable seed controlling the generated values.
     * @return array<string, mixed> The profile document.
     */
    public static function profile(int $seed): array
    {
        return [
            'name' => 'profile-' . $seed,
            'score' => round(50.0 + ($seed % 50) + ($seed % 7) / 10, 2),
            'active' => $seed % 2 === 0,
            'created' => sprintf('2024-%02d-%02d 12:00:00', ($seed % 12) + 1, ($seed % 28) + 1),
            'level' => $seed % 10,
            'tags' => array_map(static fn($i): string => 'tag-' . $i, range(0, $seed % 5)),
            'config' => [
                'timeout' => 10 + ($seed % 90),
                'flags' => ['read', 'write', 'admin'],
                'nested' => ['deep' => ['value' => $seed]],
            ],
        ];
    }

    /**
     * Builds a meta sub-document containing an untyped seo embed.
     *
     * @param int $seed Stable seed controlling the generated values.
     * @return array<string, mixed> The meta document.
     */
    public static function meta(int $seed): array
    {
        return [
            'theme' => $seed % 3 === 0 ? 'light' : 'dark',
            'count' => $seed % 13,
            'seo' => [
                'description' => 'seo-description-' . $seed,
                'keywords' => array_map(static fn($i): string => 'kw-' . $i, range(0, $seed % 4)),
                'items' => array_map(
                    static fn($i): array => ['rank' => $i + 1, 'term' => 'term-' . $i . '-' . $seed],
                    range(0, $seed % 3),
                ),
            ],
        ];
    }

    /**
     * Builds a full article document (data + meta columns) for the `articles` table.
     *
     * @param int $seed Stable seed controlling the generated values.
     * @return array{title: string, data: array<string, mixed>, meta: array<string, mixed>} The row.
     */
    public static function article(int $seed): array
    {
        return [
            'title' => 'Article ' . $seed,
            'data' => ['note' => 'note-' . $seed, 'author_id' => ($seed % 20) + 1, 'profile' => self::profile($seed)],
            'meta' => self::meta($seed),
        ];
    }

    /**
     * Builds a geographic point document stored inside a JSON column.
     *
     * @param int $seed Stable seed controlling the generated values.
     * @return array<string, mixed> The point document with `lat` / `lon`.
     */
    public static function geoPoint(int $seed): array
    {
        return [
            'lat' => round(-90.0 + ($seed % 180) + ($seed % 11) / 100, 6),
            'lon' => round(-180.0 + ($seed % 360) + ($seed % 13) / 100, 6),
            'altitude' => round(($seed % 5000) / 10, 2),
        ];
    }

    /**
     * Builds a location row document for the geolocation real-case.
     *
     * @param int $seed Stable seed controlling the generated values.
     * @return array{name: string, point: array<string, mixed>} The location row.
     */
    public static function location(int $seed): array
    {
        return [
            'name' => 'Location ' . $seed,
            'point' => self::geoPoint($seed),
        ];
    }

    /**
     * Builds a vehicle row document referencing a location through a JSON-FK.
     *
     * @param int $seed Stable seed controlling the generated values.
     * @param int $locationId Foreign key into the locations table.
     * @return array{name: string, data: array<string, mixed>} The vehicle row.
     */
    public static function vehicle(int $seed, int $locationId): array
    {
        return [
            'name' => 'Vehicle ' . $seed,
            'data' => [
                'location_id' => $locationId,
                'plate' => 'PLATE-' . $seed,
                'mileage' => ($seed % 200) * 1000,
            ],
        ];
    }

    /**
     * Builds a driver row document referencing a vehicle through a JSON-FK.
     *
     * @param int $seed Stable seed controlling the generated values.
     * @param int $vehicleId Foreign key into the vehicles table.
     * @return array{name: string, data: array<string, mixed>} The driver row.
     */
    public static function driver(int $seed, int $vehicleId): array
    {
        return [
            'name' => 'Driver ' . $seed,
            'data' => [
                'vehicle_id' => $vehicleId,
                'license' => 'LIC-' . $seed,
                'rating' => round(1.0 + ($seed % 50) / 10, 1),
            ],
        ];
    }

    /**
     * Produces an indexed list of documents from a per-index factory.
     *
     * @param int $count Number of documents to generate.
     * @param callable(int):mixed $factory Receives the index and returns a document.
     * @return list<mixed> The generated documents.
     */
    public static function batch(int $count, callable $factory): array
    {
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $out[] = $factory($i);
        }

        return $out;
    }
}
