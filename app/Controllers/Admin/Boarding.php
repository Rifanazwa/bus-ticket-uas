<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\BookingModel;
use App\Models\BookingSeatModel;
use App\Models\ScheduleModel;
use App\Models\UserModel;

class Boarding extends BaseController
{
    protected $ticketModel;
    protected $bookingModel;
    protected $bookingSeatModel;
    protected $scheduleModel;
    protected $userModel;

    public function __construct()
    {
        $this->ticketModel      = new TicketModel();
        $this->bookingModel     = new BookingModel();
        $this->bookingSeatModel = new BookingSeatModel();
        $this->scheduleModel    = new ScheduleModel();
        $this->userModel        = new UserModel();
        helper(['form', 'url', 'session']);
    }

    public function index()
    {
        // Ensure today's schedules are auto-generated
        for ($i = 0; $i < 3; $i++) {
            $targetDate = date('Y-m-d', strtotime("+$i days"));
            $this->scheduleModel->checkAndGenerateSchedulesForDate($targetDate);
        }

        $today = date('Y-m-d');
        
        $userId = session()->get('userId');
        $userRole = session()->get('userRole');
        
        $user = $this->userModel->find($userId);
        $isCrew = ($userRole === 'petugas' && $user && !empty($user['crew_role']) && in_array($user['crew_role'], ['driver_1', 'driver_2', 'conductor']));

        if ($isCrew) {
            // Only get schedules assigned to this crew member today
            $schedules = $this->scheduleModel->getDetailedSchedules(null, $today, $userId);
        } else {
            // Admin or general petugas (staff/terminal gatekeeper) sees all schedules
            $schedules = $this->scheduleModel->getDetailedSchedules(null, $today);
        }

        // Sort schedules by departure time
        usort($schedules, fn($a, $b) => strtotime($a['departure_time']) <=> strtotime($b['departure_time']));

        return view('admin/boarding', [
            'title'     => 'Portal Boarding',
            'subtitle'  => 'Manifes Penumpang & Laporan Perjalanan',
            'schedules' => $schedules,
            'isCrew'    => $isCrew
        ]);
    }

    public function manifest($scheduleId)
    {
        $schedule = $this->scheduleModel->getDetailedSchedules($scheduleId);
        if (!$schedule) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jadwal tidak ditemukan.']);
        }

        // Access check for crew members
        $userId = session()->get('userId');
        $userRole = session()->get('userRole');
        $user = $this->userModel->find($userId);
        $isCrew = ($userRole === 'petugas' && $user && !empty($user['crew_role']) && in_array($user['crew_role'], ['driver_1', 'driver_2', 'conductor']));

        if ($isCrew) {
            // Check if this crew member is assigned to the schedule
            if ($schedule['driver_1_id'] != $userId && $schedule['driver_2_id'] != $userId && $schedule['conductor_id'] != $userId) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk melihat manifes perjalanan ini.'
                ]);
            }
        }

        $manifest = $this->bookingSeatModel
            ->select('booking_seats.seat_number, booking_seats.passenger_name, tickets.status as boarding_status, tickets.qr_code, bookings.booking_code, tickets.id as ticket_id')
            ->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->join('tickets', 'tickets.booking_id = bookings.id')
            ->where('bookings.schedule_id', $scheduleId)
            ->where('bookings.booking_status !=', 'cancelled')
            ->orderBy('booking_seats.seat_number', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'status'   => 'success',
            'schedule' => $schedule,
            'manifest' => $manifest
        ]);
    }

    public function printReport($scheduleId)
    {
        $schedule = $this->scheduleModel->getDetailedSchedules($scheduleId);
        if (!$schedule) {
            return "Jadwal tidak ditemukan.";
        }

        // Access check for crew members
        $userId = session()->get('userId');
        $userRole = session()->get('userRole');
        $user = $this->userModel->find($userId);
        $isCrew = ($userRole === 'petugas' && $user && !empty($user['crew_role']) && in_array($user['crew_role'], ['driver_1', 'driver_2', 'conductor']));

        if ($isCrew) {
            // Check if this crew member is assigned to the schedule
            if ($schedule['driver_1_id'] != $userId && $schedule['driver_2_id'] != $userId && $schedule['conductor_id'] != $userId) {
                return "Anda tidak memiliki hak akses untuk mencetak laporan perjalanan ini.";
            }
        }

        $manifest = $this->bookingSeatModel
            ->select('booking_seats.seat_number, booking_seats.passenger_name, tickets.status as boarding_status, tickets.qr_code, bookings.booking_code')
            ->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->join('tickets', 'tickets.booking_id = bookings.id')
            ->where('bookings.schedule_id', $scheduleId)
            ->where('bookings.booking_status !=', 'cancelled')
            ->orderBy('booking_seats.seat_number', 'ASC')
            ->findAll();

        return view('admin/print_report', [
            'schedule' => $schedule,
            'manifest' => $manifest
        ]);
    }
}
