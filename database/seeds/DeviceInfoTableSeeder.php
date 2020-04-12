<?php

use Illuminate\Database\Seeder;

class DeviceInfoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('device_info')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'NFC on',
                'description' => 'NFC bật',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'NFC off',
                'description' => 'NFC tắt hoặc đầu đọc NFC bị lỗi',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Bluetooth on',
                'description' => 'Bluetooth bật',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Bluetooth off',
                'description' => 'Bluetooth tắt',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Bluetooth disconnected',
                'description' => 'Bluetooth không kết nối',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => '3G weak',
                'description' => 'Mạng dữ liệu di động yếu',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Pin weak', 
                'description' => 'Pin yếu',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'Paper cease', 
                'description' => 'Hết giấy',
                'type' => 0,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'Print connected', 
                'description' => 'Kết nối máy in',
                'type' => 1,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'Print disconnected', 
                'description' => 'Mất kết nối máy in',
                'type' => 1,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'Print paper cease', 
                'description' => 'Máy in hết giấy',
                'type' => 1,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'QRCode reader connected', 
                'description' => 'Kết nối đầu đọc QRCode',
                'type' => 1,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'QRCode reader disconnected', 
                'description' => 'Mất kết nối đầu đọc QRCode',
                'type' => 1,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'RFID reader connected', 
                'description' => 'Kết nối đầu đọc RFID',
                'type' => 1,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'RFID reader disconnected', 
                'description' => 'Mất kết nối đầu đọc RFID',
                'type' => 1,
                'created_at' => '2019-10-24 09:48:48',
                'updated_at' => '2019-10-24 09:48:48',
            )
        ));
    }
}
