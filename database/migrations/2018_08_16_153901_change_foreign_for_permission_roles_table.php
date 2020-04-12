<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeForeignForPermissionRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permission_roles', function ($table) {
            $table->dropForeign('lnk_permissions_permission_roles');
        });

        Schema::table('permission_roles', function(Blueprint $table) {
            $table->foreign('permission_id', 'lnk_permissions_permission_roles')->references('id')->on('permissions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission_roles', function(Blueprint $table)
        {
            $table->dropForeign('lnk_permissions_permission_roles');
        });
    }
}
