<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemplateRoutesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_routes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->time('start_time')->nullable();
			$table->time('end time')->nullable();
			$table->integer('number')->nullable();
			$table->string('name')->nullable();
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
		Schema::drop('template_routes');
	}

}
