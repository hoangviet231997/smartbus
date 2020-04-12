<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToIssuedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('issued', function(Blueprint $table)
		{
			$table->foreign('company_id', 'lnk_companies_issued')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('device_id', 'lnk_devices_issued')->references('id')->on('devices')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('issued', function(Blueprint $table)
		{
			$table->dropForeign('lnk_companies_issued');
			$table->dropForeign('lnk_devices_issued');
		});
	}

}
