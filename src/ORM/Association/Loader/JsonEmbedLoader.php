<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association\Loader;

use Cake\Datasource\EntityInterface;
use Crustum\JsonField\ORM\Association\JsonFieldAssociation;
use Crustum\JsonField\ORM\Association\JsonFieldEmbed;
use InvalidArgumentException;

/**
 * Loader for embedded associations (no SQL join).
 *
 * Delegates to `JsonFieldEmbed::hydrateEmbedded()`, building nested `JsonEntity`
 * sub-entities with their `embeddedParent` back-pointer set. This is the
 * JsonField analog of Mongo's `ODM\Association\Loader\EmbedLoader`.
 */
class JsonEmbedLoader implements JsonFieldAssociationLoaderInterface
{
    /**
     * Loads the association target from the raw value into the parent entity.
     *
     * Only embedded associations are supported; references resolve through a SQL join.
     */
    public function load(JsonFieldAssociation $association, EntityInterface $parent, mixed $raw): mixed
    {
        if (!$association instanceof JsonFieldEmbed) {
            throw new InvalidArgumentException('JsonEmbedLoader only supports embedded associations.');
        }

        return $association->hydrateEmbedded($raw, $parent);
    }
}
