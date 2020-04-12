<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMembershipsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('memberships', function(Blueprint $table)
		{
			$table->foreign('company_id', 'lnk_companies_memberships')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('memberships', function(Blueprint $table)
		{
			$table->dropForeign('lnk_companies_memberships');
		});
	}

}
