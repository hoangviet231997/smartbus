<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMembershipsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('memberships', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id')->unsigned()->index('lnk_companies_memberships');
			$table->string('rfid', 191)->nullable();
			$table->string('fullname');
			$table->string('address')->nullable();
			$table->string('phone')->nullable();
			$table->float('balance', 12, 0)->default(0);
			$table->timestamps();
			$table->softDeletes();
			$table->string('sidn')->nullable();
			$table->string('email')->nullable();
			$table->string('barcode', 50)->nullable();
			$table->dateTime('birthday')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('memberships');
	}

}
