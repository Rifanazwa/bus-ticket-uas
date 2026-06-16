<?= $this->extend('layout/admin') ?>

<?= $this->section('admin_content') ?>

<div class="space-y-6">
    <!-- Top Row Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Line Chart Widget (2/3) -->
        <div class="lg:col-span-2 panel-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <div class="p-1.5 bg-brand-500/10 text-brand-400 rounded-lg border border-brand-500/20">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        AI Tren Okupansi 7 Hari Ke Depan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 ml-9">Perbandingan antara okupansi terpesan (aktual) dan hasil prediksi model AI.</p>
                </div>
                <span class="flex items-center gap-1 text-[9px] font-bold text-brand-400 bg-brand-500/10 border border-brand-500/20 px-2 py-1 rounded-full">
                    <i data-lucide="sparkles" class="w-3 h-3"></i> Gemini AI
                </span>
            </div>

            <!-- Chart container -->
            <div class="h-64 relative w-full bg-slate-950/40 rounded-2xl p-4 border border-slate-800/40 flex items-center justify-center">
                <canvas id="occupancyPredictionChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Right: AI Advice Column (1/3) -->
        <div class="panel-card p-6 space-y-5">
            <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800/60 pb-3">
                <div class="p-1.5 bg-brand-500/10 text-brand-400 rounded-lg border border-brand-500/20">
                    <i data-lucide="bot" class="w-4 h-4"></i>
                </div>
                Rekomendasi AI Strategis
            </h3>

            <div class="space-y-4">
                <!-- 1. Armada -->
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-indigo-400">
                        <i data-lucide="bus" class="w-4 h-4"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Alokasi Armada</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed bg-slate-900/60 p-3 rounded-xl border border-slate-800/40">
                        <?= esc($advice['armada']) ?>
                    </p>
                </div>

                <!-- 2. Pricing -->
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-amber-400">
                        <i data-lucide="coins" class="w-4 h-4"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Dynamic Pricing</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed bg-slate-900/60 p-3 rounded-xl border border-slate-800/40">
                        <?= esc($advice['pricing']) ?>
                    </p>
                </div>

                <!-- 3. Marketing -->
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-emerald-400">
                        <i data-lucide="megaphone" class="w-4 h-4"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Promo &amp; Kampanye</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed bg-slate-900/60 p-3 rounded-xl border border-slate-800/40">
                        <?= esc($advice['marketing']) ?>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Row: Route list stats -->
    <div class="panel-card p-6 space-y-4">
        <h3 class="text-sm font-bold text-white flex items-center gap-2">
            <div class="p-1.5 bg-teal-500/10 text-teal-400 rounded-lg border border-teal-500/20">
                <i data-lucide="map-pinned" class="w-4 h-4"></i>
            </div>
            Tingkat Okupansi per Rute (Semua Jadwal)
        </h3>

        <div class="overflow-x-auto rounded-xl border border-slate-800/50">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-500 text-[10px] uppercase tracking-widest font-bold border-b border-slate-800/60">
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Rute Perjalanan</th>
                        <th class="px-4 py-3">Total Jadwal</th>
                        <th class="px-4 py-3">Rata-Rata Okupansi Aktual</th>
                        <th class="px-4 py-3">Status Rute</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routeStats as $i => $r): ?>
                        <?php 
                            $pct = (float)$r['occupancy']; 
                            $color = $pct > 80 ? 'bg-rose-500' : ($pct > 40 ? 'bg-amber-500' : 'bg-emerald-500');
                            $badge = $pct > 80 ? 'text-rose-400 bg-rose-500/10 border-rose-500/20' : ($pct > 40 ? 'text-amber-400 bg-amber-500/10 border-amber-500/20' : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20');
                            $status = $pct > 80 ? 'High Demand' : ($pct > 40 ? 'Stable' : 'Low Demand');
                        ?>
                        <tr class="table-row border-b border-slate-800/30">
                            <td class="px-4 py-3 text-slate-400 font-mono"><?= $i + 1 ?></td>
                            <td class="px-4 py-3 font-bold text-white"><?= esc($r['origin']) ?> &rarr; <?= esc($r['destination']) ?></td>
                            <td class="px-4 py-3 text-slate-300 font-mono"><?= $r['total_trips'] ?> kali</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-slate-850 h-2 rounded-full overflow-hidden max-w-[120px]">
                                        <div class="h-full rounded-full <?= $color ?>" style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="font-bold text-white font-mono"><?= $pct ?>%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 rounded-full border text-[9px] font-extrabold uppercase <?= $badge ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('occupancyPredictionChart').getContext('2d');
    
    const dayLabels = <?= json_encode($dayLabels) ?>;
    const actualData = <?= json_encode(array_column($predictions, 'actual')) ?>;
    const predictedData = <?= json_encode(array_column($predictions, 'predicted')) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dayLabels,
            datasets: [
                {
                    label: 'Okupansi Terpesan (%)',
                    data: actualData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6366f1'
                },
                {
                    label: 'Prediksi AI (%)',
                    data: predictedData,
                    borderColor: '#818cf8',
                    borderDash: [5, 5],
                    backgroundColor: 'transparent',
                    tension: 0.35,
                    borderWidth: 2,
                    pointBackgroundColor: '#818cf8'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#94a3b8',
                        font: { family: 'Plus Jakarta Sans', size: 10, weight: '600' }
                    }
                },
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
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    grid: { color: 'rgba(51, 65, 85, 0.25)' },
                    ticks: {
                        color: '#64748b',
                        font: { family: 'Plus Jakarta Sans', size: 9 },
                        callback: function(value) { return value + '%'; }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { family: 'Plus Jakarta Sans', size: 9 }
                    }
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
