<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePrepaidCardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('prepaid_cards', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id')->unsigned()->index('lnk_companies_prepaid_cards');
			$table->string('rfid', 191)->nullable();
			$table->float('balance', 12, 0)->default(0);
			$table->timestamps();
			$table->dateTime('expiration_date')->nullable();
			$table->softDeletes();
			$table->string('barcode', 50)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('prepaid_cards');
	}

}
