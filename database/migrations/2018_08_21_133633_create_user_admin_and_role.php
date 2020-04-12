<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;


class CreateUserAdminAndRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            // create role
            $role = new Role();
            $role->name = 'admin';
            $role->display_name = 'Administrator';
            if ($role->save()) {

                $user = new User;
                $user->username = 'admin';
                $user->role_id = $role['id'];
            $user->password = Hash::make('password');
            $user->save();
        }
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
