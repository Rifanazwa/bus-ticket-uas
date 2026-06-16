<?php
 
namespace App\Database\Seeds;
 
use CodeIgniter\Database\Seeder;
 
class RouteSeeder extends Seeder
{
    public function run()
    {
        // Truncate table to prevent duplicates on multiple seeds
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->db->table('routes')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');

        $data = [
            // Hubs
            ['origin' => 'Jakarta', 'destination' => 'Bandung', 'distance_km' => 150.00, 'estimated_duration' => 180],
            ['origin' => 'Bandung', 'destination' => 'Jakarta', 'distance_km' => 150.00, 'estimated_duration' => 180],
            ['origin' => 'Jakarta', 'destination' => 'Surabaya', 'distance_km' => 780.00, 'estimated_duration' => 600],
            ['origin' => 'Surabaya', 'destination' => 'Jakarta', 'distance_km' => 780.00, 'estimated_duration' => 600],
            ['origin' => 'Jakarta', 'destination' => 'Yogyakarta', 'distance_km' => 550.00, 'estimated_duration' => 480],
            ['origin' => 'Yogyakarta', 'destination' => 'Jakarta', 'distance_km' => 550.00, 'estimated_duration' => 480],
            ['origin' => 'Surabaya', 'destination' => 'Yogyakarta', 'distance_km' => 330.00, 'estimated_duration' => 300],
            ['origin' => 'Yogyakarta', 'destination' => 'Surabaya', 'distance_km' => 330.00, 'estimated_duration' => 300],

            // New Cities to/from Jakarta
            ['origin' => 'Jakarta', 'destination' => 'Cirebon', 'distance_km' => 220.00, 'estimated_duration' => 180],
            ['origin' => 'Cirebon', 'destination' => 'Jakarta', 'distance_km' => 220.00, 'estimated_duration' => 180],
            ['origin' => 'Jakarta', 'destination' => 'Indramayu', 'distance_km' => 200.00, 'estimated_duration' => 180],
            ['origin' => 'Indramayu', 'destination' => 'Jakarta', 'distance_km' => 200.00, 'estimated_duration' => 180],
            ['origin' => 'Jakarta', 'destination' => 'Tasikmalaya', 'distance_km' => 270.00, 'estimated_duration' => 300],
            ['origin' => 'Tasikmalaya', 'destination' => 'Jakarta', 'distance_km' => 270.00, 'estimated_duration' => 300],
            ['origin' => 'Jakarta', 'destination' => 'Garut', 'distance_km' => 210.00, 'estimated_duration' => 240],
            ['origin' => 'Garut', 'destination' => 'Jakarta', 'distance_km' => 210.00, 'estimated_duration' => 240],
            ['origin' => 'Jakarta', 'destination' => 'Semarang', 'distance_km' => 440.00, 'estimated_duration' => 360],
            ['origin' => 'Semarang', 'destination' => 'Jakarta', 'distance_km' => 440.00, 'estimated_duration' => 360],
            ['origin' => 'Jakarta', 'destination' => 'Malang', 'distance_km' => 850.00, 'estimated_duration' => 660],
            ['origin' => 'Malang', 'destination' => 'Jakarta', 'distance_km' => 850.00, 'estimated_duration' => 660],

            // New Cities to/from Bandung
            ['origin' => 'Bandung', 'destination' => 'Cirebon', 'distance_km' => 130.00, 'estimated_duration' => 150],
            ['origin' => 'Cirebon', 'destination' => 'Bandung', 'distance_km' => 130.00, 'estimated_duration' => 150],
            ['origin' => 'Bandung', 'destination' => 'Tasikmalaya', 'distance_km' => 110.00, 'estimated_duration' => 180],
            ['origin' => 'Tasikmalaya', 'destination' => 'Bandung', 'distance_km' => 110.00, 'estimated_duration' => 180],
            ['origin' => 'Bandung', 'destination' => 'Garut', 'distance_km' => 70.00, 'estimated_duration' => 120],
            ['origin' => 'Garut', 'destination' => 'Bandung', 'distance_km' => 70.00, 'estimated_duration' => 120],

            // New Cities to/from Surabaya / Yogyakarta
            ['origin' => 'Surabaya', 'destination' => 'Semarang', 'distance_km' => 350.00, 'estimated_duration' => 270],
            ['origin' => 'Semarang', 'destination' => 'Surabaya', 'distance_km' => 350.00, 'estimated_duration' => 270],
            ['origin' => 'Surabaya', 'destination' => 'Malang', 'distance_km' => 95.00, 'estimated_duration' => 120],
            ['origin' => 'Malang', 'destination' => 'Surabaya', 'distance_km' => 95.00, 'estimated_duration' => 120],
            ['origin' => 'Yogyakarta', 'destination' => 'Semarang', 'distance_km' => 120.00, 'estimated_duration' => 180],
            ['origin' => 'Semarang', 'destination' => 'Yogyakarta', 'distance_km' => 120.00, 'estimated_duration' => 180],
            ['origin' => 'Yogyakarta', 'destination' => 'Malang', 'distance_km' => 330.00, 'estimated_duration' => 300],
            ['origin' => 'Malang', 'destination' => 'Yogyakarta', 'distance_km' => 330.00, 'estimated_duration' => 300],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        $this->db->table('routes')->insertBatch($data);
    }
}
