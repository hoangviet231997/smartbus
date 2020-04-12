<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIssuedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('issued', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('device_id')->unsigned()->index('lnk_devices_issued');
			$table->integer('company_id')->unsigned()->index('lnk_companies_issued');
			$table->dateTime('issued_date');
			$table->dateTime('return_date');
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
		Schema::drop('issued');
	}

}
