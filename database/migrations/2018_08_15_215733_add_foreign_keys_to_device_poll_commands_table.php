<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDevicePollCommandsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('device_poll_commands', function(Blueprint $table)
		{
			$table->foreign('device_id', 'lnk_devices_device_poll_commands')->references('id')->on('devices')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('device_poll_commands', function(Blueprint $table)
		{
			$table->dropForeign('lnk_devices_device_poll_commands');
		});
	}

}
