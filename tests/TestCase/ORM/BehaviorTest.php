<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\Test\TestCase\ORM\BehaviorTest as CakeBehaviorTest;

/**
 * Runs CakePHP's core ORM `Behavior` test suite on the Crustum/JsonField
 * JSON driver, so we can prove the driver does not break CakePHP core ORM
 * behavior SQL generation. All test methods are inherited from the core
 * suite (fixtures/assertions untouched).
 */
class BehaviorTest extends CakeBehaviorTest
{
}
