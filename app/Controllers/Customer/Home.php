<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\BookingModel;

class Home extends BaseController
{
    protected $bookingModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
    }

    public function index()
    {
        // Cancel expired pending bookings to keep database state clean
        $this->bookingModel->cancelExpiredBookings();

        $userId = session()->get('userId');
        $bookings = $this->bookingModel->getBookingsByUser($userId);

        // Fetch seat details and review status for each booking
        $bookingSeatModel = new \App\Models\BookingSeatModel();
        $reviewModel = new \App\Models\ReviewModel();
        foreach ($bookings as &$b) {
            $b['seats'] = $bookingSeatModel->where('booking_id', $b['id'])->findAll();
            $b['reviewed'] = $reviewModel->where('booking_id', $b['id'])->first() ? true : false;
        }
        unset($b);

        // 1. Fetch upcoming booking (paid, issued, departure_time > now)
        $upcomingBooking = $this->bookingModel->select('bookings.*, schedules.departure_time, schedules.arrival_time, 
                             routes.origin, routes.destination, buses.name as bus_name, buses.type as bus_type,
                             tickets.id as ticket_id, tickets.status as ticket_status, tickets.qr_code as ticket_qr')
            ->join('schedules', 'schedules.id = bookings.schedule_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->join('tickets', 'tickets.booking_id = bookings.id')
            ->where('bookings.user_id', $userId)
            ->where('bookings.payment_status', 'paid')
            ->where('tickets.status', 'issued')
            ->where('schedules.departure_time >', date('Y-m-d H:i:s'))
            ->orderBy('schedules.departure_time', 'ASC')
            ->first();

        if ($upcomingBooking) {
            $upcomingBooking['seats'] = $bookingSeatModel->where('booking_id', $upcomingBooking['id'])->findAll();
        }

        // 2. Fetch loyalty stats (boarded trips count)
        $db = \Config\Database::connect();
        $boardedCount = $db->table('tickets')
            ->join('bookings', 'bookings.id = tickets.booking_id')
            ->where('bookings.user_id', $userId)
            ->where('tickets.status', 'boarded')
            ->countAllResults();

        $xpPoints = $boardedCount * 100;
        
        // Tier definition
        if ($boardedCount < 3) {
            $tierName = 'Bronze Member';
            $nextTierName = 'Silver Member';
            $targetCount = 3;
            $progress = round(($boardedCount / $targetCount) * 100);
        } elseif ($boardedCount < 6) {
            $tierName = 'Silver Member';
            $nextTierName = 'Gold Member';
            $targetCount = 6;
            // progress from 3 to 6
            $progress = round((($boardedCount - 3) / 3) * 100);
        } else {
            $tierName = 'Gold Member';
            $nextTierName = 'Ultimate Tier';
            $progress = 100;
        }

        // 3. Fetch saved passenger manifest (unique names from past bookings)
        $savedPassengers = $db->table('booking_seats')
            ->select('booking_seats.passenger_name, COUNT(booking_seats.id) as trip_count')
            ->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->where('bookings.user_id', $userId)
            ->groupBy('booking_seats.passenger_name')
            ->orderBy('trip_count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // 4. Fetch active promo coupons
        $promoModel = new \App\Models\PromoModel();
        $promos = $promoModel->where('valid_until >=', date('Y-m-d H:i:s'))
                             ->where('usage_limit >', 0)
                             ->findAll();

        // 5. Fetch cities for search dropdown
        $origins = $db->table('routes')->distinct()->select('origin')->get()->getResultArray();
        $destinations = $db->table('routes')->distinct()->select('destination')->get()->getResultArray();
        $cities = array_unique(array_merge(
            array_column($origins, 'origin'),
            array_column($destinations, 'destination')
        ));
        sort($cities);

        return view('customer/home', [
            'title'            => 'Dashboard Penumpang - SiTeBus',
            'bookings'         => $bookings,
            'upcomingBooking'  => $upcomingBooking,
            'boardedCount'     => $boardedCount,
            'xpPoints'         => $xpPoints,
            'tierName'         => $tierName,
            'nextTierName'     => $nextTierName,
            'progress'         => $progress,
            'savedPassengers'  => $savedPassengers,
            'promos'           => $promos,
            'cities'           => $cities
        ]);
    }
}

