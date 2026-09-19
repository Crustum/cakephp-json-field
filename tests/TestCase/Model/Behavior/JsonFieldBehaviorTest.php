<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Model\Behavior\JsonFieldBehavior;
use Crustum\JsonField\ORM\JsonFieldResultSet;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\Model\Behavior\JsonFieldBehavior
 */
#[Group('orm-deferred')]
class JsonFieldBehaviorTest extends TestCase
{
    public function testBeforeFindInstallsJsonFieldResultSet(): void
    {
        $connection = ConnectionManager::get('test');
        $table = new Table([
            'table' => 'jsonfield_articles',
            'connection' => $connection,
            'schema' => ['id' => ['type' => 'integer']],
        ]);
        $behavior = new JsonFieldBehavior($table);
        $query = new SelectQuery($table);

        $behavior->beforeFind(new Event('Model.beforeFind'), $query, new ArrayObject(), true);

        $this->assertSame(JsonFieldResultSet::class, $query->resultSetFactory()->getResultSetClass());
    }
}
