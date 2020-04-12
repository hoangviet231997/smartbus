<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBusStationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bus_stations', function(Blueprint $table)
		{
			$table->foreign('route_id', 'lnk_routes_bus_stations')->references('id')->on('routes')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bus_stations', function(Blueprint $table)
		{
			$table->dropForeign('lnk_routes_bus_stations');
		});
	}

}
