<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Database\Schema\Attribute\JsonEmbed;
use Crustum\JsonField\Database\Schema\Attribute\JsonReference;
use Crustum\JsonField\Model\Entity\JsonFieldTrait;
use JfApp\Model\Table\TagsTable;
use JfApp\Model\Table\UsersTable;

/**
 * Root document entity exercising the JSON-field SAVE/READ path and the
 * JSON-FK reference resolution (single `author` and list `tags`).
 */
#[JsonColumn('data')]
#[JsonColumn('meta')]
#[JsonEmbed(property: 'profile', documentClass: JsonFieldProfile::class, path: 'data->profile')]
#[JsonEmbed(property: 'chapters', documentClass: JsonFieldProfile::class, path: 'data->chapters', type: 'many', idField: 'uid')]
#[JsonEmbed(property: 'notes', documentClass: JsonFieldProfile::class, path: 'data->notes', type: 'many', idField: 'uid', saveStrategy: 'append')]
#[JsonReference(property: 'author', target: UsersTable::class, path: 'data->author_id', type: 'one')]
#[JsonReference(property: 'tags', target: TagsTable::class, path: 'data->tag_ids', type: 'many')]
class JsonFieldArticle extends Entity
{
    use JsonFieldTrait;
}
