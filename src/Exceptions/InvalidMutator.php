<?php

namespace Spatie\EventSorcerer\Exceptions;

use Exception;

class InvalidMutator extends Exception
{
    public static function doesNotExist(string $mutator)
    {
        return new static("The given mutator class `{$mutator}` does not exist");
    }
}