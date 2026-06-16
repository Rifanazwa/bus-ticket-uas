<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('BusSeeder');
        $this->call('UserSeeder');
        $this->call('RouteSeeder');
        $this->call('ScheduleSeeder');
        $this->call('PromoSeeder');
    }
}
