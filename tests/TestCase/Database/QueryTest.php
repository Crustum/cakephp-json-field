<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database;

use Cake\Test\TestCase\Database\QueryTest as CakeQueryTest;

/**
 * Runs CakePHP's core `Database\Query` test suite on the Crustum/JsonField
 * JSON driver, so we can prove the driver does not break CakePHP core query
 * SQL generation. All test methods are inherited from the core suite
 * (fixtures/assertions untouched).
 */
class QueryTest extends CakeQueryTest
{
}
