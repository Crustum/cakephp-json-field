<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the JSON-FK BelongsToMany target table `tags`.
 */
class TagsFixture extends JsonFieldFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_tags';

    /**
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
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
            ['name' => 'php', 'data' => ['color' => 'blue']],
            ['name' => 'cakephp', 'data' => ['color' => 'red']],
            ['name' => 'json', 'data' => ['color' => 'green']],
        ];

        parent::init();
    }
}
