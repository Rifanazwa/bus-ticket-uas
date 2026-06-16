<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'code'           => 'MUDIKASIK',
                'discount_type'  => 'percent',
                'discount_value' => 10.00,
                'valid_from'     => date('Y-01-01'),
                'valid_until'    => date('Y-12-31'),
                'usage_limit'    => 100,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'code'           => 'AIPROMO',
                'discount_type'  => 'fixed',
                'discount_value' => 25000.00,
                'valid_from'     => date('Y-01-01'),
                'valid_until'    => date('Y-12-31'),
                'usage_limit'    => 50,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'code'           => 'DISKONHEMAT',
                'discount_type'  => 'percent',
                'discount_value' => 15.00,
                'valid_from'     => date('Y-01-01'),
                'valid_until'    => date('Y-12-31'),
                'usage_limit'     => 200,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('promos')->insertBatch($data);
    }
}
