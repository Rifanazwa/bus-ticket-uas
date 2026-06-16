<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ScheduleModel;
use App\Models\RouteModel;

class Search extends BaseController
{
    protected $scheduleModel;
    protected $routeModel;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel();
        $this->routeModel = new RouteModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $origin      = $this->request->getGet('origin');
        $destination = $this->request->getGet('destination');
        $date        = $this->request->getGet('date');

        if (!$origin || !$destination || !$date) {
            return redirect()->to(base_url('customer/home'))->with('error', 'Silakan isi formulir pencarian dengan lengkap.');
        }

        // Trigger dynamic cloner for future schedules
        $this->scheduleModel->checkAndGenerateSchedulesForDate($date);

        // Search in database
        $schedules = $this->scheduleModel->select('schedules.*, routes.origin, routes.destination, routes.distance_km, routes.estimated_duration, buses.name as bus_name, buses.type as bus_type, buses.total_seats')
            ->join('routes', 'routes.id = schedules.route_id')
            ->join('buses', 'buses.id = schedules.bus_id')
            ->where('routes.origin', $origin)
            ->where('routes.destination', $destination)
            ->where('DATE(schedules.departure_time)', $date)
            ->where('schedules.status', 'scheduled')
            ->orderBy('schedules.departure_time', 'ASC')
            ->findAll();

        // Calculate remaining seats for each schedule
        $bookingSeatModel = new \App\Models\BookingSeatModel();
        foreach ($schedules as &$s) {
            $bookedCount = $bookingSeatModel->join('bookings', 'bookings.id = booking_seats.booking_id')
                ->where('bookings.schedule_id', $s['id'])
                ->where('bookings.booking_status !=', 'cancelled')
                ->countAllResults();
            $s['remaining_seats'] = $s['total_seats'] - $bookedCount;
        }
        unset($s); // break reference

        // Real Gemini AI Recommendation
        $aiRecommendation = null;
        if (!empty($schedules)) {
            $schedulesContext = "";
            foreach ($schedules as $index => $s) {
                $schedulesContext .= ($index + 1) . ". ID: " . $s['id'] 
                    . ", Bus: " . $s['bus_name'] 
                    . ", Kelas: " . $s['bus_type'] 
                    . ", Keberangkatan: " . date('H:i', strtotime($s['departure_time'])) 
                    . ", Kedatangan: " . date('H:i', strtotime($s['arrival_time'])) 
                    . ", Harga: Rp " . number_format($s['price'], 0, ',', '.') 
                    . ", Kursi Tersisa: " . $s['remaining_seats'] . "/" . $s['total_seats'] . "\n";
            }

            $prompt = "Berikut adalah daftar jadwal bus yang tersedia untuk perjalanan dari {$origin} ke {$destination} pada tanggal {$date}:\n" . $schedulesContext . "\nTolong pilih satu jadwal bus terbaik berdasarkan pertimbangan rasio harga, kenyamanan kelas bus, dan jam keberangkatan. Format respon Anda HARUS berupa JSON valid dengan format objek berikut:\n{\n  \"schedule_id\": <id_jadwal_terpilih>,\n  \"reason\": \"<alasan_singkat_mengapa_merekomendasikan_bus_ini_dalam_bahasa_indonesia_maksimal_150_karakter>\"\n}";

            $systemInstruction = "Anda adalah asisten virtual dari platform SiTeBus yang ramah dan membantu penumpang memilih jadwal tiket terbaik. Pastikan hanya merekomendasikan salah satu dari ID jadwal yang tersedia.";

            $geminiClient = new \App\Libraries\GeminiClient();
            $aiResult = $geminiClient->generateJson($prompt, $systemInstruction);

            if ($aiResult && isset($aiResult['schedule_id']) && isset($aiResult['reason'])) {
                // Ensure the schedule_id exists in the schedules list
                $validSchedule = false;
                foreach ($schedules as $s) {
                    if ($s['id'] == $aiResult['schedule_id']) {
                        $validSchedule = true;
                        break;
                    }
                }
                if ($validSchedule) {
                    $aiRecommendation = [
                        'schedule_id' => $aiResult['schedule_id'],
                        'reason'      => $aiResult['reason']
                    ];
                }
            }

            // Fallback if AI fails or returns invalid/null result
            if (!$aiRecommendation) {
                $recommended = $schedules[0];
                foreach ($schedules as $s) {
                    if ($s['bus_type'] === 'VIP' || $s['price'] < $recommended['price']) {
                        $recommended = $s;
                    }
                }
                $aiRecommendation = [
                    'schedule_id' => $recommended['id'],
                    'reason'      => 'Armada ' . $recommended['bus_name'] . ' disarankan karena memiliki rasio kenyamanan dan harga terbaik untuk perjalanan Anda.'
                ];
            }
        }


        return view('customer/search_results', [
            'title'            => 'Hasil Pencarian Jadwal - SiTeBus',
            'origin'           => $origin,
            'destination'      => $destination,
            'date'             => $date,
            'schedules'        => $schedules,
            'aiRecommendation' => $aiRecommendation
        ]);
    }
}
