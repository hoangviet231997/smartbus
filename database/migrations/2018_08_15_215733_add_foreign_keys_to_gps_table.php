<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToGpsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('gps', function(Blueprint $table)
		{
			$table->foreign('device_id', 'lnk_devices_gps')->references('id')->on('devices')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('gps', function(Blueprint $table)
		{
			$table->dropForeign('lnk_devices_gps');
		});
	}

}
