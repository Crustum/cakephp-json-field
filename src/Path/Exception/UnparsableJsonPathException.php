<?php
declare(strict_types=1);

namespace Crustum\JsonField\Path\Exception;

use Cake\Core\Exception\CakeException;

/**
 * Thrown when a string cannot be parsed as a JSON path.
 */
class UnparsableJsonPathException extends CakeException
{
    /**
     * Template used to format the exception message with the offending path.
     *
     * @var string
     */
    protected string $messageTemplate = 'Unable to parse JSON path: `%s`.';
}
