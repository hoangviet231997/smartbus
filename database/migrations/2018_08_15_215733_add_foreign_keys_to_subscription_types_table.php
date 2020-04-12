<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSubscriptionTypesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subscription_types', function(Blueprint $table)
		{
			$table->foreign('company_id', 'lnk_companies_subscription_types')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('subscription_types', function(Blueprint $table)
		{
			$table->dropForeign('lnk_companies_subscription_types');
		});
	}

}
