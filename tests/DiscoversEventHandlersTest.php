<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\DiscoverEventHandlers;
use Spatie\EventProjector\Projectionist;

final class DiscoversEventHandlersTest extends TestCase
{
    /** @test */
    public function it_can_get_all_classes_that_have_event_handlers()
    {
        /** @var \Spatie\EventProjector\Projectionist $projectionist */
        $projectionist = app(Projectionist::class);

        (new DiscoverEventHandlers())
            ->within([__DIR__ . '/TestClasses/AutoDiscoverEventHandlers'])
            ->useBasePath($this->getDiscoveryBasePath())
            ->useRootNamespace('Spatie\EventProjector\\')
            ->addToProjectionist($projectionist);

        $projectionist->pr
    }

    protected function getDiscoveryBasePath(): string
    {
        return realpath($this->testsPath() . '/../');
    }
}

