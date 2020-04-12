<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->foreign('company_id', 'lnk_companies_users')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('NO ACTION');
			$table->foreign('role_id', 'lnk_roles_users')->references('id')->on('roles')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('lnk_companies_users');
			$table->dropForeign('lnk_roles_users');
		});
	}

}
