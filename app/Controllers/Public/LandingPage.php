<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class LandingPage extends BaseController
{
    public function index()
    {
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
