<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots;

class Math
{
    public function add($base, $int)
    {
        return $base + $int;
    }

    public function multiply($base, $int)
    {
        return $base * $int;
    }
}
