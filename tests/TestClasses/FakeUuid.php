<?php

namespace Spatie\EventProjector\Tests\TestClasses;

final class FakeUuid
{
    private static $count = 1;

    public static function generate()
    {
        return 'uuid-'.self::$count++;
    }

    public static function reset()
    {
        self::$count = 1;
    }
}
