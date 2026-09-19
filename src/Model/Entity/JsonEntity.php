<?php
declare(strict_types=1);

namespace Crustum\JsonField\Model\Entity;

use Cake\ORM\Entity;

/**
 * Generic, metadata-free nested entity used to represent a JSON embed.
 *
 * Mirrors the Mongo ODM `Document` that backs an embedded sub-document: it
 * carries no schema of its own, exposes the standard CakePHP Entity API on its
 * own structure, and holds an `embeddedParent` back-pointer (provided by
 * `JsonFieldTrait`) so that saves can be routed down to the owning JSON column.
 * Path-string access (`get('a->b')`) and the embedded delete semantics are also
 * supplied by `JsonFieldTrait`, allowing embeds to nest arbitrarily deep.
 *
 * It remains a concrete base class (rather than a trait or interface) because the
 * association layer identifies embedded nodes via `instanceof JsonEntity`.
 */
class JsonEntity extends Entity
{
    use JsonFieldTrait;
}
