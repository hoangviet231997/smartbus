<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('devices', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('device_model_id')->unsigned()->index('lnk_device_models_devices');
			$table->float('lng', 22, 8)->nullable();
			$table->float('lat', 22, 8)->nullable();
			$table->string('identity');
			$table->integer('version')->default(1);
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
		Schema::drop('devices');
	}

}
