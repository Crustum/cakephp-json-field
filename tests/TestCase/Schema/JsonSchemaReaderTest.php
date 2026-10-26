<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Schema;

use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Database\JsonTypeMap;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use JfApp\Model\Entity\ReaderArticle;
use JfApp\Model\Entity\ReaderProfile;

/**
 * @uses \Crustum\JsonField\Database\Schema\JsonSchemaReader
 * @uses \Crustum\JsonField\Database\Schema\Attribute\JsonColumn
 * @uses \Crustum\JsonField\Database\Schema\Attribute\JsonEmbed
 * @uses \Crustum\JsonField\Database\Schema\Attribute\JsonPathType
 */
class JsonSchemaReaderTest extends TestCase
{
    public function testReadReturnsTypeMapWithEmbeddedPaths(): void
    {
        $map = JsonSchemaReader::read(ReaderArticle::class);

        $this->assertInstanceOf(JsonTypeMap::class, $map);
        $this->assertTrue($map->hasType('data->profile.created'));
        $this->assertTrue($map->hasType('data->profile.price'));
    }

    public function testReadHonorsRootAbsolutePathType(): void
    {
        $map = JsonSchemaReader::read(ReaderArticle::class);

        $this->assertTrue($map->hasType('data->updated'));
    }

    public function testReadIgnoresEmbedPathTypeWithoutType(): void
    {
        $map = JsonSchemaReader::read(ReaderArticle::class);

        $this->assertFalse($map->hasType('data->profile.label'));
    }

    public function testReadCachesSameInstance(): void
    {
        $first = JsonSchemaReader::read(ReaderArticle::class);
        $second = JsonSchemaReader::read(ReaderArticle::class);

        $this->assertSame($first, $second);
    }

    public function testColumnsReturnsDeclaredColumns(): void
    {
        $columns = JsonSchemaReader::columns(ReaderArticle::class);

        $this->assertSame(['data'], $columns);
    }

    public function testReadResolvesCasterForEmbeddedPath(): void
    {
        $map = JsonSchemaReader::read(ReaderArticle::class);
        $value = $map->castValue('data->profile.created', '2020-01-01', JsonTypeMap::OPERATION_MARSHAL);

        $this->assertInstanceOf(DateTime::class, $value);
    }

    public function testReadDoesNotLeakEmbedTypesToOtherClasses(): void
    {
        $articleMap = JsonSchemaReader::read(ReaderArticle::class);
        $profileMap = JsonSchemaReader::read(ReaderProfile::class);

        $this->assertTrue($articleMap->hasType('data->profile.created'));
        $this->assertFalse($profileMap->hasType('data->profile.created'));
    }

    public function testClearCacheForcesReread(): void
    {
        $first = JsonSchemaReader::read(ReaderArticle::class);
        JsonSchemaReader::clearCache();
        $second = JsonSchemaReader::read(ReaderArticle::class);

        $this->assertNotSame($first, $second);
        $this->assertTrue($second->hasType('data->profile.created'));
    }
}
