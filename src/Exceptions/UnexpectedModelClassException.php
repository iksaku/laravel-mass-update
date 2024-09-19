<?php

namespace Iksaku\Laravel\MassUpdate\Exceptions;

use Throwable;
use UnexpectedValueException;

class UnexpectedModelClassException extends UnexpectedValueException
{
    public function __construct(string $expectedClass, string $impostorClass, ?Throwable $previous = null)
    {
        parent::__construct(
            "Expected an array of [{$expectedClass}] model instances. Found an instance of [{$impostorClass}].",
            6,
            $previous
        );
    }
}
