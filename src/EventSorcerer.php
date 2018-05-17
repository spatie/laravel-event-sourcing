<?php

namespace Spatie\EventSourcerer;

class EventSorcerer
{
    /** @var \Illuminate\Support\Collection */
    public $mutators;

    /** @var \Illuminate\Support\Collection */
    public $reactors;

    public function __construct()
    {
        $this->mutators = collect();

        $this->reactors = collect();
    }

    public function addMutator(string $mutator): self
    {
        $this->mutators->push($mutator);

        return $this;
    }

    public function registerMutators(array $mutators): self
    {
        collect($mutators)->each(function ($mutator) {
            $this->addMutator($mutator);
        });

        return $this;
    }

    public function addReactor(string $reactor): self
    {
        $this->reactors->push($reactor);

        return $this;
    }

    public function registerReactors(array $reactors): self
    {
        collect($reactors)->each(function ($reactor) {
            $this->addReactor($reactor);
        });

        return $this;
    }
}
