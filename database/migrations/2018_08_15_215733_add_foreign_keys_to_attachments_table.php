<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAttachmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('attachments', function(Blueprint $table)
		{
			$table->foreign('device_id', 'lnk_devices_attachments')->references('id')->on('devices')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('vehicle_id', 'lnk_vehicles_attachments')->references('id')->on('vehicles')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('attachments', function(Blueprint $table)
		{
			$table->dropForeign('lnk_devices_attachments');
			$table->dropForeign('lnk_vehicles_attachments');
		});
	}

}
