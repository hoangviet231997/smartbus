<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTicketAllocateTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticket_allocate', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id')->unsigned()->index('lnk_companies_ticket_allocate');
			$table->integer('device_id')->unsigned()->index('lnk_devices_ticket_allocate');
			$table->integer('ticket_type_id')->unsigned()->index('lnk_ticket_types_ticket_allocate');
			$table->integer('ticket_price_id')->unsigned()->index('lnk_ticket_prices_ticket_allocate');
			$table->integer('start_number')->unsigned();
			$table->integer('end_number')->unsigned();
			$table->dateTime('created_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ticket_allocate');
	}

}
