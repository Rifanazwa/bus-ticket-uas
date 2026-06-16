<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ChatLogModel;
use App\Models\ScheduleModel;
use App\Models\PromoModel;
use App\Libraries\GeminiClient;

class Chatbot extends BaseController
{
    protected $chatLogModel;
    protected $scheduleModel;
    protected $promoModel;
    protected $geminiClient;

    public function __construct()
    {
        $this->chatLogModel  = new ChatLogModel();
        $this->scheduleModel = new ScheduleModel();
        $this->promoModel    = new PromoModel();
        $this->geminiClient  = new GeminiClient();
        helper(['url', 'form']);
    }

    public function send()
    {
        $message   = $this->request->getPost('message');
        $sessionId = $this->request->getPost('session_id') ?: 'sess_' . session_id();

        if (empty(trim($message))) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Pesan tidak boleh kosong.'
            ])->setStatusCode(400);
        }

        $lowerMsg = strtolower($message);

        // Reset chatbot context if requested
        if (preg_match('/(reset|bersih|rute baru|ganti rute|pencarian baru)/i', $lowerMsg)) {
            session()->remove('chatbot_origin');
            session()->remove('chatbot_destination');
            session()->remove('chatbot_date');
        }

        // 1. Parse cities from message
        $citiesList = ['jakarta', 'bandung', 'surabaya', 'yogyakarta', 'cirebon', 'indramayu', 'tasikmalaya', 'garut', 'semarang', 'malang'];
        $mentionedCities = [];
        foreach ($citiesList as $city) {
            $pos = strpos($lowerMsg, $city);
            if ($pos !== false) {
                $mentionedCities[$city] = $pos;
            }
        }
        asort($mentionedCities);
        $mentionedCities = array_keys($mentionedCities);

        // Update or retrieve cities from session context
        if (count($mentionedCities) >= 2) {
            $origin = $mentionedCities[0];
            $destination = $mentionedCities[1];
            session()->set('chatbot_origin', $origin);
            session()->set('chatbot_destination', $destination);
        } elseif (count($mentionedCities) === 1) {
            $city = $mentionedCities[0];
            $prevOrigin = session()->get('chatbot_origin');
            if ($prevOrigin && $prevOrigin !== $city) {
                $origin = $city;
                $destination = null;
                session()->set('chatbot_origin', $origin);
                session()->remove('chatbot_destination');
            } else {
                $origin = $city;
                $destination = session()->get('chatbot_destination');
                session()->set('chatbot_origin', $origin);
            }
        } else {
            $origin = session()->get('chatbot_origin');
            $destination = session()->get('chatbot_destination');
        }

        // 2. Parse date from message
        $targetDate = null;
        if (preg_match('/(hari\s*ini|today)/i', $lowerMsg)) {
            $targetDate = date('Y-m-d');
        } elseif (preg_match('/(besok|tomorrow)/i', $lowerMsg)) {
            $targetDate = date('Y-m-d', strtotime('+1 day'));
        } elseif (preg_match('/(lusa)/i', $lowerMsg)) {
            $targetDate = date('Y-m-d', strtotime('+2 days'));
        } elseif (preg_match('/(\d{1,2})\s*(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember|jan|feb|mar|apr|mei|jun|jul|agu|sep|okt|nov|des)/i', $lowerMsg, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthStr = $matches[2];
            $monthsMap = [
                'januari' => '01', 'jan' => '01',
                'februari' => '02', 'feb' => '02',
                'maret' => '03', 'mar' => '03',
                'april' => '04', 'apr' => '04',
                'mei' => '05',
                'juni' => '06', 'jun' => '06',
                'juli' => '07', 'jul' => '07',
                'agustus' => '08', 'agu' => '08',
                'september' => '09', 'sep' => '09',
                'oktober' => '10', 'okt' => '10',
                'november' => '11', 'nov' => '11',
                'desember' => '12', 'des' => '12',
            ];
            $month = $monthsMap[$monthStr] ?? '06';
            $year = '2026';
            if (preg_match('/202\d/', $message, $yearMatch)) {
                $year = $yearMatch[0];
            }
            $targetDate = "{$year}-{$month}-{$day}";
        }

        // Update or retrieve date from session context
        if ($targetDate) {
            session()->set('chatbot_date', $targetDate);
        } else {
            $targetDate = session()->get('chatbot_date');
        }

        // Trigger dynamic generator if date is set and in the future
        if ($targetDate && $targetDate > '2026-06-30') {
            $this->scheduleModel->checkAndGenerateSchedulesForDate($targetDate);
        }

        // 3. Query schedules with filters
        $query = $this->scheduleModel->select('schedules.*, routes.origin, routes.destination, buses.name as bus_name, buses.type as bus_type')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->where('schedules.status', 'scheduled');

        if ($targetDate) {
            $query->where('DATE(schedules.departure_time)', $targetDate);
        }

        if ($origin && $destination) {
            $query->where('routes.origin', ucfirst($origin))
                  ->where('routes.destination', ucfirst($destination));
        } elseif ($origin) {
            $query->groupStart()
                    ->where('routes.origin', ucfirst($origin))
                    ->orWhere('routes.destination', ucfirst($origin))
                  ->groupEnd();
        }

        // If no criteria specified, get upcoming schedules from now/today
        if (!$targetDate && !$origin) {
            $query->where('schedules.departure_time >=', date('Y-m-d H:i:s'))
                  ->orderBy('schedules.departure_time', 'ASC')
                  ->limit(10);
        } else {
            $query->orderBy('schedules.departure_time', 'ASC')
                  ->limit(25);
        }

        $schedules = $query->findAll();

        $schedulesText = "";
        foreach ($schedules as $s) {
            $schedulesText .= "- Rute: " . $s['origin'] . " ke " . $s['destination'] 
                . " | Bus: " . $s['bus_name'] . " (" . $s['bus_type'] . ")"
                . " | Berangkat: " . date('d M Y H:i', strtotime($s['departure_time'])) 
                . " | Harga: Rp " . number_format($s['price'], 0, ',', '.') . "\n";
        }

        // 4. Fetch unique routes list as context
        $routeModel = new \App\Models\RouteModel();
        $routes = $routeModel->select('origin, destination')->distinct()->findAll();
        $routesText = "";
        $routeGroups = [];
        foreach ($routes as $r) {
            $routeGroups[$r['origin']][] = $r['destination'];
        }
        foreach ($routeGroups as $orig => $dests) {
            $routesText .= "- Dari " . $orig . " ke: " . implode(', ', $dests) . "\n";
        }

        // 5. Fetch active promos context
        $promos = $this->promoModel->where('valid_until >=', date('Y-m-d'))
                                   ->where('usage_limit >', 0)
                                   ->findAll();
        $promosText = "";
        foreach ($promos as $p) {
            $promosText .= "- Kode Promo: " . $p['code'] 
                . " | Tipe: " . ($p['discount_type'] === 'percent' ? 'Diskon ' . $p['discount_value'] . '%' : 'Potongan Rp ' . number_format($p['discount_value'], 0, ',', '.')) 
                . " | Valid s.d: " . date('d M Y', strtotime($p['valid_until'])) . "\n";
        }

        // Build current search state text
        $filterStateText = "=== STATUS PENCARIAN SAAT INI ===\n";
        $filterStateText .= "Origin: " . ($origin ? ucfirst($origin) : "(Belum ditentukan)") . "\n";
        $filterStateText .= "Destination: " . ($destination ? ucfirst($destination) : "(Belum ditentukan)") . "\n";
        $filterStateText .= "Tanggal: " . ($targetDate ? date('d M Y', strtotime($targetDate)) : "(Belum ditentukan)") . "\n\n";

        // 6. Compile System Instruction / Context
        $systemInstruction = "Anda adalah Asisten Customer Service ramah yang bernama 'SiTeBus Helper' dari platform SiTeBus.\n"
            . "Tugas Anda adalah melayani pertanyaan calon penumpang dengan sopan, bersahabat, ringkas, dan jelas dalam bahasa Indonesia.\n\n"
            . "Gunakan informasi context aktual dari database kami di bawah ini untuk menjawab pertanyaan tentang jadwal dan promo:\n"
            . $filterStateText
            . "--- JADWAL KEBERANGKATAN TERSEDIA ---\n" . ($schedulesText ?: "Tidak ada jadwal keberangkatan aktif yang cocok saat ini.\n") . "\n"
            . "--- RUTE YANG DILAYANI PO SITEBUS ---\n" . $routesText . "\n"
            . "--- PROMO AKTIF ---\n" . ($promosText ?: "Tidak ada promo aktif saat ini.\n") . "\n"
            . "--- FAQ & ATURAN PLATFORM ---\n"
            . "- Pembatalan / Refund: Tiket yang sudah dibayar dapat direfund/dibatalkan maksimal H-1 (24 jam sebelum keberangkatan) dengan potongan biaya 10%. Pembatalan kurang dari 24 jam tidak mendapat refund.\n"
            . "- Cara Cetak Tiket: Setelah lunas, penumpang dapat mengunduh e-ticket resmi format PDF di halaman utama dashboard penumpang mereka.\n"
            . "- Cara Naik Bus (Boarding): Tunjukkan file PDF tiket atau QR Code tiket kepada petugas terminal pada hari H keberangkatan untuk discan menggunakan kamera petugas.\n\n"
            . "Jika user menanyakan hal di luar data di atas (seperti tips perjalanan atau pertanyaan umum), jawablah dengan sopan namun ingatkan bahwa Anda adalah asisten khusus SiTeBus.";

        // 7. Send request to Gemini Client
        $aiResponse = $this->geminiClient->generate($message, $systemInstruction);

        // 8. Save logs to database
        $logData = [
            'user_id'    => session()->get('userId') ?: null,
            'session_id' => $sessionId,
            'message'    => $message,
            'response'   => $aiResponse,
        ];
        $this->chatLogModel->save($logData);

        return $this->response->setJSON([
            'status'   => 'success',
            'response' => $aiResponse
        ]);
    }
}
