<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the JSON-FK BelongsTo target table `users`.
 */
class UsersFixture extends JsonFieldFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_users';

    /**
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'username' => ['type' => 'string', 'length' => 255, 'null' => false],
        'data' => ['type' => 'json'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            ['username' => 'alice', 'data' => ['role' => 'admin']],
            ['username' => 'bob', 'data' => ['role' => 'editor']],
            ['username' => 'carol', 'data' => ['role' => 'viewer']],
        ];

        parent::init();
    }
}
