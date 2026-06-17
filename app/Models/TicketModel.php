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
}
