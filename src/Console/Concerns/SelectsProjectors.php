<?php

namespace Spatie\EventProjector\Console\Concerns;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Facades\Projectionist;

trait SelectsProjectors
{
    public function selectsProjectors(array $projectorClassNames, string $allProjectorsWarning): ?Collection
    {
        if (count($projectorClassNames ?? []) === 0) {
            if (! $confirmed = $this->confirm($allProjectorsWarning)) {
                return null;
            }

            return Projectionist::getProjectors();
        }

        return collect($projectorClassNames)
            ->map(function (string $projectorName) {
                if (! $projector = $this->Projectionist->getProjector($projectorName)) {
                    throw new Exception("Projector {$projectorName} not found. Did you register it?");
                }

                return $projector;
            });
    }
}
