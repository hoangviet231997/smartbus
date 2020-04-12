<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToShiftsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('shifts', function(Blueprint $table)
		{
			$table->foreign('device_id', 'lnk_devices_shifts')->references('id')->on('devices')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'lnk_users_shifts')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('vehicle_id', 'lnk_vehicles_shifts')->references('id')->on('vehicles')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('shifts', function(Blueprint $table)
		{
			$table->dropForeign('lnk_devices_shifts');
			$table->dropForeign('lnk_users_shifts');
			$table->dropForeign('lnk_vehicles_shifts');
		});
	}

}
