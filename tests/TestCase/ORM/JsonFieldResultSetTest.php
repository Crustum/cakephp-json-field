<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM;

use Cake\TestSuite\TestCase;
use Crustum\JsonField\Model\Entity\JsonEntity;
use Crustum\JsonField\ORM\JsonFieldResultSet;
use JfApp\Model\Entity\AssociationArticle;
use JfApp\Model\Entity\AssociationChapter;
use JfApp\Model\Entity\AssociationProfile;
use JfApp\Model\Entity\JsonFieldProfile;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\ORM\JsonFieldResultSet
 */
#[Group('orm-deferred')]
class JsonFieldResultSetTest extends TestCase
{
    public function testHydratesOneAndManyEmbedsWithBackPointer(): void
    {
        $article = new AssociationArticle([
            'id' => 1,
            'data' => [
                'profile' => ['title' => 'Jane'],
                'chapters' => [['name' => 'A'], ['name' => 'B']],
            ],
        ]);

        $resultSet = new JsonFieldResultSet([$article]);
        $resultSet->rewind();

        $hydrated = $resultSet->current();

        $profile = $hydrated->get('profile');
        $this->assertInstanceOf(AssociationProfile::class, $profile);
        $this->assertInstanceOf(JsonEntity::class, $profile);
        $this->assertSame('Jane', $profile->get('title'));
        $this->assertTrue($profile->isEmbedded());

        $backPointer = $profile->getEmbeddedParent();
        $this->assertSame($hydrated, $backPointer['parent']);

        $chapters = $hydrated->get('chapters');
        $this->assertIsArray($chapters);
        $this->assertCount(2, $chapters);
        $this->assertInstanceOf(AssociationChapter::class, $chapters[1]);
        $this->assertSame('B', $chapters[1]->get('name'));
        $this->assertSame($hydrated, $chapters[1]->getEmbeddedParent()['parent']);
    }

    public function testHydratesMissingEmbedAsNullWithoutError(): void
    {
        $article = new AssociationArticle([
            'id' => 2,
            'data' => [],
        ]);

        $resultSet = new JsonFieldResultSet([$article]);
        $resultSet->rewind();

        $hydrated = $resultSet->current();

        $this->assertNull($hydrated->get('profile'));
        $this->assertNull($hydrated->get('chapters'));
    }

    public function testNoOpForEntityWithoutEmbeds(): void
    {
        $profile = new JsonFieldProfile(['name' => 'Solo']);
        $resultSet = new JsonFieldResultSet([$profile]);
        $resultSet->rewind();

        $this->assertSame($profile, $resultSet->current());
    }

    public function testHydrationRunsOncePerRow(): void
    {
        $article = new AssociationArticle([
            'id' => 3,
            'data' => ['profile' => ['title' => 'Once']],
        ]);

        $resultSet = new JsonFieldResultSet([$article]);
        $resultSet->rewind();

        $first = $resultSet->current();
        $second = $resultSet->current();

        $this->assertSame($first, $second);
        $this->assertSame($first->get('profile'), $second->get('profile'));
    }
}
