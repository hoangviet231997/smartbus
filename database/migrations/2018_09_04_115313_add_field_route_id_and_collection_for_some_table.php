<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldRouteIdAndCollectionForSomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('route_id')->unsigned()->nullable()->index('lnk_routes_vehicles');
            $table->foreign('route_id', 'lnk_routes_vehicles')->references('id')->on('routes')->onUpdate('CASCADE')->onDelete('CASCADE');            
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->integer('route_id')->unsigned()->nullable()->index('lnk_routes_shifts');
            $table->boolean('collected');
            $table->foreign('route_id', 'lnk_routes_shifts')->references('id')->on('routes')->onUpdate('CASCADE')->onDelete('CASCADE');
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
