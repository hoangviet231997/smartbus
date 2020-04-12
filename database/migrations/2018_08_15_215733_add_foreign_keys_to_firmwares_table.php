<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToFirmwaresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('firmwares', function(Blueprint $table)
		{
			$table->foreign('device_model_id', 'lnk_device_models_firmwares')->references('id')->on('device_models')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('firmwares', function(Blueprint $table)
		{
			$table->dropForeign('lnk_device_models_firmwares');
		});
	}

}
