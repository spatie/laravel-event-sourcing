<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

final class CreateStoredEventsTable extends Migration
{
    public function up()
    {
        Schema::create('stored_events', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('aggregate_uuid')->nullable();
            $table->string('event_class');
            $table->json('event_properties');
            $table->json('meta_data');
            $table->timestamp('created_at');
            $table->index('event_class');
            $table->index('aggregate_uuid');
        });
    }
}
