<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdToAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->index('lnk_companies_apps');
            $table->foreign('company_id', 'lnk_companies_apps')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apps', function (Blueprint $table) {
            //
        });
    }
}
