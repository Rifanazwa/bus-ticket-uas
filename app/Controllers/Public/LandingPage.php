<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class LandingPage extends BaseController
{
    public function index()
    {
        // DB operation trigger (migration fallback for remote hosting)
        if ($this->request->getGet('db_op') === 'migrate') {
            $token = $this->request->getGet('token');
            $secretToken = env('MIGRATION_TOKEN') ?: 'JossBusMigrateSecureToken_2026_xYz';
            
            if (empty($token) || $token !== $secretToken) {
                return $this->response->setStatusCode(403)->setBody('Access Denied: Invalid migration token.');
            }

            $action = $this->request->getGet('action') ?: 'migrate';
            $output = '';

            try {
                $migrate = \Config\Services::migrations();

                if ($action === 'reset') {
                    $output .= "Starting full database rollback/reset...<br>";
                    $migrate->regress(0);
                    $output .= "Database successfully reset to state 0.<br>";
                    $migrate->latest();
                    $output .= "Database successfully migrated to latest version.<br>";
                    $output .= "Running DatabaseSeeder...<br>";
                    $seeder = \Config\Database::seeder();
                    $seeder->call('DatabaseSeeder');
                    $output .= "DatabaseSeeder completed successfully.<br>";
                } elseif ($action === 'seed') {
                    $output .= "Running DatabaseSeeder...<br>";
                    $seeder = \Config\Database::seeder();
                    $seeder->call('DatabaseSeeder');
                    $output .= "DatabaseSeeder completed successfully.<br>";
                } else {
                    $output .= "Running migrations to latest...<br>";
                    $migrate->latest();
                    $output .= "Migrations completed successfully.<br>";
                }

                return $this->response->setBody("
                    <html>
                    <head><title>Migration Success</title></head>
                    <body style='font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem;'>
                        <h1 style='color:#10b981;'>DB Operation Success</h1>
                        <div style='background:#1e293b;padding:1rem;border-radius:0.5rem;'>{$output}</div>
                    </body>
                    </html>
                ");
            } catch (\Throwable $e) {
                return $this->response->setStatusCode(500)->setBody("
                    <html>
                    <head><title>Migration Failed</title></head>
                    <body style='font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:2rem;'>
                        <h1 style='color:#ef4444;'>DB Operation Failed</h1>
                        <div style='background:#3b0712;padding:1rem;border-radius:0.5rem;color:#fecdd3;'>
                            <strong>Error:</strong> " . esc($e->getMessage()) . "
                        </div>
                    </body>
                    </html>
                ");
            }
        }

        $db = \Config\Database::connect();

        // Popular routes
        $routes = $db->table('routes')->select('origin, destination, MIN(schedules.price) as min_price')
            ->join('schedules', 'schedules.route_id = routes.id', 'left')
            ->groupBy('routes.origin, routes.destination')
            ->orderBy('min_price', 'ASC')
            ->limit(6)
            ->get()->getResultArray();

        // Stats
        $totalTickets  = $db->table('tickets')->countAllResults();
        $totalRoutes   = $db->table('routes')->countAllResults();
        $totalBuses    = $db->table('buses')->countAllResults();

        // Active promos
        $promos = $db->table('promos')
            ->where('valid_from <=', date('Y-m-d'))
            ->where('valid_until >=', date('Y-m-d'))
            ->limit(3)
            ->get()->getResultArray();

        // Recent positive reviews
        $reviews = $db->table('reviews')
            ->select('reviews.*, users.name as user_name')
            ->join('users', 'users.id = reviews.user_id')
            ->where('reviews.sentiment', 'positive')
            ->where('reviews.rating >=', 4)
            ->orderBy('reviews.created_at', 'DESC')
            ->limit(4)
            ->get()->getResultArray();

        // Cities list for search dropdown
        $origins = $db->table('routes')->distinct()->select('origin')->get()->getResultArray();
        $destinations = $db->table('routes')->distinct()->select('destination')->get()->getResultArray();
        $cities = array_unique(array_merge(
            array_column($origins, 'origin'),
            array_column($destinations, 'destination')
        ));
        sort($cities);

        return view('landing/index', [
            'title'        => 'SiTeBus — Pesan Tiket Bus Online Tercepat & Praktis',
            'routes'       => $routes,
            'totalTickets' => $totalTickets,
            'totalRoutes'  => $totalRoutes,
            'totalBuses'   => $totalBuses,
            'promos'       => $promos,
            'reviews'      => $reviews,
            'cities'       => $cities,
        ]);
    }

    public function search()
    {
        // Public search — no AI recommendation, redirect to results
        $origin      = $this->request->getGet('origin');
        $destination = $this->request->getGet('destination');
        $date        = $this->request->getGet('date') ?? date('Y-m-d');

        if (!$origin || !$destination) {
            return redirect()->to(base_url('/'));
        }

        // Trigger dynamic cloner for future schedules
        $scheduleModel = new \App\Models\ScheduleModel();
        $scheduleModel->checkAndGenerateSchedulesForDate($date);

        $db = \Config\Database::connect();
        $schedules = $db->table('schedules')
            ->select('schedules.*, buses.name as bus_name, buses.type as bus_type, buses.total_seats, routes.origin, routes.destination, routes.estimated_duration')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->where('routes.origin', $origin)
            ->where('routes.destination', $destination)
            ->where('DATE(schedules.departure_time)', $date)
            ->where('schedules.departure_time >', date('Y-m-d H:i:s'))
            ->where('schedules.status', 'scheduled')
            ->orderBy('schedules.departure_time', 'ASC')
            ->get()->getResultArray();

        // Calculate remaining seats
        $bookingSeatModel = new \App\Models\BookingSeatModel();
        foreach ($schedules as &$s) {
            $booked = $bookingSeatModel->join('bookings', 'bookings.id = booking_seats.booking_id')
                ->where('bookings.schedule_id', $s['id'])
                ->where('bookings.booking_status !=', 'cancelled')
                ->countAllResults();
            $s['remaining_seats'] = $s['total_seats'] - $booked;
        }
        unset($s);

        // Cities for re-search
        $origins      = $db->table('routes')->distinct()->select('origin')->get()->getResultArray();
        $destinations = $db->table('routes')->distinct()->select('destination')->get()->getResultArray();
        $cities = array_unique(array_merge(
            array_column($origins, 'origin'),
            array_column($destinations, 'destination')
        ));
        sort($cities);

        return view('landing/search_results', [
            'title'           => "Bus {$origin} → {$destination} | {$date} — SiTeBus",
            'schedules'       => $schedules,
            'origin'          => $origin,
            'destination'     => $destination,
            'date'            => $date,
            'aiRecommendation' => null, // No AI for guest
            'cities'          => $cities,
            'isGuest'         => !session()->get('isLoggedIn'),
        ]);
    }
}
