<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMembershipsSubscriptionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('memberships_subscription', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('membership_id')->unsigned()->index('lnk_memberships_memberships_subscription');
			$table->integer('subscription_id')->unsigned()->index('lnk_subscription_types_memberships_subscription');
			$table->dateTime('expiration_date')->nullable();
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
		Schema::drop('memberships_subscription');
	}

}
