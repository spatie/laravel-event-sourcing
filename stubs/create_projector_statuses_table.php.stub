<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectorStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('projector_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('projector_name');
            $table->string('stream')->nullable();
            $table->integer('last_processed_event_id')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projector_statuses');
    }
}
