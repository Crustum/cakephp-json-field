<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\ORM\Association;

use Cake\Datasource\EntityInterface;
use Cake\TestSuite\TestCase;
use Crustum\JsonField\Database\Schema\JsonSchemaReader;
use Crustum\JsonField\ORM\Association\JsonFieldAssociation;
use Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder;
use Crustum\JsonField\ORM\Association\JsonFieldBelongsTo;
use Crustum\JsonField\ORM\Association\JsonFieldBelongsToMany;
use Crustum\JsonField\ORM\Association\JsonFieldHasMany;
use Crustum\JsonField\ORM\Association\JsonFieldHasOne;
use Crustum\JsonField\ORM\Association\JsonFieldReference;
use Crustum\JsonField\ORM\Association\Loader\JsonEmbedLoader;
use Crustum\JsonField\Path\JsonPath;
use InvalidArgumentException;
use JfApp\Model\Entity\AssociationArticle;
use JfApp\Model\Entity\AssociationChapter;
use JfApp\Model\Entity\AssociationProfile;
use PHPUnit\Framework\Attributes\Group;

/**
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldAssociation
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldEmbed
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldHasOne
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldHasMany
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldReference
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldBelongsTo
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldBelongsToMany
 * @uses \Crustum\JsonField\ORM\Association\JsonFieldAssociationBuilder
 * @uses \Crustum\JsonField\ORM\Association\Loader\JsonEmbedLoader
 */
#[Group('orm-deferred')]
class JsonFieldAssociationTest extends TestCase
{
    public function testHasOneCardinalityAndStrategy(): void
    {
        $association = new JsonFieldHasOne('profile', [
            'property' => 'profile',
            'className' => AssociationProfile::class,
            'path' => 'data->profile',
        ]);

        $this->assertSame(JsonFieldAssociation::TYPE_ONE_TO_ONE, $association->type());
        $this->assertSame(JsonFieldAssociation::STRATEGY_EMBED, $association->getStrategy());
        $this->assertSame('profile', $association->getProperty());
        $this->assertSame('data->profile', $association->getPath());
        $this->assertSame('profile', $association->getLocalKey());
    }

    public function testHasManyCardinality(): void
    {
        $association = new JsonFieldHasMany('chapters', [
            'property' => 'chapters',
            'className' => AssociationChapter::class,
            'path' => 'data->chapters',
        ]);

        $this->assertSame(JsonFieldAssociation::TYPE_ONE_TO_MANY, $association->type());
        $this->assertSame(JsonFieldAssociation::STRATEGY_EMBED, $association->getStrategy());
    }

    public function testEmbedResolvePathReturnsJsonPath(): void
    {
        $association = new JsonFieldHasOne('profile', [
            'property' => 'profile',
            'className' => AssociationProfile::class,
            'path' => 'data->profile',
        ]);

        $path = $association->resolvePath();
        $this->assertInstanceOf(JsonPath::class, $path);
        $this->assertSame('data', $path->column);
        $this->assertSame(['profile'], $path->nodes);
    }

    public function testEmbedWritePathForChild(): void
    {
        $association = new JsonFieldHasMany('chapters', [
            'property' => 'chapters',
            'className' => AssociationChapter::class,
            'path' => 'data->chapters',
        ]);

        $childPath = $association->writePathForChild(2);
        $this->assertSame('data', $childPath->column);
        $this->assertSame(['chapters', 2], $childPath->nodes);
    }

    public function testEmbedRejectsNonEmbedStrategy(): void
    {
        $association = new JsonFieldHasOne('profile', [
            'property' => 'profile',
            'className' => AssociationProfile::class,
            'path' => 'data->profile',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $association->setStrategy(JsonFieldAssociation::STRATEGY_REFERENCE);
    }

    public function testHydrateEmbeddedHasOneBuildsEntityWithParent(): void
    {
        $association = new JsonFieldHasOne('profile', [
            'property' => 'profile',
            'className' => AssociationProfile::class,
            'path' => 'data->profile',
        ]);

        $parent = new AssociationArticle(['id' => 1]);
        $entity = $association->hydrateEmbedded(['title' => 'Jane'], $parent);

        $this->assertInstanceOf(AssociationProfile::class, $entity);
        $this->assertSame('Jane', $entity->get('title'));
        $this->assertTrue($entity->isEmbedded());
        $backPointer = $entity->getEmbeddedParent();
        $this->assertSame($parent, $backPointer['parent']);
        $this->assertSame($association, $backPointer['association']);
    }

    public function testHydrateEmbeddedHasManyBuildsList(): void
    {
        $association = new JsonFieldHasMany('chapters', [
            'property' => 'chapters',
            'className' => AssociationChapter::class,
            'path' => 'data->chapters',
        ]);

        $parent = new AssociationArticle(['id' => 1]);
        $entities = $association->hydrateEmbedded(
            [['name' => 'A'], ['name' => 'B']],
            $parent,
        );

        $this->assertIsArray($entities);
        $this->assertCount(2, $entities);
        $this->assertSame('B', $entities[1]->get('name'));
        $this->assertSame($association, $entities[1]->getEmbeddedParent()['association']);
    }

    public function testHydrateEmbeddedNullReturnsNull(): void
    {
        $association = new JsonFieldHasOne('profile', [
            'property' => 'profile',
            'className' => AssociationProfile::class,
            'path' => 'data->profile',
        ]);

        $this->assertNull($association->hydrateEmbedded(null, new AssociationArticle(['id' => 1])));
    }

    public function testBuilderProducesOneAndManyFromAttributes(): void
    {
        $builder = new JsonFieldAssociationBuilder();
        $associations = $builder->buildEmbeds(AssociationArticle::class);

        $this->assertArrayHasKey('profile', $associations);
        $this->assertArrayHasKey('chapters', $associations);
        $this->assertInstanceOf(JsonFieldHasOne::class, $associations['profile']);
        $this->assertInstanceOf(JsonFieldHasMany::class, $associations['chapters']);
        $this->assertSame(AssociationProfile::class, $associations['profile']->getClassName());
        $this->assertSame(AssociationChapter::class, $associations['chapters']->getClassName());
    }

    public function testEmbedLoaderDelegatesToHydrate(): void
    {
        $association = new JsonFieldHasOne('profile', [
            'property' => 'profile',
            'className' => AssociationProfile::class,
            'path' => 'data->profile',
        ]);

        $parent = new AssociationArticle(['id' => 1]);
        $loaded = new JsonEmbedLoader()->load($association, $parent, ['title' => 'Jane']);

        $this->assertInstanceOf(AssociationProfile::class, $loaded);
        $this->assertSame('Jane', $loaded->get('title'));
    }

    public function testReferenceBelongsToCardinalityAndForeignKeyPath(): void
    {
        $association = new JsonFieldBelongsTo('author', [
            'property' => 'author',
            'foreignKey' => 'data->authorId',
            'target' => 'Authors',
        ]);

        $this->assertSame(JsonFieldAssociation::TYPE_MANY_TO_ONE, $association->type());
        $this->assertSame(JsonFieldAssociation::STRATEGY_REFERENCE, $association->getStrategy());
        $this->assertSame('Authors', $association->getTargetClass());
        $this->assertSame('data', $association->getForeignKeyPath()->column);
        $this->assertSame(['authorId'], $association->getForeignKeyPath()->nodes);
    }

    public function testReferenceBelongsToManyCardinality(): void
    {
        $association = new JsonFieldBelongsToMany('editors', [
            'property' => 'editors',
            'foreignKey' => 'data->editorIds',
            'target' => 'Editors',
        ]);

        $this->assertSame(JsonFieldAssociation::TYPE_MANY_TO_MANY, $association->type());
        $this->assertInstanceOf(JsonFieldReference::class, $association);
    }

    public function testReferenceRejectsNonReferenceStrategy(): void
    {
        $association = new JsonFieldBelongsTo('author', [
            'property' => 'author',
            'foreignKey' => 'data->authorId',
            'target' => 'Authors',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $association->setStrategy(JsonFieldAssociation::STRATEGY_EMBED);
    }

    public function testEmbeddedValidatorIsInvokedAndCanReject(): void
    {
        $association = new JsonFieldHasMany('chapters', [
            'property' => 'chapters',
            'className' => AssociationChapter::class,
            'path' => 'data->chapters',
            'idField' => 'uid',
        ]);

        $calls = 0;
        $association->setEmbeddedValidator(static function ($item) use (&$calls): void {
            $calls++;
            if (is_array($item) || ($item instanceof EntityInterface && $item->get('name') === 'bad')) {
                throw new InvalidArgumentException('invalid chapter');
            }
        });

        $parent = new AssociationArticle();
        $parent->set('chapters', [new AssociationChapter(['name' => 'ok', 'uid' => 'u1'])]);

        $typeMap = JsonSchemaReader::read(AssociationArticle::class);
        $association->exportToColumns($parent, $typeMap, null, false);

        $this->assertSame(1, $calls);

        $parent->set('chapters', [new AssociationChapter(['name' => 'bad', 'uid' => 'u2'])]);
        $this->expectException(InvalidArgumentException::class);
        $association->exportToColumns($parent, $typeMap, null, false);
    }
}
