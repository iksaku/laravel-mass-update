<?php

namespace Iksaku\Laravel\MassUpdate\Exceptions;

use Throwable;
use UnexpectedValueException;

class EmptyUniqueByException extends UnexpectedValueException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            'Second parameter expects an array of column names, used to properly filter your mass updatable values, but no names were given.',
            1,
            $previous
        );
    }
}
