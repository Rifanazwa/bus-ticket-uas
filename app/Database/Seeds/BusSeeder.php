<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run()
    {
        // Truncate buses table to clean up old data
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->db->table('buses')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');

        // Programmatically generate a 2-2 seat layout for Eksekutif (40 seats)
        $execLayout = [];
        for ($row = 1; $row <= 10; $row++) {
            $execLayout[] = ['row' => $row, 'col' => 'A', 'number' => $row . 'A', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'B', 'number' => $row . 'B', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'aisle', 'number' => null, 'type' => 'aisle'];
            $execLayout[] = ['row' => $row, 'col' => 'C', 'number' => $row . 'C', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'D', 'number' => $row . 'D', 'type' => 'seat'];
        }

        // Programmatically generate a 2-1 seat layout for Bisnis (24 seats)
        $vipLayout = [];
        for ($row = 1; $row <= 8; $row++) {
            $vipLayout[] = ['row' => $row, 'col' => 'A', 'number' => $row . 'A', 'type' => 'seat'];
            $vipLayout[] = ['row' => $row, 'col' => 'B', 'number' => $row . 'B', 'type' => 'seat'];
            $vipLayout[] = ['row' => $row, 'col' => 'aisle', 'number' => null, 'type' => 'aisle'];
            $vipLayout[] = ['row' => $row, 'col' => 'C', 'number' => $row . 'C', 'type' => 'seat'];
        }

        // Programmatically generate a 2-2 seat layout for Ekonomi (48 seats)
        $ekoLayout = [];
        for ($row = 1; $row <= 12; $row++) {
            $ekoLayout[] = ['row' => $row, 'col' => 'A', 'number' => $row . 'A', 'type' => 'seat'];
            $ekoLayout[] = ['row' => $row, 'col' => 'B', 'number' => $row . 'B', 'type' => 'seat'];
            $ekoLayout[] = ['row' => $row, 'col' => 'aisle', 'number' => null, 'type' => 'aisle'];
            $ekoLayout[] = ['row' => $row, 'col' => 'C', 'number' => $row . 'C', 'type' => 'seat'];
            $ekoLayout[] = ['row' => $row, 'col' => 'D', 'number' => $row . 'D', 'type' => 'seat'];
        }

        $data = [];

        // 1. Generate 33 Eksekutif buses
        for ($i = 1; $i <= 33; $i++) {
            $code = sprintf('JB-EXE%02d', $i);
            $data[] = [
                'code'        => $code,
                'name'        => "Joss Bus (Eksekutif)",
                'type'        => 'Eksekutif',
                'seat_layout' => json_encode($execLayout),
                'total_seats' => 40,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }

        // 2. Generate 33 Bisnis buses
        for ($i = 1; $i <= 33; $i++) {
            $code = sprintf('JB-BIS%02d', $i);
            $data[] = [
                'code'        => $code,
                'name'        => "Joss Bus (Bisnis)",
                'type'        => 'Bisnis',
                'seat_layout' => json_encode($vipLayout),
                'total_seats' => 24,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }

        // 3. Generate 34 Ekonomi buses
        for ($i = 1; $i <= 34; $i++) {
            $code = sprintf('JB-EKO%02d', $i);
            $data[] = [
                'code'        => $code,
                'name'        => "Joss Bus (Ekonomi)",
                'type'        => 'Ekonomi',
                'seat_layout' => json_encode($ekoLayout),
                'total_seats' => 48,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('buses')->insertBatch($data);
    }
}
