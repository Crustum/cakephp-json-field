<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association\Loader;

use Cake\Datasource\EntityInterface;
use Crustum\JsonField\ORM\Association\JsonFieldAssociation;

/**
 * Maps raw JSON-column data into a hydrated association target on the parent entity.
 *
 * The loader is the public result-mapping seam used by the hydration layer
 * (Phase 5 `JsonFieldResultSet`). It receives the owning association, the parent
 * document entity, and the raw decoded value, and returns the hydrated target.
 */
interface JsonFieldAssociationLoaderInterface
{
    /**
     * Loads the association target from the raw value into the parent entity.
     *
     * @param \Crustum\JsonField\ORM\Association\JsonFieldAssociation $association The association being loaded.
     * @param \Cake\Datasource\EntityInterface $parent The parent document entity.
     * @param mixed $raw The raw value decoded from the JSON column.
     * @return mixed The hydrated target (entity, list of entities, or null).
     */
    public function load(JsonFieldAssociation $association, EntityInterface $parent, mixed $raw): mixed;
}
