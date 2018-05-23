<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoredEventsTable extends Migration
{
    public function up()
    {
        Schema::create('stored_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_class');
            $table->text('event_properties');
            $table->schemalessAttributes('meta_data');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stored_events');
    }
}
