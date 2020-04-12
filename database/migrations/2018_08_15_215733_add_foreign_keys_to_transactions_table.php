<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('transactions', function(Blueprint $table)
		{
			$table->foreign('company_id', 'lnk_companies_transactions')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('device_id', 'lnk_devices_transactions')->references('id')->on('devices')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('shift_id', 'lnk_shifts_transactions')->references('id')->on('shifts')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('ticket_price_id', 'lnk_ticket_prices_transactions')->references('id')->on('ticket_prices')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transactions', function(Blueprint $table)
		{
			$table->dropForeign('lnk_companies_transactions');
			$table->dropForeign('lnk_devices_transactions');
			$table->dropForeign('lnk_shifts_transactions');
			$table->dropForeign('lnk_ticket_prices_transactions');
		});
	}

}
