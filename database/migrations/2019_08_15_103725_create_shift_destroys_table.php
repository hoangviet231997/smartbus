<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftDestroysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_destroys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('shift_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('driver_id')->unsigned()->nullable();
            $table->integer('subdriver_id')->unsigned()->nullable();
            $table->string('description')->nullable();
            $table->string('work_time')->nullable();
            $table->tinyInteger('accept')->default(0);
            $table->string('license_plates')->nullable();
            $table->integer('route_id')->unsigned();
            $table->double('total_pos')->default(0);
            $table->double('total_charge')->default(0);
            $table->double('total_deposit')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_destroys');
    }
}
