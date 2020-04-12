<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftSupervisorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_supervisor', function (Blueprint $table) {
            $table->increments('id');
      			$table->integer('shift_id')->unsigned();
      			$table->integer('user_id')->unsigned();
      			$table->dateTime('started')->nullable();
      			$table->dateTime('ended')->nullable();
            $table->integer('station_up_id')->nullable();
            $table->integer('station_down_id')->nullable();
            $table->string('shift_supervisor_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_supervisor');
    }
}
