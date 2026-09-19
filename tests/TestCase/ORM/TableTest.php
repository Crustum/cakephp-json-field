<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\Test\TestCase\ORM\TableTest as CakeTableTest;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Runs CakePHP's core ORM `Table` test suite on the Crustum/JsonField
 * JSON driver, so we can prove the driver does not break CakePHP core ORM
 * table SQL generation. All test methods are inherited from the core suite
 * (fixtures/assertions untouched).
 *
 * The inherited core tests use mocks without explicit expectations; opt out
 * of PHPUnit's strict mock-functionality check at the class level.
 */
#[AllowMockObjectsWithoutExpectations]
class TableTest extends CakeTableTest
{
}
