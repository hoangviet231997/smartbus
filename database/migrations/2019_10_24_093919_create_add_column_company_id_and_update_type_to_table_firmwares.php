<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddColumnCompanyIdAndUpdateTypeToTableFirmwares extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('firmwares', function (Blueprint $table) {
            $table->integer('company_id')->nullable();
            $table->string('update_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('firmwares', function (Blueprint $table) {
            
        });
    }
}
