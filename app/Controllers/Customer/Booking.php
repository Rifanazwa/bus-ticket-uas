<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ScheduleModel;
use App\Models\BookingModel;
use App\Models\BookingSeatModel;
use App\Models\PromoModel;

class Booking extends BaseController
{
    protected $scheduleModel;
    protected $bookingModel;
    protected $bookingSeatModel;
    protected $promoModel;

    public function __construct()
    {
        $this->scheduleModel    = new ScheduleModel();
        $this->bookingModel     = new BookingModel();
        $this->bookingSeatModel = new BookingSeatModel();
        $this->promoModel       = new PromoModel();
        helper(['form', 'url']);
    }

    public function create($scheduleId)
    {
        // Cancel expired pending bookings to release seats
        $this->bookingModel->cancelExpiredBookings();

        $schedule = $this->scheduleModel->getDetailedSchedules($scheduleId);
        if (!$schedule) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Jadwal keberangkatan tidak ditemukan.');
        }

        // Check if schedule's departure time has already passed
        $now = date('Y-m-d H:i:s');
        if ($schedule['departure_time'] <= $now) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Mohon maaf, jadwal bus ini sudah lewat. Silakan pesan jadwal di lain hari/waktu.');
        }

        // Fetch already booked seat numbers for this schedule
        $bookedSeats = $this->bookingSeatModel->select('booking_seats.seat_number')
            ->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->where('bookings.schedule_id', $scheduleId)
            ->where('bookings.booking_status !=', 'cancelled')
            ->findAll();
        
        $bookedSeatNumbers = array_column($bookedSeats, 'seat_number');

        return view('customer/booking/create', [
            'title'             => 'Pilih Kursi & Booking - SiTeBus',
            'schedule'          => $schedule,
            'bookedSeatNumbers' => $bookedSeatNumbers
        ]);
    }

    public function store()
    {
        // Cancel expired pending bookings to release seats
        $this->bookingModel->cancelExpiredBookings();

        $scheduleId = $this->request->getPost('schedule_id');
        $seats      = $this->request->getPost('selected_seats'); // Comma-separated string: "1A,1B"
        $passengers = $this->request->getPost('passengers'); // Array of names keyed by seat: ["1A" => "Name A", "1B" => "Name B"]
        $promoCode  = strtoupper(trim($this->request->getPost('promo_code') ?? ''));

        if (!$scheduleId || !$seats || empty($passengers)) {
            return redirect()->back()->withInput()->with('error', 'Silakan pilih kursi dan isi nama penumpang.');
        }

        $schedule = $this->scheduleModel->getDetailedSchedules($scheduleId);
        if (!$schedule) {
            return redirect()->back()->withInput()->with('error', 'Jadwal tidak valid.');
        }

        // Check if schedule's departure time has already passed
        $now = date('Y-m-d H:i:s');
        if ($schedule['departure_time'] <= $now) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Mohon maaf, jadwal bus ini sudah lewat. Silakan pesan jadwal di lain hari/waktu.');
        }

        $selectedSeatNumbers = array_filter(array_map('trim', explode(',', $seats)));
        // Ensure all seats are unique to prevent booking the same seat multiple times in one request
        $uniqueSeatNumbers = array_unique($selectedSeatNumbers);
        if (count($selectedSeatNumbers) !== count($uniqueSeatNumbers)) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak dapat memilih kursi yang sama lebih dari sekali.');
        }

        $selectedSeatNumbers = $uniqueSeatNumbers;

        // 1. Double Booking Check (Server-side validation)
        $alreadyBooked = $this->bookingSeatModel->select('booking_seats.seat_number')
            ->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->where('bookings.schedule_id', $scheduleId)
            ->whereIn('booking_seats.seat_number', $selectedSeatNumbers)
            ->where('bookings.booking_status !=', 'cancelled')
            ->findAll();

        if (!empty($alreadyBooked)) {
            $bookedList = implode(', ', array_column($alreadyBooked, 'seat_number'));
            return redirect()->back()->withInput()->with('error', "Kursi berikut telah dipesan oleh orang lain atau transaksi Anda yang sebelumnya: {$bookedList}. Silakan pilih kursi lain.");
        }

        // 2. Price calculation
        $seatPrice  = $schedule['price'];
        $seatCount  = count($selectedSeatNumbers);
        $totalPrice = $seatPrice * $seatCount;
        $discount   = 0.00;
        $promoId    = null;

        // 3. Apply promo if provided
        if (!empty($promoCode)) {
            $promo = $this->promoModel->where('code', $promoCode)
                ->where('valid_from <=', date('Y-m-d'))
                ->where('valid_until >=', date('Y-m-d'))
                ->where('usage_limit >', 0)
                ->first();

            if ($promo) {
                $promoId = $promo['id'];
                if ($promo['discount_type'] === 'percent') {
                    $discount = $totalPrice * ($promo['discount_value'] / 100);
                } else {
                    $discount = min($promo['discount_value'], $totalPrice); // cannot exceed total price
                }
                $totalPrice = $totalPrice - $discount;
            } else {
                return redirect()->back()->withInput()->with('error', 'Kode promo tidak valid, kedaluwarsa, atau limit telah habis.');
            }
        }

        // 4. Create Booking
        $db = \Config\Database::connect();
        $db->transStart();

        $bookingCode = 'BK-' . strtoupper(bin2hex(random_bytes(4)));

        $bookingData = [
            'booking_code'   => $bookingCode,
            'user_id'        => session()->get('userId'),
            'schedule_id'    => $scheduleId,
            'total_price'    => $totalPrice,
            'payment_status' => 'pending',
            'booking_status' => 'active',
        ];
        
        $this->bookingModel->save($bookingData);
        $bookingId = $this->bookingModel->getInsertID();

        // 5. Save seats
        foreach ($selectedSeatNumbers as $seatNum) {
            $seatData = [
                'booking_id'     => $bookingId,
                'seat_number'    => $seatNum,
                'passenger_name' => $passengers[$seatNum] ?? 'Penumpang',
            ];
            $this->bookingSeatModel->save($seatData);
        }

        // 6. Decrement promo limit if applied
        if ($promoId) {
            $db->table('promos')
                ->where('id', $promoId)
                ->decrement('usage_limit', 1);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pemesanan. Silakan coba lagi.');
        }

        return redirect()->to(base_url('customer/payment/' . $bookingId));
    }

    public function checkPromo()
    {
        $json = $this->request->getJSON(true);
        $promoCode = strtoupper(trim($json['promo_code'] ?? ''));
        $totalPrice = (float)($json['total_price'] ?? 0);

        if (empty($promoCode)) {
            return $this->response->setJSON([
                'valid'   => false,
                'message' => 'Kode promo tidak boleh kosong.'
            ]);
        }

        $promo = $this->promoModel->where('code', $promoCode)
            ->where('valid_from <=', date('Y-m-d'))
            ->where('valid_until >=', date('Y-m-d'))
            ->where('usage_limit >', 0)
            ->first();

        if (!$promo) {
            return $this->response->setJSON([
                'valid'   => false,
                'message' => 'Kode promo tidak valid, kedaluwarsa, atau limit telah habis.'
            ]);
        }

        $discount = 0.00;
        if ($promo['discount_type'] === 'percent') {
            $discount = $totalPrice * ($promo['discount_value'] / 100);
        } else {
            $discount = min($promo['discount_value'], $totalPrice);
        }

        return $this->response->setJSON([
            'valid'           => true,
            'code'            => $promo['code'],
            'discount_type'   => $promo['discount_type'],
            'discount_value'  => (float)$promo['discount_value'],
            'discount_amount' => $discount,
            'final_price'     => $totalPrice - $discount
        ]);
    }
}
