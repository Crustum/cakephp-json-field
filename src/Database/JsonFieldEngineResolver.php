<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

use Cake\Database\Driver;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlite;
use InvalidArgumentException;

/**
 * Resolves the engine adapter for the active database driver.
 *
 * Picks the matching `JsonFieldEngineInterface` implementation from the connection
 * driver, keeping the rest of the plugin engine-neutral. Adding a new engine means
 * extending this resolver with one more branch.
 */
class JsonFieldEngineResolver
{
    /**
     * Returns the engine adapter for the given database driver.
     *
     * @param \Cake\Database\Driver $driver The active connection driver.
     * @return \Crustum\JsonField\Database\JsonFieldEngineInterface The resolved engine.
     * @throws \InvalidArgumentException When no engine is available for the driver.
     */
    public function engine(Driver $driver): JsonFieldEngineInterface
    {
        if ($driver instanceof Postgres) {
            return new PostgresEngine();
        }

        if ($driver instanceof Mysql) {
            return new MysqlEngine();
        }

        if ($driver instanceof Sqlite) {
            return new SqliteEngine();
        }

        throw new InvalidArgumentException(sprintf(
            'No JsonField engine adapter is available for driver `%s`.',
            $driver::class,
        ));
    }
}
