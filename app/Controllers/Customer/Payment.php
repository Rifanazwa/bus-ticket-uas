<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\BookingSeatModel;
use App\Models\TicketModel;
use App\Models\PaymentModel;

class Payment extends BaseController
{
    protected $bookingModel;
    protected $bookingSeatModel;
    protected $ticketModel;
    protected $paymentModel;

    public function __construct()
    {
        $this->bookingModel     = new BookingModel();
        $this->bookingSeatModel = new BookingSeatModel();
        $this->ticketModel      = new TicketModel();
        $this->paymentModel     = new PaymentModel();
        helper(['form', 'url']);
    }

    public function index($bookingId)
    {
        $booking = $this->bookingModel->getDetailedBooking($bookingId);
        if (!$booking) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Pemesanan tidak ditemukan.');
        }

        // Security check — only owner can pay
        if ($booking['user_id'] != session()->get('userId')) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Anda tidak memiliki akses ke pemesanan ini.');
        }

        // Only allow payment if booking is pending
        if ($booking['payment_status'] !== 'pending') {
            return redirect()->to(base_url('customer/home'))->with('error', 'Pemesanan ini sudah diproses.');
        }

        // Fetch selected seats
        $seats = $this->bookingSeatModel->where('booking_id', $bookingId)->findAll();

        // Configure Midtrans
        \Midtrans\Config::$serverKey    = env('midtrans.serverKey') ?: 'SB-Mid-server-YOUR_SERVER_KEY';
        \Midtrans\Config::$clientKey    = env('midtrans.clientKey') ?: 'SB-Mid-client-YOUR_CLIENT_KEY';
        \Midtrans\Config::$isProduction = env('midtrans.isProduction') ?: false;
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        $snapToken    = '';
        $errorMessage = '';

        try {
            $params = [
                'transaction_details' => [
                    'order_id'     => $booking['booking_code'],
                    'gross_amount' => (int) $booking['total_price'],
                ],
                'customer_details' => [
                    'first_name' => $booking['customer_name'],
                    'email'      => $booking['customer_email'],
                    'phone'      => $booking['customer_phone'],
                ],
                'item_details' => [
                    [
                        'id'       => $booking['schedule_id'],
                        'price'    => (int) ($booking['total_price'] / max(count($seats), 1)),
                        'quantity' => count($seats),
                        'name'     => 'Tiket Bus: ' . $booking['origin'] . ' ke ' . $booking['destination'],
                    ]
                ]
            ];

            if (strpos(\Midtrans\Config::$serverKey, 'YOUR_SERVER_KEY') !== false) {
                $snapToken = 'MOCK-SNAP-TOKEN-' . bin2hex(random_bytes(10));
            } else {
                $snapToken = \Midtrans\Snap::getSnapToken($params);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $snapToken = 'MOCK-SNAP-TOKEN-' . bin2hex(random_bytes(10));
        }

        return view('customer/payment/index', [
            'title'        => 'Pembayaran Tiket - SiTeBus',
            'booking'      => $booking,
            'seats'        => $seats,
            'snapToken'    => $snapToken,
            'clientKey'    => \Midtrans\Config::$clientKey,
            'errorMessage' => $errorMessage
        ]);
    }

    /**
     * After webhook fires (real Midtrans) or simulation, redirect here.
     * We also ensure a ticket is generated if somehow webhook missed.
     */
    public function success()
    {
        // Grab last booking of the logged-in user that is paid and has no ticket yet
        $userId = session()->get('userId');

        // Try to find the most recently paid booking that might not yet have a ticket
        $db = \Config\Database::connect();
        $booking = $db->table('bookings')
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->orderBy('updated_at', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $bookingId = null;

        if ($booking) {
            $bookingId = $booking['id'];
            // Ensure ticket exists
            $existingTicket = $this->ticketModel->where('booking_id', $bookingId)->first();
            if (!$existingTicket) {
                $qrToken = 'TKT-' . strtoupper(bin2hex(random_bytes(6)));
                $this->ticketModel->save([
                    'booking_id' => $bookingId,
                    'qr_code'    => $qrToken,
                    'status'     => 'issued',
                    'issued_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return view('customer/payment/success', [
            'title'     => 'Pembayaran Berhasil - SiTeBus',
            'bookingId' => $bookingId,
        ]);
    }
}
