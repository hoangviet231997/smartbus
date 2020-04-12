<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicePollCommandsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('device_poll_commands', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('device_id')->unsigned()->index('lnk_devices_device_poll_commands');
			$table->string('command');
			$table->text('params', 65535);
			$table->boolean('is_running');
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
		Schema::drop('device_poll_commands');
	}

}
