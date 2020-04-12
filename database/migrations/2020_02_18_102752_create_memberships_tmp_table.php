<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipsTmpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memberships_tmp', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->index('lnk_companies_memberships');
      			$table->string('fullname', 100);
            $table->dateTime('birthday')->nullable();
            $table->tinyInteger('gender')->nullable();
      			$table->string('cmnd', 20)->nullable();
            $table->string('phone', 15)->nullable();
      			$table->string('email', 100)->nullable();
            $table->string('address', 255)->nullable();
      			$table->string('avatar')->nullable();
      			$table->tinyInteger('accept')->default(0);
      			$table->integer('membershiptype_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memberships_tmp');
    }
}
