<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the geolocation real-case `vehicles` table.
 *
 * Faithful clone of `geolocation_data.sql` (8 rows). `id` keeps the
 * original UUID; `data->geocode_id` preserves the source value, and
 * `data->location_id` stores the *numeric* `locations.id` whose `point.vehicle.id`
 * equals that geocode_id, so the {@see \JfApp\Model\Entity\Vehicle} JSON-FK
 * to {@see \JfApp\Model\Entity\Location} still resolves.
 */
class VehiclesFixture extends JsonFieldFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_vehicles';

    /**
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'uuid'],
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
            [
                'id' => '48f0a309-a684-39a2-80cf-79f4e312d08d',
                'name' => 'Veh 423EF',
                'data' => [
                        'ref_id' => '48f0a309-a684-39a2-80cf-79f4e312d08d',
                        'geocode_id' => '423EF',
                        'location_id' => 5,
                    ],
            ],
            [
                'id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'name' => 'Veh 425EF',
                'data' => [
                        'ref_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                        'geocode_id' => '425EF',
                        'location_id' => 10,
                    ],
            ],
            [
                'id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                'name' => 'Veh 421EF',
                'data' => [
                        'ref_id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                        'geocode_id' => '421EF',
                        'location_id' => 4,
                    ],
            ],
            [
                'id' => 'adf440ed-6bfa-35cc-b545-6e41558011b5',
                'name' => 'Veh 426EF',
                'data' => [
                        'ref_id' => 'adf440ed-6bfa-35cc-b545-6e41558011b5',
                        'geocode_id' => '426EF',
                        'location_id' => 2,
                    ],
            ],
            [
                'id' => 'cbf42b9b-0b42-3792-b282-93b54fe522b7',
                'name' => 'Veh 422EF',
                'data' => [
                        'ref_id' => 'cbf42b9b-0b42-3792-b282-93b54fe522b7',
                        'geocode_id' => '422EF',
                        'location_id' => 21,
                    ],
            ],
            [
                'id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'name' => 'Veh 427EF',
                'data' => [
                        'ref_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                        'geocode_id' => '427EF',
                        'location_id' => 12,
                    ],
            ],
            [
                'id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'name' => 'Veh 424EF',
                'data' => [
                        'ref_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                        'geocode_id' => '424EF',
                        'location_id' => 3,
                    ],
            ],
            [
                'id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'name' => 'Veh 420EF',
                'data' => [
                        'ref_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                        'geocode_id' => '420EF',
                        'location_id' => 1,
                    ],
            ],
        ];

        parent::init();
    }
}
