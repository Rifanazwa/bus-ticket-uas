<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\BookingSeatModel;
use App\Libraries\GeminiClient;

class Anomalies extends BaseController
{
    protected $bookingModel;
    protected $bookingSeatModel;
    protected $geminiClient;

    public function __construct()
    {
        $this->bookingModel     = new BookingModel();
        $this->bookingSeatModel = new BookingSeatModel();
        $this->geminiClient     = new GeminiClient();
        helper(['url', 'session']);
    }

    public function index()
    {
        $anomalies = $this->gatherDetailedAnomalies();
        
        return view('admin/anomalies', [
            'title'     => 'AI Deteksi Anomali',
            'subtitle'  => 'Sistem Deteksi Fraud & Keamanan Booking',
            'anomalies' => $anomalies,
        ]);
    }

    public function resolve()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $key = $this->request->getPost('key');
        if ($key) {
            $resolved = session()->get('resolved_anomalies') ?? [];
            if (!in_array($key, $resolved)) {
                $resolved[] = $key;
                session()->set('resolved_anomalies', $resolved);
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Anomali berhasil diselesaikan.']);
        }

        return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Key tidak valid.']);
    }

    public function reset()
    {
        session()->remove('resolved_anomalies');
        return redirect()->to(base_url('admin/anomalies'))->with('success', 'Semua anomali di-reset.');
    }

    private function gatherDetailedAnomalies(): array
    {
        $anomalies = [];
        $db = \Config\Database::connect();
        $resolved = session()->get('resolved_anomalies') ?? [];

        // --- Rule 1: Satu user beli >2 kursi pada schedule yang sama --------
        $suspicious = $db->query("
            SELECT 
                b.id AS booking_id,
                b.user_id,
                b.schedule_id,
                u.name  AS user_name,
                u.email AS user_email,
                s.departure_time,
                r.origin,
                r.destination,
                COUNT(bs.id) AS seat_count
            FROM bookings b
            JOIN users u          ON u.id  = b.user_id
            JOIN schedules s      ON s.id  = b.schedule_id
            JOIN routes r         ON r.id  = s.route_id
            JOIN booking_seats bs ON bs.booking_id = b.id
            WHERE b.booking_status != 'cancelled'
            GROUP BY b.user_id, b.schedule_id
            HAVING seat_count > 2
            ORDER BY seat_count DESC
        ")->getResultArray();

        foreach ($suspicious as $sb) {
            $key = 'massal_' . $sb['user_id'] . '_' . $sb['schedule_id'];
            if (in_array($key, $resolved)) continue;

            $anomalies[] = [
                'key'      => $key,
                'type'     => 'Pembelian Massal',
                'severity' => 'high',
                'user'     => $sb['user_name'],
                'email'    => $sb['user_email'],
                'time'     => date('d M Y H:i', strtotime($sb['departure_time'])) . ' WIB',
                'details'  => "Membeli {$sb['seat_count']} kursi sekaligus pada rute "
                            . "{$sb['origin']} → {$sb['destination']}, "
                            . "keberangkatan " . date('d M Y H:i', strtotime($sb['departure_time'])) . " WIB.",
            ];
        }

        // --- Rule 2: Booking pending terlalu lama (>30 menit) ---------------
        $expiredPending = $db->query("
            SELECT b.booking_code, u.name, u.email, b.created_at, b.total_price
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            WHERE b.payment_status = 'pending'
              AND b.booking_status != 'cancelled'
              AND b.created_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ORDER BY b.created_at ASC
            LIMIT 10
        ")->getResultArray();

        foreach ($expiredPending as $ep) {
            $key = 'pending_' . $ep['booking_code'];
            if (in_array($key, $resolved)) continue;

            $anomalies[] = [
                'key'      => $key,
                'type'     => 'Pembayaran Tertunda',
                'severity' => 'medium',
                'user'     => $ep['name'],
                'email'    => $ep['email'],
                'time'     => date('d M H:i', strtotime($ep['created_at'])) . ' WIB',
                'details'  => "Booking {$ep['booking_code']} (Rp " . number_format($ep['total_price'], 0, ',', '.') . ") "
                            . "pending sejak " . date('d M H:i', strtotime($ep['created_at'])) . " WIB, belum dibayar >30 menit.",
            ];
        }

        // --- Rule 3: Satu user beli tiket di >3 jadwal berbeda dalam 24 jam -
        $multiSchedule = $db->query("
            SELECT 
                b.user_id,
                u.name  AS user_name,
                u.email AS user_email,
                COUNT(DISTINCT b.schedule_id) AS schedule_count,
                SUM(b.total_price) AS total_spent
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            WHERE b.payment_status IN ('paid','pending')
              AND b.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY b.user_id
            HAVING schedule_count > 3
        ")->getResultArray();

        foreach ($multiSchedule as $ms) {
            $key = 'multi_' . $ms['user_id'];
            if (in_array($key, $resolved)) continue;

            $anomalies[] = [
                'key'      => $key,
                'type'     => 'Aktivitas Mencurigakan',
                'severity' => 'high',
                'user'     => $ms['user_name'],
                'email'    => $ms['user_email'],
                'time'     => '24 Jam Terakhir',
                'details'  => "Memesan {$ms['schedule_count']} jadwal berbeda dalam 24 jam terakhir "
                            . "(Total: Rp " . number_format($ms['total_spent'], 0, ',', '.') . "). Kemungkinan reseller/bot.",
            ];
        }

        // --- Rule 4: AI Gemini deep-analysis jika ada anomali ---------------
        if (!empty($anomalies)) {
            $anomalySummary = implode("\n", array_map(fn($a) =>
                "- [{$a['type']}] {$a['user']}: {$a['details']}", $anomalies
            ));

            $aiPrompt = "Berikut daftar anomali booking yang terdeteksi oleh sistem pada platform tiket bus:\n\n"
                . $anomalySummary . "\n\n"
                . "Berikan analisis singkat risiko bisnis dan rekomendasi tindakan yang harus diambil admin. "
                . "Maksimal 100 kata, dalam Bahasa Indonesia, nada profesional.";

            $aiAnalysis = $this->geminiClient->generate($aiPrompt, "Anda adalah sistem fraud detection AI untuk PO Bus.");

            // Handle API failure fallback
            if (!$aiAnalysis || str_starts_with($aiAnalysis, '[') || trim($aiAnalysis) === '') {
                $recommendations = [];
                $hasMassal = false;
                $hasPending = false;
                $hasMulti = false;
                foreach ($anomalies as $a) {
                    if ($a['type'] === 'Pembelian Massal') $hasMassal = true;
                    if ($a['type'] === 'Pembayaran Tertunda') $hasPending = true;
                    if ($a['type'] === 'Aktivitas Mencurigakan') $hasMulti = true;
                }
                if ($hasMassal) {
                    $recommendations[] = "Lakukan verifikasi manual terhadap pembelian massal (>2 kursi) untuk mencegah calo.";
                }
                if ($hasPending) {
                    $recommendations[] = "Aktifkan pembatalan otomatis untuk transaksi pending >30 menit agar melepas kursi kembali ke sistem.";
                }
                if ($hasMulti) {
                    $recommendations[] = "Batasi jumlah pesanan aktif per akun dalam 24 jam guna menekan bot.";
                }
                $aiAnalysis = "Rekomendasi Keamanan: " . implode(" ", $recommendations);
            }

            // Attach AI analysis as a special entry
            $anomalies[] = [
                'key'      => 'ai_recommendation',
                'type'     => 'Rekomendasi AI',
                'severity' => 'info',
                'user'     => 'Gemini AI Summary',
                'email'    => '',
                'time'     => date('H:i:s') . ' WIB',
                'details'  => $aiAnalysis,
            ];
        }

        return $anomalies;
    }
}
