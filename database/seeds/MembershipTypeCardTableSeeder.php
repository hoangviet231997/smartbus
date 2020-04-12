<?php

use Illuminate\Database\Seeder;

class MembershipTypeCardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('membership_types')->insert( array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Thẻ thường',
                'deduction' => 36,
                'code'=> 0,
                'created_at' => '2019-01-05 00:00:00',
                'updated_at' => '2019-01-05 00:00:00',
                'deleted_at' => NULL,
                'company_id' => 6,
            ),
        //     1 => 
        //     array (
        //         'id' => 2,
        //         'name' => 'Thẻ HSSV',
        //         'deduction' => 20,
        //         'code'=> 0,
        //         'created_at' => '2019-01-05 00:00:00',
        //         'updated_at' => '2019-01-05 00:00:00',
        //         'deleted_at' => NULL,
        //         'company_id' => 6,
        //     ),
        //     2 => 
        //     array (
        //         'id' => 3,
        //         'name' => 'Thẻ người già',
        //         'deduction' => 50,
        //         'code'=> 0,
        //         'created_at' => '2019-01-05 00:00:00',
        //         'updated_at' => '2019-01-05 00:00:00',
        //         'deleted_at' => NULL,
        //         'company_id' => 6,
        //     ),
        //     3 => 
        //     array (
        //         'id' => 4,
        //         'name' => 'Thẻ thương binh / bệnh binh',
        //         'deduction' => 100,
        //         'code'=> 0,
        //         'created_at' => '2019-01-05 00:00:00',
        //         'updated_at' => '2019-01-05 00:00:00',
        //         'deleted_at' => NULL,
        //         'company_id' => 6,
        //     ),
        ));
    }
}
