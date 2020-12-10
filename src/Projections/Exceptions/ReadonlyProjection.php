<?php

namespace Spatie\EventSourcing\Projections\Exceptions;

use Exception;

class ReadonlyProjection extends Exception
{
    public static function new(string $modelClass): self
    {
        return new self("The `{$modelClass}` projection is not writeable at this point, please call `\$model->writeable()` first.");
    }
}
