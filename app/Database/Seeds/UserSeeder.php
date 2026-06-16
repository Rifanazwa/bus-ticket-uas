<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'       => 'Super Admin PO Bus',
                'email'      => 'admin@bus.com',
                'phone'      => '081234567890',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'bus_id'     => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Budi Petugas Terminal',
                'email'      => 'petugas@bus.com',
                'phone'      => '081234567891',
                'password'   => password_hash('petugas123', PASSWORD_DEFAULT),
                'role'       => 'petugas',
                'bus_id'     => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Andi Penumpang Setia',
                'email'      => 'customer@bus.com',
                'phone'      => '081234567892',
                'password'   => password_hash('user123', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'bus_id'     => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
