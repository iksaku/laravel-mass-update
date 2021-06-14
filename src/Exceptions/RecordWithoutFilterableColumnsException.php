<?php

namespace iksaku\Laravel\MassUpdate\Exceptions;

use Throwable;
use UnexpectedValueException;

class RecordWithoutFilterableColumnsException extends UnexpectedValueException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            "None of the specified 'uniqueBy' columns where found on current record.",
            2,
            $previous
        );
    }
}
