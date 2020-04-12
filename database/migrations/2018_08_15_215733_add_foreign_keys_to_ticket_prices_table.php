<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTicketPricesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ticket_prices', function(Blueprint $table)
		{
			$table->foreign('ticket_type_id', 'lnk_ticket_types_ticket_prices')->references('id')->on('ticket_types')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ticket_prices', function(Blueprint $table)
		{
			$table->dropForeign('lnk_ticket_types_ticket_prices');
		});
	}

}
