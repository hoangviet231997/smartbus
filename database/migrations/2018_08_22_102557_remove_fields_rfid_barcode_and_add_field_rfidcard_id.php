<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFieldsRfidBarcodeAndAddFieldRfidcardId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('barcode');
            $table->dropColumn('rfid');

            $table->integer('rfidcard_id')->unsigned()->nullable()->index('lnk_rfidcards_memberships');

            $table->foreign('rfidcard_id', 'lnk_rfidcards_memberships')->references('id')->on('rfidcards')->onUpdate('CASCADE')->onDelete('CASCADE'); 
        });

        Schema::table('prepaid_cards', function (Blueprint $table) {
            $table->dropColumn('barcode');
            $table->dropColumn('rfid');

            $table->integer('rfidcard_id')->unsigned()->nullable()->index('lnk_rfidcards_prepaid_cards');

            $table->foreign('rfidcard_id', 'lnk_rfidcards_prepaid_cards')->references('id')->on('rfidcards')->onUpdate('CASCADE')->onDelete('CASCADE');    
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rfid');

            $table->integer('rfidcard_id')->unsigned()->nullable()->index('lnk_rfidcards_users');

            $table->foreign('rfidcard_id', 'lnk_rfidcards_users')->references('id')->on('rfidcards')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('rfid');

            $table->integer('rfidcard_id')->unsigned()->nullable()->index('lnk_rfidcards_vehicles');

            $table->foreign('rfidcard_id', 'lnk_rfidcards_vehicles')->references('id')->on('rfidcards')->onUpdate('CASCADE')->onDelete('CASCADE');                   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
