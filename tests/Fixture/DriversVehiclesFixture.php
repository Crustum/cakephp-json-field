<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the geolocation real-case `drivers_vehicles` link table.
 *
 * Faithful clone of `geolocation_data.sql` (50 rows). The reference uses
 * this SQL join table to associate drivers with vehicles; `driver_id` /
 * `vehicle_id` keep the original UUIDs. In our JSON-FK design the
 * {@see \JfApp\Model\Entity\Driver} embeds `data->vehicle_id`, but the link
 * rows are kept here for data fidelity.
 */
class DriversVehiclesFixture extends TestFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_drivers_vehicles';

    /**
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'uuid'],
        'driver_id' => ['type' => 'uuid', 'null' => false],
        'vehicle_id' => ['type' => 'uuid', 'null' => false],
        'beginning' => ['type' => 'datetime', 'null' => false],
        'ending' => ['type' => 'datetime', 'null' => false],
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
                'id' => '05da3372-a039-3de0-8d2b-9bf059876fcf',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                'beginning' => '2022-07-13 13:03:41',
                'ending' => '2022-07-13 14:03:41',
            ],
            [
                'id' => '0c069527-cbb5-3214-8544-7356c26ced8c',
                'driver_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-13 15:56:55',
                'ending' => '2022-07-13 16:56:55',
            ],
            [
                'id' => '10729627-7b8a-3f2e-8a2f-bc93afa35b3f',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'beginning' => '2022-07-13 16:44:47',
                'ending' => '2022-07-13 17:44:47',
            ],
            [
                'id' => '10c09ce8-92e4-3968-96e5-c8ff898164e5',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'beginning' => '2022-07-13 15:57:13',
                'ending' => '2022-07-13 16:57:13',
            ],
            [
                'id' => '1212f94b-f0bc-34a1-b1d3-4277e5b5a875',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'beginning' => '2022-07-13 18:19:46',
                'ending' => '2022-07-13 19:19:46',
            ],
            [
                'id' => '12d1fa54-591f-346d-81f9-80d3858aa268',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'beginning' => '2022-07-13 06:46:50',
                'ending' => '2022-07-13 07:46:50',
            ],
            [
                'id' => '14182b12-8f77-3edc-88f3-854eb2858711',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'beginning' => '2022-07-13 13:55:04',
                'ending' => '2022-07-13 14:55:04',
            ],
            [
                'id' => '17b219c4-508c-3a24-ae47-d091a66e69cb',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-14 01:06:45',
                'ending' => '2022-07-14 02:06:45',
            ],
            [
                'id' => '1e1aec37-7d0f-3653-9f3c-828ad602474c',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => 'adf440ed-6bfa-35cc-b545-6e41558011b5',
                'beginning' => '2022-07-13 23:51:45',
                'ending' => '2022-07-14 00:51:45',
            ],
            [
                'id' => '225f8f40-ed53-3803-865c-a7c79ecce4e2',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'beginning' => '2022-07-13 12:22:06',
                'ending' => '2022-07-13 13:22:06',
            ],
            [
                'id' => '2ad3aec2-1bce-3565-8e8c-369e1aa0adcb',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => 'cbf42b9b-0b42-3792-b282-93b54fe522b7',
                'beginning' => '2022-07-13 17:31:41',
                'ending' => '2022-07-13 18:31:41',
            ],
            [
                'id' => '2c412e84-bce8-3332-a33b-eaf24d0e65ea',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-14 09:13:14',
                'ending' => '2022-07-14 10:13:14',
            ],
            [
                'id' => '3033d51a-19d4-3c9f-afa4-9af48a55f0cd',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-13 18:43:14',
                'ending' => '2022-07-13 19:43:14',
            ],
            [
                'id' => '354f4b94-98fc-3263-b94c-827d59f8f93c',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => 'adf440ed-6bfa-35cc-b545-6e41558011b5',
                'beginning' => '2022-07-13 17:45:43',
                'ending' => '2022-07-13 18:45:43',
            ],
            [
                'id' => '37c56cba-4a84-37be-9bc1-7ef17acae6f5',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'beginning' => '2022-07-14 02:51:56',
                'ending' => '2022-07-14 03:51:56',
            ],
            [
                'id' => '3e0d41eb-7b76-3ff3-89b9-725dc6e7c83d',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                'beginning' => '2022-07-14 08:35:57',
                'ending' => '2022-07-14 09:35:57',
            ],
            [
                'id' => '40d764a4-02ae-333e-9219-045eebb30a50',
                'driver_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'beginning' => '2022-07-13 08:36:16',
                'ending' => '2022-07-13 09:36:16',
            ],
            [
                'id' => '438e4776-37e8-3c4b-bd3f-8f98edb45000',
                'driver_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-14 02:21:24',
                'ending' => '2022-07-14 03:21:24',
            ],
            [
                'id' => '44013a5d-95a4-3034-a5ae-30ee3e5dc714',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-13 09:59:07',
                'ending' => '2022-07-13 10:59:07',
            ],
            [
                'id' => '47e112c3-a82c-345c-b0d7-24baa406eaae',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-13 06:36:34',
                'ending' => '2022-07-13 07:36:34',
            ],
            [
                'id' => '4c3b44e4-ed17-3fc4-baf6-efc5f4b8a7b5',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                'beginning' => '2022-07-13 21:08:22',
                'ending' => '2022-07-13 22:08:22',
            ],
            [
                'id' => '54f7ce47-d3af-30fa-8d8f-ac59657b4688',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => '48f0a309-a684-39a2-80cf-79f4e312d08d',
                'beginning' => '2022-07-13 06:43:18',
                'ending' => '2022-07-13 07:43:18',
            ],
            [
                'id' => '5e07116d-6f04-3699-ace4-0c794354a482',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-13 18:02:22',
                'ending' => '2022-07-13 19:02:22',
            ],
            [
                'id' => '66173f6b-0a70-3a88-9ef3-3451eb82594d',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-13 13:27:08',
                'ending' => '2022-07-13 14:27:08',
            ],
            [
                'id' => '6d1ed79a-310d-36a0-9c47-802533086fa1',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => 'adf440ed-6bfa-35cc-b545-6e41558011b5',
                'beginning' => '2022-07-14 06:26:14',
                'ending' => '2022-07-14 07:26:14',
            ],
            [
                'id' => '6d52b5b0-2be3-3a41-95b2-f67633941858',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => 'cbf42b9b-0b42-3792-b282-93b54fe522b7',
                'beginning' => '2022-07-13 21:13:50',
                'ending' => '2022-07-13 22:13:50',
            ],
            [
                'id' => '73247fa0-cd75-3e63-9ede-d748b2e99411',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'beginning' => '2022-07-13 07:20:37',
                'ending' => '2022-07-13 08:20:37',
            ],
            [
                'id' => '74c029a4-186b-3755-af17-46467d9692cb',
                'driver_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'beginning' => '2022-07-13 14:35:57',
                'ending' => '2022-07-13 15:35:57',
            ],
            [
                'id' => '7abca8c9-e013-3eae-968c-5ce8cde6255d',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-13 09:33:39',
                'ending' => '2022-07-13 10:33:39',
            ],
            [
                'id' => '7b86da90-0b6b-3463-ab03-4f28afd4c67a',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-13 06:46:38',
                'ending' => '2022-07-13 07:46:38',
            ],
            [
                'id' => '800d8324-b87d-3934-868c-043d159b8404',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => 'cbf42b9b-0b42-3792-b282-93b54fe522b7',
                'beginning' => '2022-07-14 01:03:44',
                'ending' => '2022-07-14 02:03:44',
            ],
            [
                'id' => '86885049-2308-34e5-9183-c7227f8ea40f',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => 'cbf42b9b-0b42-3792-b282-93b54fe522b7',
                'beginning' => '2022-07-13 13:34:33',
                'ending' => '2022-07-13 14:34:33',
            ],
            [
                'id' => '8921ed19-1e14-3c70-9da5-ca9bbe3da3d1',
                'driver_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'beginning' => '2022-07-14 00:55:53',
                'ending' => '2022-07-14 01:55:53',
            ],
            [
                'id' => '91b53280-83ef-36f5-aad6-3b9ffb23e8d0',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'beginning' => '2022-07-13 14:46:44',
                'ending' => '2022-07-13 15:46:44',
            ],
            [
                'id' => '92111ed8-de0d-35e4-bd31-68f2b0dd4ac9',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                'beginning' => '2022-07-13 17:39:42',
                'ending' => '2022-07-13 18:39:42',
            ],
            [
                'id' => '9ae683ee-b71f-3347-aa0b-3b6ec28e40dd',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-14 07:14:41',
                'ending' => '2022-07-14 08:14:41',
            ],
            [
                'id' => 'a4476062-3e50-3840-9fb2-0da017b21ab2',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-14 01:39:07',
                'ending' => '2022-07-14 02:39:07',
            ],
            [
                'id' => 'a5bb8049-7bf2-31bf-8ca6-75e0dbf05494',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-13 15:36:57',
                'ending' => '2022-07-13 16:36:57',
            ],
            [
                'id' => 'ad931150-a4b9-3929-8c85-8f24855c24ba',
                'driver_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'vehicle_id' => '48f0a309-a684-39a2-80cf-79f4e312d08d',
                'beginning' => '2022-07-13 07:52:40',
                'ending' => '2022-07-13 08:52:40',
            ],
            [
                'id' => 'ade32840-edd8-3a01-95c0-ca26af7ecd9c',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => '48f0a309-a684-39a2-80cf-79f4e312d08d',
                'beginning' => '2022-07-14 06:15:10',
                'ending' => '2022-07-14 07:15:10',
            ],
            [
                'id' => 'b7c800ed-ebc4-3419-a010-e84710f5c9e7',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => '76e577de-5541-3fcb-a67d-e1c2f9329043',
                'beginning' => '2022-07-13 23:32:31',
                'ending' => '2022-07-14 00:32:31',
            ],
            [
                'id' => 'b9d9f8ce-a92d-3843-9699-ce8c090a0704',
                'driver_id' => 'aa86f72c-476a-3355-b54c-614a815fae40',
                'vehicle_id' => '48f0a309-a684-39a2-80cf-79f4e312d08d',
                'beginning' => '2022-07-13 20:56:33',
                'ending' => '2022-07-13 21:56:33',
            ],
            [
                'id' => 'baa415a7-1383-32be-bcf5-d10e7f12e380',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-13 08:00:14',
                'ending' => '2022-07-13 09:00:14',
            ],
            [
                'id' => 'bdcd1fa8-3c2d-33da-98ab-019e4ca38995',
                'driver_id' => 'a853b8f7-39fd-31d7-ad78-84b4739f79e6',
                'vehicle_id' => 'f8ffb9d3-74f9-321b-a117-f45be6311982',
                'beginning' => '2022-07-14 01:36:03',
                'ending' => '2022-07-14 02:36:03',
            ],
            [
                'id' => 'c2623aae-b71f-3964-9a03-37df77bd7f04',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-13 08:55:10',
                'ending' => '2022-07-13 09:55:10',
            ],
            [
                'id' => 'ca4842c8-3ee1-3e3b-becd-e122256f7ab5',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-14 06:28:59',
                'ending' => '2022-07-14 07:28:59',
            ],
            [
                'id' => 'e4ad6187-885a-3b1d-98ff-41ba69511f05',
                'driver_id' => 'dadd8f85-cb46-34e4-8558-61716774de62',
                'vehicle_id' => 'e9cda508-5778-330b-9885-2be9215fc19c',
                'beginning' => '2022-07-13 16:03:24',
                'ending' => '2022-07-13 17:03:24',
            ],
            [
                'id' => 'e711e8b2-0c5b-3ab0-bbb1-790fcb6cc687',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => 'adf440ed-6bfa-35cc-b545-6e41558011b5',
                'beginning' => '2022-07-13 23:21:33',
                'ending' => '2022-07-14 00:21:33',
            ],
            [
                'id' => 'e8d9a24f-ee39-3a12-8004-fbb551700424',
                'driver_id' => '1e9966ec-2e7a-3e42-8dd2-7b17e3337fec',
                'vehicle_id' => '7016ac5b-cfc8-3476-b27e-deaf3c5959c8',
                'beginning' => '2022-07-13 16:52:26',
                'ending' => '2022-07-13 17:52:26',
            ],
            [
                'id' => 'ededa534-1210-317d-bae1-b10fe28d57df',
                'driver_id' => '41383aaa-e629-3270-89e4-26350d8447b3',
                'vehicle_id' => 'd87b5427-f074-31cf-811e-9c891ec99c32',
                'beginning' => '2022-07-14 06:05:05',
                'ending' => '2022-07-14 07:05:05',
            ],
        ];

        parent::init();
    }
}
