<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // $this->call(RolesTableSeeder::class);
        // $this->call(SubscriptionTypeTableSeeder::class);
        // $this->call(MembershipTypeCardTableSeeder::class);
        $this->call(ModuleAppsTableSeeder::class);
        // $this->call(DeviceInfoTableSeeder::class);
    }
}
