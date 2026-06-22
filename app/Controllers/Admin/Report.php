<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BookingModel;
use App\Models\PaymentModel;
use App\Models\BusModel;
use App\Models\ScheduleModel;
use App\Libraries\GeminiClient;

class Report extends BaseController
{
    protected $bookingModel;
    protected $paymentModel;
    protected $busModel;
    protected $scheduleModel;
    protected $geminiClient;

    public function __construct()
    {
        $this->bookingModel  = new BookingModel();
        $this->paymentModel  = new PaymentModel();
        $this->busModel      = new BusModel();
        $this->scheduleModel = new ScheduleModel();
        $this->geminiClient  = new GeminiClient();
        helper(['form', 'url', 'session']);
    }

    public function index()
    {
        // 1. Get Date Range Filters (default: last 30 days)
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d', strtotime('-30 days'));
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        // 2. Financial Summary
        $financialSummary = $this->getFinancialSummary($startDate, $endDate);

        // Recent Payments List for Financial Table
        $payments = $this->paymentModel
            ->select('payments.*, bookings.booking_code, users.name as customer_name')
            ->join('bookings', 'bookings.id = payments.booking_id')
            ->join('users', 'users.id = bookings.user_id')
            ->where('payments.paid_at >=', $startDate . ' 00:00:00')
            ->where('payments.paid_at <=', $endDate . ' 23:59:59')
            ->orderBy('payments.paid_at', 'DESC')
            ->findAll();

        // 3. Chart Data
        $weeklyRevenue = $this->getWeeklyRevenueData();
        $monthlyRevenue = $this->getMonthlyRevenueData();

        // 4. Fleet Performance Report
        $fleetReport = $this->getFleetPerformanceData($startDate, $endDate);

        // Calculate Average Fleet Occupancy
        $totalTrips = 0;
        $totalOccupancyPctSum = 0;
        $activeBusesCount = 0;
        
        foreach ($fleetReport as $f) {
            if ($f['total_trips'] > 0) {
                $totalTrips += $f['total_trips'];
                $totalOccupancyPctSum += $f['avg_occupancy_pct'];
                $activeBusesCount++;
            }
        }
        $avgFleetOccupancy = $activeBusesCount > 0 ? round($totalOccupancyPctSum / $activeBusesCount, 1) : 0;

        // Find top performing buses
        $topBus = null;
        $topRevenueBus = null;
        if (!empty($fleetReport)) {
            $sortedByPassengers = $fleetReport;
            usort($sortedByPassengers, fn($a, $b) => $b['total_passengers'] <=> $a['total_passengers']);
            $topBus = $sortedByPassengers[0];

            $sortedByRevenue = $fleetReport;
            usort($sortedByRevenue, fn($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);
            $topRevenueBus = $sortedByRevenue[0];
        }

        // 5. Gemini AI Decisions & Strategic Insights
        $aiInsights = $this->getAiReportInsights($startDate, $endDate, $financialSummary, $avgFleetOccupancy, $topBus, $topRevenueBus);

        return view('admin/report/index', [
            'title'             => 'Laporan & Analitik',
            'subtitle'          => 'Laporan Keuangan & Performa Bisnis PO Bus',
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'financialSummary'  => $financialSummary,
            'payments'          => $payments,
            'weeklyRevenue'     => $weeklyRevenue,
            'monthlyRevenue'    => $monthlyRevenue,
            'fleetReport'       => $fleetReport,
            'avgFleetOccupancy' => $avgFleetOccupancy,
            'topBus'            => $topBus,
            'topRevenueBus'     => $topRevenueBus,
            'aiInsights'        => $aiInsights
        ]);
    }

    public function exportFinancial()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d', strtotime('-30 days'));
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        $payments = $this->paymentModel
            ->select('payments.transaction_id, bookings.booking_code, users.name as customer_name, payments.method, payments.amount, payments.status, payments.paid_at')
            ->join('bookings', 'bookings.id = payments.booking_id')
            ->join('users', 'users.id = bookings.user_id')
            ->where('payments.paid_at >=', $startDate . ' 00:00:00')
            ->where('payments.paid_at <=', $endDate . ' 23:59:59')
            ->orderBy('payments.paid_at', 'DESC')
            ->findAll();

        $filename = 'Laporan_Keuangan_' . $startDate . '_s_d_' . $endDate . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compliance
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Column headings
        fputcsv($output, ['ID Transaksi', 'Kode Booking', 'Nama Pelanggan', 'Metode Pembayaran', 'Jumlah (IDR)', 'Status', 'Tanggal Lunas']);

        foreach ($payments as $p) {
            fputcsv($output, [
                $p['transaction_id'],
                $p['booking_code'],
                $p['customer_name'],
                strtoupper($p['method']),
                (float)$p['amount'],
                strtoupper($p['status']),
                $p['paid_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportFleet()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d', strtotime('-30 days'));
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        $fleetReport = $this->getFleetPerformanceData($startDate, $endDate);

        $filename = 'Laporan_Performa_Armada_' . $startDate . '_s_d_' . $endDate . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compliance
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Column headings
        fputcsv($output, ['ID Bus', 'Nama Bus', 'Tipe Bus', 'Kapasitas Kursi', 'Total Perjalanan (Trip)', 'Total Penumpang', 'Rata-rata Okupansi (%)', 'Pendapatan Dihasilkan (IDR)']);

        foreach ($fleetReport as $f) {
            fputcsv($output, [
                $f['bus_id'],
                $f['bus_name'],
                $f['bus_type'],
                $f['total_seats'],
                $f['total_trips'],
                $f['total_passengers'],
                $f['avg_occupancy_pct'] . '%',
                (float)$f['total_revenue']
            ]);
        }

        fclose($output);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function getFinancialSummary($startDate, $endDate): array
    {
        $db = \Config\Database::connect();
        
        // Total revenue
        $rev = $this->paymentModel
            ->where('status', 'success')
            ->where('paid_at >=', $startDate . ' 00:00:00')
            ->where('paid_at <=', $endDate . ' 23:59:59')
            ->selectSum('amount')
            ->selectCount('id', 'count')
            ->first();

        $totalRevenue = (float)($rev['amount'] ?? 0);
        $totalTransactions = (int)($rev['count'] ?? 0);
        $avgBookingVal = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 1) : 0;

        // Breakdown by payment methods
        $methodsQuery = $db->query("
            SELECT method, COUNT(*) as count, SUM(amount) as total
            FROM payments
            WHERE status = 'success'
              AND paid_at >= ?
              AND paid_at <= ?
            GROUP BY method
            ORDER BY count DESC
        ", [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->getResultArray();

        $methods = [];
        foreach ($methodsQuery as $m) {
            $methods[strtoupper($m['method'])] = [
                'count' => (int)$m['count'],
                'total' => (float)$m['total']
            ];
        }

        return [
            'total_revenue'      => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'avg_booking_val'    => $avgBookingVal,
            'methods'            => $methods
        ];
    }

    private function getWeeklyRevenueData(): array
    {
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime($date));
            $dayTranslations = [
                'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 
                'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab', 'Sun' => 'Min'
            ];
            $dayNameInd = $dayTranslations[$dayName] ?? $dayName;
            
            $rev = $this->paymentModel
                ->where('status', 'success')
                ->where('DATE(paid_at)', $date)
                ->selectSum('amount')
                ->first();
                
            $weeklyData[] = [
                'label' => $dayNameInd . ' (' . date('d/m', strtotime($date)) . ')',
                'value' => (float)($rev['amount'] ?? 0)
            ];
        }
        return $weeklyData;
    }

    private function getMonthlyRevenueData(): array
    {
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            $mName = date('M', strtotime($monthStart));
            $monthTranslations = [
                'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 
                'May' => 'Mei', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Agt', 
                'Sep' => 'Sep', 'Oct' => 'Okt', 'Nov' => 'Nov', 'Dec' => 'Des'
            ];
            $monthLabelInd = ($monthTranslations[$mName] ?? $mName) . ' ' . date('Y', strtotime($monthStart));

            $rev = $this->paymentModel
                ->where('status', 'success')
                ->where('paid_at >=', $monthStart . ' 00:00:00')
                ->where('paid_at <=', $monthEnd . ' 23:59:59')
                ->selectSum('amount')
                ->first();

            $monthlyData[] = [
                'label' => $monthLabelInd,
                'value' => (float)($rev['amount'] ?? 0)
            ];
        }
        return $monthlyData;
    }

    private function getFleetPerformanceData($startDate = null, $endDate = null): array
    {
        $db = \Config\Database::connect();
        
        $params = [];
        $dateFilter = "";
        
        if ($startDate !== null && $endDate !== null) {
            $dateFilter = " AND s.departure_time >= ? AND s.departure_time <= ? ";
            $params = [
                $startDate . ' 00:00:00', $endDate . ' 23:59:59',
                $startDate . ' 00:00:00', $endDate . ' 23:59:59',
                $startDate . ' 00:00:00', $endDate . ' 23:59:59'
            ];
        }
        
        $queryStr = "
            SELECT 
                b.id AS bus_id,
                b.name AS bus_name,
                b.type AS bus_type,
                b.total_seats,
                (SELECT COUNT(*) FROM schedules s 
                 WHERE s.bus_id = b.id {$dateFilter}) AS total_trips,
                (SELECT COUNT(bs.id) 
                 FROM booking_seats bs
                 JOIN bookings bo ON bo.id = bs.booking_id
                 JOIN schedules s ON s.id = bo.schedule_id
                 WHERE s.bus_id = b.id AND bo.booking_status != 'cancelled' {$dateFilter}) AS total_passengers,
                (SELECT COALESCE(SUM(bo.total_price), 0)
                 FROM bookings bo
                 JOIN schedules s ON s.id = bo.schedule_id
                 WHERE s.bus_id = b.id AND bo.payment_status = 'paid' AND bo.booking_status != 'cancelled' {$dateFilter}) AS total_revenue
            FROM buses b
            ORDER BY total_revenue DESC
        ";
        
        $fleetReport = $db->query($queryStr, $params)->getResultArray();

        foreach ($fleetReport as &$row) {
            $totalPossibleSeats = $row['total_trips'] * $row['total_seats'];
            $row['avg_occupancy_pct'] = $totalPossibleSeats > 0 
                ? round(($row['total_passengers'] / $totalPossibleSeats) * 100, 1) 
                : 0;
        }
        unset($row);

        return $fleetReport;
    }

    private function getAiReportInsights($startDate, $endDate, $financialSummary, $avgFleetOccupancy, $topBus, $topRevenueBus): string
    {
        $totalReviewCount = $financialSummary['total_transactions'];
        
        // Formulate prompt
        $prompt = "Berikut adalah ringkasan laporan operasional dan performa PO Bus SiTeBus:\n"
            . "- Periode Laporan: " . date('d M Y', strtotime($startDate)) . " s/d " . date('d M Y', strtotime($endDate)) . "\n"
            . "- Total Pendapatan Periode Ini: Rp " . number_format($financialSummary['total_revenue'], 0, ',', '.') . "\n"
            . "- Jumlah Transaksi Sukses: " . $financialSummary['total_transactions'] . "\n"
            . "- Rata-rata Nilai Pemesanan: Rp " . number_format($financialSummary['avg_booking_val'], 0, ',', '.') . "\n"
            . "- Metode Pembayaran Terpopuler: " . json_encode($financialSummary['methods']) . "\n"
            . "- Bus Terpopuler (Penumpang Terbanyak): " . ($topBus ? "{$topBus['bus_name']} ({$topBus['total_passengers']} penumpang)" : '-') . "\n"
            . "- Bus Berpendapatan Tertinggi: " . ($topRevenueBus ? "{$topRevenueBus['bus_name']} (Rp " . number_format($topRevenueBus['total_revenue'], 0, ',', '.') . ")" : '-') . "\n"
            . "- Rata-rata Okupansi Armada: " . $avgFleetOccupancy . "%\n\n"
            . "Berikan analisis performa bisnis dan rekomendasi keputusan strategis (misal penyesuaian rute, flash-sale, promo, atau optimalisasi armada). "
            . "Tulis analisis Anda dalam Bahasa Indonesia dengan format markdown terstruktur (menggunakan sub-heading dan poin-poin). "
            . "Maksimal 250 kata, nada profesional, analitis, dan solutif.";

        $aiAnalysis = $this->geminiClient->generate($prompt, "Anda adalah sistem business intelligence AI untuk PO Bus.");

        // Fallback analysis if API fails
        if (!$aiAnalysis || str_starts_with($aiAnalysis, '[') || trim($aiAnalysis) === '') {
            $recommendations = [];
            if ($avgFleetOccupancy < 50) {
                $recommendations[] = "### Rekomendasi 1: Penyesuaian Harga Dinamis\nRata-rata okupansi armada saat ini relatif rendah ({$avgFleetOccupancy}%). Disarankan meluncurkan promo/flash-sale pada jam-jam sepi (non-peak hours) untuk mendongkrak tingkat keterisian kursi.";
            } else {
                $recommendations[] = "### Rekomendasi 1: Penambahan Frekuensi Rute Utama\nOkupansi rata-rata yang solid ({$avgFleetOccupancy}%) menunjukkan tingginya minat. Disarankan menambah frekuensi keberangkatan pada rute/bus berkinerja tinggi seperti " . ($topRevenueBus ? esc($topRevenueBus['bus_name']) : 'armada utama') . ".";
            }

            if ($topBus) {
                $recommendations[] = "### Rekomendasi 2: Pemeliharaan Preventif Terjadwal\nBus **" . esc($topBus['bus_name']) . "** mengangkut volume penumpang terbesar ({$topBus['total_passengers']} orang). Jadwalkan inspeksi teknis secara rutin untuk meminimalisir risiko breakdown di jalan.";
            }

            $recommendations[] = "### Rekomendasi 3: Optimasi Metode Pembayaran\nMetode pembayaran digital mendominasi. Pastikan integrasi gerbang pembayaran berjalan stabil untuk mempertahankan tingkat konversi transaksi yang lancar.";

            $aiAnalysis = "## Analisis Kinerja Keuangan\n"
                . "Performa finansial selama periode laporan menunjukkan perolehan total pendapatan sebesar **Rp " . number_format($financialSummary['total_revenue'], 0, ',', '.') . "** dari total **" . $financialSummary['total_transactions'] . "** transaksi sukses.\n\n"
                . "## Rekomendasi Bisnis & Strategis\n"
                . implode("\n\n", $recommendations);
        }

        return $aiAnalysis;
    }
}
