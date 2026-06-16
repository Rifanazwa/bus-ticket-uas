<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run()
    {
        // Programmatically generate a 2-2 seat layout for Executive (40 seats)
        $execLayout = [];
        for ($row = 1; $row <= 10; $row++) {
            $execLayout[] = ['row' => $row, 'col' => 'A', 'number' => $row . 'A', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'B', 'number' => $row . 'B', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'aisle', 'number' => null, 'type' => 'aisle'];
            $execLayout[] = ['row' => $row, 'col' => 'C', 'number' => $row . 'C', 'type' => 'seat'];
            $execLayout[] = ['row' => $row, 'col' => 'D', 'number' => $row . 'D', 'type' => 'seat'];
        }

        // Programmatically generate a 2-1 seat layout for VIP (24 seats)
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

        $busesList = [
            ['code' => 'RI-EXE01', 'name' => 'Rosalia Indah (Executive)', 'type' => 'Executive'],
            ['code' => 'SJ-EXE02', 'name' => 'Sinar Jaya (Executive)', 'type' => 'Executive'],
            ['code' => 'HJ-VIP03', 'name' => 'Harapan Jaya (VIP)', 'type' => 'VIP'],
            ['code' => 'HR-EXE04', 'name' => 'PO Haryanto (Executive)', 'type' => 'Executive'],
            ['code' => 'PJ-EKO05', 'name' => 'Primajasa (Ekonomi)', 'type' => 'Ekonomi'],
            ['code' => 'KD-VIP06', 'name' => 'Kramat Djati (VIP)', 'type' => 'VIP'],
            ['code' => 'LR-EXE07', 'name' => 'Lorena (Executive)', 'type' => 'Executive'],
            ['code' => 'KR-VIP08', 'name' => 'Karina (VIP)', 'type' => 'VIP'],
            ['code' => 'PK-EXE09', 'name' => 'Pahala Kencana (Executive)', 'type' => 'Executive'],
            ['code' => 'NS-EXE10', 'name' => 'PO Nusantara (Executive)', 'type' => 'Executive'],
            ['code' => 'SR-EKO11', 'name' => 'Sugeng Rahayu (Ekonomi)', 'type' => 'Ekonomi'],
            ['code' => 'SS-EKO12', 'name' => 'Sumber Selamat (Ekonomi)', 'type' => 'Ekonomi'],
            ['code' => 'EK-EXE13', 'name' => 'Eka (Executive)', 'type' => 'Executive'],
            ['code' => 'MR-EKO14', 'name' => 'Mira (Ekonomi)', 'type' => 'Ekonomi'],
            ['code' => 'SD-VIP15', 'name' => 'Safari Dharma Raya (VIP)', 'type' => 'VIP'],
            ['code' => 'HD-EXE16', 'name' => 'Handoyo (Executive)', 'type' => 'Executive'],
            ['code' => 'AK-EKO17', 'name' => 'Akas (Ekonomi)', 'type' => 'Ekonomi'],
            ['code' => 'ST-EXE18', 'name' => 'Sudiro Tungga Jaya (Executive)', 'type' => 'Executive'],
            ['code' => 'BJ-VIP19', 'name' => 'Bejeu (VIP)', 'type' => 'VIP'],
            ['code' => 'EF-EXE20', 'name' => 'Efisiensi (Executive)', 'type' => 'Executive'],
        ];

        $data = [];
        foreach ($busesList as $bus) {
            $layout = [];
            $seats = 0;
            if ($bus['type'] === 'VIP') {
                $layout = $vipLayout;
                $seats = 24;
            } elseif ($bus['type'] === 'Executive') {
                $layout = $execLayout;
                $seats = 40;
            } else {
                $layout = $ekoLayout;
                $seats = 48;
            }

            $data[] = [
                'code'        => $bus['code'],
                'name'        => $bus['name'],
                'type'        => $bus['type'],
                'seat_layout' => json_encode($layout),
                'total_seats' => $seats,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('buses')->insertBatch($data);
    }
}

