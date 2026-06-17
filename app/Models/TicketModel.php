<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table            = 'tickets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['booking_id', 'qr_code', 'status', 'issued_at', 'scanned_by', 'scanned_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'booking_id' => 'required|numeric',
        'qr_code'    => 'required|max_length[255]',
        'status'     => 'required|in_list[issued,boarded,expired]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Get ticket details
    public function getDetailedTicket($ticketId)
    {
        return $this->select('tickets.*, bookings.booking_code, bookings.total_price, users.name as customer_name, users.phone as customer_phone,
                             schedules.departure_time, schedules.arrival_time, routes.origin, routes.destination, buses.name as bus_name, buses.type as bus_type, buses.id as bus_id')
            ->join('bookings', 'bookings.id = tickets.booking_id')
            ->join('users', 'users.id = bookings.user_id')
            ->join('schedules', 'schedules.id = bookings.schedule_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->where('tickets.id', $ticketId)
            ->first();
    }

    public function getBoardingHistory($limit = 5)
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('scanned_by', 'tickets')) {
            return [];
        }

        $history = $this->select('tickets.*, bookings.booking_code, users.name as customer_name,
                             routes.origin, routes.destination, buses.name as bus_name')
            ->join('bookings', 'bookings.id = tickets.booking_id')
            ->join('users', 'users.id = bookings.user_id')
            ->join('schedules', 'schedules.id = bookings.schedule_id')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->where('tickets.status', 'boarded')
            ->orderBy('tickets.id', 'DESC') // fallback
            ->limit($limit);

        // Safely join scanner name if scanned_by is present
        $history = $history->select('scanners.name as scanner_name')
            ->join('users scanners', 'scanners.id = tickets.scanned_by', 'left');

        if ($db->fieldExists('scanned_at', 'tickets')) {
            $history = $history->orderBy('tickets.scanned_at', 'DESC');
        }

        $results = $history->findAll();

        $bookingSeatModel = new \App\Models\BookingSeatModel();
        foreach ($results as &$item) {
            $seats = $bookingSeatModel->where('booking_id', $item['booking_id'])->findAll();
            $item['seats'] = implode(', ', array_column($seats, 'seat_number'));
        }

        return $results;
    }
}
