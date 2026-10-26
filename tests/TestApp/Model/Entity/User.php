<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * TestApp target entity for JSON-FK BelongsTo reference resolution.
 */
class User extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $patchable = ['*' => true];
}
