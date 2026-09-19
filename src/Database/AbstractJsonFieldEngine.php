<?php
declare(strict_types=1);

namespace Crustum\JsonField\Database;

/**
 * Base class for JSON field engine adapters.
 *
 * Concrete engines implement {@see set()} and {@see cast()} for the
 * SAVE path. READ-path queries are handled by the dialect trait
 * ({@see \Crustum\JsonField\Database\Dialect\JsonFieldDialectTrait}).
 */
abstract class AbstractJsonFieldEngine implements JsonFieldEngineInterface
{
}
