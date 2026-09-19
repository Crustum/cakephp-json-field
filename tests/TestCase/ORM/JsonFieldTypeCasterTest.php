<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use Crustum\JsonField\ORM\JsonFieldResultSet;
use Crustum\JsonField\ORM\JsonFieldTypeCaster;
use JfApp\Model\Entity\ReaderArticle;
use JfApp\Model\Entity\ReaderProfile;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\ORM\JsonFieldTypeCaster
 */
#[Group('orm-deferred')]
class JsonFieldTypeCasterTest extends TestCase
{
    public function testCastsEmbedFieldsToPhpTypes(): void
    {
        $article = new ReaderArticle([
            'id' => 1,
            'data' => [
                'profile' => ['created' => '2024-01-15 10:30:00', 'price' => '9.99', 'label' => 'x'],
            ],
        ]);

        $resultSet = new JsonFieldResultSet([$article]);
        $resultSet->rewind();

        $hydrated = $resultSet->current();

        $map = JsonSchemaReader::read(ReaderArticle::class);
        $driver = ConnectionManager::get('test')->getDriver();

        (new JsonFieldTypeCaster())->castEmbeds($hydrated, $map, $driver);

        $profile = $hydrated->get('profile');
        $this->assertInstanceOf(ReaderProfile::class, $profile);
        $this->assertInstanceOf(DateTime::class, $profile->get('created'));
        $this->assertSame('9.99', $profile->get('price'));
        $this->assertSame('x', $profile->get('label'));
    }

    public function testCastingIsNoOpWithoutTypes(): void
    {
        $article = new ReaderArticle(['id' => 1, 'data' => ['profile' => ['created' => '2024-01-15 10:30:00']]]);
        $map = new JsonTypeMap();

        $resultSet = new JsonFieldResultSet([$article]);
        $resultSet->rewind();

        $hydrated = $resultSet->current();

        (new JsonFieldTypeCaster())->castEmbeds($hydrated, $map, ConnectionManager::get('test')->getDriver());

        $this->assertIsString($hydrated->get('profile')->get('created'));
    }

    public function testResultSetCastsWhenDriverResolvable(): void
    {
        $connection = ConnectionManager::get('test');
        TableRegistry::getTableLocator()->set(
            'ReaderCasted',
            new Table([
                'table' => 'reader_casted',
                'connection' => $connection,
                'schema' => ['id' => ['type' => 'integer']],
            ]),
        );

        $article = new ReaderArticle(['id' => 2, 'data' => ['profile' => ['created' => '2024-02-01 08:00:00']]]);
        $article->setSource('ReaderCasted');

        $resultSet = new JsonFieldResultSet([$article]);
        $resultSet->rewind();

        $hydrated = $resultSet->current();

        $this->assertInstanceOf(DateTime::class, $hydrated->get('profile')->get('created'));

        TableRegistry::getTableLocator()->remove('ReaderCasted');
    }
}
