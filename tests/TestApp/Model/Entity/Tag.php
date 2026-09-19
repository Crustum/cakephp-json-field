<?php
declare(strict_types=1);

namespace JfApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * TestApp target entity for JSON-FK BelongsToMany reference resolution.
 */
class Tag extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = ['*' => true];
}
