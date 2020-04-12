<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGpsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gps', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('device_id')->unsigned()->nullable()->index('lnk_devices_gps');
			$table->float('lat', 22, 8)->nullable();
			$table->float('lng', 22, 8)->nullable();
			$table->dateTime('date')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('gps');
	}

}
