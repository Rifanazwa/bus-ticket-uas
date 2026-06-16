<?= $this->extend('layout/admin') ?>

<?php
$title    = 'Dashboard Admin';
$subtitle = 'Ringkasan Kinerja & AI Insights';

// ─── PHP helper: render schedule table on initial page load ──────────────────
if (!function_exists('includeScheduleTable')) {
    function includeScheduleTable(array $schedules): string
    {
        if (empty($schedules)) {
            return '<p class="text-xs text-slate-500 text-center py-6">Belum ada jadwal aktif.</p>';
        }
        $rows = '';
        foreach ($schedules as $s) {
            $pct   = (float)($s['occupancy_pct'] ?? 0);
            $color = $pct > 80 ? '#f43f5e' : ($pct > 50 ? '#f59e0b' : '#10b981');
            $txtCl = $pct > 80 ? 'text-rose-400' : ($pct > 50 ? 'text-amber-400' : 'text-emerald-400');
            $rows .= '<tr class="table-row border-b border-slate-800/30">'
                . '<td class="px-4 py-3"><p class="font-bold text-white text-xs">' . esc($s['origin']) . ' <span class="text-slate-500">&rarr;</span> ' . esc($s['destination']) . '</p>'
                . '<p class="text-[10px] text-slate-500 mt-0.5">' . esc($s['bus_name']) . ' &middot; ' . esc(ucfirst($s['bus_type'] ?? '')) . '</p></td>'
                . '<td class="px-4 py-3"><p class="text-xs text-slate-300 font-mono">' . date('d M Y', strtotime($s['departure_time'])) . '</p>'
                . '<p class="text-xs text-indigo-400 font-bold font-mono">' . date('H:i', strtotime($s['departure_time'])) . ' <span class="text-slate-600 text-[9px]">WIB</span></p></td>'
                . '<td class="px-4 py-3 text-xs font-semibold text-white">Rp ' . number_format($s['price'], 0, ',', '.') . '</td>'
                . '<td class="px-4 py-3"><div class="flex items-center gap-2"><div class="flex-1 bg-slate-800 h-1.5 rounded-full overflow-hidden max-w-[70px]">'
                . '<div class="h-full rounded-full transition-all" style="width:' . $pct . '%; background-color:' . $color . '"></div></div>'
                . '<span class="text-[10px] font-bold ' . $txtCl . ' w-8">' . $pct . '%</span></div>'
                . '<p class="text-[9px] text-slate-600 mt-1">' . ($s['booked_seats'] ?? 0) . '/' . ($s['total_seats'] ?? 0) . ' kursi</p></td>'
                . '</tr>';
        }
        return '<table class="w-full text-xs text-left">'
            . '<thead><tr class="bg-slate-950/60 text-slate-500 text-[9px] uppercase tracking-widest font-bold border-b border-slate-800/60">'
            . '<th class="px-4 py-3">Rute &amp; Armada</th><th class="px-4 py-3">Keberangkatan</th>'
            . '<th class="px-4 py-3">Harga</th><th class="px-4 py-3">Keterisian</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table>';
    }
}

// ─── PHP helper: render anomaly list on initial page load ────────────────────
if (!function_exists('renderAnomalies')) {
    function renderAnomalies(array $anomalies): string
    {
        if (empty($anomalies)) {
            return '<div class="p-5 bg-emerald-500/5 border border-emerald-500/15 rounded-2xl text-center space-y-2">'
                . '<div class="inline-flex p-2.5 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20 mb-1">'
                . '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></div>'
                . '<p class="text-xs font-bold text-emerald-400">Sistem Aman</p>'
                . '<p class="text-[10px] text-slate-500 leading-relaxed">Tidak ada anomali booking terdeteksi saat ini.</p></div>';
        }
        $styles = [
            'high'   => ['bg-rose-500/5',  'border-rose-500/20',  'text-rose-400',  '#f43f5e'],
            'medium' => ['bg-amber-500/5', 'border-amber-500/20', 'text-amber-400', '#f59e0b'],
            'info'   => ['bg-indigo-500/5','border-indigo-500/20','text-indigo-400','#6366f1'],
        ];
        $html = '';
        foreach ($anomalies as $a) {
            $sev      = $styles[$a['severity']] ?? $styles['medium'];
            $emailTag = !empty($a['email'])
                ? '<p class="text-[9px] text-slate-600 pl-3.5">' . esc($a['email']) . '</p>'
                : '';
            $html .= '<div class="p-3 ' . $sev[0] . ' border ' . $sev[1] . ' rounded-xl space-y-1.5">'
                . '<div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:' . $sev[3] . '"></span>'
                . '<span class="text-[10px] font-extrabold ' . $sev[2] . '">' . esc($a['type']) . '</span></div>'
                . '<p class="text-[10px] font-semibold text-slate-300 pl-3.5">' . esc($a['user']) . '</p>'
                . $emailTag
                . '<p class="text-[10px] text-slate-500 pl-3.5 leading-relaxed">' . esc($a['details']) . '</p></div>';
        }
        return $html;
    }
}
?>

<?= $this->section('admin_content') ?>

<?php $hasReviews = array_sum($sentimentCounts) > 0; ?>

<!-- Auto-refresh indicator -->
<div id="refresh-bar" class="flex items-center justify-between mb-1">
    <p class="text-[10px] text-slate-600">
        Data terakhir diperbarui: <span id="last-update-time" class="text-brand-400 font-mono font-bold"><?= $lastUpdated ?> WIB</span>
    </p>
    <div class="flex items-center gap-2">
        <div id="refresh-indicator" class="w-2 h-2 rounded-full bg-emerald-400 dot-pulse"></div>
        <span class="text-[10px] text-slate-500">Live • Auto-refresh <span id="refresh-countdown" class="text-slate-400 font-mono font-bold">30</span>s</span>
        <button onclick="forceRefresh()" class="text-[10px] text-brand-400 hover:text-brand-300 flex items-center gap-1 transition-colors ml-1">
            <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refresh
        </button>
    </div>
</div>

<!-- ==================== KPI STAT CARDS ==================== -->
<div id="kpi-grid" class="grid grid-cols-2 xl:grid-cols-4 gap-4">

    <!-- 1. Total Pendapatan -->
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden col-span-2 sm:col-span-1">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Total Pendapatan</p>
                <p class="text-xl font-extrabold text-white font-inter leading-none" id="kpi-revenue">
                    Rp <?= number_format($totalRevenue, 0, ',', '.') ?>
                </p>
                <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full">
                        <i data-lucide="trending-up" class="w-2.5 h-2.5"></i> Dari transaksi lunas
                    </span>
                </div>
                <p class="text-[10px] text-slate-500 mt-1.5">
                    Hari ini: <span class="text-emerald-400 font-bold" id="kpi-today-revenue">Rp <?= number_format($todayRevenue, 0, ',', '.') ?></span>
                </p>
            </div>
            <div class="p-3 bg-brand-500/10 text-brand-400 rounded-xl border border-brand-500/15 flex-shrink-0 ml-3">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- 2. Tiket Diterbitkan -->
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Tiket Diterbitkan</p>
                <p class="text-xl font-extrabold text-white font-inter leading-none" id="kpi-tickets">
                    <?= $totalTickets ?> <span class="text-sm font-semibold text-slate-400">tiket</span>
                </p>
                <div class="mt-2 space-y-0.5">
                    <p class="text-[10px] text-slate-500">
                        Boarded: <span class="text-emerald-400 font-bold" id="kpi-boarded"><?= $boardedTickets ?></span>
                        &nbsp;•&nbsp; Aktif: <span class="text-brand-400 font-bold" id="kpi-active"><?= $activeTickets ?></span>
                    </p>
                    <p class="text-[10px] text-slate-500">
                        Booking pending: <span class="text-amber-400 font-bold" id="kpi-pending"><?= $pendingBookings ?></span>
                    </p>
                </div>
            </div>
            <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/15 flex-shrink-0 ml-3">
                <i data-lucide="ticket" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- 3. Rata-rata Okupansi -->
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Rata-rata Okupansi</p>
                <p class="text-xl font-extrabold text-white font-inter leading-none" id="kpi-occupancy">
                    <?= $avgOccupancy ?><span class="text-sm font-semibold text-slate-400">%</span>
                </p>
                <div class="mt-2">
                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div id="kpi-occupancy-bar" class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-brand-400 transition-all duration-700"
                             style="width: <?= min($avgOccupancy, 100) ?>%"></div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1" id="kpi-seats-info">
                        <?= $bookedSeats ?>/<?= $totalSeats ?> kursi terisi
                    </p>
                </div>
            </div>
            <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-xl border border-indigo-500/15 flex-shrink-0 ml-3">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- 4. AI Prediksi -->
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden border border-brand-500/20">
        <div class="absolute top-0 right-0 w-20 h-20 bg-brand-500/5 rounded-full blur-xl -translate-y-4 translate-x-4"></div>
        <div class="flex items-start justify-between relative">
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-brand-400/70 uppercase tracking-widest mb-1.5 flex items-center gap-1">
                    <i data-lucide="sparkles" class="w-3 h-3"></i> AI Prediksi 7 Hari
                </p>
                <p class="text-xl font-extrabold text-brand-400 font-inter leading-none" id="kpi-prediction">
                    <?= $predictedOccupancy['percentage'] ?><span class="text-sm font-semibold text-brand-600">%</span>
                </p>
                <p class="text-[9px] text-slate-500 mt-2 leading-relaxed line-clamp-2" id="kpi-prediction-analysis">
                    <?= esc($predictedOccupancy['analysis']) ?>
                </p>
            </div>
            <div class="p-3 bg-brand-500/10 text-brand-400 rounded-xl border border-brand-500/20 flex-shrink-0 ml-3">
                <i data-lucide="bot" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

</div>

<!-- ==================== MAIN PANELS ==================== -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <!-- LEFT: AI Occupancy + Schedule Table (2/3) -->
    <div class="xl:col-span-2 space-y-5">

        <!-- AI Analisis Tren & Okupansi -->
        <div id="ai-occupancy" class="panel-card p-6 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <div class="p-1.5 bg-brand-500/10 text-brand-400 rounded-lg border border-brand-500/20">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        AI Analisis Tren & Okupansi
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 ml-9">Prediksi keterisian untuk optimasi harga & armada.</p>
                </div>
                <span class="flex items-center gap-1 text-[9px] font-bold text-brand-400 bg-brand-500/10 border border-brand-500/20 px-2 py-1 rounded-full">
                    <i data-lucide="sparkles" class="w-3 h-3"></i> Gemini AI
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- SVG Donut -->
                <div class="bg-slate-950/60 rounded-2xl p-5 border border-slate-800/60 flex flex-col items-center justify-center text-center space-y-3">
                    <div class="relative h-28 w-28 flex items-center justify-center">
                        <svg class="h-28 w-28 transform -rotate-90" viewBox="0 0 112 112">
                            <circle cx="56" cy="56" r="46" stroke-width="8" stroke="#1e293b" fill="transparent"/>
                            <circle id="donut-circle" cx="56" cy="56" r="46" stroke-width="8" stroke="#6366f1" fill="transparent"
                                    stroke-linecap="round"
                                    stroke-dasharray="<?= round(2 * M_PI * 46, 2) ?>"
                                    stroke-dashoffset="<?= round(2 * M_PI * 46 * (1 - $predictedOccupancy['percentage'] / 100), 2) ?>"/>
                        </svg>
                        <div class="absolute text-center">
                            <span id="donut-pct" class="text-xl font-extrabold text-white font-mono leading-none"><?= $predictedOccupancy['percentage'] ?>%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-300">Prediksi Keterisian</p>
                        <p class="text-[9px] text-slate-500">7 hari mendatang · Gemini AI</p>
                    </div>
                </div>

                <!-- Analysis + Comparison -->
                <div class="sm:col-span-2 bg-slate-950/60 rounded-2xl p-5 border border-slate-800/60 flex flex-col justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1 text-[9px] font-bold text-brand-400 bg-brand-500/10 border border-brand-500/20 px-2 py-1 rounded-full uppercase tracking-wider mb-3">
                            <i data-lucide="sparkles" class="w-3 h-3"></i> Gemini AI Insights
                        </span>
                        <p id="ai-analysis-text" class="text-xs text-slate-300 leading-relaxed mt-2">
                            "<?= esc($predictedOccupancy['analysis']) ?>"
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-3 pt-3 border-t border-slate-800/50">
                        <div class="text-center">
                            <p class="text-[9px] text-slate-500 uppercase tracking-wide font-bold">Aktual Sekarang</p>
                            <p class="text-sm font-extrabold text-white mt-0.5" id="stat-actual"><?= $avgOccupancy ?>%</p>
                        </div>
                        <div class="text-center border-x border-slate-800/40">
                            <p class="text-[9px] text-slate-500 uppercase tracking-wide font-bold">Prediksi 7 Hari</p>
                            <p class="text-sm font-extrabold text-brand-400 mt-0.5" id="stat-predicted"><?= $predictedOccupancy['percentage'] ?>%</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[9px] text-slate-500 uppercase tracking-wide font-bold">Selisih</p>
                            <?php $diff = $predictedOccupancy['percentage'] - $avgOccupancy; ?>
                            <p class="text-sm font-extrabold mt-0.5 <?= $diff >= 0 ? 'text-emerald-400' : 'text-rose-400' ?>" id="stat-diff">
                                <?= $diff >= 0 ? '+' : '' ?><?= $diff ?>%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Kapasitas per Jadwal -->
        <div id="schedule-capacity" class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 bg-teal-500/10 text-teal-400 rounded-lg border border-teal-500/20">
                        <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    </div>
                    Status Kapasitas per Jadwal
                    <span class="text-[9px] text-slate-500 font-normal ml-1">(<?= count($schedules) ?> jadwal)</span>
                </h3>
                <a href="<?= base_url('admin/schedule') ?>" class="text-[10px] font-bold text-brand-400 hover:text-brand-300 flex items-center gap-1 transition-colors">
                    Kelola Semua <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>

            <div id="schedule-table-wrap" class="overflow-x-auto rounded-xl">
                <?= includeScheduleTable($schedules) ?>
            </div>
        </div>

    </div>

    <!-- RIGHT: Sentiment + Anomaly + Quick Links (1/3) -->
    <div class="space-y-5">

        <!-- Sentiment Review -->
        <div id="ai-sentiment" class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 bg-emerald-500/10 text-emerald-400 rounded-lg border border-emerald-500/20">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                    </div>
                    Sentimen Review AI
                </h3>
                <span class="text-[9px] text-slate-500 font-semibold bg-slate-800/60 px-2 py-0.5 rounded-full border border-slate-700/30">
                    ⭐ <?= $avgRating ?>/5
                    <span class="text-slate-600 ml-1">(<?= $totalReviews ?> ulasan)</span>
                </span>
            </div>

            <div class="bg-slate-950/60 rounded-2xl p-4 border border-slate-800/60 flex flex-col items-center">
                <?php if ($hasReviews): ?>
                    <div class="h-40 w-40 mb-3">
                        <canvas id="sentimentChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="h-40 flex flex-col items-center justify-center text-center space-y-2 opacity-40">
                        <i data-lucide="message-square-dashed" class="w-8 h-8 text-slate-500"></i>
                        <p class="text-[10px] text-slate-500">Belum ada review.</p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-3 gap-2 w-full mt-2">
                    <div class="text-center p-2 rounded-xl bg-emerald-500/5 border border-emerald-500/15">
                        <p class="text-sm font-extrabold text-emerald-400" id="sent-pos"><?= $sentimentCounts['positive'] ?></p>
                        <p class="text-[9px] text-slate-500 mt-0.5">Positif</p>
                    </div>
                    <div class="text-center p-2 rounded-xl bg-slate-800/40 border border-slate-700/30">
                        <p class="text-sm font-extrabold text-slate-300" id="sent-neu"><?= $sentimentCounts['neutral'] ?></p>
                        <p class="text-[9px] text-slate-500 mt-0.5">Netral</p>
                    </div>
                    <div class="text-center p-2 rounded-xl bg-rose-500/5 border border-rose-500/15">
                        <p class="text-sm font-extrabold text-rose-400" id="sent-neg"><?= $sentimentCounts['negative'] ?></p>
                        <p class="text-[9px] text-slate-500 mt-0.5">Negatif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deteksi Anomali Booking -->
        <div id="ai-anomaly" class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 bg-rose-500/10 text-rose-400 rounded-lg border border-rose-500/20">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    </div>
                    Deteksi Anomali Booking
                </h3>
                <span id="anomaly-badge"
                      class="text-[9px] font-bold px-2 py-0.5 rounded-full border <?= count($anomalies) > 0 ? 'text-rose-400 bg-rose-500/10 border-rose-500/20' : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' ?>">
                    <?= count($anomalies) ?> kasus
                </span>
            </div>

            <div id="anomaly-list" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                <?= renderAnomalies($anomalies) ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="panel-card p-5 space-y-3">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Aksi Cepat</h3>
            <div class="grid grid-cols-2 gap-2">
                <a href="<?= base_url('admin/schedule/create') ?>"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800/60 hover:border-brand-500/30 hover:bg-brand-500/5 transition-all text-center group">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-brand-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-bold text-slate-300">Tambah Jadwal</span>
                </a>
                <a href="<?= base_url('admin/bus/create') ?>"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800/60 hover:border-teal-500/30 hover:bg-teal-500/5 transition-all text-center group">
                    <i data-lucide="bus" class="w-5 h-5 text-teal-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-bold text-slate-300">Tambah Armada</span>
                </a>
                <a href="<?= base_url('admin/promo/create') ?>"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800/60 hover:border-amber-500/30 hover:bg-amber-500/5 transition-all text-center group">
                    <i data-lucide="ticket-percent" class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-bold text-slate-300">Buat Promo</span>
                </a>
                <a href="<?= base_url('petugas/scan') ?>"
                   class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800/60 hover:border-emerald-500/30 hover:bg-emerald-500/5 transition-all text-center group">
                    <i data-lucide="scan-line" class="w-5 h-5 text-emerald-400 group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-bold text-slate-300">Portal Boarding</span>
                </a>
            </div>
        </div>

    </div>
</div>

<!-- ==================== SCRIPTS ==================== -->
<?php if ($hasReviews): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<script>
const STATS_URL = '<?= base_url('admin/dashboard/stats') ?>';
let sentimentChart = null;
let refreshTimer   = null;
let countdown      = 30;

// ─── Build schedule table HTML from JSON data ────────────────────────────────
function buildScheduleTable(schedules) {
    if (!schedules || schedules.length === 0) {
        return '<p class="text-xs text-slate-500 text-center py-6">Belum ada jadwal.</p>';
    }

    let rows = schedules.map(s => {
        const pct   = parseFloat(s.occupancy_pct) || 0;
        const color = pct > 80 ? '#f43f5e' : (pct > 50 ? '#f59e0b' : '#10b981');
        const txtCl = pct > 80 ? 'text-rose-400' : (pct > 50 ? 'text-amber-400' : 'text-emerald-400');

        const depDate = s.departure_time
            ? new Date(s.departure_time).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric',timeZone:'Asia/Jakarta'})
            : '-';
        const depTime = s.departure_time
            ? new Date(s.departure_time).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',timeZone:'Asia/Jakarta',hour12:false})
            : '-';

        return `<tr class="table-row border-b border-slate-800/30">
            <td class="px-4 py-3">
                <p class="font-bold text-white text-xs">${s.origin} <span class="text-slate-500">→</span> ${s.destination}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">${s.bus_name} · ${s.bus_type ? s.bus_type.charAt(0).toUpperCase() + s.bus_type.slice(1) : ''}</p>
            </td>
            <td class="px-4 py-3">
                <p class="text-xs text-slate-300 font-mono">${depDate}</p>
                <p class="text-xs text-brand-400 font-bold font-mono">${depTime} <span class="text-slate-600 text-[9px]">WIB</span></p>
            </td>
            <td class="px-4 py-3 text-xs font-semibold text-white">Rp ${parseInt(s.price).toLocaleString('id-ID')}</td>
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-slate-800 h-1.5 rounded-full overflow-hidden max-w-[70px]">
                        <div class="h-full rounded-full transition-all" style="width:${pct}%; background-color:${color}"></div>
                    </div>
                    <span class="text-[10px] font-bold ${txtCl} w-8">${pct}%</span>
                </div>
                <p class="text-[9px] text-slate-600 mt-1">${s.booked_seats}/${s.total_seats} kursi</p>
            </td>
        </tr>`;
    });

    return `<table class="w-full text-xs text-left">
        <thead>
            <tr class="bg-slate-950/60 text-slate-500 text-[9px] uppercase tracking-widest font-bold border-b border-slate-800/60">
                <th class="px-4 py-3">Rute & Armada</th>
                <th class="px-4 py-3">Keberangkatan</th>
                <th class="px-4 py-3">Harga</th>
                <th class="px-4 py-3">Keterisian</th>
            </tr>
        </thead>
        <tbody>${rows.join('')}</tbody>
    </table>`;
}

// ─── Build anomaly list HTML from JSON data ───────────────────────────────────
function buildAnomalyList(anomalies) {
    if (!anomalies || anomalies.length === 0) {
        return `<div class="p-5 bg-emerald-500/5 border border-emerald-500/15 rounded-2xl text-center space-y-2">
            <div class="inline-flex p-2.5 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <p class="text-xs font-bold text-emerald-400">Sistem Aman</p>
            <p class="text-[10px] text-slate-500 leading-relaxed">Tidak ada anomali booking terdeteksi saat ini.</p>
        </div>`;
    }

    const severityStyles = {
        'high':   { bg: 'bg-rose-500/5',   border: 'border-rose-500/20',   text: 'text-rose-400',    dot: '#f43f5e' },
        'medium': { bg: 'bg-amber-500/5',  border: 'border-amber-500/20',  text: 'text-amber-400',   dot: '#f59e0b' },
        'info':   { bg: 'bg-brand-500/5',  border: 'border-brand-500/20',  text: 'text-brand-400',   dot: '#6366f1' },
    };

    return anomalies.map(a => {
        const s = severityStyles[a.severity] || severityStyles['medium'];
        const emailTag = a.email ? `<p class="text-[9px] text-slate-600">${a.email}</p>` : '';
        return `<div class="p-3 ${s.bg} border ${s.border} rounded-xl space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:${s.dot}"></span>
                <span class="text-[10px] font-extrabold ${s.text}">${a.type}</span>
            </div>
            <p class="text-[10px] font-semibold text-slate-300 pl-3.5">${a.user}</p>
            ${emailTag}
            <p class="text-[10px] text-slate-500 pl-3.5 leading-relaxed">${a.details}</p>
        </div>`;
    }).join('');
}

// ─── Main refresh function ────────────────────────────────────────────────────
async function forceRefresh() {
    clearInterval(refreshTimer);

    const ind = document.getElementById('refresh-indicator');
    if (ind) { ind.style.background = '#f59e0b'; }

    try {
        const res  = await fetch(STATS_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        // ── KPI Updates ─────────────────────────────────────────────────────
        setText('kpi-revenue',         'Rp ' + parseInt(data.totalRevenue).toLocaleString('id-ID'));
        setText('kpi-today-revenue',   'Rp ' + parseInt(data.todayRevenue).toLocaleString('id-ID'));
        setText('kpi-tickets',         data.totalTickets + '<span class="text-sm font-semibold text-slate-400"> tiket</span>');
        setText('kpi-boarded',         data.boardedTickets);
        setText('kpi-active',          data.activeTickets);
        setText('kpi-pending',         data.pendingBookings);
        setText('kpi-occupancy',       data.avgOccupancy + '<span class="text-sm font-semibold text-slate-400">%</span>');
        setText('kpi-seats-info',      data.bookedSeats + '/' + data.totalSeats + ' kursi terisi');
        setText('kpi-prediction',      data.predictedOccupancy.percentage + '<span class="text-sm font-semibold text-brand-600">%</span>');
        setText('kpi-prediction-analysis', data.predictedOccupancy.analysis);

        // Occupancy bar
        const bar = document.getElementById('kpi-occupancy-bar');
        if (bar) bar.style.width = Math.min(data.avgOccupancy, 100) + '%';

        // ── AI Occupancy Panel ───────────────────────────────────────────────
        setText('donut-pct',       data.predictedOccupancy.percentage + '%');
        setText('ai-analysis-text', '"' + data.predictedOccupancy.analysis + '"');
        setText('stat-actual',     data.avgOccupancy + '%');
        setText('stat-predicted',  data.predictedOccupancy.percentage + '%');
        const diff = data.predictedOccupancy.percentage - data.avgOccupancy;
        const diffEl = document.getElementById('stat-diff');
        if (diffEl) {
            diffEl.textContent = (diff >= 0 ? '+' : '') + diff + '%';
            diffEl.className = 'text-sm font-extrabold mt-0.5 ' + (diff >= 0 ? 'text-emerald-400' : 'text-rose-400');
        }
        // Donut SVG
        const circ = document.getElementById('donut-circle');
        if (circ) {
            const r   = 46;
            const tot = 2 * Math.PI * r;
            circ.setAttribute('stroke-dashoffset', (tot * (1 - data.predictedOccupancy.percentage / 100)).toFixed(2));
        }

        // ── Schedule Table ───────────────────────────────────────────────────
        const stw = document.getElementById('schedule-table-wrap');
        if (stw) stw.innerHTML = buildScheduleTable(data.schedules);

        // ── Anomaly List ─────────────────────────────────────────────────────
        const anomList = document.getElementById('anomaly-list');
        if (anomList) anomList.innerHTML = buildAnomalyList(data.anomalies);
        const badge = document.getElementById('anomaly-badge');
        if (badge) {
            const cnt = (data.anomalies || []).length;
            badge.textContent = cnt + ' kasus';
            badge.className = 'text-[9px] font-bold px-2 py-0.5 rounded-full border ' +
                (cnt > 0 ? 'text-rose-400 bg-rose-500/10 border-rose-500/20' : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20');
        }

        // ── Sentiment ────────────────────────────────────────────────────────
        setText('sent-pos', data.sentimentCounts.positive);
        setText('sent-neu', data.sentimentCounts.neutral);
        setText('sent-neg', data.sentimentCounts.negative);
        if (sentimentChart) {
            sentimentChart.data.datasets[0].data = [
                data.sentimentCounts.positive,
                data.sentimentCounts.neutral,
                data.sentimentCounts.negative
            ];
            sentimentChart.update();
        }

        // ── Timestamp ────────────────────────────────────────────────────────
        setText('last-update-time', data.lastUpdated + ' WIB');
        if (ind) ind.style.background = '#10b981';

    } catch (err) {
        console.error('Dashboard refresh failed:', err);
        if (ind) ind.style.background = '#f43f5e';
    }

    // restart countdown
    countdown = 30;
    startCountdown();
}

function setText(id, html) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = html;
}

function startCountdown() {
    clearInterval(refreshTimer);
    refreshTimer = setInterval(() => {
        countdown--;
        const el = document.getElementById('refresh-countdown');
        if (el) el.textContent = countdown;
        if (countdown <= 0) {
            forceRefresh();
        }
    }, 1000);
}

// ─── Init on DOM ready ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();

    // Chart.js sentiment donut
    const ctx = document.getElementById('sentimentChart');
    if (ctx) {
        sentimentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Positif', 'Netral', 'Negatif'],
                datasets: [{
                    data: [
                        <?= $sentimentCounts['positive'] ?>,
                        <?= $sentimentCounts['neutral'] ?>,
                        <?= $sentimentCounts['negative'] ?>
                    ],
                    backgroundColor: ['rgba(16,185,129,.85)', 'rgba(100,116,139,.85)', 'rgba(244,63,94,.85)'],
                    borderColor: '#0d1117',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#e2e8f0',
                        bodyColor: '#94a3b8',
                        borderColor: 'rgba(99,102,241,.2)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 10
                    }
                },
                cutout: '72%',
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Start auto-refresh
    startCountdown();
});
</script>

<?php if (!$hasReviews): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<?= $this->endSection() ?>
