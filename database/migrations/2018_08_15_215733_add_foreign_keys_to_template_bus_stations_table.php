<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTemplateBusStationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('template_bus_stations', function(Blueprint $table)
		{
			$table->foreign('template_route_id', 'lnk_template_routes_template_bus_stations')->references('id')->on('template_routes')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('template_bus_stations', function(Blueprint $table)
		{
			$table->dropForeign('lnk_template_routes_template_bus_stations');
		});
	}

}
