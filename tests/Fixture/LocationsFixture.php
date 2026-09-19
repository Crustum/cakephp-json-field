<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for the geolocation real-case `locations` table.
 *
 * Faithful clone of the reference `cakephp-orm-json` `geolocation_data.sql`
 * (500 rows). The reference stores each row as `data` =
 * `{vehicle:{id}, position:{lat,lon}, timestamp}`; it is preserved verbatim
 * inside the `point` JSON column so `point->position->lat` / `point->position->lon`
 * BETWEEN queries run against the exact original coordinates. The reference
 * has no id, so an auto-increment numeric `id` and a derived `name` are added.
 */
class LocationsFixture extends JsonFieldFixture
{
    /**
     * @var string
     */
    public string $table = 'jf_locations';

    /**
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'point' => ['type' => 'json'],
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
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.06492,
                            'lon' => 4.55262,
                        ],
                        'timestamp' => 1657735231,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.7989,
                            'lon' => 4.59046,
                        ],
                        'timestamp' => 1657784114,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.82499,
                            'lon' => 4.55083,
                        ],
                        'timestamp' => 1657703755,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.25895,
                            'lon' => 4.52264,
                        ],
                        'timestamp' => 1657782863,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.87682,
                            'lon' => 4.54114,
                        ],
                        'timestamp' => 1657786493,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.31437,
                            'lon' => 4.59613,
                        ],
                        'timestamp' => 1657713997,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.89486,
                            'lon' => 4.56293,
                        ],
                        'timestamp' => 1657701998,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.94411,
                            'lon' => 4.50506,
                        ],
                        'timestamp' => 1657758248,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.45104,
                            'lon' => 4.59798,
                        ],
                        'timestamp' => 1657736549,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.52343,
                            'lon' => 4.52394,
                        ],
                        'timestamp' => 1657723074,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.95998,
                            'lon' => 4.575,
                        ],
                        'timestamp' => 1657705917,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.54207,
                            'lon' => 4.55147,
                        ],
                        'timestamp' => 1657765803,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.89999,
                            'lon' => 4.51795,
                        ],
                        'timestamp' => 1657765747,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.91968,
                            'lon' => 4.54635,
                        ],
                        'timestamp' => 1657717098,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.80466,
                            'lon' => 4.50592,
                        ],
                        'timestamp' => 1657713067,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.08409,
                            'lon' => 4.55967,
                        ],
                        'timestamp' => 1657739504,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.16207,
                            'lon' => 4.54945,
                        ],
                        'timestamp' => 1657778364,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.35409,
                            'lon' => 4.56991,
                        ],
                        'timestamp' => 1657794706,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.36843,
                            'lon' => 4.57304,
                        ],
                        'timestamp' => 1657799239,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.57127,
                            'lon' => 4.514,
                        ],
                        'timestamp' => 1657767909,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.96024,
                            'lon' => 4.59645,
                        ],
                        'timestamp' => 1657775084,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.29644,
                            'lon' => 4.57843,
                        ],
                        'timestamp' => 1657745100,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.87239,
                            'lon' => 4.52187,
                        ],
                        'timestamp' => 1657777499,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.9791,
                            'lon' => 4.55514,
                        ],
                        'timestamp' => 1657756957,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.95483,
                            'lon' => 4.53152,
                        ],
                        'timestamp' => 1657702719,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.37735,
                            'lon' => 4.50564,
                        ],
                        'timestamp' => 1657791897,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.88495,
                            'lon' => 4.5881,
                        ],
                        'timestamp' => 1657750959,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.2555,
                            'lon' => 4.53771,
                        ],
                        'timestamp' => 1657702884,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.54936,
                            'lon' => 4.51418,
                        ],
                        'timestamp' => 1657745493,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.85879,
                            'lon' => 4.55159,
                        ],
                        'timestamp' => 1657774383,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.57316,
                            'lon' => 4.53982,
                        ],
                        'timestamp' => 1657767708,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.52396,
                            'lon' => 4.54695,
                        ],
                        'timestamp' => 1657771469,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.18093,
                            'lon' => 4.50883,
                        ],
                        'timestamp' => 1657745601,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.46773,
                            'lon' => 4.57665,
                        ],
                        'timestamp' => 1657786607,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.87095,
                            'lon' => 4.50981,
                        ],
                        'timestamp' => 1657788158,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.31287,
                            'lon' => 4.53247,
                        ],
                        'timestamp' => 1657735848,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.75796,
                            'lon' => 4.59945,
                        ],
                        'timestamp' => 1657748285,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.94193,
                            'lon' => 4.55722,
                        ],
                        'timestamp' => 1657731186,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.87127,
                            'lon' => 4.53149,
                        ],
                        'timestamp' => 1657711014,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.13601,
                            'lon' => 4.55209,
                        ],
                        'timestamp' => 1657745612,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.39474,
                            'lon' => 4.57663,
                        ],
                        'timestamp' => 1657778873,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.99676,
                            'lon' => 4.5683,
                        ],
                        'timestamp' => 1657712421,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.45505,
                            'lon' => 4.55247,
                        ],
                        'timestamp' => 1657704511,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.91494,
                            'lon' => 4.51537,
                        ],
                        'timestamp' => 1657728414,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.76069,
                            'lon' => 4.56463,
                        ],
                        'timestamp' => 1657773044,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.05526,
                            'lon' => 4.51865,
                        ],
                        'timestamp' => 1657721488,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.77888,
                            'lon' => 4.52572,
                        ],
                        'timestamp' => 1657718814,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.53476,
                            'lon' => 4.57205,
                        ],
                        'timestamp' => 1657767484,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.93372,
                            'lon' => 4.52922,
                        ],
                        'timestamp' => 1657782155,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.63084,
                            'lon' => 4.51629,
                        ],
                        'timestamp' => 1657746315,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.7897,
                            'lon' => 4.5165,
                        ],
                        'timestamp' => 1657779257,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.21123,
                            'lon' => 4.57221,
                        ],
                        'timestamp' => 1657788041,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.98034,
                            'lon' => 4.52565,
                        ],
                        'timestamp' => 1657797406,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.30744,
                            'lon' => 4.57861,
                        ],
                        'timestamp' => 1657709259,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.40465,
                            'lon' => 4.52726,
                        ],
                        'timestamp' => 1657774925,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.42387,
                            'lon' => 4.52092,
                        ],
                        'timestamp' => 1657795792,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.02494,
                            'lon' => 4.56773,
                        ],
                        'timestamp' => 1657728691,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.53286,
                            'lon' => 4.55556,
                        ],
                        'timestamp' => 1657706303,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.83983,
                            'lon' => 4.51712,
                        ],
                        'timestamp' => 1657700924,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.92604,
                            'lon' => 4.57035,
                        ],
                        'timestamp' => 1657798432,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.62791,
                            'lon' => 4.54911,
                        ],
                        'timestamp' => 1657781604,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.45499,
                            'lon' => 4.57875,
                        ],
                        'timestamp' => 1657741430,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.17632,
                            'lon' => 4.57871,
                        ],
                        'timestamp' => 1657760647,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.04514,
                            'lon' => 4.58789,
                        ],
                        'timestamp' => 1657753933,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.38276,
                            'lon' => 4.5288,
                        ],
                        'timestamp' => 1657749226,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.7609,
                            'lon' => 4.52967,
                        ],
                        'timestamp' => 1657748615,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.9186,
                            'lon' => 4.51509,
                        ],
                        'timestamp' => 1657750418,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.73575,
                            'lon' => 4.52322,
                        ],
                        'timestamp' => 1657719434,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.8004,
                            'lon' => 4.51342,
                        ],
                        'timestamp' => 1657703123,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.54537,
                            'lon' => 4.50107,
                        ],
                        'timestamp' => 1657722408,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.51376,
                            'lon' => 4.5004,
                        ],
                        'timestamp' => 1657724411,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.75624,
                            'lon' => 4.54105,
                        ],
                        'timestamp' => 1657772820,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.85064,
                            'lon' => 4.56979,
                        ],
                        'timestamp' => 1657742822,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.47223,
                            'lon' => 4.58768,
                        ],
                        'timestamp' => 1657740113,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.30944,
                            'lon' => 4.55991,
                        ],
                        'timestamp' => 1657702186,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.44584,
                            'lon' => 4.56616,
                        ],
                        'timestamp' => 1657743408,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.31311,
                            'lon' => 4.55931,
                        ],
                        'timestamp' => 1657778887,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.84274,
                            'lon' => 4.53995,
                        ],
                        'timestamp' => 1657735927,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.67844,
                            'lon' => 4.52222,
                        ],
                        'timestamp' => 1657770244,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.24601,
                            'lon' => 4.53253,
                        ],
                        'timestamp' => 1657792621,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.05735,
                            'lon' => 4.59884,
                        ],
                        'timestamp' => 1657705262,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.04116,
                            'lon' => 4.53046,
                        ],
                        'timestamp' => 1657746104,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.12755,
                            'lon' => 4.57451,
                        ],
                        'timestamp' => 1657786510,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.14541,
                            'lon' => 4.51948,
                        ],
                        'timestamp' => 1657791166,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.666,
                            'lon' => 4.50055,
                        ],
                        'timestamp' => 1657743214,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.68961,
                            'lon' => 4.53691,
                        ],
                        'timestamp' => 1657731853,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.98658,
                            'lon' => 4.57675,
                        ],
                        'timestamp' => 1657715532,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.3807,
                            'lon' => 4.5529,
                        ],
                        'timestamp' => 1657741062,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.02424,
                            'lon' => 4.52894,
                        ],
                        'timestamp' => 1657722218,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.33507,
                            'lon' => 4.56905,
                        ],
                        'timestamp' => 1657772363,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.90554,
                            'lon' => 4.59184,
                        ],
                        'timestamp' => 1657762979,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.84144,
                            'lon' => 4.5973,
                        ],
                        'timestamp' => 1657765944,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.58905,
                            'lon' => 4.50719,
                        ],
                        'timestamp' => 1657757799,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.43887,
                            'lon' => 4.53635,
                        ],
                        'timestamp' => 1657779084,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.52765,
                            'lon' => 4.56573,
                        ],
                        'timestamp' => 1657791561,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.82107,
                            'lon' => 4.55296,
                        ],
                        'timestamp' => 1657766988,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.75317,
                            'lon' => 4.51335,
                        ],
                        'timestamp' => 1657737015,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.08439,
                            'lon' => 4.5337,
                        ],
                        'timestamp' => 1657749180,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.0729,
                            'lon' => 4.50268,
                        ],
                        'timestamp' => 1657719919,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.04058,
                            'lon' => 4.56036,
                        ],
                        'timestamp' => 1657746949,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.67341,
                            'lon' => 4.58154,
                        ],
                        'timestamp' => 1657773797,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.79114,
                            'lon' => 4.54753,
                        ],
                        'timestamp' => 1657717411,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.87346,
                            'lon' => 4.59573,
                        ],
                        'timestamp' => 1657721110,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.92354,
                            'lon' => 4.53783,
                        ],
                        'timestamp' => 1657757206,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.44058,
                            'lon' => 4.59432,
                        ],
                        'timestamp' => 1657788395,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.26117,
                            'lon' => 4.53706,
                        ],
                        'timestamp' => 1657757232,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.6295,
                            'lon' => 4.56084,
                        ],
                        'timestamp' => 1657742537,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.1191,
                            'lon' => 4.51598,
                        ],
                        'timestamp' => 1657706982,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.79509,
                            'lon' => 4.59206,
                        ],
                        'timestamp' => 1657783760,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.47983,
                            'lon' => 4.51176,
                        ],
                        'timestamp' => 1657771719,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.77659,
                            'lon' => 4.53216,
                        ],
                        'timestamp' => 1657757042,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.10588,
                            'lon' => 4.54143,
                        ],
                        'timestamp' => 1657776457,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.50292,
                            'lon' => 4.54905,
                        ],
                        'timestamp' => 1657728827,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.04729,
                            'lon' => 4.55015,
                        ],
                        'timestamp' => 1657751673,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.91771,
                            'lon' => 4.53083,
                        ],
                        'timestamp' => 1657727551,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.07998,
                            'lon' => 4.52285,
                        ],
                        'timestamp' => 1657723286,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.21545,
                            'lon' => 4.53093,
                        ],
                        'timestamp' => 1657768821,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.56892,
                            'lon' => 4.58562,
                        ],
                        'timestamp' => 1657784790,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.67254,
                            'lon' => 4.58391,
                        ],
                        'timestamp' => 1657734772,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.13105,
                            'lon' => 4.58286,
                        ],
                        'timestamp' => 1657772668,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.70607,
                            'lon' => 4.56336,
                        ],
                        'timestamp' => 1657738797,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.33941,
                            'lon' => 4.53333,
                        ],
                        'timestamp' => 1657719882,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.44259,
                            'lon' => 4.5269,
                        ],
                        'timestamp' => 1657703898,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.56661,
                            'lon' => 4.56875,
                        ],
                        'timestamp' => 1657774223,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.74174,
                            'lon' => 4.54439,
                        ],
                        'timestamp' => 1657713227,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.53266,
                            'lon' => 4.52882,
                        ],
                        'timestamp' => 1657760493,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.68894,
                            'lon' => 4.55363,
                        ],
                        'timestamp' => 1657784535,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.19055,
                            'lon' => 4.51306,
                        ],
                        'timestamp' => 1657791476,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.70797,
                            'lon' => 4.58376,
                        ],
                        'timestamp' => 1657784702,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.1534,
                            'lon' => 4.51614,
                        ],
                        'timestamp' => 1657705596,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.2801,
                            'lon' => 4.51582,
                        ],
                        'timestamp' => 1657747167,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.97298,
                            'lon' => 4.50262,
                        ],
                        'timestamp' => 1657724667,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.37484,
                            'lon' => 4.55446,
                        ],
                        'timestamp' => 1657711237,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.47333,
                            'lon' => 4.53336,
                        ],
                        'timestamp' => 1657721575,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.2534,
                            'lon' => 4.51061,
                        ],
                        'timestamp' => 1657777108,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.73097,
                            'lon' => 4.57665,
                        ],
                        'timestamp' => 1657722552,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.44851,
                            'lon' => 4.56299,
                        ],
                        'timestamp' => 1657724465,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.35439,
                            'lon' => 4.59609,
                        ],
                        'timestamp' => 1657779034,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.26875,
                            'lon' => 4.57105,
                        ],
                        'timestamp' => 1657716518,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.94732,
                            'lon' => 4.56244,
                        ],
                        'timestamp' => 1657738371,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.57329,
                            'lon' => 4.52839,
                        ],
                        'timestamp' => 1657791625,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.5213,
                            'lon' => 4.59178,
                        ],
                        'timestamp' => 1657707044,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.99213,
                            'lon' => 4.56142,
                        ],
                        'timestamp' => 1657738072,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.96173,
                            'lon' => 4.56324,
                        ],
                        'timestamp' => 1657766137,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.59313,
                            'lon' => 4.59079,
                        ],
                        'timestamp' => 1657797870,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.91315,
                            'lon' => 4.52595,
                        ],
                        'timestamp' => 1657717194,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.20041,
                            'lon' => 4.52601,
                        ],
                        'timestamp' => 1657703522,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.15426,
                            'lon' => 4.5132,
                        ],
                        'timestamp' => 1657790002,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.00736,
                            'lon' => 4.57828,
                        ],
                        'timestamp' => 1657722244,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.33003,
                            'lon' => 4.52572,
                        ],
                        'timestamp' => 1657781453,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.52694,
                            'lon' => 4.50348,
                        ],
                        'timestamp' => 1657737035,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.87962,
                            'lon' => 4.56421,
                        ],
                        'timestamp' => 1657714168,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.99884,
                            'lon' => 4.59826,
                        ],
                        'timestamp' => 1657776272,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.89508,
                            'lon' => 4.56258,
                        ],
                        'timestamp' => 1657748950,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.10948,
                            'lon' => 4.56095,
                        ],
                        'timestamp' => 1657712361,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.99135,
                            'lon' => 4.53999,
                        ],
                        'timestamp' => 1657716914,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.04487,
                            'lon' => 4.58902,
                        ],
                        'timestamp' => 1657737119,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.29145,
                            'lon' => 4.56782,
                        ],
                        'timestamp' => 1657785213,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.01926,
                            'lon' => 4.5208,
                        ],
                        'timestamp' => 1657768011,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.39858,
                            'lon' => 4.5708,
                        ],
                        'timestamp' => 1657776968,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.81454,
                            'lon' => 4.53519,
                        ],
                        'timestamp' => 1657711074,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.03578,
                            'lon' => 4.55554,
                        ],
                        'timestamp' => 1657750957,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.69392,
                            'lon' => 4.54492,
                        ],
                        'timestamp' => 1657794709,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.91191,
                            'lon' => 4.56557,
                        ],
                        'timestamp' => 1657700361,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.89915,
                            'lon' => 4.58459,
                        ],
                        'timestamp' => 1657773758,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.99569,
                            'lon' => 4.50611,
                        ],
                        'timestamp' => 1657760691,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.34923,
                            'lon' => 4.56117,
                        ],
                        'timestamp' => 1657770907,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.19993,
                            'lon' => 4.54475,
                        ],
                        'timestamp' => 1657767190,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.24255,
                            'lon' => 4.56298,
                        ],
                        'timestamp' => 1657796182,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.75086,
                            'lon' => 4.56948,
                        ],
                        'timestamp' => 1657710003,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.92294,
                            'lon' => 4.55043,
                        ],
                        'timestamp' => 1657707243,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.47477,
                            'lon' => 4.50995,
                        ],
                        'timestamp' => 1657750672,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.78471,
                            'lon' => 4.56984,
                        ],
                        'timestamp' => 1657773702,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.56832,
                            'lon' => 4.54849,
                        ],
                        'timestamp' => 1657793373,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.62438,
                            'lon' => 4.54912,
                        ],
                        'timestamp' => 1657775012,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.48815,
                            'lon' => 4.55944,
                        ],
                        'timestamp' => 1657725999,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.61397,
                            'lon' => 4.52358,
                        ],
                        'timestamp' => 1657729663,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.94581,
                            'lon' => 4.52608,
                        ],
                        'timestamp' => 1657789486,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.63601,
                            'lon' => 4.56672,
                        ],
                        'timestamp' => 1657762252,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.72764,
                            'lon' => 4.55692,
                        ],
                        'timestamp' => 1657770161,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.50821,
                            'lon' => 4.59332,
                        ],
                        'timestamp' => 1657763597,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.24026,
                            'lon' => 4.54908,
                        ],
                        'timestamp' => 1657713390,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.94228,
                            'lon' => 4.50956,
                        ],
                        'timestamp' => 1657702861,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.751,
                            'lon' => 4.57102,
                        ],
                        'timestamp' => 1657723895,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.11748,
                            'lon' => 4.59116,
                        ],
                        'timestamp' => 1657738075,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.85248,
                            'lon' => 4.54491,
                        ],
                        'timestamp' => 1657790477,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.58736,
                            'lon' => 4.5891,
                        ],
                        'timestamp' => 1657727268,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.02897,
                            'lon' => 4.56351,
                        ],
                        'timestamp' => 1657789164,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.51577,
                            'lon' => 4.55441,
                        ],
                        'timestamp' => 1657769583,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.51742,
                            'lon' => 4.57923,
                        ],
                        'timestamp' => 1657751435,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.14749,
                            'lon' => 4.52552,
                        ],
                        'timestamp' => 1657785009,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.06136,
                            'lon' => 4.58377,
                        ],
                        'timestamp' => 1657738812,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.26457,
                            'lon' => 4.52396,
                        ],
                        'timestamp' => 1657764042,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.87773,
                            'lon' => 4.57954,
                        ],
                        'timestamp' => 1657722614,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.67446,
                            'lon' => 4.59402,
                        ],
                        'timestamp' => 1657714854,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.1451,
                            'lon' => 4.57241,
                        ],
                        'timestamp' => 1657745543,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.48556,
                            'lon' => 4.57684,
                        ],
                        'timestamp' => 1657715436,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.70078,
                            'lon' => 4.59721,
                        ],
                        'timestamp' => 1657780832,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.07069,
                            'lon' => 4.54366,
                        ],
                        'timestamp' => 1657756831,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.6033,
                            'lon' => 4.52062,
                        ],
                        'timestamp' => 1657706367,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.0264,
                            'lon' => 4.51402,
                        ],
                        'timestamp' => 1657732015,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.88308,
                            'lon' => 4.56853,
                        ],
                        'timestamp' => 1657751014,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.58073,
                            'lon' => 4.547,
                        ],
                        'timestamp' => 1657705941,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.90859,
                            'lon' => 4.55412,
                        ],
                        'timestamp' => 1657790613,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.00934,
                            'lon' => 4.5411,
                        ],
                        'timestamp' => 1657795286,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.28847,
                            'lon' => 4.54015,
                        ],
                        'timestamp' => 1657794335,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.73765,
                            'lon' => 4.54199,
                        ],
                        'timestamp' => 1657788010,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.99266,
                            'lon' => 4.54608,
                        ],
                        'timestamp' => 1657765008,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.01031,
                            'lon' => 4.58575,
                        ],
                        'timestamp' => 1657738160,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.40625,
                            'lon' => 4.58714,
                        ],
                        'timestamp' => 1657754212,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.33595,
                            'lon' => 4.50529,
                        ],
                        'timestamp' => 1657731576,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.67093,
                            'lon' => 4.51359,
                        ],
                        'timestamp' => 1657730872,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.35444,
                            'lon' => 4.51659,
                        ],
                        'timestamp' => 1657763136,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.09997,
                            'lon' => 4.51662,
                        ],
                        'timestamp' => 1657724653,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.67481,
                            'lon' => 4.51801,
                        ],
                        'timestamp' => 1657767210,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.06913,
                            'lon' => 4.59886,
                        ],
                        'timestamp' => 1657764999,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.67531,
                            'lon' => 4.56279,
                        ],
                        'timestamp' => 1657781538,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.38053,
                            'lon' => 4.56255,
                        ],
                        'timestamp' => 1657793536,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.40359,
                            'lon' => 4.56767,
                        ],
                        'timestamp' => 1657790187,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.11072,
                            'lon' => 4.54342,
                        ],
                        'timestamp' => 1657787330,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.88155,
                            'lon' => 4.50117,
                        ],
                        'timestamp' => 1657724264,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.47298,
                            'lon' => 4.59166,
                        ],
                        'timestamp' => 1657781893,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.65119,
                            'lon' => 4.51992,
                        ],
                        'timestamp' => 1657745329,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.59435,
                            'lon' => 4.50863,
                        ],
                        'timestamp' => 1657752300,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.19787,
                            'lon' => 4.51346,
                        ],
                        'timestamp' => 1657752002,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.78799,
                            'lon' => 4.57528,
                        ],
                        'timestamp' => 1657713968,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.84932,
                            'lon' => 4.53414,
                        ],
                        'timestamp' => 1657726171,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.37332,
                            'lon' => 4.52821,
                        ],
                        'timestamp' => 1657794367,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.76499,
                            'lon' => 4.53093,
                        ],
                        'timestamp' => 1657759189,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.068,
                            'lon' => 4.53614,
                        ],
                        'timestamp' => 1657760032,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.52903,
                            'lon' => 4.54326,
                        ],
                        'timestamp' => 1657789065,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.88154,
                            'lon' => 4.56598,
                        ],
                        'timestamp' => 1657717376,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.02288,
                            'lon' => 4.53365,
                        ],
                        'timestamp' => 1657732026,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.81044,
                            'lon' => 4.51791,
                        ],
                        'timestamp' => 1657764195,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.81426,
                            'lon' => 4.53426,
                        ],
                        'timestamp' => 1657711087,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.24357,
                            'lon' => 4.5965,
                        ],
                        'timestamp' => 1657788647,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.71845,
                            'lon' => 4.52136,
                        ],
                        'timestamp' => 1657727562,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.30617,
                            'lon' => 4.53227,
                        ],
                        'timestamp' => 1657754474,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.93781,
                            'lon' => 4.52797,
                        ],
                        'timestamp' => 1657739163,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.97796,
                            'lon' => 4.51347,
                        ],
                        'timestamp' => 1657769118,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.94146,
                            'lon' => 4.55997,
                        ],
                        'timestamp' => 1657773106,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.44146,
                            'lon' => 4.53709,
                        ],
                        'timestamp' => 1657759595,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.69778,
                            'lon' => 4.53663,
                        ],
                        'timestamp' => 1657792065,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.51467,
                            'lon' => 4.55056,
                        ],
                        'timestamp' => 1657764653,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.89483,
                            'lon' => 4.59642,
                        ],
                        'timestamp' => 1657716753,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.46548,
                            'lon' => 4.56361,
                        ],
                        'timestamp' => 1657706155,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.33452,
                            'lon' => 4.55076,
                        ],
                        'timestamp' => 1657774099,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.92698,
                            'lon' => 4.54093,
                        ],
                        'timestamp' => 1657716919,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.03668,
                            'lon' => 4.53022,
                        ],
                        'timestamp' => 1657729465,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.31136,
                            'lon' => 4.57798,
                        ],
                        'timestamp' => 1657783508,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.65813,
                            'lon' => 4.57921,
                        ],
                        'timestamp' => 1657793767,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.09861,
                            'lon' => 4.50704,
                        ],
                        'timestamp' => 1657751923,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.45211,
                            'lon' => 4.54604,
                        ],
                        'timestamp' => 1657789081,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.45223,
                            'lon' => 4.51972,
                        ],
                        'timestamp' => 1657708077,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.41406,
                            'lon' => 4.58625,
                        ],
                        'timestamp' => 1657796323,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.47214,
                            'lon' => 4.53622,
                        ],
                        'timestamp' => 1657783716,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.07494,
                            'lon' => 4.53864,
                        ],
                        'timestamp' => 1657752168,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.29261,
                            'lon' => 4.52495,
                        ],
                        'timestamp' => 1657720829,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.1349,
                            'lon' => 4.59559,
                        ],
                        'timestamp' => 1657715342,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.91161,
                            'lon' => 4.50378,
                        ],
                        'timestamp' => 1657761290,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.03649,
                            'lon' => 4.57646,
                        ],
                        'timestamp' => 1657704945,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.12664,
                            'lon' => 4.59467,
                        ],
                        'timestamp' => 1657766086,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.02937,
                            'lon' => 4.59816,
                        ],
                        'timestamp' => 1657746297,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.99938,
                            'lon' => 4.50317,
                        ],
                        'timestamp' => 1657750029,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.55096,
                            'lon' => 4.57285,
                        ],
                        'timestamp' => 1657754685,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.7003,
                            'lon' => 4.59853,
                        ],
                        'timestamp' => 1657795746,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.58315,
                            'lon' => 4.59533,
                        ],
                        'timestamp' => 1657709388,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.94429,
                            'lon' => 4.50397,
                        ],
                        'timestamp' => 1657794842,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.1366,
                            'lon' => 4.5596,
                        ],
                        'timestamp' => 1657730949,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.83955,
                            'lon' => 4.55118,
                        ],
                        'timestamp' => 1657711229,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.58684,
                            'lon' => 4.58893,
                        ],
                        'timestamp' => 1657797268,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.01999,
                            'lon' => 4.52024,
                        ],
                        'timestamp' => 1657776559,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.7847,
                            'lon' => 4.54588,
                        ],
                        'timestamp' => 1657726671,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.31126,
                            'lon' => 4.58663,
                        ],
                        'timestamp' => 1657771956,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.38413,
                            'lon' => 4.56972,
                        ],
                        'timestamp' => 1657713835,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.8579,
                            'lon' => 4.52883,
                        ],
                        'timestamp' => 1657789978,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.2897,
                            'lon' => 4.56334,
                        ],
                        'timestamp' => 1657734991,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.07856,
                            'lon' => 4.51447,
                        ],
                        'timestamp' => 1657701499,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.04866,
                            'lon' => 4.52395,
                        ],
                        'timestamp' => 1657713388,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.44746,
                            'lon' => 4.51078,
                        ],
                        'timestamp' => 1657777931,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.8307,
                            'lon' => 4.59722,
                        ],
                        'timestamp' => 1657715006,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.48821,
                            'lon' => 4.51802,
                        ],
                        'timestamp' => 1657762288,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.98039,
                            'lon' => 4.50391,
                        ],
                        'timestamp' => 1657757117,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.0519,
                            'lon' => 4.51815,
                        ],
                        'timestamp' => 1657746202,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.54936,
                            'lon' => 4.50361,
                        ],
                        'timestamp' => 1657734139,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.58894,
                            'lon' => 4.51071,
                        ],
                        'timestamp' => 1657796735,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.04423,
                            'lon' => 4.55464,
                        ],
                        'timestamp' => 1657766451,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.37941,
                            'lon' => 4.59154,
                        ],
                        'timestamp' => 1657775596,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.74229,
                            'lon' => 4.56056,
                        ],
                        'timestamp' => 1657715256,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.72137,
                            'lon' => 4.54148,
                        ],
                        'timestamp' => 1657754253,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.91874,
                            'lon' => 4.58477,
                        ],
                        'timestamp' => 1657772312,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.18532,
                            'lon' => 4.55174,
                        ],
                        'timestamp' => 1657770834,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.72294,
                            'lon' => 4.5217,
                        ],
                        'timestamp' => 1657798279,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.21168,
                            'lon' => 4.52819,
                        ],
                        'timestamp' => 1657732749,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.91349,
                            'lon' => 4.59338,
                        ],
                        'timestamp' => 1657706414,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.20327,
                            'lon' => 4.50317,
                        ],
                        'timestamp' => 1657723420,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.45243,
                            'lon' => 4.50596,
                        ],
                        'timestamp' => 1657792321,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.31609,
                            'lon' => 4.55724,
                        ],
                        'timestamp' => 1657749540,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.33685,
                            'lon' => 4.5207,
                        ],
                        'timestamp' => 1657748711,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.9238,
                            'lon' => 4.54631,
                        ],
                        'timestamp' => 1657795625,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.67587,
                            'lon' => 4.58202,
                        ],
                        'timestamp' => 1657757278,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.81968,
                            'lon' => 4.51584,
                        ],
                        'timestamp' => 1657785698,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.02183,
                            'lon' => 4.50563,
                        ],
                        'timestamp' => 1657705572,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.14432,
                            'lon' => 4.5863,
                        ],
                        'timestamp' => 1657770603,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.5687,
                            'lon' => 4.5871,
                        ],
                        'timestamp' => 1657721521,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.48864,
                            'lon' => 4.52505,
                        ],
                        'timestamp' => 1657709389,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.43242,
                            'lon' => 4.5753,
                        ],
                        'timestamp' => 1657757521,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.7242,
                            'lon' => 4.55427,
                        ],
                        'timestamp' => 1657709871,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.48451,
                            'lon' => 4.54875,
                        ],
                        'timestamp' => 1657760563,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.87922,
                            'lon' => 4.51676,
                        ],
                        'timestamp' => 1657719360,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.21741,
                            'lon' => 4.58443,
                        ],
                        'timestamp' => 1657749875,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.54355,
                            'lon' => 4.5142,
                        ],
                        'timestamp' => 1657756273,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.45359,
                            'lon' => 4.5898,
                        ],
                        'timestamp' => 1657722659,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.64594,
                            'lon' => 4.57214,
                        ],
                        'timestamp' => 1657798108,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.09795,
                            'lon' => 4.50967,
                        ],
                        'timestamp' => 1657724839,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.11208,
                            'lon' => 4.55875,
                        ],
                        'timestamp' => 1657798465,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.38711,
                            'lon' => 4.56362,
                        ],
                        'timestamp' => 1657707849,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.39193,
                            'lon' => 4.50085,
                        ],
                        'timestamp' => 1657764666,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.67732,
                            'lon' => 4.51923,
                        ],
                        'timestamp' => 1657742934,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.23569,
                            'lon' => 4.5122,
                        ],
                        'timestamp' => 1657721339,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.89176,
                            'lon' => 4.56633,
                        ],
                        'timestamp' => 1657712583,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.68824,
                            'lon' => 4.58648,
                        ],
                        'timestamp' => 1657796354,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.42033,
                            'lon' => 4.53903,
                        ],
                        'timestamp' => 1657794589,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.61068,
                            'lon' => 4.58166,
                        ],
                        'timestamp' => 1657759237,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.92463,
                            'lon' => 4.52953,
                        ],
                        'timestamp' => 1657799305,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.51543,
                            'lon' => 4.51852,
                        ],
                        'timestamp' => 1657754889,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.36087,
                            'lon' => 4.55707,
                        ],
                        'timestamp' => 1657745417,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.50048,
                            'lon' => 4.50805,
                        ],
                        'timestamp' => 1657771121,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.16923,
                            'lon' => 4.59608,
                        ],
                        'timestamp' => 1657751022,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.22772,
                            'lon' => 4.55831,
                        ],
                        'timestamp' => 1657717346,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.53232,
                            'lon' => 4.51169,
                        ],
                        'timestamp' => 1657704033,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.00866,
                            'lon' => 4.56682,
                        ],
                        'timestamp' => 1657795310,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.07714,
                            'lon' => 4.55242,
                        ],
                        'timestamp' => 1657745688,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.44348,
                            'lon' => 4.54754,
                        ],
                        'timestamp' => 1657717846,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.0291,
                            'lon' => 4.57363,
                        ],
                        'timestamp' => 1657708751,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.74034,
                            'lon' => 4.58302,
                        ],
                        'timestamp' => 1657780434,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.22547,
                            'lon' => 4.54653,
                        ],
                        'timestamp' => 1657751952,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.64407,
                            'lon' => 4.52594,
                        ],
                        'timestamp' => 1657769383,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.30685,
                            'lon' => 4.53135,
                        ],
                        'timestamp' => 1657784839,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.96585,
                            'lon' => 4.55753,
                        ],
                        'timestamp' => 1657752103,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.14826,
                            'lon' => 4.55728,
                        ],
                        'timestamp' => 1657714484,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.99122,
                            'lon' => 4.50027,
                        ],
                        'timestamp' => 1657774656,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.23715,
                            'lon' => 4.51069,
                        ],
                        'timestamp' => 1657798378,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.8072,
                            'lon' => 4.56096,
                        ],
                        'timestamp' => 1657757242,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.30228,
                            'lon' => 4.51135,
                        ],
                        'timestamp' => 1657726480,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.12797,
                            'lon' => 4.57551,
                        ],
                        'timestamp' => 1657769716,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.77748,
                            'lon' => 4.52615,
                        ],
                        'timestamp' => 1657747780,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.93712,
                            'lon' => 4.56141,
                        ],
                        'timestamp' => 1657771548,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.88729,
                            'lon' => 4.52005,
                        ],
                        'timestamp' => 1657761669,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.40007,
                            'lon' => 4.53582,
                        ],
                        'timestamp' => 1657759150,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.96541,
                            'lon' => 4.5703,
                        ],
                        'timestamp' => 1657777925,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.86717,
                            'lon' => 4.50975,
                        ],
                        'timestamp' => 1657732078,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.32693,
                            'lon' => 4.58746,
                        ],
                        'timestamp' => 1657735407,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.62039,
                            'lon' => 4.56067,
                        ],
                        'timestamp' => 1657759851,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.85401,
                            'lon' => 4.52959,
                        ],
                        'timestamp' => 1657765070,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.79689,
                            'lon' => 4.5966,
                        ],
                        'timestamp' => 1657738842,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.12948,
                            'lon' => 4.55307,
                        ],
                        'timestamp' => 1657762147,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.18041,
                            'lon' => 4.53479,
                        ],
                        'timestamp' => 1657719710,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.63115,
                            'lon' => 4.53961,
                        ],
                        'timestamp' => 1657705147,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.74444,
                            'lon' => 4.57441,
                        ],
                        'timestamp' => 1657745335,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.25651,
                            'lon' => 4.54559,
                        ],
                        'timestamp' => 1657766379,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.22454,
                            'lon' => 4.5393,
                        ],
                        'timestamp' => 1657786597,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.25652,
                            'lon' => 4.59466,
                        ],
                        'timestamp' => 1657753927,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.41331,
                            'lon' => 4.56947,
                        ],
                        'timestamp' => 1657726472,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.13801,
                            'lon' => 4.54271,
                        ],
                        'timestamp' => 1657756214,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.45161,
                            'lon' => 4.51076,
                        ],
                        'timestamp' => 1657742332,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.64973,
                            'lon' => 4.59297,
                        ],
                        'timestamp' => 1657768030,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.9053,
                            'lon' => 4.51966,
                        ],
                        'timestamp' => 1657708857,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.80301,
                            'lon' => 4.5864,
                        ],
                        'timestamp' => 1657776339,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.00575,
                            'lon' => 4.58776,
                        ],
                        'timestamp' => 1657729182,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.49419,
                            'lon' => 4.52726,
                        ],
                        'timestamp' => 1657729850,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.38264,
                            'lon' => 4.52065,
                        ],
                        'timestamp' => 1657722232,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.28781,
                            'lon' => 4.51064,
                        ],
                        'timestamp' => 1657703221,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.9603,
                            'lon' => 4.5237,
                        ],
                        'timestamp' => 1657722674,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.66725,
                            'lon' => 4.55422,
                        ],
                        'timestamp' => 1657799760,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.13163,
                            'lon' => 4.55281,
                        ],
                        'timestamp' => 1657783301,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.80549,
                            'lon' => 4.5914,
                        ],
                        'timestamp' => 1657759375,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.03281,
                            'lon' => 4.52923,
                        ],
                        'timestamp' => 1657750544,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.20986,
                            'lon' => 4.51949,
                        ],
                        'timestamp' => 1657778195,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.1003,
                            'lon' => 4.50943,
                        ],
                        'timestamp' => 1657774123,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.50306,
                            'lon' => 4.53062,
                        ],
                        'timestamp' => 1657707168,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.12717,
                            'lon' => 4.51031,
                        ],
                        'timestamp' => 1657765085,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.14392,
                            'lon' => 4.52123,
                        ],
                        'timestamp' => 1657797939,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.01941,
                            'lon' => 4.5995,
                        ],
                        'timestamp' => 1657718568,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.01073,
                            'lon' => 4.53455,
                        ],
                        'timestamp' => 1657715607,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.35461,
                            'lon' => 4.53947,
                        ],
                        'timestamp' => 1657713887,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.93134,
                            'lon' => 4.52102,
                        ],
                        'timestamp' => 1657714876,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.66162,
                            'lon' => 4.55918,
                        ],
                        'timestamp' => 1657768740,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.78588,
                            'lon' => 4.57395,
                        ],
                        'timestamp' => 1657714771,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.77456,
                            'lon' => 4.55741,
                        ],
                        'timestamp' => 1657732722,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.24565,
                            'lon' => 4.50674,
                        ],
                        'timestamp' => 1657747757,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.02232,
                            'lon' => 4.5553,
                        ],
                        'timestamp' => 1657768913,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.68856,
                            'lon' => 4.5782,
                        ],
                        'timestamp' => 1657737336,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.66122,
                            'lon' => 4.55153,
                        ],
                        'timestamp' => 1657718445,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.12434,
                            'lon' => 4.58783,
                        ],
                        'timestamp' => 1657782682,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.09363,
                            'lon' => 4.56873,
                        ],
                        'timestamp' => 1657742871,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.68862,
                            'lon' => 4.53155,
                        ],
                        'timestamp' => 1657751992,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.06394,
                            'lon' => 4.50283,
                        ],
                        'timestamp' => 1657750693,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.79867,
                            'lon' => 4.59504,
                        ],
                        'timestamp' => 1657786865,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 47.16932,
                            'lon' => 4.58149,
                        ],
                        'timestamp' => 1657749368,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.47413,
                            'lon' => 4.51047,
                        ],
                        'timestamp' => 1657718512,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.25826,
                            'lon' => 4.52404,
                        ],
                        'timestamp' => 1657729688,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.76336,
                            'lon' => 4.52889,
                        ],
                        'timestamp' => 1657751284,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.83325,
                            'lon' => 4.57229,
                        ],
                        'timestamp' => 1657787569,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 47.00375,
                            'lon' => 4.57787,
                        ],
                        'timestamp' => 1657723198,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.99813,
                            'lon' => 4.55455,
                        ],
                        'timestamp' => 1657712625,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.72645,
                            'lon' => 4.55425,
                        ],
                        'timestamp' => 1657761835,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.32988,
                            'lon' => 4.54518,
                        ],
                        'timestamp' => 1657703242,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.48222,
                            'lon' => 4.58925,
                        ],
                        'timestamp' => 1657728641,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.2332,
                            'lon' => 4.56681,
                        ],
                        'timestamp' => 1657726558,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.23548,
                            'lon' => 4.54717,
                        ],
                        'timestamp' => 1657750091,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 47.49244,
                            'lon' => 4.55711,
                        ],
                        'timestamp' => 1657789947,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.16807,
                            'lon' => 4.59545,
                        ],
                        'timestamp' => 1657760656,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.76076,
                            'lon' => 4.50461,
                        ],
                        'timestamp' => 1657766430,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.92824,
                            'lon' => 4.57218,
                        ],
                        'timestamp' => 1657750450,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.16387,
                            'lon' => 4.50226,
                        ],
                        'timestamp' => 1657785297,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.40311,
                            'lon' => 4.58274,
                        ],
                        'timestamp' => 1657793968,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.66107,
                            'lon' => 4.52417,
                        ],
                        'timestamp' => 1657730764,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.4159,
                            'lon' => 4.54516,
                        ],
                        'timestamp' => 1657701434,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.06151,
                            'lon' => 4.58602,
                        ],
                        'timestamp' => 1657747738,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.41315,
                            'lon' => 4.55007,
                        ],
                        'timestamp' => 1657759634,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.03643,
                            'lon' => 4.53571,
                        ],
                        'timestamp' => 1657709443,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.96532,
                            'lon' => 4.50373,
                        ],
                        'timestamp' => 1657790693,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.33885,
                            'lon' => 4.54672,
                        ],
                        'timestamp' => 1657778689,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.34583,
                            'lon' => 4.55885,
                        ],
                        'timestamp' => 1657788233,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.72544,
                            'lon' => 4.55583,
                        ],
                        'timestamp' => 1657766479,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.96308,
                            'lon' => 4.54416,
                        ],
                        'timestamp' => 1657789795,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.17928,
                            'lon' => 4.5479,
                        ],
                        'timestamp' => 1657727219,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.0376,
                            'lon' => 4.51926,
                        ],
                        'timestamp' => 1657783825,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.7771,
                            'lon' => 4.55978,
                        ],
                        'timestamp' => 1657772391,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.65349,
                            'lon' => 4.56139,
                        ],
                        'timestamp' => 1657769546,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.02846,
                            'lon' => 4.55448,
                        ],
                        'timestamp' => 1657735721,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.96528,
                            'lon' => 4.56277,
                        ],
                        'timestamp' => 1657796556,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.94986,
                            'lon' => 4.57847,
                        ],
                        'timestamp' => 1657780748,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.2675,
                            'lon' => 4.58362,
                        ],
                        'timestamp' => 1657727582,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.4861,
                            'lon' => 4.59866,
                        ],
                        'timestamp' => 1657757720,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.72155,
                            'lon' => 4.52536,
                        ],
                        'timestamp' => 1657780674,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.51017,
                            'lon' => 4.5148,
                        ],
                        'timestamp' => 1657733303,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.7922,
                            'lon' => 4.53544,
                        ],
                        'timestamp' => 1657786275,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.8719,
                            'lon' => 4.58985,
                        ],
                        'timestamp' => 1657726615,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.41761,
                            'lon' => 4.51231,
                        ],
                        'timestamp' => 1657765504,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 47.16783,
                            'lon' => 4.53471,
                        ],
                        'timestamp' => 1657737715,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.54606,
                            'lon' => 4.58461,
                        ],
                        'timestamp' => 1657795031,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.19156,
                            'lon' => 4.50986,
                        ],
                        'timestamp' => 1657762341,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.00876,
                            'lon' => 4.51035,
                        ],
                        'timestamp' => 1657759127,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 46.72721,
                            'lon' => 4.52688,
                        ],
                        'timestamp' => 1657797489,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.64941,
                            'lon' => 4.55675,
                        ],
                        'timestamp' => 1657708397,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.80821,
                            'lon' => 4.57887,
                        ],
                        'timestamp' => 1657722203,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 45.87634,
                            'lon' => 4.55155,
                        ],
                        'timestamp' => 1657724529,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.19062,
                            'lon' => 4.52555,
                        ],
                        'timestamp' => 1657739889,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.01386,
                            'lon' => 4.54829,
                        ],
                        'timestamp' => 1657798825,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.45506,
                            'lon' => 4.57713,
                        ],
                        'timestamp' => 1657730416,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.90841,
                            'lon' => 4.57712,
                        ],
                        'timestamp' => 1657775494,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.23713,
                            'lon' => 4.54795,
                        ],
                        'timestamp' => 1657742062,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.61858,
                            'lon' => 4.508,
                        ],
                        'timestamp' => 1657783173,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.06882,
                            'lon' => 4.57871,
                        ],
                        'timestamp' => 1657778834,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.90173,
                            'lon' => 4.50495,
                        ],
                        'timestamp' => 1657703613,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.63338,
                            'lon' => 4.54001,
                        ],
                        'timestamp' => 1657788308,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.76096,
                            'lon' => 4.51481,
                        ],
                        'timestamp' => 1657702846,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.17064,
                            'lon' => 4.58106,
                        ],
                        'timestamp' => 1657700777,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 46.06307,
                            'lon' => 4.58996,
                        ],
                        'timestamp' => 1657728569,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.39237,
                            'lon' => 4.55149,
                        ],
                        'timestamp' => 1657781357,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.70177,
                            'lon' => 4.5702,
                        ],
                        'timestamp' => 1657724708,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.08188,
                            'lon' => 4.56469,
                        ],
                        'timestamp' => 1657744414,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.67804,
                            'lon' => 4.5318,
                        ],
                        'timestamp' => 1657718291,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.03136,
                            'lon' => 4.55392,
                        ],
                        'timestamp' => 1657785313,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.2442,
                            'lon' => 4.57921,
                        ],
                        'timestamp' => 1657705533,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.8038,
                            'lon' => 4.54501,
                        ],
                        'timestamp' => 1657710475,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 45.58414,
                            'lon' => 4.55857,
                        ],
                        'timestamp' => 1657784821,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 47.08668,
                            'lon' => 4.51272,
                        ],
                        'timestamp' => 1657747126,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.47216,
                            'lon' => 4.52791,
                        ],
                        'timestamp' => 1657762037,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.09914,
                            'lon' => 4.53279,
                        ],
                        'timestamp' => 1657791224,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.82643,
                            'lon' => 4.52139,
                        ],
                        'timestamp' => 1657748822,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.41238,
                            'lon' => 4.55031,
                        ],
                        'timestamp' => 1657763681,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 45.73027,
                            'lon' => 4.5707,
                        ],
                        'timestamp' => 1657786665,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.42387,
                            'lon' => 4.51494,
                        ],
                        'timestamp' => 1657734423,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.74616,
                            'lon' => 4.54155,
                        ],
                        'timestamp' => 1657757705,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.90562,
                            'lon' => 4.50385,
                        ],
                        'timestamp' => 1657778781,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.2754,
                            'lon' => 4.54865,
                        ],
                        'timestamp' => 1657732631,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 45.88655,
                            'lon' => 4.5676,
                        ],
                        'timestamp' => 1657740494,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.85919,
                            'lon' => 4.51535,
                        ],
                        'timestamp' => 1657738824,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 45.65572,
                            'lon' => 4.58955,
                        ],
                        'timestamp' => 1657799557,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.8932,
                            'lon' => 4.52821,
                        ],
                        'timestamp' => 1657739919,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 46.92197,
                            'lon' => 4.5591,
                        ],
                        'timestamp' => 1657719341,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.28934,
                            'lon' => 4.52019,
                        ],
                        'timestamp' => 1657741859,
                    ],
            ],
            [
                'name' => 'loc-427EF',
                'point' => [
                        'vehicle' => [
                            'id' => '427EF',
                        ],
                        'position' => [
                            'lat' => 47.0523,
                            'lon' => 4.50842,
                        ],
                        'timestamp' => 1657745931,
                    ],
            ],
            [
                'name' => 'loc-422EF',
                'point' => [
                        'vehicle' => [
                            'id' => '422EF',
                        ],
                        'position' => [
                            'lat' => 45.67692,
                            'lon' => 4.52562,
                        ],
                        'timestamp' => 1657731484,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 45.8799,
                            'lon' => 4.58718,
                        ],
                        'timestamp' => 1657794172,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.13756,
                            'lon' => 4.5983,
                        ],
                        'timestamp' => 1657710685,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.80951,
                            'lon' => 4.5796,
                        ],
                        'timestamp' => 1657735899,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.09329,
                            'lon' => 4.53994,
                        ],
                        'timestamp' => 1657720533,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 47.30637,
                            'lon' => 4.52609,
                        ],
                        'timestamp' => 1657746094,
                    ],
            ],
            [
                'name' => 'loc-425EF',
                'point' => [
                        'vehicle' => [
                            'id' => '425EF',
                        ],
                        'position' => [
                            'lat' => 46.13415,
                            'lon' => 4.56239,
                        ],
                        'timestamp' => 1657756296,
                    ],
            ],
            [
                'name' => 'loc-426EF',
                'point' => [
                        'vehicle' => [
                            'id' => '426EF',
                        ],
                        'position' => [
                            'lat' => 46.54097,
                            'lon' => 4.58377,
                        ],
                        'timestamp' => 1657774887,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 46.17189,
                            'lon' => 4.56581,
                        ],
                        'timestamp' => 1657723261,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.74479,
                            'lon' => 4.52384,
                        ],
                        'timestamp' => 1657709654,
                    ],
            ],
            [
                'name' => 'loc-423EF',
                'point' => [
                        'vehicle' => [
                            'id' => '423EF',
                        ],
                        'position' => [
                            'lat' => 46.93777,
                            'lon' => 4.53262,
                        ],
                        'timestamp' => 1657772753,
                    ],
            ],
            [
                'name' => 'loc-420EF',
                'point' => [
                        'vehicle' => [
                            'id' => '420EF',
                        ],
                        'position' => [
                            'lat' => 47.29841,
                            'lon' => 4.58266,
                        ],
                        'timestamp' => 1657787547,
                    ],
            ],
            [
                'name' => 'loc-421EF',
                'point' => [
                        'vehicle' => [
                            'id' => '421EF',
                        ],
                        'position' => [
                            'lat' => 46.3689,
                            'lon' => 4.55539,
                        ],
                        'timestamp' => 1657746087,
                    ],
            ],
            [
                'name' => 'loc-424EF',
                'point' => [
                        'vehicle' => [
                            'id' => '424EF',
                        ],
                        'position' => [
                            'lat' => 45.91211,
                            'lon' => 4.535,
                        ],
                        'timestamp' => 1657740611,
                    ],
            ],
        ];

        parent::init();
    }
}
