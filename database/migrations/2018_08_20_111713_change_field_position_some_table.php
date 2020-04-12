<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldPositionSomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bus_stations', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('bus_stations', function (Blueprint $table) {
            $table->point('position')->nullable();
        }); 

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->point('position')->nullable();
        });        

        Schema::table('gps', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('gps', function (Blueprint $table) {
            $table->point('position')->nullable();
        });

        Schema::table('template_bus_stations', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('template_bus_stations', function (Blueprint $table) {
            $table->point('position')->nullable();
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
