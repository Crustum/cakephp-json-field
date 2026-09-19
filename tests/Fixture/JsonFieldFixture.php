<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\Fixture;

use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * Base fixture that makes JSON-column seeding work on SQLite.
 *
 * On SQLite a `json` column is reflected back as TEXT, so Cake marshals the
 * seeded PHP array with StringType and the insert fails ("Cannot convert value
 * `Array` to string"). For SQLite we encode each JSON-column value to a JSON
 * string and redeclare those columns as `text`, so the string is stored
 * verbatim; the test tables still type the columns as `json`, so reads decode
 * them back. Postgres/MySQL keep `json` + arrays, which their JsonType marshals
 * natively.
 */
class JsonFieldFixture extends TestFixture
{
    /**
     * @return void
     */
    public function init(): void
    {
        if (ConnectionManager::get('test')->getDriver() instanceof Sqlite) {
            $this->encodeJsonColumnsForSqlite();
        }

        parent::init();
    }

    /**
     * Encodes JSON-column array values to JSON strings and switches those
     * columns to `text` so SQLite stores them verbatim.
     *
     * @return void
     */
    private function encodeJsonColumnsForSqlite(): void
    {
        if (!isset($this->records)) {
            return;
        }

        foreach ($this->fields as $column => $definition) {
            if (($definition['type'] ?? null) !== 'json') {
                continue;
            }

            $this->fields[$column]['type'] = 'text';

            foreach ($this->records as &$row) {
                if (isset($row[$column]) && is_array($row[$column])) {
                    $row[$column] = json_encode($row[$column]);
                }
            }
        }
    }
}
