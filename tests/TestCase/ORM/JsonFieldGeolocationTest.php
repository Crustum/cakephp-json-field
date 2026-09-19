<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use JfApp\Model\Entity\Location;
use JfApp\Model\Entity\Vehicle;
use JfApp\Model\Table\DriversTable;
use JfApp\Model\Table\LocationsTable;
use JfApp\Model\Table\VehiclesTable;
use PHPUnit\Framework\Attributes\Group;

/**
 * Real-case coverage ported from the reference `cakephp-orm-json`
 * `geolocation_data.sql`: BETWEEN on JSON `point->position.lat/lon` over the
 * full 500-row dataset, plus the JSON-FK chain driver -> vehicle -> location
 * resolved from the original UUID identifiers.
 *
 * @uses \Crustum\JsonField\ORM\JsonFieldAwareTrait
 */
#[Group('orm-deferred')]
class JsonFieldGeolocationTest extends TestCase
{
    /**
     * @var list<string>
     */
    protected array $fixtures = [
        'plugin.Crustum/JsonField.Locations',
        'plugin.Crustum/JsonField.Drivers',
        'plugin.Crustum/JsonField.Vehicles',
        'plugin.Crustum/JsonField.DriversVehicles',
    ];

    private LocationsTable $locations;

    private DriversTable $drivers;

    private VehiclesTable $vehicles;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \JfApp\Model\Table\LocationsTable $locations */
        $locations = TableRegistry::getTableLocator()->get('Locations', ['className' => LocationsTable::class]);
        /** @var \JfApp\Model\Table\DriversTable $drivers */
        $drivers = TableRegistry::getTableLocator()->get('Drivers', ['className' => DriversTable::class]);
        /** @var \JfApp\Model\Table\VehiclesTable $vehicles */
        $vehicles = TableRegistry::getTableLocator()->get('Vehicles', ['className' => VehiclesTable::class]);

        $this->locations = $locations;
        $this->drivers = $drivers;
        $this->vehicles = $vehicles;
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->clear();

        parent::tearDown();
    }

    public function testBetweenOnLatitudeCoversAllRows(): void
    {
        $this->assertSame(500, $this->locations->find()->all()->count());

        // Every reference latitude sits between 45.5 and 47.5.
        $this->assertSame(500, $this->locations->whereJsonPathBetween('point->position.lat', 45.0, 48.0)->count());
        $this->assertSame(500, $this->locations->whereJsonPathBetween('point->position.lon', 4.0, 5.0)->count());
    }

    public function testBetweenOnLatitudeNarrowsToSubset(): void
    {
        $expected = 0;
        foreach ($this->locations->find()->all() as $row) {
            $lat = (float)($row->point['position']['lat'] ?? null);
            if ($lat >= 46.0 && $lat <= 46.5) {
                $expected++;
            }
        }

        $this->assertGreaterThan(0, $expected);
        $this->assertLessThan(500, $expected);
        $this->assertSame($expected, $this->locations->whereJsonPathBetween('point->position.lat', 46.0, 46.5)->count());
    }

    public function testBetweenOnLongitudeNarrowsToSubset(): void
    {
        $expected = 0;
        foreach ($this->locations->find()->all() as $row) {
            $lon = (float)($row->point['position']['lon'] ?? null);
            if ($lon >= 4.55 && $lon <= 4.59) {
                $expected++;
            }
        }

        $this->assertGreaterThan(0, $expected);
        $this->assertSame($expected, $this->locations->whereJsonPathBetween('point->position.lon', 4.55, 4.59)->count());
    }

    public function testOrderByLatitude(): void
    {
        $rows = $this->locations->orderByJsonPath('point->position.lat', 'ASC')->all()->toList();

        $lats = array_map(static fn($r): float => (float)$r->point['position']['lat'], $rows);
        $sorted = $lats;
        usort($sorted, static fn($a, $b): int => $a <=> $b);
        $this->assertSame($sorted, $lats);
    }

    public function testJsonForeignKeyChainResolves(): void
    {
        /** @var \JfApp\Model\Entity\Driver $driver */
        $driver = $this->drivers->find()->where(['name' => 'Miss Lou Hermiston'])->first();
        $this->assertNotNull($driver);
        $this->drivers->loadJsonReferences([$driver]);

        $this->assertNotNull($driver->vehicle);
        $this->assertInstanceOf(Vehicle::class, $driver->vehicle);

        $this->vehicles->loadJsonReferences([$driver->vehicle]);
        $this->assertNotNull($driver->vehicle->location);
        $this->assertInstanceOf(Location::class, $driver->vehicle->location);
    }
}
