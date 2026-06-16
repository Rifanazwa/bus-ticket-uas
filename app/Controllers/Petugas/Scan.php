<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use App\Models\BookingModel;
use App\Models\BookingSeatModel;
use App\Models\ScheduleModel;

class Scan extends BaseController
{
    protected $ticketModel;
    protected $bookingModel;
    protected $bookingSeatModel;
    protected $scheduleModel;

    public function __construct()
    {
        $this->ticketModel      = new TicketModel();
        $this->bookingModel     = new BookingModel();
        $this->bookingSeatModel = new BookingSeatModel();
        $this->scheduleModel    = new ScheduleModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Auto-generate schedules for today and the next 2 days to ensure active schedule data
        for ($i = 0; $i < 3; $i++) {
            $targetDate = date('Y-m-d', strtotime("+$i days"));
            $this->scheduleModel->checkAndGenerateSchedulesForDate($targetDate);
        }

        $today = date('Y-m-d');
        $schedules = $this->scheduleModel->getDetailedSchedules(null, $today);

        // Sort by departure_time ascending
        usort($schedules, fn($a, $b) => strtotime($a['departure_time']) <=> strtotime($b['departure_time']));

        return view('petugas/scan', [
            'title'     => 'Portal Boarding Petugas - SiTeBus',
            'schedules' => $schedules
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

        $seats = $this->bookingSeatModel->where('booking_id', $ticket['booking_id'])->findAll();

        return $this->response->setJSON([
            'status'    => 'success',
            'ticket'    => $details,
            'seats'     => $seats,
            'isBoarded' => $ticket['status'] === 'boarded'
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

        // Update status to boarded
        if ($this->ticketModel->update($ticketId, ['status' => 'boarded'])) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Boarding berhasil dikonfirmasi. Selamat jalan penumpang!',
                'schedule_id' => $this->bookingModel->find($ticket['booking_id'])['schedule_id'] ?? null
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal melakukan konfirmasi boarding.'
        ]);
    }

    public function manifest($scheduleId)
    {
        $schedule = $this->scheduleModel->getDetailedSchedules($scheduleId);
        if (!$schedule) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jadwal tidak ditemukan.']);
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

        $manifest = $this->bookingSeatModel
            ->select('booking_seats.seat_number, booking_seats.passenger_name, tickets.status as boarding_status, tickets.qr_code, bookings.booking_code')
            ->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->join('tickets', 'tickets.booking_id = bookings.id')
            ->where('bookings.schedule_id', $scheduleId)
            ->where('bookings.booking_status !=', 'cancelled')
            ->orderBy('booking_seats.seat_number', 'ASC')
            ->findAll();

        return view('petugas/print_report', [
            'schedule' => $schedule,
            'manifest' => $manifest
        ]);
    }
}
