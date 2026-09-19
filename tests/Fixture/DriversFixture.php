<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the geolocation real-case `drivers` table.
 *
 * Faithful clone of `geolocation_data.sql` (5 rows). `id` keeps the
 * original UUID; `data->vehicle_id` stores the *UUID* of the first linked
 * vehicle (from `drivers_vehicles`), so the {@see \JfApp\Model\Entity\Driver}
 * JSON-FK to {@see \JfApp\Model\Entity\Vehicle} resolves.
 */
class DriversFixture extends JsonFieldFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_drivers';

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
                'id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'name' => 'Miss Lou Hermiston',
                'data' => [
                        'ref_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                        'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                    ],
            ],
            [
                'id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'name' => 'Prof. Reece Legros IV',
                'data' => [
                        'ref_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                        'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                    ],
            ],
            [
                'id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'name' => 'Mr. Aric Bartoletti',
                'data' => [
                        'ref_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                        'vehicle_id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                    ],
            ],
            [
                'id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'name' => 'Brandyn Mitchell',
                'data' => [
                        'ref_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                        'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                    ],
            ],
            [
                'id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'name' => 'Rod Walsh',
                'data' => [
                        'ref_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                        'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                    ],
            ],
        ];

        parent::init();
    }
}
