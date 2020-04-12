<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShiftsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shifts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('device_id')->unsigned()->index('lnk_devices_shifts');
			$table->integer('user_id')->unsigned()->nullable()->index('lnk_users_shifts');
			$table->dateTime('started')->nullable();
			$table->dateTime('ended')->nullable();
			$table->timestamps();
			$table->integer('subdriver_id')->unsigned()->nullable();
			$table->integer('vehicle_id')->unsigned()->nullable()->index('lnk_vehicles_shifts');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shifts');
	}

}
