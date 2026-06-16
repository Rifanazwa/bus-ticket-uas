<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table            = 'bookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['booking_code', 'user_id', 'schedule_id', 'total_price', 'payment_status', 'booking_status'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'booking_code'   => 'required|is_unique[bookings.booking_code,id,{id}]',
        'user_id'        => 'required|numeric',
        'schedule_id'    => 'required|numeric',
        'total_price'    => 'required|decimal',
        'payment_status' => 'required|in_list[pending,paid,failed,refunded]',
        'booking_status' => 'required|in_list[active,completed,cancelled]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Get Booking details with Customer, Schedule, Route and Bus info
    public function getDetailedBooking($bookingId)
    {
        return $this->select('bookings.*, users.name as customer_name, users.email as customer_email, users.phone as customer_phone,
                             schedules.departure_time, schedules.arrival_time, schedules.price as seat_price,
                             routes.origin, routes.destination, routes.estimated_duration, buses.name as bus_name, buses.type as bus_type')
            ->join('users', 'users.id = bookings.user_id')
            ->join('schedules', 'schedules.id = bookings.schedule_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->where('bookings.id', $bookingId)
            ->first();
    }

    public function getDetailedBookingByCode($bookingCode)
    {
        return $this->select('bookings.*, users.name as customer_name, users.email as customer_email, users.phone as customer_phone,
                             schedules.departure_time, schedules.arrival_time, schedules.price as seat_price,
                             routes.origin, routes.destination, routes.estimated_duration, buses.name as bus_name, buses.type as bus_type')
            ->join('users', 'users.id = bookings.user_id')
            ->join('schedules', 'schedules.id = bookings.schedule_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->where('bookings.booking_code', $bookingCode)
            ->first();
    }

    public function getBookingsByUser($userId)
    {
        return $this->select('bookings.*, schedules.departure_time, schedules.arrival_time, 
                             routes.origin, routes.destination, buses.name as bus_name, buses.type as bus_type,
                             tickets.qr_code as ticket_qr, tickets.status as ticket_status, tickets.id as ticket_id')
            ->join('schedules', 'schedules.id = bookings.schedule_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->join('tickets', 'tickets.booking_id = bookings.id', 'left')
            ->where('bookings.user_id', $userId)
            ->orderBy('bookings.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Automatically cancel bookings that have been pending for more than 15 minutes
     */
    public function cancelExpiredBookings()
    {
        $expiryTime = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        return $this->where('payment_status', 'pending')
            ->where('booking_status', 'active')
            ->where('created_at <', $expiryTime)
            ->set(['booking_status' => 'cancelled', 'payment_status' => 'failed'])
            ->update();
    }
}

