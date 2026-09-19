<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the `objects` table used by the ported reference `Database/Driver`
 * tests. Mirrors the reference `objects` table: an `id` plus two JSON columns
 * (`attributes`, `at2`). Rows are inserted by the tests themselves through the
 * `ObjectsTable` (our database layer), so `$records` stays empty.
 */
class ObjectsFixture extends JsonFieldFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_objects';

    /**
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'uuid'],
        'attributes' => ['type' => 'json'],
        'at2' => ['type' => 'json'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];
}
