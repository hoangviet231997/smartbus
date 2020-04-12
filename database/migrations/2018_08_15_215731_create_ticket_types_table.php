<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTicketTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticket_types', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id')->unsigned()->index('lnk_companies_ticket_types');
			$table->string('name');
			$table->string('description')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->string('sign', 100)->nullable();
			$table->string('sign_form', 100)->nullable();
			$table->string('order_code', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ticket_types');
	}

}
