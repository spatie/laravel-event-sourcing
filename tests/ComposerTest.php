<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Str;
use Spatie\EventProjector\Composer;

final class ComposerTest extends TestCase
{
    /** @test */
    public function it_can_get_all_loaded_files()
    {
        $pathToComposerJson = __DIR__.'/../composer.json';

        $files = Composer::getAutoloadedFiles($pathToComposerJson);

        $files = array_map(function (string $path) {
            return Str::after($path, $this->pathToTests());
        }, $files);

        $this->assertEquals([
            '/TestClasses/AutoDiscoverEventHandlers/functions.php',
        ], $files);
    }
}
