<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\BookingModel;
use App\Models\BookingSeatModel;

class Scan extends BaseController
{
    protected $ticketModel;
    protected $bookingModel;
    protected $bookingSeatModel;

    public function __construct()
    {
        $this->ticketModel      = new TicketModel();
        $this->bookingModel     = new BookingModel();
        $this->bookingSeatModel = new BookingSeatModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        return view('petugas/scan', [
            'title' => 'Portal Boarding Petugas - SiTeBus'
        ]);
    }

    public function verify()
    {
        $code = trim($this->request->getPost('booking_code') ?? '');

        if (empty($code)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Kode booking atau QR Code tidak boleh kosong.'
            ]);
        }

        // Try to find by ticket QR Code first
        $ticket = $this->ticketModel->select('tickets.*, bookings.booking_code, bookings.payment_status, bookings.booking_status')
            ->join('bookings', 'bookings.id = tickets.booking_id')
            ->where('tickets.qr_code', $code)
            ->first();

        // If not found, try to find by Booking Code
        if (!$ticket) {
            $booking = $this->bookingModel->where('booking_code', $code)->first();
            if ($booking) {
                $ticket = $this->ticketModel->where('booking_id', $booking['id'])->first();
            }
        }

        if (!$ticket) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tiket atau Kode Booking tidak ditemukan.'
            ]);
        }

        // Validate payment status
        if ($ticket['payment_status'] !== 'paid') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tiket ini belum lunas. Pembayaran status: ' . strtoupper($ticket['payment_status'])
            ]);
        }

        // Get detailed booking info
        $details = $this->ticketModel->getDetailedTicket($ticket['id']);

        // Check if the logged-in petugas has a crew role and verify assignment
        $userId   = session()->get('userId');
        $userRole = session()->get('userRole');
        
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        
        $warning = null;
        $isCrew  = ($userRole === 'petugas' && $user && !empty($user['crew_role']) && in_array($user['crew_role'], ['driver_1', 'driver_2', 'conductor']));
        
        if ($isCrew) {
            $ticketScheduleId = $details['schedule_id'] ?? null;
            if (!$ticketScheduleId) {
                $booking = $this->bookingModel->find($ticket['booking_id']);
                $ticketScheduleId = $booking['schedule_id'];
            }
            
            $scheduleModel = new \App\Models\ScheduleModel();
            $today = date('Y-m-d');
            
            $assignedSchedules = $scheduleModel->getDetailedSchedules(null, $today, $userId);
            $assignedScheduleIds = array_column($assignedSchedules, 'id');
            
            if (!in_array($ticketScheduleId, $assignedScheduleIds)) {
                if (empty($assignedSchedules)) {
                    $warning = "Anda tidak ditugaskan pada jadwal perjalanan manapun hari ini. Penumpang terdaftar pada " . $details['bus_name'] . " (" . $details['origin'] . " -> " . $details['destination'] . ") keberangkatan " . date('H:i', strtotime($details['departure_time'])) . " WIB.";
                } else {
                    $firstSched = $assignedSchedules[0];
                    $warning = "Salah Armada! Penumpang terdaftar pada " . $details['bus_name'] . " (" . $details['origin'] . " -> " . $details['destination'] . "), sedangkan Anda hari ini ditugaskan untuk " . $firstSched['bus_name'] . " (" . $firstSched['origin'] . " -> " . $firstSched['destination'] . ").";
                }
            }
        }

        $seats = $this->bookingSeatModel->where('booking_id', $ticket['booking_id'])->findAll();

        return $this->response->setJSON([
            'status'    => 'success',
            'ticket'    => $details,
            'seats'     => $seats,
            'isBoarded' => $ticket['status'] === 'boarded',
            'warning'   => $warning
        ]);
    }

    public function confirmBoarding()
    {
        $ticketId = $this->request->getPost('ticket_id');

        if (!$ticketId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID Tiket tidak valid.'
            ]);
        }

        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tiket tidak ditemukan.'
            ]);
        }

        if ($ticket['status'] === 'boarded') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Penumpang ini sudah melakukan boarding sebelumnya.'
            ]);
        }

        $userId = session()->get('userId');
        $updateData = ['status' => 'boarded'];

        // Audit Logging: Check if scanned_by column exists in table to avoid errors if migration has not been run yet
        $db = \Config\Database::connect();
        if ($db->fieldExists('scanned_by', 'tickets')) {
            $updateData['scanned_by'] = $userId;
            $updateData['scanned_at'] = date('Y-m-d H:i:s');
        }

        // Update status to boarded
        if ($this->ticketModel->update($ticketId, $updateData)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Boarding berhasil dikonfirmasi. Selamat jalan penumpang!'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal melakukan konfirmasi boarding.'
        ]);
    }
}
