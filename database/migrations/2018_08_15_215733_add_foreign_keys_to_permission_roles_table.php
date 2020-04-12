<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPermissionRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('permission_roles', function(Blueprint $table)
		{
			$table->foreign('id', 'lnk_permissions_permission_roles')->references('id')->on('permissions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('role_id', 'lnk_roles_permission_roles')->references('id')->on('roles')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('permission_roles', function(Blueprint $table)
		{
			$table->dropForeign('lnk_permissions_permission_roles');
			$table->dropForeign('lnk_roles_permission_roles');
		});
	}

}
