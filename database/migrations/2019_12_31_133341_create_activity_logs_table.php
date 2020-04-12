<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');//company
            $table->integer('user_id');//use
            $table->text('user_down')->nullable();//administrator
            $table->string('action');//edit, add
            $table->string('subject_type');//vehicle,user,ticket,route,mbs,bus_station,group_bus_station,...
            $table->text('subject_data');//content data type
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
        Schema::dropIfExists('activity_logs');
    }
}
