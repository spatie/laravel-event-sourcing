<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('aggregate_uuid');
            $table->unsignedInteger('aggregate_version');
            $table->json('state');
            $table->timestamp('created_at');
            $table->index('aggregate_uuid');
        });
    }
}
