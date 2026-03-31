<?php

namespace Iksaku\Laravel\MassUpdate\Exceptions;

use Throwable;
use UnexpectedValueException;

class OrphanValueException extends UnexpectedValueException
{
    public function __construct(mixed $orphanValue, ?Throwable $previous = null)
    {
        parent::__construct(
            "Expected column name on which value should be updated, but none was given.\n"
            ."Affected value: $orphanValue",
            4,
            $previous
        );
    }
}
