<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonEmbed;
use Crustum\JsonField\Database\Schema\Attribute\JsonReference;
use JfApp\Model\Table\TagsTable;
use JfApp\Model\Table\UsersTable;

#[JsonReference(property: 'author', target: UsersTable::class, path: 'data->author_id', type: 'one')]
#[JsonReference(property: 'tags', target: TagsTable::class, path: 'data->tag_ids', type: 'many')]
#[JsonEmbed(property: 'profile', documentClass: JsonFieldProfile::class, path: 'data->profile', type: 'one')]
#[JsonEmbed(property: 'chapters', documentClass: JsonFieldProfile::class, path: 'data->chapters', type: 'many')]
class JsonFieldAssociationProbe extends Entity
{
}

function expects_string(string $x): void
{
}

$probe = new JsonFieldAssociationProbe();

// The `JsonFieldAssociationPropertyVisitor` injects `@property` for the
// association properties declared via `#[JsonReference]` / `#[JsonEmbed]`, so
// each access below resolves to the target entity / collection type instead of
// the `__get` mixed type. Passing a typed value to a string param must error.
expects_string($probe->author);
expects_string($probe->tags);
expects_string($probe->profile);
expects_string($probe->chapters);
