<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemplateBusStationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_bus_stations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name');
			$table->string('address')->nullable();
			$table->integer('template_route_id')->unsigned()->index('lnk_template_routes_template_bus_stations');
			$table->float('lng', 22, 8)->nullable();
			$table->float('lat', 22, 8)->nullable();
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
		Schema::drop('template_bus_stations');
	}

}
