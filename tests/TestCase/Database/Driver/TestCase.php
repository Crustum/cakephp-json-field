<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase as CakeTestCase;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use Crustum\JsonField\Test\Fixture\DataGenerator;
use JfApp\Model\Entity\JsonObject;
use JfApp\Model\Table\ObjectsTable;

/**
 * Base case for the ported reference `Database/Driver` tests.
 *
 * Unlike the reference suite (which upgraded a raw connection with "dat fields"),
 * our database layer lives in {@see ObjectsTable} + {@see JsonFieldBehavior}:
 * the engine is never rewired. A `attributes->callback` path is registered with
 * a programmatic toPHP / toDatabase caster to exercise the caster API.
 */
abstract class TestCase extends CakeTestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Crustum/JsonField.Objects',
    ];

    /**
     * @var \JfApp\Model\Table\ObjectsTable
     */
    protected ObjectsTable $table;

    /**
     * @var \Cake\Database\Connection
     */
    protected $connection;

    /**
     * @var \Crustum\JsonField\Test\Fixture\DataGenerator
     */
    protected DataGenerator $generator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        JsonSchemaReader::clearCache();
        JsonSchemaReader::registerCaster(
            JsonObject::class,
            'attributes->callback',
            static fn($year): DateTime => DateTime::createFromFormat('Y', (string)$year),
            static fn(DateTime $time): string => (string)($time->year - 2),
        );
        JsonSchemaReader::registerCaster(
            JsonObject::class,
            'attributes->year',
            static fn($year): DateTime => DateTime::createFromFormat('Y', (string)$year),
            static fn(DateTime $time): int => $time->year,
        );
        JsonSchemaReader::registerCaster(
            JsonObject::class,
            'attributes->relativeYear',
            static fn($year): DateTime => DateTime::createFromFormat('Y', (string)$year),
            static function ($value, $driver = null, ?array $attributes = null): int {
                $dt = is_string($attributes['datetime'])
                    ? DateTime::createFromFormat('Y-m-d H:i:s', $attributes['datetime'])
                    : $attributes['datetime'];

                return $dt->year;
            },
        );

        $this->table = TableRegistry::getTableLocator()->get(
            'Objects',
            ['className' => ObjectsTable::class],
        );
        $this->connection = $this->table->getConnection();
        $this->generator = new DataGenerator();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        JsonSchemaReader::clearCache();

        TableRegistry::getTableLocator()->clear();

        parent::tearDown();
    }
}
