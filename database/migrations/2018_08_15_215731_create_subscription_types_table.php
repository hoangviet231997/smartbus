<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubscriptionTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subscription_types', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('display_name');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('duration')->nullable();
			$table->float('price', 22, 0);
			$table->integer('company_id')->unsigned()->index('lnk_companies_subscription_types');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subscription_types');
	}

}
