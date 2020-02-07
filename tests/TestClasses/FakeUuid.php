<?php

namespace Spatie\EventSourcing\Tests\TestClasses;

class FakeUuid
{
    private static int $count = 1;

    public static function generate()
    {
        return '594713b3-0000-4000-b300-'.sprintf('%012d', self::$count++);
    }

    public static function reset()
    {
        self::$count = 1;
    }
}
