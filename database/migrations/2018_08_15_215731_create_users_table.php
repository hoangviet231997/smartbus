<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('company_id')->unsigned()->nullable()->index('lnk_companies_users');
			$table->integer('role_id')->unsigned()->nullable()->index('lnk_roles_users');
			$table->string('rfid')->nullable();
			$table->string('username', 150);
			$table->string('password');
			$table->string('email')->nullable();
			$table->string('fullname', 100)->nullable();
			$table->dateTime('birthday')->nullable();
			$table->string('address')->nullable();
			$table->string('sidn', 30)->nullable();
			$table->boolean('gender')->nullable();
			$table->string('phone', 20)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->string('pin_code', 6)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
