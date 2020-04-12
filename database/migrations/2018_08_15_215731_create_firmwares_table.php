<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFirmwaresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('firmwares', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('device_model_id')->unsigned()->index('lnk_device_models_firmwares');
			$table->string('server_ip', 50);
			$table->string('username', 100);
			$table->string('password');
			$table->string('path');
			$table->integer('version');
			$table->string('filename');
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
		Schema::drop('firmwares');
	}

}
