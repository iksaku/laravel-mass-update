<?php

namespace Iksaku\Laravel\MassUpdate\Exceptions;

use Throwable;
use UnexpectedValueException;

class MassUpdatingAndFilteringModelUsingTheSameColumn extends UnexpectedValueException
{
    public function __construct(array $columns, Throwable $previous = null)
    {
        parent::__construct(
            "It appears that an Eloquent Model's column was updated "
            . 'and is being used at the same time for mass filtering. '
            . 'This may cause filtering issues and may not even update the value properly. '
            . "Affected columns:\n - " . implode("\n - ", $columns),
            5,
            $previous
        );
    }
}
