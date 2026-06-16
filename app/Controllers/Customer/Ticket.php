<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\BookingSeatModel;
use App\Models\TicketModel;
use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class Ticket extends BaseController
{
    protected $bookingModel;
    protected $bookingSeatModel;
    protected $ticketModel;

    public function __construct()
    {
        $this->bookingModel     = new BookingModel();
        $this->bookingSeatModel = new BookingSeatModel();
        $this->ticketModel      = new TicketModel();
        helper(['form', 'url']);
    }

    public function download($bookingId)
    {
        $booking = $this->bookingModel->getDetailedBooking($bookingId);
        if (!$booking) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Pemesanan tidak ditemukan.');
        }

        // Security check: Only the owner or admin/petugas can view/download
        if (session()->get('userRole') === 'customer' && $booking['user_id'] != session()->get('userId')) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Anda tidak memiliki hak akses untuk tiket ini.');
        }

        $ticket = $this->ticketModel->where('booking_id', $bookingId)->first();
        if (!$ticket) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Tiket belum diterbitkan. Pastikan status pemesanan sudah lunas.');
        }

        $seats = $this->bookingSeatModel->where('booking_id', $bookingId)->findAll();

        // Generate QR Code as Base64 Data URI
        $base64Qr = '';
        try {
            $builder = new Builder();
            $result = $builder->build(
                writer: new PngWriter(),
                writerOptions: [],
                data: $ticket['qr_code'],
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 150,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin
            );
            
            $base64Qr = $result->getDataUri();
        } catch (\Throwable $e) {
            // Fallback to a mock QR code/placeholder or online QR generator if endroid fails
            $base64Qr = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $ticket['qr_code'];
        }

        // Fetch payment details
        $paymentModel = new \App\Models\PaymentModel();
        $payment = $paymentModel->where('booking_id', $bookingId)->first();

        // Instantiate and use the dompdf class
        $dompdf = new Dompdf();
        
        // Load HTML content
        $html = view('customer/ticket/pdf', [
            'booking'  => $booking,
            'ticket'   => $ticket,
            'seats'    => $seats,
            'base64Qr' => $base64Qr,
            'payment'  => $payment
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 420, 595], 'portrait'); // A5 size portrait for a neat boarding pass look
        $dompdf->render();

        // Output the generated PDF to Browser
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setBody($dompdf->output());
    }
}
