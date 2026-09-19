<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;
use JfApp\Model\Entity\JsonFieldArticle;
use JfApp\Model\Entity\JsonFieldProfile;

/**
 * @uses \Crustum\JsonField\Model\Entity\JsonFieldTrait
 * @uses \Crustum\JsonField\Model\Entity\JsonEntity
 */
class JsonFieldTraitTest extends TestCase
{
    public function testGetResolvesNestedEmbedPath(): void
    {
        $profile = new JsonFieldProfile([
            'name' => 'Jane',
            'address' => ['city' => 'NYC'],
        ]);
        $article = new JsonFieldArticle(['id' => 1, 'data' => $profile]);

        $this->assertSame('Jane', $article->get('data->name'));
        $this->assertSame('NYC', $article->get('data->address.city'));
    }

    public function testGetResolvesNestedArrayPath(): void
    {
        $article = new JsonFieldArticle([
            'id' => 1,
            'data' => ['profile' => ['name' => 'X']],
        ]);

        $this->assertSame('X', $article->get('data->profile.name'));
    }

    public function testGetReturnsDefaultForMissingPath(): void
    {
        $article = new JsonFieldArticle(['id' => 1, 'data' => []]);

        $this->assertSame('fallback', $article->get('data->missing', 'fallback'));
    }

    public function testSetPropagatesToNestedEmbed(): void
    {
        $profile = new JsonFieldProfile(['name' => 'Jane']);
        $article = new JsonFieldArticle(['id' => 1, 'data' => $profile]);

        $article->set('data->name', 'Bob');

        $this->assertSame('Bob', $profile->get('name'));
        $this->assertTrue($profile->isDirty('name'));
        $this->assertTrue($article->isDirty('data->name'));
    }

    public function testSetRebuildsNestedArray(): void
    {
        $article = new JsonFieldArticle([
            'id' => 1,
            'data' => ['profile' => ['name' => 'X']],
        ]);

        $article->set('data->profile.name', 'Y');

        $this->assertSame('Y', $article->get('data->profile.name'));
        $this->assertSame(['profile' => ['name' => 'Y']], $article->get('data'));
    }

    public function testIsDirtyOnPath(): void
    {
        $profile = new JsonFieldProfile(['name' => 'Jane']);
        $article = new JsonFieldArticle(['id' => 1, 'data' => $profile]);
        $profile->clean();

        $this->assertFalse($article->isDirty('data->name'));

        $article->set('data->name', 'Bob');

        $this->assertTrue($article->isDirty('data->name'));
        $this->assertFalse($article->isDirty('data->address'));
    }

    public function testV1NotationPath(): void
    {
        $profile = new JsonFieldProfile(['name' => 'Jane']);
        $article = new JsonFieldArticle(['id' => 1, 'data' => $profile]);

        $this->assertSame('Jane', $article->get('name@data'));
    }

    public function testGetNestedEmbedWithinEmbed(): void
    {
        $address = new JsonFieldProfile(['city' => 'NYC']);
        $profile = new JsonFieldProfile(['name' => 'Jane', 'address' => $address]);
        $article = new JsonFieldArticle(['id' => 1, 'data' => $profile]);

        $this->assertSame('NYC', $article->get('data->address.city'));
    }
}
