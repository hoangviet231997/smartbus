<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMembershipsSubscriptionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('memberships_subscription', function(Blueprint $table)
		{
			$table->foreign('membership_id', 'lnk_memberships_memberships_subscription')->references('id')->on('memberships')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('subscription_id', 'lnk_subscription_types_memberships_subscription')->references('id')->on('subscription_types')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('memberships_subscription', function(Blueprint $table)
		{
			$table->dropForeign('lnk_memberships_memberships_subscription');
			$table->dropForeign('lnk_subscription_types_memberships_subscription');
		});
	}

}
