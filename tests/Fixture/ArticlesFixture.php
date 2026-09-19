<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the JSON-field SAVE/READ integration tests.
 *
 * Demonstrates a real `articles` table with two JSON columns (`data`, `meta`),
 * one typed embed (`profile`) and one fully-untyped embed (`seo` in `meta`).
 *
 * NOTE: the fixture loader does not yet marshal PHP arrays into `json` columns,
 * so the JSON values are encoded to strings in {@see init()}.
 */
class ArticlesFixture extends JsonFieldFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_articles';

    /**
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false],
        'data' => ['type' => 'json'],
        'meta' => ['type' => 'json'],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * @return void
     */
    public function init(): void
    {
        $rows = [
            [
                'title' => 'Seeded article',
                'data' => [
                    'profile' => [
                        'name' => 'seeded-name',
                        'created' => '2024-01-02 03:04:05',
                        'score' => '9.5',
                        'nickname' => 'seeded-nick',
                    ],
                    'note' => 'reference-note',
                    'author_id' => 2,
                    'tag_ids' => [1, 3],
                    'published' => '2024-05-01',
                    'score' => 9.5,
                ],
                'meta' => [
                    'seo' => [
                        'description' => 'seeded-desc',
                        'keywords' => ['a', 'b'],
                    ],
                    'theme' => 'seeded-theme',
                ],
            ],
        ];

        unset($row);

        $this->records = $rows;

        parent::init();
    }
}
