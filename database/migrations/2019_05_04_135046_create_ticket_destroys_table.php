<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketDestroysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_destroys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->index('lnk_companies_tickets');
            $table->integer('transaction_id')->unsigned();
			$table->integer('shift_id')->unsigned()->nullable()->index('lnk_shifts_tickets');
			$table->integer('ticket_price_id')->unsigned()->nullable()->index('lnk_ticket_prices_tickets');
			$table->string('ticket_number', 50)->nullable();
			$table->string('type', 50)->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->float('amount', 22, 0)->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->string('printed_at')->nullable();
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
        Schema::dropIfExists('ticket_destroys');
    }
}
