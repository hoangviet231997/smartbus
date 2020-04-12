<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketAllocateQueneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_allocate_quene', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->tinyInteger('status');
            $table->integer('ticket_type_id');
            $table->integer('ticket_price_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_allocate_quene');
    }
}
