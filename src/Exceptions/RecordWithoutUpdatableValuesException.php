<?php

namespace iksaku\Laravel\MassUpdate\Exceptions;

use Throwable;
use UnexpectedValueException;

class RecordWithoutUpdatableValuesException extends UnexpectedValueException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            'No updatable columns where found for the current record.',
            3,
            $previous
        );
    }
}
