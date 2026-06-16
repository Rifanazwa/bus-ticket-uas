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

        // (Assignment enforcement removed to allow all officers to scan all tickets)

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

        // (Assignment enforcement removed to allow all officers to confirm all boarding tickets)

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
                'message' => 'Boarding berhasil dikonfirmasi. Selamat jalan penumpang!'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal melakukan konfirmasi boarding.'
        ]);
    }
}
