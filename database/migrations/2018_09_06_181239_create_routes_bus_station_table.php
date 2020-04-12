<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoutesBusStationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('routes_bus_station', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('route_id')->unsigned()->index('lnk_routes_routes_bus_station');
            $table->integer('bus_station_id')->index('lnk_bus_stations_routes_bus_station');
            $table->dateTime('create_at')->nullable();

            $table->foreign('route_id', 'lnk_routes_routes_bus_station')->references('id')->on('routes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('bus_station_id', 'lnk_bus_stations_routes_bus_station')->references('id')->on('bus_stations')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routes_bus_station');
    }
}
