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

        // 1. Fetch dynamic schedules context
        $schedules = $this->scheduleModel->getDetailedSchedules();
        $schedulesText = "";
        foreach ($schedules as $s) {
            if ($s['status'] === 'scheduled') {
                $schedulesText .= "- Rute: " . $s['origin'] . " ke " . $s['destination'] 
                    . " | Bus: " . $s['bus_name'] . " (" . $s['bus_type'] . ")"
                    . " | Berangkat: " . date('d M Y H:i', strtotime($s['departure_time'])) 
                    . " | Harga: Rp " . number_format($s['price'], 0, ',', '.') . "\n";
            }
        }

        // 2. Fetch active promos context
        $promos = $this->promoModel->where('valid_until >=', date('Y-m-d'))
                                   ->where('usage_limit >', 0)
                                   ->findAll();
        $promosText = "";
        foreach ($promos as $p) {
            $promosText .= "- Kode Promo: " . $p['code'] 
                . " | Tipe: " . ($p['discount_type'] === 'percent' ? 'Diskon ' . $p['discount_value'] . '%' : 'Potongan Rp ' . number_format($p['discount_value'], 0, ',', '.')) 
                . " | Valid s.d: " . date('d M Y', strtotime($p['valid_until'])) . "\n";
        }

        // 3. Compile System Instruction / Context
        $systemInstruction = "Anda adalah Asisten Customer Service ramah yang bernama 'SiTeBus Helper' dari platform SiTeBus.\n"
            . "Tugas Anda adalah melayani pertanyaan calon penumpang dengan sopan, bersahabat, ringkas, dan jelas dalam bahasa Indonesia.\n\n"
            . "Gunakan informasi context aktual dari database kami di bawah ini untuk menjawab pertanyaan tentang jadwal dan promo:\n"
            . "--- JADWAL KEBERANGKATAN AKTIF ---\n" . ($schedulesText ?: "Tidak ada jadwal keberangkatan aktif saat ini.\n") . "\n"
            . "--- PROMO AKTIF ---\n" . ($promosText ?: "Tidak ada promo aktif saat ini.\n") . "\n"
            . "--- FAQ & ATURAN PLATFORM ---\n"
            . "- Pembatalan / Refund: Tiket yang sudah dibayar dapat direfund/dibatalkan maksimal H-1 (24 jam sebelum keberangkatan) dengan potongan biaya 10%. Pembatalan kurang dari 24 jam tidak mendapat refund.\n"
            . "- Cara Cetak Tiket: Setelah lunas, penumpang dapat mengunduh e-ticket resmi format PDF di halaman utama dashboard penumpang mereka.\n"
            . "- Cara Naik Bus (Boarding): Tunjukkan file PDF tiket atau QR Code tiket kepada petugas terminal pada hari H keberangkatan untuk discan menggunakan kamera petugas.\n\n"
            . "Jika user menanyakan hal di luar data di atas (seperti tips perjalanan atau pertanyaan umum), jawablah dengan sopan namun ingatkan bahwa Anda adalah asisten khusus SiTeBus.";

        // 4. Send request to Gemini Client
        $aiResponse = $this->geminiClient->generate($message, $systemInstruction);

        // 5. Save logs to database
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
