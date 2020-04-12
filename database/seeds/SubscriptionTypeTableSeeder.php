<?php

use Illuminate\Database\Seeder;

class SubscriptionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('subscription_types')->insert( array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Thẻ trả trước',
                'display_name' => 'Thẻ trả trước',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 5,
                'company_id' => 6,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Thẻ tháng',
                'display_name' => 'Thẻ tháng',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 100,
                'company_id' => 6,
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Thẻ HSSV',
                'display_name' => 'Thẻ HSSV',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 2,
                'company_id' => 6,
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Thẻ người già/khuyết tật',
                'display_name' => 'Thẻ người già/khuyết tật',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 100,
                'company_id' => 6,
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Thẻ trả trước',
                'display_name' => 'Thẻ trả trước',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 5,
                'company_id' => 8,
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Thẻ tháng',
                'display_name' => 'Thẻ tháng',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 100,
                'company_id' => 8,
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Thẻ HSSV',
                'display_name' => 'Thẻ HSSV',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 2,
                'company_id' => 8,
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'Thẻ người già/khuyết tật',
                'display_name' => 'Thẻ người già/khuyết tật',
                'created_at' => '2019-01-01 00:00:00',
                'updated_at' => '2019-01-01 00:00:00',
                'deleted_at' => NULL,
                'duration' => 361,
                'price' => 100,
                'company_id' => 8,
            ),
        ));
    }
}
