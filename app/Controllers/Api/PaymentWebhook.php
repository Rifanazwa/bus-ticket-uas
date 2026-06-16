<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\PaymentModel;
use App\Models\TicketModel;

class PaymentWebhook extends BaseController
{
    protected $bookingModel;
    protected $paymentModel;
    protected $ticketModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
        $this->paymentModel = new PaymentModel();
        $this->ticketModel  = new TicketModel();
    }

    public function index()
    {
        // Get JSON body from Midtrans
        $json = $this->request->getJSON(true);

        if (empty($json)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Empty payload'])->setStatusCode(400);
        }

        $orderId           = $json['order_id'] ?? null;
        $transactionStatus = $json['transaction_status'] ?? null;
        $paymentType       = $json['payment_type'] ?? 'unknown';
        $transactionId     = $json['transaction_id'] ?? null;

        if (!$orderId || !$transactionStatus) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid parameters'])->setStatusCode(400);
        }

        // Fetch corresponding booking by code
        $booking = $this->bookingModel->where('booking_code', $orderId)->first();
        if (!$booking) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Booking not found'])->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $paymentStatus = 'pending';
        $bookingStatus = 'active';
        $paymentSuccess = false;

        // Process status
        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            $paymentStatus = 'paid';
            $bookingStatus = 'active';
            $paymentSuccess = true;
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel', 'failure'])) {
            $paymentStatus = 'failed';
            $bookingStatus = 'cancelled';
        }

        // 1. Update Booking
        $this->bookingModel->update($booking['id'], [
            'payment_status' => $paymentStatus,
            'booking_status' => $bookingStatus,
        ]);

        // 2. Insert or Update Payment
        $existingPayment = $this->paymentModel->where('booking_id', $booking['id'])->first();
        $paymentData = [
            'booking_id'     => $booking['id'],
            'method'         => $paymentType,
            'amount'         => $booking['total_price'],
            'transaction_id' => $transactionId,
            'status'         => $paymentSuccess ? 'success' : 'failed',
            'paid_at'        => $paymentSuccess ? date('Y-m-d H:i:s') : null,
        ];

        if ($existingPayment) {
            $this->paymentModel->update($existingPayment['id'], $paymentData);
        } else {
            $this->paymentModel->save($paymentData);
        }

        // 3. Generate Ticket if payment is successful
        if ($paymentSuccess) {
            $existingTicket = $this->ticketModel->where('booking_id', $booking['id'])->first();
            if (!$existingTicket) {
                // Generate a unique token for the QR code
                $qrToken = 'TKT-' . strtoupper(bin2hex(random_bytes(6)));
                
                $ticketData = [
                    'booking_id' => $booking['id'],
                    'qr_code'    => $qrToken,
                    'status'     => 'issued',
                    'issued_at'  => date('Y-m-d H:i:s'),
                ];
                $this->ticketModel->save($ticketData);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Transaction failed'])->setStatusCode(500);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Status updated successfully']);
    }
}
