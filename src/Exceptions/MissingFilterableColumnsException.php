<?php

namespace Iksaku\Laravel\MassUpdate\Exceptions;

use Throwable;
use UnexpectedValueException;

class MissingFilterableColumnsException extends UnexpectedValueException
{
    public function __construct(array $missingColumns, Throwable $previous = null)
    {
        parent::__construct(
            "One of your records is missing some of the specified 'uniqueBy' columns. Make sure to include them all:\n"
            . '[' . implode(', ', $missingColumns) . ']',
            7,
            $previous
        );
    }
}
