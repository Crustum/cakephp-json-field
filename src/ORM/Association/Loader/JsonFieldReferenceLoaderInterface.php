<?php
declare(strict_types=1);

namespace Crustum\JsonField\ORM\Association\Loader;

use Crustum\JsonField\ORM\Association\JsonFieldReference;

/**
 * Loader contract for JSON-FK reference associations.
 *
 * A reference stores a foreign key (or list of keys) inside the parent's JSON
 * column. The loader reads the key(s) from the already-decoded JSON data, queries
 * the target Table for the matching rows, and assigns them back onto the root
 * entities' reference property — the JsonField analog of Mongo's `DBRef` resolver.
 */
interface JsonFieldReferenceLoaderInterface
{
    /**
     * Resolves and assigns the reference target onto each provided root entity.
     *
     * @param array<int, \Cake\Datasource\EntityInterface> $entities The root document entities to populate.
     * @param \Crustum\JsonField\ORM\Association\JsonFieldReference $association The reference association.
     * @return void
     */
    public function load(array $entities, JsonFieldReference $association): void;
}
