<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">

    <!-- ===================== SEARCH HEADER BAR ===================== -->
    <div class="glass rounded-2xl p-4 sm:p-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="p-2.5 bg-brand-500/10 text-brand-400 rounded-xl border border-brand-500/20">
                <i data-lucide="map-pinned" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 text-white font-bold text-lg">
                    <span><?= esc($origin) ?></span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-slate-500"></i>
                    <span><?= esc($destination) ?></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <?= date('l, d F Y', strtotime($date)) ?>
                    <span class="mx-1 text-slate-700">·</span>
                    <span class="font-semibold text-slate-300"><?= count($schedules) ?> Jadwal Tersedia</span>
                </p>
            </div>
        </div>

        <!-- Re-search mini form -->
        <div class="flex items-center gap-2">
            <?php if ($isGuest): ?>
                <a href="<?= base_url('login') ?>" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-500 text-white transition-all flex items-center gap-1.5 shadow-md shadow-brand-600/20">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Login untuk Rekomendasi AI
                </a>
            <?php endif; ?>
            <a href="<?= base_url('/') ?>" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-300 border border-white/10 hover:bg-white/5 flex items-center gap-1.5 transition-all">
                <i data-lucide="search" class="w-3.5 h-3.5"></i> Ubah Pencarian
            </a>
        </div>
    </div>

    <!-- Guest AI Upsell Banner -->
    <?php if ($isGuest): ?>
        <div class="flex items-start sm:items-center gap-4 p-4 rounded-2xl bg-brand-500/5 border border-brand-500/15 mb-6">
            <div class="p-2.5 bg-brand-500/10 rounded-xl flex-shrink-0">
                <i data-lucide="sparkles" class="w-5 h-5 text-brand-400"></i>
            </div>
            <div class="flex-grow min-w-0">
                <p class="text-sm font-semibold text-white">Dapatkan Rekomendasi AI Gratis!</p>
                <p class="text-xs text-slate-400 mt-0.5">Login atau daftar untuk mendapatkan rekomendasi jadwal terbaik yang dipilih AI berdasarkan harga, kenyamanan, dan waktu perjalanan Anda.</p>
            </div>
            <div class="flex-shrink-0 flex gap-2">
                <a href="<?= base_url('login') ?>" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 border border-white/10 hover:bg-white/5 transition-all whitespace-nowrap">Masuk</a>
                <a href="<?= base_url('register') ?>" class="px-3 py-2 rounded-xl text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 transition-all whitespace-nowrap">Daftar</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- AI Recommendation Banner (Logged-in only) -->
    <?php if ($aiRecommendation && !empty($schedules)): ?>
        <?php
            $recSchedule = null;
            foreach ($schedules as $s) {
                if ($s['id'] == $aiRecommendation['schedule_id']) {
                    $recSchedule = $s;
                    break;
                }
            }
        ?>
        <?php if ($recSchedule): ?>
            <div class="relative p-5 rounded-2xl bg-gradient-to-r from-brand-900/40 to-indigo-950/40 border border-brand-500/25 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 shadow-xl shadow-brand-900/20">
                <div class="absolute -top-3 left-5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gradient-to-r from-brand-600 to-indigo-600 text-white flex items-center gap-1 shadow-md">
                    <i data-lucide="sparkles" class="w-3 h-3"></i> Pilihan Terbaik AI
                </div>
                <div class="flex-grow mt-1">
                    <h4 class="font-bold text-white"><?= esc($recSchedule['bus_name']) ?> <span class="font-normal text-slate-400 text-sm">(<?= esc($recSchedule['bus_type']) ?>)</span></h4>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed"><?= esc($aiRecommendation['reason']) ?></p>
                </div>
                <a href="<?= base_url('customer/booking/create/' . $recSchedule['id']) ?>" class="flex-shrink-0 px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-bold shadow-md shadow-brand-600/20 flex items-center gap-1.5 transition-all w-full sm:w-auto justify-center">
                    Pilih Ini <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ===================== RESULTS LIST ===================== -->
    <div class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider px-1">Semua Jadwal</h3>

        <?php if (empty($schedules)): ?>
            <div class="glass rounded-3xl p-16 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/80 flex items-center justify-center mx-auto">
                    <i data-lucide="calendar-x" class="w-8 h-8 text-slate-600"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-white">Jadwal Tidak Ditemukan</h4>
                    <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Maaf, tidak ada jadwal bus untuk rute dan tanggal yang Anda pilih. Coba tanggal lain.</p>
                </div>
                <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 transition-all">
                    <i data-lucide="search" class="w-4 h-4"></i> Cari Ulang
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($schedules as $s): ?>
                <?php
                    $pct = $s['total_seats'] > 0 ? round((($s['total_seats'] - $s['remaining_seats']) / $s['total_seats']) * 100) : 0;
                    $typeColor = match($s['bus_type']) {
                        'Bisnis'    => 'amber',
                        'Eksekutif' => 'brand',
                        default     => 'emerald'
                    };
                    $dur_h = floor($s['estimated_duration'] / 60);
                    $dur_m = $s['estimated_duration'] % 60;
                    $durStr = ($dur_h > 0 ? $dur_h . 'j ' : '') . ($dur_m > 0 ? $dur_m . 'm' : '');
                ?>
                <div class="bus-card glass-light rounded-2xl overflow-hidden">
                    <!-- Top portion -->
                    <div class="p-5 flex flex-col md:flex-row md:items-center gap-5">
                        <!-- Bus Icon + Name -->
                        <div class="flex items-center gap-3 md:w-48 flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-<?= $typeColor ?>-500/10 border border-<?= $typeColor ?>-500/20 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="bus" class="w-5 h-5 text-<?= $typeColor ?>-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-white text-sm leading-tight"><?= esc($s['bus_name']) ?></p>
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-<?= $typeColor ?>-500/10 text-<?= $typeColor ?>-400 border border-<?= $typeColor ?>-500/20">
                                    <?= esc($s['bus_type']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="flex-grow flex items-center gap-4 justify-between md:justify-center">
                            <!-- Departure -->
                            <div class="text-center sm:text-left">
                                <p class="text-2xl font-extrabold text-white font-inter tracking-tight"><?= date('H:i', strtotime($s['departure_time'])) ?></p>
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-0.5"><?= esc($s['origin']) ?></p>
                            </div>

                            <!-- Duration line -->
                            <div class="flex-grow flex flex-col items-center gap-1.5 min-w-[80px] max-w-[140px]">
                                <span class="text-[10px] text-slate-500 font-mono font-semibold"><?= $durStr ?></span>
                                <div class="w-full flex items-center gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-600 flex-shrink-0"></div>
                                    <div class="flex-grow h-0.5 bg-gradient-to-r from-slate-700 via-brand-600/40 to-slate-700 relative">
                                        <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-1.5 h-1.5 -mt-0.5 rounded-full bg-brand-500"></div>
                                    </div>
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-600 flex-shrink-0"></div>
                                </div>
                                <span class="text-[10px] text-slate-600 font-semibold">Langsung</span>
                            </div>

                            <!-- Arrival -->
                            <div class="text-center sm:text-right">
                                <p class="text-2xl font-extrabold text-white font-inter tracking-tight"><?= date('H:i', strtotime($s['arrival_time'])) ?></p>
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-0.5"><?= esc($s['destination']) ?></p>
                            </div>
                        </div>

                        <!-- Price + Action -->
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 sm:w-40 flex-shrink-0 border-t md:border-t-0 md:border-l border-white/5 pt-4 md:pt-0 md:pl-5">
                            <div class="text-left sm:text-right">
                                <p class="text-[10px] text-slate-500 mb-0.5">Harga/kursi</p>
                                <p class="text-xl font-extrabold text-emerald-400 font-inter">Rp <?= number_format($s['price'], 0, ',', '.') ?></p>
                            </div>

                            <?php if ($s['remaining_seats'] <= 0): ?>
                                <button disabled class="w-full sm:w-auto px-5 py-2.5 bg-slate-800 text-slate-600 rounded-xl text-xs font-bold border border-slate-700 cursor-not-allowed">
                                    Habis Terjual
                                </button>
                            <?php elseif ($isGuest): ?>
                                <a href="<?= base_url('login') ?>" class="w-full sm:w-auto px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-md shadow-brand-600/20">
                                    Login & Pilih <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('customer/booking/create/' . $s['id']) ?>" class="w-full sm:w-auto px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-md shadow-brand-600/20">
                                    Pilih Kursi <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bottom strip: occupancy + remaining seats -->
                    <div class="px-5 py-2.5 border-t border-white/5 bg-slate-950/30 flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-2 flex-grow max-w-xs">
                            <div class="flex-grow h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full <?= $pct > 80 ? 'bg-rose-500' : ($pct > 50 ? 'bg-amber-500' : 'bg-emerald-500') ?> progress-fill" style="width: <?= $pct ?>%"></div>
                            </div>
                            <span class="text-[11px] text-slate-400 whitespace-nowrap font-medium">
                                <span class="font-bold text-white"><?= $s['remaining_seats'] ?></span> / <?= $s['total_seats'] ?> kursi tersisa
                            </span>
                        </div>
                        <?php if ($s['remaining_seats'] <= 5 && $s['remaining_seats'] > 0): ?>
                            <span class="text-[10px] text-rose-400 font-bold flex items-center gap-1 bg-rose-500/10 px-2 py-1 rounded-full border border-rose-500/20">
                                <i data-lucide="flame" class="w-3 h-3"></i> Hampir habis!
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<?= $this->endSection() ?>
