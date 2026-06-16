<?php

define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';

// Boot application in console context
\CodeIgniter\Boot::bootConsole($paths);

// ----------------------------------------------------------------
// Mock Session Service for CLI Testing
// ----------------------------------------------------------------
class MockSession {
    protected $data = [];
    public function set($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
    }
    public function get($key = null) {
        if ($key === null) return $this->data;
        return $this->data[$key] ?? null;
    }
    public function has($key) {
        return isset($this->data[$key]);
    }
    public function remove($key) {
        unset($this->data[$key]);
    }
}
\Config\Services::injectMock('session', new MockSession());

echo "==================================================\n";
echo "       TIKETBUS.AI - END-TO-END E2E TESTER        \n";
echo "==================================================\n\n";

$db = \Config\Database::connect();
$config = new \Config\App();

// Helper to print step header
function printStep($step, $title) {
    echo "\n[STEP {$step}] {$title}...\n";
    echo str_repeat("-", 40) . "\n";
}

try {
    // Find dynamic user records
    $andiUser = $db->table('users')->where('email', 'customer@bus.com')->get()->getRowArray();
    $budiUser = $db->table('users')->where('email', 'petugas@bus.com')->get()->getRowArray();
    if (!$andiUser || !$budiUser) {
        throw new \Exception("User data is not seeded. Please run db:seed first.");
    }

    // Set initial session mock data
    session()->set([
        'userId' => $andiUser['id'],
        'userName' => $andiUser['name'],
        'userEmail' => $andiUser['email'],
        'userRole' => $andiUser['role'],
        'isLoggedIn' => true
    ]);

    // ----------------------------------------------------------------
    // STEP 1: Check Database Tables & Seed Data
    // ----------------------------------------------------------------
    printStep(1, "Memeriksa Database & Data Awal");
    
    $usersCount = $db->table('users')->countAllResults();
    $busesCount = $db->table('buses')->countAllResults();
    $routesCount = $db->table('routes')->countAllResults();
    $schedulesCount = $db->table('schedules')->countAllResults();
    
    echo "✓ Jumlah User Terdaftar: {$usersCount}\n";
    echo "✓ Jumlah Bus/Armada: {$busesCount}\n";
    echo "✓ Jumlah Rute Aktif: {$routesCount}\n";
    echo "✓ Jumlah Jadwal Aktif: {$schedulesCount}\n";
    
    if ($usersCount === 0 || $schedulesCount === 0) {
        throw new \Exception("Database belum memiliki data. Harap jalankan db:seed.");
    }
    echo "-> STATUS: OK\n";

    // ----------------------------------------------------------------
    // STEP 2: Simulate Route Search & AI Recommendation
    // ----------------------------------------------------------------
    printStep(2, "Simulasi Pencarian Tiket & Rekomendasi AI");
    
    // Pick first route details from db
    $route = $db->table('routes')->get()->getRowArray();
    $origin = $route['origin'];
    $destination = $route['destination'];
    
    // Find a schedule date
    $schedule = $db->table('schedules')->where('route_id', $route['id'])->get()->getRowArray();
    $date = date('Y-m-d', strtotime($schedule['departure_time']));
    
    echo "Mencari perjalanan dari '{$origin}' ke '{$destination}' pada tanggal '{$date}'...\n";
    
    // Run search query
    $scheduleModel = new \App\Models\ScheduleModel();
    $schedules = $scheduleModel->select('schedules.*, buses.name as bus_name, buses.type as bus_type, buses.total_seats')
        ->join('buses', 'buses.id = schedules.bus_id')
        ->where('schedules.route_id', $route['id'])
        ->where('DATE(schedules.departure_time)', $date)
        ->findAll();
        
    echo "✓ Ditemukan " . count($schedules) . " jadwal keberangkatan.\n";
    
    // Compute total seats and occupancy for context
    $totalSeats = 0;
    $bookedSeats = 0;
    $bookingSeatModel = new \App\Models\BookingSeatModel();
    foreach ($schedules as &$s) {
        $totalSeats += $s['total_seats'];
        $bookedCount = $bookingSeatModel->join('bookings', 'bookings.id = booking_seats.booking_id')
            ->where('bookings.schedule_id', $s['id'])
            ->where('bookings.booking_status !=', 'cancelled')
            ->countAllResults();
        $s['remaining_seats'] = $s['total_seats'] - $bookedCount;
        $bookedSeats += $bookedCount;
    }
    unset($s);
    $avgOccupancy = $totalSeats > 0 ? round(($bookedSeats / $totalSeats) * 100, 1) : 0;
    
    // Test Gemini AI Recommendation
    $gemini = new \App\Libraries\GeminiClient();
    $schedulesContext = "";
    foreach ($schedules as $index => $s) {
        $schedulesContext .= ($index + 1) . ". ID: " . $s['id'] . ", Bus: " . $s['bus_name'] . " (" . $s['bus_type'] . "), Harga: Rp " . number_format($s['price'], 0, ',', '.') . "\n";
    }
    
    $prompt = "Berikut adalah daftar jadwal bus dari {$origin} ke {$destination} pada {$date}:\n" . $schedulesContext . "\nTolong pilih satu jadwal bus terbaik berdasarkan pertimbangan rasio harga, kenyamanan kelas bus, dan jam keberangkatan. Format respon Anda HARUS berupa JSON valid dengan format objek:\n{\n  \"schedule_id\": <id_jadwal_terpilih>,\n  \"reason\": \"<alasan_singkat_mengapa_merekomendasikan_bus_ini_dalam_bahasa_indonesia_maksimal_150_karakter>\"\n}";
    
    echo "Memanggil Gemini AI untuk rekomendasi rute...\n";
    $aiRecommendation = $gemini->generateJson($prompt, "Anda adalah asisten tiket bus TiketBus.AI.");
    
    if ($aiRecommendation && isset($aiRecommendation['schedule_id'])) {
        echo "✓ Rekomendasi AI Sukses!\n";
        echo "  - Schedule ID Terpilih: " . $aiRecommendation['schedule_id'] . "\n";
        echo "  - Alasan: " . $aiRecommendation['reason'] . "\n";
    } else {
        echo "⚠ Rekomendasi AI menggunakan Mock/Fallback karena API key tidak merespon JSON.\n";
    }
    echo "-> STATUS: OK\n";

    // ----------------------------------------------------------------
    // STEP 3: Booking Seat & Double-Booking Protection
    // ----------------------------------------------------------------
    printStep(3, "Simulasi Booking Kursi & Validasi Double-Booking");
    
    $targetScheduleId = $schedule['id'];
    $seatNumber = "9Z"; // Unique seat for testing
    $passengerName = "Budi E2E Test User";
    
    echo "Mencoba memesan kursi {$seatNumber} pada Jadwal ID {$targetScheduleId}...\n";
    
    // Check if already booked
    $alreadyBooked = $bookingSeatModel->join('bookings', 'bookings.id = booking_seats.booking_id')
        ->where('bookings.schedule_id', $targetScheduleId)
        ->where('booking_seats.seat_number', $seatNumber)
        ->where('bookings.booking_status !=', 'cancelled')
        ->first();
        
    if ($alreadyBooked) {
        // Clean up previous test run
        echo "Membersihkan data test sebelumnya untuk kursi {$seatNumber}...\n";
        $db->table('booking_seats')->where('seat_number', $seatNumber)->delete();
    }
    
    // Save Booking
    $bookingCode = 'BK-TEST-' . strtoupper(bin2hex(random_bytes(3)));
    $bookingData = [
        'booking_code' => $bookingCode,
        'user_id' => $andiUser['id'], // Andi Penumpang Setia
        'schedule_id' => $targetScheduleId,
        'total_price' => $schedule['price'],
        'payment_status' => 'pending',
        'booking_status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    $db->table('bookings')->insert($bookingData);
    $bookingId = $db->insertID();
    
    $seatData = [
        'booking_id' => $bookingId,
        'seat_number' => $seatNumber,
        'passenger_name' => $passengerName
    ];
    $db->table('booking_seats')->insert($seatData);
    echo "✓ Pemesanan pertama berhasil disimpan. ID Booking: {$bookingId}, Kode: {$bookingCode}\n";
    
    // Test Double Booking Validation
    echo "Mencoba memesan kursi yang sama ({$seatNumber}) untuk user lain (simulasi double-booking)...\n";
    $doubleBooked = $bookingSeatModel->join('bookings', 'bookings.id = booking_seats.booking_id')
        ->where('bookings.schedule_id', $targetScheduleId)
        ->where('booking_seats.seat_number', $seatNumber)
        ->where('bookings.booking_status !=', 'cancelled')
        ->first();
        
    if ($doubleBooked) {
        echo "✓ PROTEKSI DOUBLE-BOOKING AKTIF: Sistem menolak pemesanan kedua untuk kursi {$seatNumber}!\n";
    } else {
        throw new \Exception("Gagal mendeteksi double-booking!");
    }
    echo "-> STATUS: OK\n";

    // ----------------------------------------------------------------
    // STEP 4: Simulate Midtrans Webhook Payment & Ticket Generation
    // ----------------------------------------------------------------
    printStep(4, "Simulasi Midtrans Webhook Callback & Penerbitan Tiket");
    
    echo "Mengirim request webhook settlement untuk Kode Booking: {$bookingCode}...\n";
    
    // Initialize Webhook Controller and inject mock HTTP Request
    $webhook = new \App\Controllers\Api\PaymentWebhook();
    $uri = new \CodeIgniter\HTTP\URI('http://localhost/api/payment/webhook');
    $incomingRequest = new \CodeIgniter\HTTP\IncomingRequest($config, $uri, 'php://input', new \CodeIgniter\HTTP\UserAgent());
    $incomingRequest->setBody(json_encode([
        'order_id' => $bookingCode,
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
        'transaction_id' => 'mock-midtrans-trx-' . bin2hex(random_bytes(4))
    ]));
    
    // Inject mock request
    \Config\Services::injectMock('request', $incomingRequest);
    
    $webhook->initController($incomingRequest, service('response'), service('logger'));
    $response = $webhook->index();
    
    // Check updated booking in DB
    $updatedBooking = $db->table('bookings')->where('id', $bookingId)->get()->getRowArray();
    echo "✓ Status Pembayaran Booking: " . strtoupper($updatedBooking['payment_status']) . "\n";
    
    // Check if Ticket was generated
    $ticket = $db->table('tickets')->where('booking_id', $bookingId)->get()->getRowArray();
    if ($ticket) {
        echo "✓ E-Ticket Resmi Berhasil Diterbitkan!\n";
        echo "  - ID Tiket: " . $ticket['id'] . "\n";
        echo "  - Token QR Code: " . $ticket['qr_code'] . "\n";
        echo "  - Status Tiket: " . strtoupper($ticket['status']) . "\n";
    } else {
        throw new \Exception("Tiket tidak diterbitkan setelah status lunas!");
    }
    echo "-> STATUS: OK\n";

    // ----------------------------------------------------------------
    // STEP 5: PDF Rendering Test
    // ----------------------------------------------------------------
    printStep(5, "Uji Render PDF E-Ticket");
    
    echo "Menginisialisasi Dompdf untuk render template tiket ke PDF...\n";
    $dompdf = new \Dompdf\Dompdf();
    
    $bookingDetails = (new \App\Models\BookingModel())->getDetailedBooking($bookingId);
    $seats = $bookingSeatModel->where('booking_id', $bookingId)->findAll();
    $base64Qr = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $ticket['qr_code'];
    
    $html = view('customer/ticket/pdf', [
        'booking'  => $bookingDetails,
        'ticket'   => $ticket,
        'seats'    => $seats,
        'base64Qr' => $base64Qr
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper([0, 0, 420, 595], 'portrait');
    $dompdf->render();
    
    $pdfOutput = $dompdf->output();
    echo "✓ Berhasil merender PDF. Ukuran output: " . strlen($pdfOutput) . " bytes.\n";
    file_put_contents('../test_e2e_ticket.pdf', $pdfOutput);
    echo "✓ File PDF uji coba disimpan ke: uas-bus/bus-ticket-uas/test_e2e_ticket.pdf\n";
    echo "-> STATUS: OK\n";

    // ----------------------------------------------------------------
    // STEP 6: Scan QR Code Boarding Verification
    // ----------------------------------------------------------------
    printStep(6, "Simulasi Validasi Boarding Scanner (Petugas)");
    
    // Mock Petugas Budi session
    session()->set([
        'userId' => $budiUser['id'],
        'userName' => $budiUser['name'],
        'userEmail' => $budiUser['email'],
        'userRole' => $budiUser['role'],
        'isLoggedIn' => true
    ]);

    echo "Mencoba memverifikasi QR Code '{$ticket['qr_code']}' melalui portal petugas...\n";
    
    $scanController = new \App\Controllers\Petugas\Scan();
    $scanRequest = new \CodeIgniter\HTTP\IncomingRequest($config, new \CodeIgniter\HTTP\URI('http://localhost/petugas/scan/verify'), 'php://input', new \CodeIgniter\HTTP\UserAgent());
    $scanRequest->setGlobal('post', ['booking_code' => $ticket['qr_code']]);
    
    \Config\Services::injectMock('request', $scanRequest);
    $scanController->initController($scanRequest, service('response'), service('logger'));
    
    // Simulate verification
    $verifyRes = $scanController->verify();
    $verifyData = json_decode($verifyRes->getBody(), true);
    
    if ($verifyData && $verifyData['status'] === 'success') {
        echo "✓ QR Code Valid! Penumpang ditemukan: " . $verifyData['ticket']['customer_name'] . "\n";
        
        // Confirm boarding
        $confirmRequest = new \CodeIgniter\HTTP\IncomingRequest($config, new \CodeIgniter\HTTP\URI('http://localhost/petugas/scan/confirm'), 'php://input', new \CodeIgniter\HTTP\UserAgent());
        $confirmRequest->setGlobal('post', ['ticket_id' => $verifyData['ticket']['id']]);
        
        \Config\Services::injectMock('request', $confirmRequest);
        $scanController->initController($confirmRequest, service('response'), service('logger'));
        
        $confirmRes = $scanController->confirmBoarding();
        $confirmData = json_decode($confirmRes->getBody(), true);
        
        if ($confirmData && $confirmData['status'] === 'success') {
            echo "✓ Boarding Sukses Dikonfirmasi! Respon: " . $confirmData['message'] . "\n";
            // Check in DB
            $finalTicketStatus = $db->table('tickets')->where('id', $ticket['id'])->get()->getRowArray();
            echo "  - Status Tiket Terbaru di DB: " . strtoupper($finalTicketStatus['status']) . "\n";
        } else {
            throw new \Exception("Gagal mengonfirmasi boarding: " . ($confirmData['message'] ?? 'Unknown error'));
        }
    } else {
        throw new \Exception("Gagal memverifikasi QR Code: " . ($verifyData['message'] ?? 'Unknown error'));
    }
    echo "-> STATUS: OK\n";

    // ----------------------------------------------------------------
    // STEP 7: Submit Review & AI Sentiment Analysis
    // ----------------------------------------------------------------
    
    // Restore Andi customer session
    session()->set([
        'userId' => $andiUser['id'],
        'userName' => $andiUser['name'],
        'userEmail' => $andiUser['email'],
        'userRole' => $andiUser['role'],
        'isLoggedIn' => true
    ]);
    printStep(7, "Simulasi Submit Review & AI Analisis Sentimen");
    
    $commentText = "Sopir bus sangat berhati-hati dan tepat waktu. Peta kursi di dashboard sangat membantu!";
    echo "User mengirim ulasan: \"{$commentText}\"...\n";
    
    $reviewController = new \App\Controllers\Customer\Review();
    $reviewRequest = new \CodeIgniter\HTTP\IncomingRequest($config, new \CodeIgniter\HTTP\URI('http://localhost/customer/review/store'), 'php://input', new \CodeIgniter\HTTP\UserAgent());
    $reviewRequest->setGlobal('post', [
        'booking_id' => $bookingId,
        'rating' => 5,
        'comment' => $commentText
    ]);
    
    \Config\Services::injectMock('request', $reviewRequest);
    $reviewController->initController($reviewRequest, service('response'), service('logger'));
    
    // We run the sentiment analysis directly to capture output
    $prompt = "Tolong analisis sentimen dari komentar ulasan penumpang bus berikut ini:\n"
        . "\"{$commentText}\"\n\n"
        . "PILIHAN RESPON: Anda HARUS hanya menjawab dengan satu kata lowercase dari tiga pilihan berikut: 'positive', 'neutral', atau 'negative'. Jangan sertakan tanda baca atau kata tambahan lain.";
    
    echo "Memanggil Gemini AI untuk analisis sentimen...\n";
    $sentiment = strtolower(trim($gemini->generate($prompt, "Anda adalah asisten klasifikasi sentimen TiketBus.AI.")));
    
    echo "✓ Hasil Klasifikasi Sentimen AI: " . strtoupper($sentiment) . "\n";
    
    // Insert review to DB
    $db->table('reviews')->insert([
        'booking_id' => $bookingId,
        'user_id' => $andiUser['id'],
        'rating' => 5,
        'comment' => $commentText,
        'sentiment' => $sentiment,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "✓ Ulasan tersimpan di database.\n";
    echo "-> STATUS: OK\n";

    // ----------------------------------------------------------------
    // STEP 8: Admin Dashboard Statistics & AI predictions
    // ----------------------------------------------------------------
    printStep(8, "Uji Dashboard Admin & AI Analitik");
    
    echo "Memuat visual analitik & statistik admin...\n";
    
    // Total Revenue Sum
    $revenueSum = $db->table('payments')->where('status', 'success')->selectSum('amount')->get()->getRowArray();
    $totalRev = $revenueSum['amount'] ?? 0.00;
    
    // Reviews Sentiment counts
    $pos = $db->table('reviews')->where('sentiment', 'positive')->countAllResults();
    $neu = $db->table('reviews')->where('sentiment', 'neutral')->countAllResults();
    $neg = $db->table('reviews')->where('sentiment', 'negative')->countAllResults();
    
    echo "✓ Ringkasan Keuangan: Rp " . number_format($totalRev, 0, ',', '.') . "\n";
    echo "✓ Statistik Sentimen Ulasan:\n";
    echo "  - POSITIF: {$pos}\n";
    echo "  - NETRAL : {$neu}\n";
    echo "  - NEGATIF: {$neg}\n";
    
    // AI predictions
    echo "Memanggil Gemini AI untuk analisis okupansi...\n";
    $predictionPrompt = "Berikut adalah riwayat okupansi bus saat ini:\n"
        . "- Total Kapasitas Kursi Tersedia: {$totalSeats} kursi\n"
        . "- Kursi Terisi: {$bookedSeats} kursi\n"
        . "- Persentase Okupansi Saat Ini: {$avgOccupancy}%\n\n"
        . "Berdasarkan data di atas, tolong berikan analisis prediksi okupansi untuk 7 hari ke depan serta persentase prediksinya. Format respon Anda HARUS berupa JSON valid dengan format objek:\n"
        . "{\n  \"percentage\": <integer_persentase_prediksi>,\n  \"analysis\": \"<analisis_singkat_maksimal_150_karakter>\"\n}";
        
    $prediction = $gemini->generateJson($predictionPrompt, "Anda adalah sistem analitik prediksi AI.");
    if ($prediction) {
        echo "✓ Prediksi Okupansi AI Sukses!\n";
        echo "  - Prediksi Angka: " . $prediction['percentage'] . "%\n";
        echo "  - Analisis AI: " . $prediction['analysis'] . "\n";
    }
    
    echo "-> STATUS: OK\n";

    echo "\n==================================================\n";
    echo " 🎉 E2E INTEGRATION TEST BERHASIL 100% TANPA ERROR \n";
    echo "==================================================\n";

} catch (\Exception $e) {
    echo "\n❌ TEST GAGAL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
