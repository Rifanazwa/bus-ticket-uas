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

        // Fetch and map crew users by bus_id and crew_role
        $crewUsers = $this->db->table('users')->where('role', 'petugas')->get()->getResultArray();
        $crewMap = [];
        foreach ($crewUsers as $cu) {
            if ($cu['bus_id']) {
                $crewMap[$cu['bus_id']][$cu['crew_role']] = $cu['id'];
            }
        }
 
        $schedules = [];
        $now = date('Y-m-d H:i:s');
        $totalBuses = count($buses);
        $totalRoutes = count($routes);
 
        // Loop through June 1, 2026 to June 30, 2026 (30 days of schedules)
        for ($day = 1; $day <= 30; $day++) {
            $currentDate = sprintf('2026-06-%02d', $day);
 
            // Shift the buses deterministically each day to rotate their assignments
            $dayBuses = $buses;
            for ($i = 0; $i < $day; $i++) {
                $temp = array_shift($dayBuses);
                $dayBuses[] = $temp;
            }
 
            // Loop through each of the 100 buses to assign it a route schedule
            foreach ($dayBuses as $busIndex => $bus) {
                // Determine route index. Since we have 90 routes and 100 buses:
                // - Buses 0 to 89 get routes 0 to 89 (ensuring all 90 routes are covered)
                // - Buses 90 to 99 get routes 0 to 9 (adding a second departure on those routes)
                $routeIndex = $busIndex % $totalRoutes;
                $route = $routes[$routeIndex];
 
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
 
                // Spread departure hours deterministically between 06:00 and 20:00
                $baseHour = 6 + (($routeIndex * 2) % 10); // e.g. 06:00 to 14:00
                
                // If this is a duplicate route departure (bus index >= 90), push it 4 hours later to avoid overlap
                if ($busIndex >= $totalRoutes) {
                    $departureHour = $baseHour + 4; // e.g. 10:00 to 18:00
                } else {
                    $departureHour = $baseHour;
                }
                
                $departureTime = sprintf('%s %02d:00:00', $currentDate, $departureHour);
 
                // Calculate arrival time
                $departureTimestamp = strtotime($departureTime);
                $arrivalTimestamp = $departureTimestamp + ($route['estimated_duration'] * 60);
                $arrivalTime = date('Y-m-d H:i:s', $arrivalTimestamp);
 
                $busId = $bus['id'];
                $driver1Id   = $crewMap[$busId]['driver_1'] ?? null;
                $driver2Id   = $crewMap[$busId]['driver_2'] ?? null;
                $conductorId = $crewMap[$busId]['conductor'] ?? null;

                $schedules[] = [
                    'route_id'       => $route['id'],
                    'bus_id'         => $busId,
                    'departure_time' => $departureTime,
                    'arrival_time'   => $arrivalTime,
                    'price'          => $price,
                    'status'         => 'scheduled',
                    'driver_1_id'    => $driver1Id,
                    'driver_2_id'    => $driver2Id,
                    'conductor_id'   => $conductorId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }
 
        // Insert in batches of 100 rows to ensure clean execution and prevent memory limits
        $chunks = array_chunk($schedules, 100);
        foreach ($chunks as $chunk) {
            $this->db->table('schedules')->insertBatch($chunk);
        }
    }
}
