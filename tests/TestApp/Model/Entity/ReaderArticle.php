<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Database\Schema\Attribute\JsonEmbed;
use Crustum\JsonField\Database\Schema\Attribute\JsonPathType;
use Crustum\JsonField\Model\Entity\JsonFieldTrait;

/**
 * A dummy root document entity used to verify attribute reflection in tests.
 */
#[JsonColumn('data')]
#[JsonEmbed(property: 'profile', documentClass: ReaderProfile::class, path: 'data->profile')]
#[JsonPathType('data->updated', type: 'datetime')]
class ReaderArticle extends Entity
{
    use JsonFieldTrait;
}
