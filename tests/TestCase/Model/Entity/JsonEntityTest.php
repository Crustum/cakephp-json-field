<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use Crustum\JsonField\ORM\Association\JsonFieldEmbed;
use Crustum\JsonField\ORM\Association\JsonFieldHasOne;
use JfApp\Model\Entity\JsonFieldArticle;
use JfApp\Model\Entity\JsonFieldProfile;

/**
 * @uses \Crustum\JsonField\Model\Entity\JsonEntity
 * @uses \Crustum\JsonField\Model\Entity\JsonFieldTrait
 */
class JsonEntityTest extends TestCase
{
    public function testEmbeddedParentBackPointer(): void
    {
        $article = new JsonFieldArticle(['id' => 1]);
        $profile = new JsonFieldProfile(['name' => 'Jane']);

        $this->assertFalse($profile->isEmbedded());
        $this->assertNull($profile->getEmbeddedParent());

        $profile->setEmbeddedParent($article, new JsonFieldHasOne('profile'));

        $this->assertTrue($profile->isEmbedded());
        $parent = $profile->getEmbeddedParent();
        $this->assertSame($article, $parent['parent']);
        $this->assertInstanceOf(JsonFieldEmbed::class, $parent['association']);
    }

    public function testNonPathCallsPassThrough(): void
    {
        $profile = new JsonFieldProfile(['name' => 'Jane', 'age' => 30]);
        $profile->clean();

        $this->assertSame('Jane', $profile->get('name'));
        $this->assertSame(30, $profile->get('age'));
        $this->assertFalse($profile->isDirty('name'));

        $profile->set('age', 31);
        $this->assertTrue($profile->isDirty('age'));
        $this->assertSame(31, $profile->get('age'));
    }

    public function testTraitHandlesRawArrayPaths(): void
    {
        // Mirrors the unhydrated query path: the embed arrives as a plain array
        // (no JsonEntity), yet path access must still resolve in pure PHP.
        $profile = new JsonFieldProfile([
            'name' => 'Jane',
            'data' => ['extra' => ['k' => 'v']],
        ]);

        $this->assertSame('v', $profile->get('data->extra.k'));
        $this->assertTrue($profile->has('data->extra.k'));

        $profile->set('data->extra.k', 'w');
        $this->assertTrue($profile->isDirty('data'));
        $this->assertSame('w', $profile->get('data->extra.k'));

        $profile->unset('data->extra.k');
        $this->assertFalse($profile->has('data->extra.k'));
    }
}
