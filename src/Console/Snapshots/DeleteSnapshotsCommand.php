<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;

class DeleteSnapshotsCommand extends Command
{
    protected $signature = 'event-projector:delete-snapshots';

    protected $description = 'Delete all snapshots';

    public function handle()
    {

    }
}

