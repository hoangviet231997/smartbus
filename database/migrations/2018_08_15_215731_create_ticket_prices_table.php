<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTicketPricesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ticket_prices', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('ticket_type_id')->unsigned()->index('lnk_ticket_types_ticket_prices');
			$table->float('price', 12, 0);
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ticket_prices');
	}

}
