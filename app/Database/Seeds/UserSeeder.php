<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Truncate users table to clean up
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->db->table('users')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');

        // Static standard accounts
        $data = [
            [
                'name'       => 'Super Admin PO Bus',
                'email'      => 'admin@bus.com',
                'phone'      => '081234567890',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'crew_role'  => 'staff',
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
                'crew_role'  => 'staff',
                'bus_id'     => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Andi Penumpang Setia',
                'email'      => 'customer@bus.com',
                'phone'      => '081234567892',
                'password'   => password_hash('user123', PASSWORD_DEFAULT),
                'role'       => 'customer',
                'crew_role'  => 'staff',
                'bus_id'     => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Insert baseline accounts first
        $this->db->table('users')->insertBatch($data);

        // Generate 100 Driver 1s, 100 Driver 2s, and 100 Conductors deterministically
        $firstNames = ['Agus', 'Budi', 'Cahyono', 'Dedi', 'Eko', 'Fajar', 'Gatot', 'Hendra', 'Indra', 'Joko', 'Kurniawan', 'Lukman', 'Mulyono', 'Nugroho', 'Oki', 'Prasetyo', 'Rian', 'Slamet', 'Teguh', 'Utomo', 'Wawan', 'Yanto', 'Zainal', 'Adi', 'Bambang', 'Dwi', 'Edi', 'Heri', 'Iwan', 'Rudi', 'Sigit', 'Sugeng', 'Sutrisno', 'Tri', 'Wahyu', 'Anang', 'Aris', 'Asep', 'Basuki', 'Cecep', 'Dadang', 'Darsono', 'Dewo', 'Endro', 'Gunawan', 'Herman', 'Hartono', 'Imron', 'Kusuma', 'Maman', 'Purnomo', 'Roni', 'Subagyo', 'Suherman', 'Supriadi', 'Suryo', 'Toni', 'Ujang', 'Wibowo', 'Yayan'];
        $lastNames = ['Santoso', 'Prabowo', 'Hidayat', 'Saputra', 'Setiawan', 'Wijaya', 'Kusuma', 'Susilo', 'Subagyo', 'Nugraha', 'Raharjo', 'Gunawan', 'Budiman', 'Suherman', 'Kurnia', 'Pramono', 'Wibowo', 'Sutrisno', 'Hariyanto', 'Hartono', 'Purnama', 'Setiadi', 'Kusumah', 'Suharto', 'Mulyadi', 'Siswanto', 'Heryanto', 'Yulianto', 'Supriatna', 'Sudrajat'];

        $crewData = [];
        $now = date('Y-m-d H:i:s');
        $hashedDriverPassword = password_hash('supir123', PASSWORD_DEFAULT);
        $hashedCondPassword = password_hash('kondektur123', PASSWORD_DEFAULT);

        for ($i = 1; $i <= 100; $i++) {
            // Driver 1
            $first1 = $firstNames[($i - 1) % count($firstNames)];
            $last1  = $lastNames[($i * 3) % count($lastNames)];
            $driver1Name = $first1 . ' ' . $last1;

            $crewData[] = [
                'name'       => $driver1Name,
                'email'      => "supir1.{$i}@bus.com",
                'phone'      => '0812' . sprintf('%08d', 10000000 + $i),
                'password'   => $hashedDriverPassword,
                'role'       => 'petugas',
                'crew_role'  => 'driver_1',
                'bus_id'     => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Driver 2
            $first2 = $firstNames[($i + 15) % count($firstNames)];
            $last2  = $lastNames[($i * 5 + 4) % count($lastNames)];
            $driver2Name = $first2 . ' ' . $last2;

            $crewData[] = [
                'name'       => $driver2Name,
                'email'      => "supir2.{$i}@bus.com",
                'phone'      => '0812' . sprintf('%08d', 20000000 + $i),
                'password'   => $hashedDriverPassword,
                'role'       => 'petugas',
                'crew_role'  => 'driver_2',
                'bus_id'     => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Conductor
            $firstCond = $firstNames[($i + 30) % count($firstNames)];
            $lastCond  = $lastNames[($i * 7 + 8) % count($lastNames)];
            $conductorName = $firstCond . ' ' . $lastCond;

            $crewData[] = [
                'name'       => $conductorName,
                'email'      => "kondektur.{$i}@bus.com",
                'phone'      => '0812' . sprintf('%08d', 30000000 + $i),
                'password'   => $hashedCondPassword,
                'role'       => 'petugas',
                'crew_role'  => 'conductor',
                'bus_id'     => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert all crew members in batches of 100 to avoid memory/query limits
        $chunks = array_chunk($crewData, 100);
        foreach ($chunks as $chunk) {
            $this->db->table('users')->insertBatch($chunk);
        }
    }
}
