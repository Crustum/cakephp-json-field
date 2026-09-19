<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Database\Schema\Attribute\JsonEmbed;
use Crustum\JsonField\Model\Entity\JsonFieldTrait;

/**
 * Root document fixture carrying one-to-one and one-to-many embeds for association tests.
 */
#[JsonColumn('data')]
#[JsonEmbed(property: 'profile', documentClass: AssociationProfile::class, path: 'data->profile')]
#[JsonEmbed(property: 'chapters', documentClass: AssociationChapter::class, path: 'data->chapters', type: 'many')]
class AssociationArticle extends Entity
{
    use JsonFieldTrait;
}
