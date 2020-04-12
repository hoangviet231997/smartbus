<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_account', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('name', 100);
            $table->string('partner_code');
            $table->string('url_api');
            $table->string('username_login');
            $table->string('password_login');
            $table->text('public_key');
            $table->text('private_key')->nulllable();
            $table->text('description')->nulllable();
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
        Schema::dropIfExists('partner_account');
    }
}
