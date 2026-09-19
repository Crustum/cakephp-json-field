<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association;

use Cake\Datasource\RepositoryInterface;
use Crustum\JsonField\Database\Schema\Attribute\JsonEmbed;
use Crustum\JsonField\Database\Schema\Attribute\JsonReference;
use ReflectionClass;

/**
 * Builds JsonField association objects from declarative entity attributes.
 *
 * The builder reflects a root document entity class and turns each `#[JsonEmbed]`
 * declaration into a concrete `JsonFieldEmbed` association (`JsonFieldHasOne` for
 * `type: 'one'`, `JsonFieldHasMany` for `type: 'many'`), and each `#[JsonReference]`
 * declaration into a concrete `JsonFieldReference` association (`JsonFieldBelongsTo`
 * for `type: 'one'`, `JsonFieldBelongsToMany` for `type: 'many'`). This is the
 * association analog of `JsonSchemaReader` for the type map.
 */
class JsonFieldAssociationBuilder
{
    /**
     * Builds the embedded associations declared on a root entity class.
     *
     * @param class-string $entityClass The fully-qualified root document entity class.
     * @param \Cake\Datasource\RepositoryInterface|null $source The owning repository (Table), if known.
     * @return array<string, \Crustum\JsonField\ORM\Association\JsonFieldEmbed> Associations keyed by property name.
     * @throws \ReflectionException When the entity class cannot be reflected.
     */
    public function buildEmbeds(string $entityClass, ?RepositoryInterface $source = null): array
    {
        $reflection = new ReflectionClass($entityClass);
        $associations = [];

        foreach ($reflection->getAttributes(JsonEmbed::class) as $attribute) {
            /** @var \Crustum\JsonField\Database\Schema\Attribute\JsonEmbed $embed */
            $embed = $attribute->newInstance();
            $name = $embed->property();

            $options = [
                'property' => $embed->property(),
                'className' => $embed->documentClass(),
                'path' => $embed->path(),
                'idField' => $embed->idField(),
                'saveStrategy' => $embed->saveStrategy(),
            ];

            $association = $embed->type() === 'many'
                ? new JsonFieldHasMany($name, $options)
                : new JsonFieldHasOne($name, $options);

            if ($source instanceof RepositoryInterface) {
                $association->setSource($source);
            }

            $associations[$name] = $association;
        }

        return $associations;
    }

    /**
     * Builds the JSON-FK reference associations declared on a root entity class.
     *
     * Each `#[JsonReference]` becomes a `JsonFieldBelongsTo` (`type: 'one'`) or a
     * `JsonFieldBelongsToMany` (`type: 'many'`), carrying the foreign-key JSON path
     * and the target Table class. These resolve through a real SQL join on the
     * foreign key (see the reference loaders), unlike embedded associations.
     *
     * @param class-string $entityClass The fully-qualified root document entity class.
     * @param \Cake\Datasource\RepositoryInterface|null $source The owning repository (Table), if known.
     * @return array<string, \Crustum\JsonField\ORM\Association\JsonFieldReference> Associations keyed by property name.
     * @throws \ReflectionException When the entity class cannot be reflected.
     */
    public function buildReferences(string $entityClass, ?RepositoryInterface $source = null): array
    {
        $reflection = new ReflectionClass($entityClass);
        $associations = [];

        foreach ($reflection->getAttributes(JsonReference::class) as $attribute) {
            /** @var \Crustum\JsonField\Database\Schema\Attribute\JsonReference $reference */
            $reference = $attribute->newInstance();
            $name = $reference->property();

            $options = [
                'property' => $reference->property(),
                'target' => $reference->target(),
                'foreignKey' => $reference->path(),
                'idField' => $reference->idField(),
            ];

            $association = $reference->type() === 'many'
                ? new JsonFieldBelongsToMany($name, $options)
                : new JsonFieldBelongsTo($name, $options);

            if ($source instanceof RepositoryInterface) {
                $association->setSource($source);
            }

            $associations[$name] = $association;
        }

        return $associations;
    }
}
