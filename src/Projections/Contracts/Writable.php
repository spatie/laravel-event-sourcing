<?php

namespace Spatie\EventSourcing\Projections\Contracts;

interface Writable
{
    /**
     * Verify if the projection is writable
     *
     * @return boolean
     */
    public function isWriteable(): bool;
}
