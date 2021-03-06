<?php
declare(strict_types = 1);

namespace DASPRiD\Formidable\Exception;

use OutOfBoundsException;

class NonExistentKey extends OutOfBoundsException implements ExceptionInterface
{
    public static function fromNonExistentKey(string $key)
    {
        return new self(sprintf('Non-existent key "%s" provided', $key));
    }
}
