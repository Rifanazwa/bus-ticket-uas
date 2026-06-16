<?= $this->extend('layout/admin') ?>

<?= $this->section('admin_actions') ?>
<form method="GET" action="<?= base_url('admin/report') ?>" class="flex flex-wrap items-center gap-2">
    <div class="flex items-center gap-1 bg-slate-900/60 border border-slate-800/60 rounded-xl px-2.5 py-1">
        <label for="start_date" class="text-[9px] font-bold text-slate-500 uppercase">Dari</label>
        <input type="date" name="start_date" id="start_date" value="<?= esc($startDate) ?>" 
               class="bg-transparent border-0 text-slate-200 text-xs focus:ring-0 p-0 w-28 text-center" />
    </div>
    <div class="flex items-center gap-1 bg-slate-900/60 border border-slate-800/60 rounded-xl px-2.5 py-1">
        <label for="end_date" class="text-[9px] font-bold text-slate-500 uppercase">Sampai</label>
        <input type="date" name="end_date" id="end_date" value="<?= esc($endDate) ?>" 
               class="bg-transparent border-0 text-slate-200 text-xs focus:ring-0 p-0 w-28 text-center" />
    </div>
    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-[11px] font-bold transition-all shadow-md shadow-brand-600/15 flex items-center gap-1">
        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
        Filter
    </button>
    <?php if (service('request')->getGet('start_date') || service('request')->getGet('end_date')): ?>
        <a href="<?= base_url('admin/report') ?>" class="p-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-slate-200 transition-all" title="Reset Filter">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
        </a>
    <?php endif; ?>
</form>
<?= $this->endSection() ?>

<?= $this->section('admin_content') ?>

<?php
/**
 * Simple Markdown Parser for Executive AI Analysis
 */
if (!function_exists('parseMarkdown')) {
    function parseMarkdown($text) {
        $text = str_replace("\r", "", $text);
        
        // Escape HTML
        $text = esc($text);

        // Bold
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);

        // Bullet lists
        $text = preg_replace('/^\-\s+(.+)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/^\*\s+(.+)$/m', '<li>$1</li>', $text);
        
        // Wrap lists
        $text = preg_replace('/((?:<li>.*<\/li>\n*)+)/', '<ul class="list-disc pl-5 my-2 text-slate-300 space-y-1">$1</ul>', $text);

        // Headers (h3)
        $text = preg_replace('/^###\s+(.+)$/m', '<h3 class="text-sm font-bold text-indigo-300 mt-4 mb-2 flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>$1</h3>', $text);
        // Headers (h2)
        $text = preg_replace('/^##\s+(.+)$/m', '<h2 class="text-base font-bold text-white mt-5 mb-3 pb-1 border-b border-slate-850">$1</h2>', $text);
        // Headers (h1)
        $text = preg_replace('/^#\s+(.+)$/m', '<h1 class="text-lg font-extrabold text-white mt-6 mb-4">$1</h1>', $text);

        // Paragraphs
        $blocks = explode("\n\n", $text);
        foreach ($blocks as &$block) {
            $block = trim($block);
            if ($block === '') continue;
            if (!preg_match('/^<(h1|h2|h3|ul|li|p|div)/', $block)) {
                $block = '<p class="text-xs text-slate-300 leading-relaxed my-2.5">' . nl2br($block) . '</p>';
            }
        }
        
        return implode("\n", $blocks);
    }
}
?>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div x-data="{ activeTab: 'ringkasan' }" class="space-y-6">

    <!-- Premium Tab Controls -->
    <div class="flex items-center justify-between border-b border-slate-800/60 pb-3 flex-wrap gap-3">
        <div class="flex items-center gap-1.5 bg-slate-950/40 p-1.5 rounded-2xl border border-slate-800/50">
            <button @click="activeTab = 'ringkasan'" 
                    :class="activeTab === 'ringkasan' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/15' : 'text-slate-400 hover:text-slate-200'"
                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                Ringkasan &amp; Analisis AI
            </button>
            <button @click="activeTab = 'keuangan'" 
                    :class="activeTab === 'keuangan' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/15' : 'text-slate-400 hover:text-slate-200'"
                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                Laporan Keuangan
            </button>
            <button @click="activeTab = 'armada'" 
                    :class="activeTab === 'armada' ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/15' : 'text-slate-400 hover:text-slate-200'"
                    class="px-4 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                Laporan Armada
            </button>
        </div>

        <!-- Download Buttons mapped to Active Tab -->
        <div class="flex items-center gap-2">
            <a x-show="activeTab === 'keuangan'" 
               href="<?= base_url('admin/report/export/financial?start_date=' . $startDate . '&end_date=' . $endDate) ?>"
               class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-bold text-slate-200 hover:text-white transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="download" class="w-3.5 h-3.5 text-brand-400"></i>
                Ekspor Keuangan (CSV)
            </a>
            <a x-show="activeTab === 'armada'" 
               href="<?= base_url('admin/report/export/fleet') ?>"
               class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-bold text-slate-200 hover:text-white transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="download" class="w-3.5 h-3.5 text-indigo-400"></i>
                Ekspor Armada (CSV)
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: RINGKASAN & ANALISIS AI -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'ringkasan'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-6">
         
        <!-- Top Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Revenue Card -->
            <div class="panel-card p-6 flex flex-col justify-between space-y-4 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-20 h-20 bg-brand-500/5 rounded-full blur-xl translate-x-4 -translate-y-4"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Total Pendapatan</h3>
                    <div class="p-2 bg-brand-500/10 text-brand-400 rounded-xl border border-brand-500/20 group-hover:border-brand-500/40 transition-colors">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="space-y-1">
                    <span class="text-2xl font-extrabold text-white font-mono tracking-tight">
                        Rp <?= number_format($financialSummary['total_revenue'], 0, ',', '.') ?>
                    </span>
                    <p class="text-[10px] text-slate-500">
                        Periode: <span class="font-semibold text-slate-400"><?= date('d M', strtotime($startDate)) ?> - <?= date('d M Y', strtotime($endDate)) ?></span>
                    </p>
                </div>
            </div>

            <!-- Transaction Volume Card -->
            <div class="panel-card p-6 flex flex-col justify-between space-y-4 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-500/5 rounded-full blur-xl translate-x-4 -translate-y-4"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Transaksi Tiket</h3>
                    <div class="p-2 bg-emerald-500/10 text-emerald-400 rounded-xl border border-emerald-500/20 group-hover:border-emerald-500/40 transition-colors">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-extrabold text-white font-mono tracking-tight"><?= $financialSummary['total_transactions'] ?></span>
                        <span class="text-xs text-slate-500 font-semibold">Berhasil</span>
                    </div>
                    <p class="text-[10px] text-slate-500">
                        Rata-rata tiket: <span class="font-semibold text-slate-400">Rp <?= number_format($financialSummary['avg_booking_val'], 0, ',', '.') ?></span>
                    </p>
                </div>
            </div>

            <!-- Occupancy Card -->
            <div class="panel-card p-6 flex flex-col justify-between space-y-4 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-20 h-20 bg-indigo-500/5 rounded-full blur-xl translate-x-4 -translate-y-4"></div>
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Tingkat Okupansi Armada</h3>
                    <div class="p-2 bg-indigo-500/10 text-indigo-400 rounded-xl border border-indigo-500/20 group-hover:border-indigo-500/40 transition-colors">
                        <i data-lucide="percent" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-extrabold text-white font-mono tracking-tight"><?= $avgFleetOccupancy ?>%</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-bold uppercase tracking-wider">Rata-rata</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-slate-900 mt-2 overflow-hidden border border-slate-800/40">
                        <div class="h-full bg-gradient-to-r from-brand-500 to-indigo-400 rounded-full" style="width: <?= $avgFleetOccupancy ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Weekly Chart Card -->
            <div class="panel-card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/50 pb-3">
                    <div>
                        <h4 class="text-xs font-bold text-white flex items-center gap-1.5">
                            <i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-brand-400"></i>
                            Tren Pendapatan Harian
                        </h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Pendapatan sukses dalam 7 hari terakhir.</p>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas id="weeklyRevenueChart"></canvas>
                </div>
            </div>

            <!-- Monthly Chart Card -->
            <div class="panel-card p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800/50 pb-3">
                    <div>
                        <h4 class="text-xs font-bold text-white flex items-center gap-1.5">
                            <i data-lucide="line-chart" class="w-3.5 h-3.5 text-indigo-400"></i>
                            Tren Pendapatan Bulanan
                        </h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Akumulasi pendapatan sukses 6 bulan terakhir.</p>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Google Gemini Strategic Insights Panel -->
        <div class="p-6 bg-gradient-to-br from-indigo-950/40 to-brand-950/20 border border-indigo-500/25 rounded-3xl relative overflow-hidden space-y-4">
            <!-- Backdrop glow effect -->
            <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl -translate-y-12 translate-x-12 pointer-events-none"></div>
            
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-gradient-to-tr from-brand-600 to-indigo-400 text-white rounded-2xl border border-indigo-400/30 flex-shrink-0 shadow-lg shadow-brand-500/10">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[9px] font-extrabold uppercase tracking-widest text-indigo-400">Keputusan &amp; Rekomendasi Strategis AI</span>
                        <h4 class="text-sm font-bold text-white flex items-center gap-1.5 mt-0.5">
                            Dianalisis oleh Google Gemini
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">Live Intelligence</span>
                        </h4>
                    </div>
                </div>
                <span class="text-[9px] text-slate-500 bg-slate-900/60 border border-slate-800/60 px-2.5 py-1 rounded-xl font-mono">
                    Updated: <?= date('d M Y, H:i') ?> WIB
                </span>
            </div>

            <!-- AI Output Container -->
            <div class="ai-output bg-slate-950/60 border border-slate-900/80 p-5 rounded-2xl relative">
                <?= parseMarkdown($aiInsights) ?>
            </div>
            
            <p class="text-[10px] text-slate-500 flex items-center gap-1">
                <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-500"></i>
                Rekomendasi ini dihasilkan secara real-time berdasarkan data keuangan, okupansi, armada terpopuler, dan volume transaksi.
            </p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: LAPORAN KEUANGAN -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'keuangan'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-6"
         x-cloak>
         
        <!-- Financial KPI Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="panel-card p-4 space-y-2">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pendapatan Bersih</p>
                <p class="text-lg font-bold text-white font-mono">Rp <?= number_format($financialSummary['total_revenue'], 0, ',', '.') ?></p>
            </div>
            <div class="panel-card p-4 space-y-2">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Volume Transaksi</p>
                <p class="text-lg font-bold text-white font-mono"><?= $financialSummary['total_transactions'] ?> Transaksi</p>
            </div>
            <div class="panel-card p-4 space-y-2">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nilai Tiket Rata-rata</p>
                <p class="text-lg font-bold text-white font-mono">Rp <?= number_format($financialSummary['avg_booking_val'], 0, ',', '.') ?></p>
            </div>
            <div class="panel-card p-4 space-y-2">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Metode Pembayaran</p>
                <div class="flex flex-wrap gap-1.5 pt-0.5">
                    <?php if (empty($financialSummary['methods'])): ?>
                        <span class="text-[10px] text-slate-600">-</span>
                    <?php else: ?>
                        <?php foreach ($financialSummary['methods'] as $method => $meta): ?>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300">
                                <?= esc($method) ?> (<?= $meta['count'] ?>)
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Transactions Manifest Table -->
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800/60 pb-4">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <div class="p-1.5 bg-brand-500/10 text-brand-400 rounded-lg border border-brand-500/20">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        Rincian Manifest Transaksi Keuangan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 ml-9">Daftar lengkap pembayaran terfilter rentang tanggal di atas.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal Pembayaran</th>
                            <th class="py-3 px-4">ID Transaksi</th>
                            <th class="py-3 px-4">Kode Booking</th>
                            <th class="py-3 px-4">Nama Pelanggan</th>
                            <th class="py-3 px-4">Metode</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50">
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-slate-500">
                                    <div class="flex flex-col items-center gap-1">
                                        <i data-lucide="receipt" class="w-6 h-6 text-slate-600"></i>
                                        <span>Tidak ada data pembayaran dalam rentang tanggal ini.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                                <tr class="table-row text-xs">
                                    <td class="py-3.5 px-4 font-medium text-slate-400">
                                        <?= date('d M Y, H:i', strtotime($p['paid_at'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-semibold text-slate-300">
                                        <?= esc($p['transaction_id']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-extrabold text-indigo-400">
                                        <?= esc($p['booking_code']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-200">
                                        <?= esc($p['customer_name']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-400">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-900 border border-slate-800">
                                            <?= strtoupper(esc($p['method'])) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-white">
                                        Rp <?= number_format($p['amount'], 0, ',', '.') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ($p['status'] === 'success'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 uppercase">
                                                <span class="w-1 h-1 rounded-full bg-emerald-400"></span> Sukses
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-500/10 text-rose-400 border border-rose-500/25 uppercase">
                                                <span class="w-1 h-1 rounded-full bg-rose-400"></span> <?= esc($p['status']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: LAPORAN ARMADA -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'armada'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-6"
         x-cloak>
         
        <!-- Fleet KPI Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Armada -->
            <div class="panel-card p-5 space-y-2">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Armada Bus</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-white font-mono"><?= count($fleetReport) ?></span>
                    <span class="text-xs text-slate-500">Unit terdaftar</span>
                </div>
            </div>
            
            <!-- Fleet Occupancy -->
            <div class="panel-card p-5 space-y-2">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rata-rata Okupansi</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-400 font-mono"><?= $avgFleetOccupancy ?>%</span>
                    <span class="text-xs text-slate-500">Kapasitas Kursi</span>
                </div>
            </div>
            
            <!-- Top Passengers Bus -->
            <div class="panel-card p-5 space-y-2 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-16 h-16 bg-indigo-500/5 rounded-full blur-xl translate-x-4 -translate-y-4"></div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bus Terpopuler (Penumpang)</p>
                <?php if ($topBus): ?>
                    <p class="text-xs font-extrabold text-white truncate"><?= esc($topBus['bus_name']) ?></p>
                    <p class="text-[9px] text-slate-400 font-mono"><?= $topBus['total_passengers'] ?> Penumpang &middot; <?= $topBus['total_trips'] ?> Trip</p>
                <?php else: ?>
                    <p class="text-xs font-extrabold text-slate-500">-</p>
                <?php endif; ?>
            </div>
            
            <!-- Top Revenue Bus -->
            <div class="panel-card p-5 space-y-2 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-16 h-16 bg-brand-500/5 rounded-full blur-xl translate-x-4 -translate-y-4"></div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bus Berpendapatan Tertinggi</p>
                <?php if ($topRevenueBus): ?>
                    <p class="text-xs font-extrabold text-indigo-400 truncate"><?= esc($topRevenueBus['bus_name']) ?></p>
                    <p class="text-[9px] text-slate-300 font-mono">Rp <?= number_format($topRevenueBus['total_revenue'], 0, ',', '.') ?></p>
                <?php else: ?>
                    <p class="text-xs font-extrabold text-slate-500">-</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fleet Performance Analysis Table -->
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800/60 pb-4">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <div class="p-1.5 bg-indigo-500/10 text-indigo-400 rounded-lg border border-indigo-500/20">
                            <i data-lucide="bar-chart-horizontal" class="w-4 h-4"></i>
                        </div>
                        Laporan Efisiensi &amp; Kinerja Armada Bus
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 ml-9">Metrik performansi perjalanan, volume okupansi penumpang, dan total hasil operasional.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Bus</th>
                            <th class="py-3 px-4">Tipe Bus</th>
                            <th class="py-3 px-4 text-center">Kapasitas</th>
                            <th class="py-3 px-4 text-center">Total Trip</th>
                            <th class="py-3 px-4 text-center">Total Penumpang</th>
                            <th class="py-3 px-4" style="width: 200px;">Rata-rata Okupansi (%)</th>
                            <th class="py-3 px-4 text-right">Pendapatan Dihasilkan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850/50">
                        <?php if (empty($fleetReport)): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-slate-500">
                                    <div class="flex flex-col items-center gap-1">
                                        <i data-lucide="truck" class="w-6 h-6 text-slate-600"></i>
                                        <span>Tidak ada data armada bus terdaftar.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fleetReport as $f): ?>
                                <tr class="table-row text-xs">
                                    <td class="py-3.5 px-4 font-bold text-slate-200">
                                        <?= esc($f['bus_name']) ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-900 border border-slate-800 text-[10px] text-slate-400 font-semibold uppercase tracking-wider">
                                            <?= esc($f['bus_type']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono text-slate-400">
                                        <?= $f['total_seats'] ?> kursi
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-300">
                                        <?= $f['total_trips'] ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-300">
                                        <?= $f['total_passengers'] ?> orang
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-8 text-right text-[10px] font-mono font-bold text-slate-300">
                                                <?= $f['avg_occupancy_pct'] ?>%
                                            </span>
                                            <div class="flex-1 h-1.5 rounded-full bg-slate-900 overflow-hidden border border-slate-800/40">
                                                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-indigo-400" 
                                                     style="width: <?= $f['avg_occupancy_pct'] ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-extrabold text-white">
                                        Rp <?= number_format($f['total_revenue'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Setup Chart.js scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Weekly Revenue Chart
    const weeklyLabels = <?= json_encode(array_column($weeklyRevenue, 'label')) ?>;
    const weeklyValues = <?= json_encode(array_column($weeklyRevenue, 'value')) ?>;
    
    const ctxWeekly = document.getElementById('weeklyRevenueChart').getContext('2d');
    
    // Gradient for weekly revenue bars
    const gradientWeekly = ctxWeekly.createLinearGradient(0, 0, 0, 300);
    gradientWeekly.addColorStop(0, 'rgba(99, 102, 241, 0.85)');
    gradientWeekly.addColorStop(1, 'rgba(99, 102, 241, 0.15)');

    new Chart(ctxWeekly, {
        type: 'bar',
        data: {
            labels: weeklyLabels,
            datasets: [{
                label: 'Pendapatan (IDR)',
                data: weeklyValues,
                backgroundColor: gradientWeekly,
                borderColor: '#6366f1',
                borderWidth: 1.5,
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.55
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0d1526',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(99, 102, 241, 0.25)',
                    borderWidth: 1,
                    cornerRadius: 12,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 10, family: 'Plus Jakarta Sans' } }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.04)', drawTicks: false },
                    border: { dash: [4, 4] },
                    ticks: {
                        color: '#64748b',
                        font: { size: 10, family: 'Plus Jakarta Sans' },
                        callback: function(value) {
                            if (value >= 1e6) return 'Rp ' + (value / 1e6).toFixed(1) + 'jt';
                            if (value >= 1e3) return 'Rp ' + (value / 1e3).toFixed(0) + 'rb';
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });

    // 2. Monthly Revenue Chart
    const monthlyLabels = <?= json_encode(array_column($monthlyRevenue, 'label')) ?>;
    const monthlyValues = <?= json_encode(array_column($monthlyRevenue, 'value')) ?>;
    
    const ctxMonthly = document.getElementById('monthlyRevenueChart').getContext('2d');
    
    // Gradient for monthly area fill
    const gradientMonthly = ctxMonthly.createLinearGradient(0, 0, 0, 300);
    gradientMonthly.addColorStop(0, 'rgba(14, 165, 233, 0.25)');
    gradientMonthly.addColorStop(1, 'rgba(14, 165, 233, 0)');

    new Chart(ctxMonthly, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Pendapatan Bulanan (IDR)',
                data: monthlyValues,
                borderColor: '#0ea5e9',
                borderWidth: 3,
                pointBackgroundColor: '#0ea5e9',
                pointBorderColor: '#0a0f1e',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.35,
                fill: true,
                backgroundColor: gradientMonthly
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0d1526',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(14, 165, 233, 0.25)',
                    borderWidth: 1,
                    cornerRadius: 12,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 10, family: 'Plus Jakarta Sans' } }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.04)', drawTicks: false },
                    border: { dash: [4, 4] },
                    ticks: {
                        color: '#64748b',
                        font: { size: 10, family: 'Plus Jakarta Sans' },
                        callback: function(value) {
                            if (value >= 1e6) return 'Rp ' + (value / 1e6).toFixed(1) + 'jt';
                            if (value >= 1e3) return 'Rp ' + (value / 1e3).toFixed(0) + 'rb';
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
/* CSS Styles for Executive AI Analysis Output */
.ai-output h2 { 
    font-size: 1.05rem; 
    font-weight: 800; 
    color: #ffffff; 
    margin-top: 1.25rem; 
    margin-bottom: 0.5rem; 
    letter-spacing: -0.01em;
}
.ai-output h3 { 
    font-size: 0.85rem; 
    font-weight: 700; 
    color: #a5b4fc; 
    margin-top: 0.75rem; 
    margin-bottom: 0.25rem;
}
.ai-output p { 
    font-size: 0.8rem; 
    color: #cbd5e1; 
    line-height: 1.6; 
    margin-bottom: 0.75rem; 
}
.ai-output ul { 
    list-style-type: disc; 
    padding-left: 1.25rem; 
    margin-bottom: 0.75rem; 
}
.ai-output li { 
    font-size: 0.8rem; 
    color: #cbd5e1; 
    margin-bottom: 0.25rem; 
}
.ai-output strong { 
    color: #ffffff; 
    font-weight: 700; 
}
</style>

<?= $this->endSection() ?>
