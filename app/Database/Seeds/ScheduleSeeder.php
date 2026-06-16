<?php
 
namespace App\Database\Seeds;
 
use CodeIgniter\Database\Seeder;
 
class ScheduleSeeder extends Seeder
{
    public function run()
    {
        // Truncate schedules table to clean up
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->db->table('schedules')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');

        // Get routes and buses
        $routes = $this->db->table('routes')->get()->getResultArray();
        $buses = $this->db->table('buses')->get()->getResultArray();

        if (empty($routes) || empty($buses)) {
            return;
        }

        $schedules = [];
        $now = date('Y-m-d H:i:s');

        // Loop through June 1, 2026 to June 30, 2026
        for ($day = 1; $day <= 30; $day++) {
            $currentDate = sprintf('2026-06-%02d', $day);

            foreach ($routes as $routeIndex => $route) {
                // Determine rotating bus index
                $busIndex = ($routeIndex + $day) % count($buses);
                $bus = $buses[$busIndex];

                // Pricing logic based on distance and class
                $basePrice = 50000;
                $pricePerKm = 450;
                $price = $basePrice + ($route['distance_km'] * $pricePerKm);

                if ($bus['type'] === 'Bisnis') {
                    $price *= 1.25;
                } elseif ($bus['type'] === 'Eksekutif') {
                    $price *= 1.10;
                } else {
                    $price *= 0.90;
                }

                // Round price to nearest Rp 5.000
                $price = round($price / 5000) * 5000;

                // Spread departure times deterministically between 06:00 and 20:00
                $startHour = 6 + (($routeIndex * 3) % 15);
                $departureTime = sprintf('%s %02d:00:00', $currentDate, $startHour);

                // Calculate arrival time
                $departureTimestamp = strtotime($departureTime);
                $arrivalTimestamp = $departureTimestamp + ($route['estimated_duration'] * 60);
                $arrivalTime = date('Y-m-d H:i:s', $arrivalTimestamp);

                $schedules[] = [
                    'route_id'       => $route['id'],
                    'bus_id'         => $bus['id'],
                    'departure_time' => $departureTime,
                    'arrival_time'   => $arrivalTime,
                    'price'          => $price,
                    'status'         => 'scheduled',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        // Insert in batches of 100 rows to ensure clean execution
        $chunks = array_chunk($schedules, 100);
        foreach ($chunks as $chunk) {
            $this->db->table('schedules')->insertBatch($chunk);
        }
    }
}
