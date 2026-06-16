<?php
 
namespace App\Database\Seeds;
 
use CodeIgniter\Database\Seeder;
 
class RouteSeeder extends Seeder
{
    // Latitude and Longitude coordinates of the cities
    private array $cities = [
        'Jakarta'      => ['lat' => -6.2088, 'lon' => 106.8456],
        'Bandung'      => ['lat' => -6.9175, 'lon' => 107.6191],
        'Surabaya'     => ['lat' => -7.2575, 'lon' => 112.7521],
        'Yogyakarta'   => ['lat' => -7.7956, 'lon' => 110.3695],
        'Cirebon'      => ['lat' => -6.7216, 'lon' => 108.5560],
        'Indramayu'    => ['lat' => -6.3263, 'lon' => 108.3249],
        'Tasikmalaya'  => ['lat' => -7.3274, 'lon' => 108.2207],
        'Garut'        => ['lat' => -7.2279, 'lon' => 107.9087],
        'Semarang'     => ['lat' => -6.9667, 'lon' => 110.4167],
        'Malang'       => ['lat' => -7.9839, 'lon' => 112.6214],
    ];

    public function run()
    {
        // Truncate table to prevent duplicates on multiple seeds
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->db->table('routes')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');

        $data = [];
        $cityNames = array_keys($this->cities);

        // Generate routes from/to every city combination (fully-connected network)
        foreach ($cityNames as $origin) {
            foreach ($cityNames as $destination) {
                if ($origin !== $destination) {
                    $originCoords = $this->cities[$origin];
                    $destCoords = $this->cities[$destination];

                    $distance = $this->calculateRoadDistance(
                        $originCoords['lat'],
                        $originCoords['lon'],
                        $destCoords['lat'],
                        $destCoords['lon']
                    );

                    // Assume average bus speed is 60 km/h to calculate duration in minutes
                    // Round to nearest 10 minutes
                    $duration = (int) (round(($distance / 60) * 60 / 10) * 10);
                    if ($duration < 60) {
                        $duration = 60; // minimum 1 hour travel time
                    }

                    $data[] = [
                        'origin'             => $origin,
                        'destination'        => $destination,
                        'distance_km'        => $distance,
                        'estimated_duration' => $duration,
                    ];
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        $this->db->table('routes')->insertBatch($data);
    }

    /**
     * Calculates estimated road distance (Haversine distance * road multiplier)
     */
    private function calculateRoadDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $airDistance = $earthRadius * $c;

        // Multiply by 1.25 to estimate driving/road distance
        return round($airDistance * 1.25, 2);
    }
}
