<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {   
        \DB::table('roles')->insert(array (
            // 0 => 
            // array (
            //     'id' => 2,
            //     'name' => 'manager',
            //     'display_name' => 'Manager',
            //     'created_at' => '2018-09-04 13:24:22',
            //     'updated_at' => '2018-09-04 13:24:22',
            //     'deleted_at' => NULL,
            // ),
            // 1 => 
            // array (
            //     'id' => 3,
            //     'name' => 'staff',
            //     'display_name' => 'Staff',
            //     'created_at' => '2018-09-05 20:34:36',
            //     'updated_at' => '2018-09-05 20:34:36',
            //     'deleted_at' => NULL,
            // ),
            // 2 => 
            // array (
            //     'id' => 4,
            //     'name' => 'driver',
            //     'display_name' => 'Driver',
            //     'created_at' => '2018-09-05 20:35:03',
            //     'updated_at' => '2018-09-05 20:35:03',
            //     'deleted_at' => NULL,
            // ),
            // 3 => 
            // array (
            //     'id' => 5,
            //     'name' => 'subdriver',
            //     'display_name' => 'Subdriver',
            //     'created_at' => '2018-09-05 20:35:17',
            //     'updated_at' => '2018-09-05 20:35:17',
            //     'deleted_at' => NULL,
            // ),
            // 4 => 
            // array (
            //     'id' => 6,
            //     'name' => 'teller',
            //     'display_name' => 'Teller',
            //     'created_at' => '2018-09-05 20:35:32',
            //     'updated_at' => '2018-09-05 20:35:32',
            //     'deleted_at' => NULL,
            // ),
            // 0 => 
            // array (
            //     'id' => 8,
            //     'name' => 'accountant',
            //     'display_name' => 'Accountant',
            //     'created_at' => '2019-04-22 09:00:00',
            //     'updated_at' => '2019-04-22 09:00:00',
            //     'deleted_at' => NULL,
            // ),
            0 => 
            array (
                'id' => 9,
                'name' => 'executive',
                'display_name' => 'Executive',
                'created_at' => '2019-12-05 09:00:00',
                'updated_at' => '2019-12-05 09:00:00',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}