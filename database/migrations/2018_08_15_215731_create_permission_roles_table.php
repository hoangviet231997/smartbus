<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_roles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('permission_id')->unsigned();
			$table->integer('role_id')->unsigned()->index('lnk_roles_permission_roles');
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
		Schema::drop('permission_roles');
	}

}
