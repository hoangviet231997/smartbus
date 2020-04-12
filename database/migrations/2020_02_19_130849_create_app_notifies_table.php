<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppNotifiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_notifies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('company_id');
            $table->string('url_img')->nullable();
            $table->string('description',256);
            $table->text('content')->nullable();
            $table->integer('weigth')->nullable();
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
        Schema::dropIfExists('app_notifies');
    }
}
