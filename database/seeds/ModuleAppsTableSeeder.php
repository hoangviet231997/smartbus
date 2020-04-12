<?php

use Illuminate\Database\Seeder;

class ModuleAppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('module_apps')->insert(
            array (
                // 0 =>
                // array (
                //     'id' => 1,
                //     'name' => 'Module_Ve_Luot',
                //     'description' => 'Sử dụng bán vé lượt',
                //     'created_at' => '2019-01-01 08:00:00',
                //     'updated_at' => '2019-01-01 09:00:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Vé lượt',
                // ),
                // 1 =>
                // array (
                //     'id' => 2,
                //     'name' => 'Module_The_Tra_Truoc',
                //     'description' => 'Sử dụng quẹt thẻ trả trước',
                //     'created_at' => '2019-01-01 11:00:00',
                //     'updated_at' => '2019-01-01 11:30:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Thẻ trả trước'
                // ),
                // 2 =>
                // array (
                //     'id' => 3,
                //     'name' => 'Module_The_Tra_KM',
                //     'description' => 'Sử dụng quẹt thẻ tính theo Km khách đi',
                //     'created_at' => '2019-01-01 10:00:00',
                //     'updated_at' => '2019-01-01 11:00:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Thẻ Km'
                // ),
                // 3 =>
                // array (
                //     'id' => 4,
                //     'name' => 'Module_The_Dong_Gia',
                //     'description' => 'Sử dụng quẹt thẻ đồng giá',
                //     'created_at' => '2019-01-01 14:00:00',
                //     'updated_at' => '2019-01-01 14:30:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Thẻ đồng giá'
                // ),
                // 4 =>
                // array (
                //     'id' => 5,
                //     'name' => 'Module_Doc_QR_Code',
                //     'description' => 'Sử dụng đọc QR code',
                //     'created_at' => '2019-01-01 15:00:00',
                //     'updated_at' => '2019-01-01 15:24:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Đọc QR code'
                // ),

                //add module car: 2019-06-19 16:22:00
                // 0 =>
                // array (
                //     'id' => 6,
                //     'name' => 'Module_Taxi',
                //     'description' => 'Sử dụng tiền mặt và quẹt thẻ cho taxi',
                //     'created_at' => '2019-06-19 16:22:00',
                //     'updated_at' => '2019-06-19 16:22:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Module taxi'
                // ),

                //add module print ticket after charge card: 2019-07-03 10:22:00
                // 0 =>
                // array (
                //     'id' => 7,
                //     'name' => 'Module_In_Ve_The',
                //     'description' => 'Sử dụng in vé sau khi quẹt thẻ',
                //     'created_at' => '2019-07-03 10:22:00',
                //     'updated_at' => '2019-07-03 10:22:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Module in vé thẻ'
                // ),

                //add module card by options month: 2019-08-06 09:10:00
                // 0 =>
                // array (
                //     'id' => 8,
                //     'name' => 'Module_TT_Km',
                //     'description' => 'Sử dụng cho đối tượng thẻ tháng theo khoảng cách quy định',
                //     'created_at' => '2019-08-06 09:10:00',
                //     'updated_at' => '2019-08-06 09:10:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Module thẻ tháng theo km'
                // ),
                // 1 =>
                // array (
                //     'id' => 9,
                //     'name' => 'Module_TT_SL_Quet',
                //     'description' => 'Sử dụng cho đối tượng thẻ tháng theo số lần quẹt đã quy đinh',
                //     'created_at' => '2019-08-06 09:10:00',
                //     'updated_at' => '2019-08-06 09:10:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Module thẻ tháng theo số lần quẹt'
                // ),

                // //add module denomination goods: 2019-09-10 11:35:00
                // 0 =>
                // array (
                //     'id' => 10,
                //     'name' => 'Module_VC_Hang_Hoa',
                //     'description' => 'Áp dụng cho công ty vận chuyển hàng hóa',
                //     'created_at' => '2019-09-10 11:35:00',
                //     'updated_at' => '2019-09-10 11:35:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Module Áp dụng vận chuyển hàng hóa'
                // )

                //add module print ticket for momo: 2019-09-13 14:35:00
                // 0 =>
                // array (
                //     'id' => 11,
                //     'name' => 'Module_In_Ve_Momo',
                //     'description' => 'Áp dụng in vé sau khi thanh toán bằng ví điện tử Momo',
                //     'created_at' => '2019-09-13 14:35:00',
                //     'updated_at' => '2019-09-13 14:35:00',
                //     'deleted_at' => NULL,
                //     'display_name' => 'Module Áp dụng in vé thanh toán bằng Momo'
                // )

                //add module car: 2019-12-13 15:01:00
                0 =>
                array (
                    'id' => 12,
                    'name' => 'Module_Xe_Khach',
                    'description' => 'Áp dụng cho dự án xe khách',
                    'created_at' => '2019-12-13 15:01:00',
                    'updated_at' => '2019-12-13 15:01:00',
                    'deleted_at' => NULL,
                    'display_name' => 'Module Áp dụng cho dự án xe khách'
                )

                //add module card prepaid: 2020-02-13 10:43:00
                0 =>
                array (
                    'id' => 13,
                    'name' => 'Module_TTT_Km',
                    'description' => 'Sử dụng cho đối tượng thẻ trả trước theo khoảng cách quy định',
                    'created_at' => '2020-02-13 10:43:00',
                    'updated_at' => '2020-02-13 10:43:00',
                    'deleted_at' => NULL,
                    'display_name' => 'Module thẻ trả trước theo km'
                ),
                1 =>
                array (
                    'id' => 14,
                    'name' => 'Module_TTT_SL_Quet',
                    'description' => 'Sử dụng cho đối tượng thẻ trả trước theo số lần quẹt đã quy đinh',
                    'created_at' => '2020-02-13 10:43:00',
                    'updated_at' => '2020-02-13 10:43:00',
                    'deleted_at' => NULL,
                    'display_name' => 'Module thẻ trả trước theo số lần quẹt'
                ),
            )
        );
    }
}
