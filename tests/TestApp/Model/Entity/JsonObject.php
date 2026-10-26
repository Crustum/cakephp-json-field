<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;
use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Database\Schema\Attribute\JsonPathType;
use Crustum\JsonField\Model\Entity\JsonFieldTrait;

/**
 * Root document entity for the `objects` table, exercising the JSON-field
 * database layer (typed JSON path casting via `#[JsonPathType]` plus a
 * programmatically registered callback caster for `attributes->callback`).
 */
#[JsonColumn('attributes')]
#[JsonColumn('at2')]
#[JsonPathType('attributes->datetime', 'datetime')]
#[JsonPathType('attributes->callback', 'datetime')]
#[JsonPathType('attributes->year', 'datetime')]
#[JsonPathType('attributes->relativeYear', 'datetime')]
#[JsonPathType('attributes->date', 'date')]
class JsonObject extends Entity
{
    use JsonFieldTrait;

    /**
     * @var array<string, bool>
     */
    protected array $patchable = ['*' => true];
}
