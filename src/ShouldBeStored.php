<?php

namespace Spatie\EventSaucer;

interface ShouldBeStored
{
    public function getEventLogProperties(): array;
}