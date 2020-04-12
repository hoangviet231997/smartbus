<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddColumnsToTableGroupBusStations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_bus_stations', function (Blueprint $table) {
            $table->tinyInteger('direction')->default(0);
            $table->integer('parent_gr_bus_station_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_bus_stations', function (Blueprint $table) {
            //
        });
    }
}
