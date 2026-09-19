<?php
declare(strict_types=1);

/**
 * Test database schema for Crustum/JsonField.
 *
 * Layout:
 *  - The full CakePHP core test schema (`vendor/cakephp/cakephp/tests/schema.php`)
 *    is merged in UNPREFIXED. This lets any `core.*` fixture (articles, authors,
 *    comments, menu_link_trees, ...) resolve against our JSON driver without us
 *    touching CakePHP's own fixtures.
 *  - Our plugin tables are prefixed `jf_` so they never collide with core tables,
 *    now or in future tests.
 *
 * Every JSON column is declared as plain `'type' => 'json'`. CakePHP's core
 * schema dialects map this to `jsonb` on PostgreSQL, `json` on MySQL, and
 * `json`/text on SQLite — so no per-driver branching is needed here.
 *
 * Loaded by `SchemaLoader::loadInternalFile()` in `tests/bootstrap.php`.
 *
 * @return array<string, mixed>
 */

$coreSchema = require dirname(__DIR__) . '/vendor/cakephp/cakephp/tests/schema.php';

return array_merge($coreSchema, [
    'jf_articles' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'title' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'data' => [
                'type' => 'json',
            ],
            'meta' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_users' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'username' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'data' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_comments' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'article_id' => [
                'type' => 'integer',
                'null' => true,
            ],
            'body' => [
                'type' => 'text',
            ],
            'data' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
            'article_fk' => [
                'type' => 'foreign',
                'columns' => ['article_id'],
                'references' => ['jf_articles', 'id'],
            ],
        ],
    ],
    'jf_logs' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'level' => [
                'type' => 'string',
                'length' => 32,
                'null' => false,
            ],
            'data' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_tags' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'name' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'data' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_reference_articles' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'title' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'data' => [
                'type' => 'json',
            ],
            'meta' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_locations' => [
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'name' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'point' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_drivers' => [
        'columns' => [
            'id' => [
                'type' => 'uuid',
                'null' => false,
            ],
            'name' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'data' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_vehicles' => [
        'columns' => [
            'id' => [
                'type' => 'uuid',
                'null' => false,
            ],
            'name' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'data' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_drivers_vehicles' => [
        'columns' => [
            'id' => [
                'type' => 'uuid',
                'null' => false,
            ],
            'driver_id' => [
                'type' => 'uuid',
                'null' => false,
            ],
            'vehicle_id' => [
                'type' => 'uuid',
                'null' => false,
            ],
            'beginning' => [
                'type' => 'datetime',
                'null' => false,
            ],
            'ending' => [
                'type' => 'datetime',
                'null' => false,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
    'jf_objects' => [
        'columns' => [
            'id' => [
                'type' => 'uuid',
                'null' => false,
            ],
            'attributes' => [
                'type' => 'json',
            ],
            'at2' => [
                'type' => 'json',
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
]);
