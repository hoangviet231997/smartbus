<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRfidcardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rfidcards', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('rfid', 191)->nullable();
			$table->string('barcode', 50)->nullable();
			$table->softDeletes();
			$table->timestamps();
			$table->string('usage_type', 50)->nullable();
			$table->integer('target_id')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('rfidcards');
	}

}
