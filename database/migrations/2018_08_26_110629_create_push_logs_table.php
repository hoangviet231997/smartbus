<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable()->index('lnk_companies_push_logs');
            $table->string('active', 50)->nullable();            
            $table->integer('subject_id')->unsigned()->nullable();
            $table->string('subject_type', 50)->nullable();    
            $table->dateTime('created_at')->nullable();
            $table->foreign('company_id', 'lnk_companies_push_logs')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push_logs');
    }
}
