<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id')->unsigned()->index('lnk_companies_tickets');
			$table->integer('device_id')->unsigned()->nullable()->index('lnk_devices_tickets');
			$table->integer('shift_id')->unsigned()->nullable()->index('lnk_shifts_tickets');
			$table->integer('ticket_price_id')->unsigned()->nullable()->index('lnk_ticket_prices_tickets');
			$table->string('ticket_number', 50)->nullable();
			$table->boolean('is_used')->nullable();
			$table->string('type', 50)->nullable();
			$table->timestamps();
			$table->integer('user_id')->unsigned()->nullable();
			$table->float('amount', 22, 0)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transactions');
	}

}
