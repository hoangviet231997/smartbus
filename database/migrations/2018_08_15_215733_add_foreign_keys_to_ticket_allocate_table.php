<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTicketAllocateTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ticket_allocate', function(Blueprint $table)
		{
			$table->foreign('company_id', 'lnk_companies_ticket_allocate')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('device_id', 'lnk_devices_ticket_allocate')->references('id')->on('devices')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('ticket_price_id', 'lnk_ticket_prices_ticket_allocate')->references('id')->on('ticket_prices')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('ticket_type_id', 'lnk_ticket_types_ticket_allocate')->references('id')->on('ticket_types')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ticket_allocate', function(Blueprint $table)
		{
			$table->dropForeign('lnk_companies_ticket_allocate');
			$table->dropForeign('lnk_devices_ticket_allocate');
			$table->dropForeign('lnk_ticket_prices_ticket_allocate');
			$table->dropForeign('lnk_ticket_types_ticket_allocate');
		});
	}

}
