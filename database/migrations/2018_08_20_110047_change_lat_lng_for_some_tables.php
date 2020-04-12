<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLatLngForSomeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bus_stations', function (Blueprint $table) {
            $table->dropColumn('lng');
            $table->dropColumn('lat');
        });

        Schema::table('bus_stations', function (Blueprint $table) {
            $table->point('position');
        });        

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('lng');
            $table->dropColumn('lat');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->point('position');
        });        

        Schema::table('gps', function (Blueprint $table) {
            $table->dropColumn('lng');
            $table->dropColumn('lat');
        });

        Schema::table('gps', function (Blueprint $table) {
            $table->point('position');
        });        

        Schema::table('template_bus_stations', function (Blueprint $table) {
            $table->dropColumn('lng');
            $table->dropColumn('lat');
        });

        Schema::table('template_bus_stations', function (Blueprint $table) {
            $table->point('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
