<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow"
      x-data="{ showReviewModal: false, reviewBookingId: null, rating: 5, comment: '' }">

    <!-- ===================== GREETING HEADER ===================== -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">Selamat datang kembali 👋</p>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-0.5"><?= esc(session()->get('userName')) ?></h1>
        </div>
        <a href="#search-section" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-600/20 transition-all transform hover:-translate-y-0.5">
            <i data-lucide="search" class="w-4 h-4"></i> Cari Rute Baru
        </a>
    </div>

    <!-- ===================== GRID LAYOUT ===================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- KOLOM KIRI (2/3 width on desktop) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- 1. UPCOMING TRIP COUNTDOWN CARD -->
            <?php if (!empty($upcomingBooking)): ?>
                <div x-data="countdownTimer('<?= $upcomingBooking['departure_time'] ?>')" 
                     class="glass rounded-3xl p-6 border border-brand-500/20 bg-gradient-to-br from-slate-900 via-brand-950/10 to-slate-900 shadow-2xl relative overflow-hidden">
                    
                    <!-- Decors -->
                    <div class="absolute -right-16 -top-16 w-40 h-40 rounded-full bg-brand-500/10 blur-3xl"></div>
                    <div class="absolute -left-16 -bottom-16 w-40 h-40 rounded-full bg-teal-500/10 blur-3xl"></div>
                    
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-white/5 pb-4 mb-4">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-400"></span> PERJALANAN TERDEKAT
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <span class="text-xs font-mono text-slate-400">No. Tiket: </span>
                            <span class="text-xs font-bold text-white bg-slate-800 px-2.5 py-0.5 rounded-lg border border-white/5 font-mono">
                                TKT-<?= esc($upcomingBooking['ticket_id']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                        <!-- Route Timeline info -->
                        <div class="md:col-span-2 space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="text-left">
                                    <p class="text-2xl font-black text-white font-inter"><?= date('H:i', strtotime($upcomingBooking['departure_time'])) ?></p>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide"><?= esc($upcomingBooking['origin']) ?></p>
                                </div>
                                <div class="flex-grow max-w-[120px] flex flex-col items-center gap-1">
                                    <span class="text-[9px] font-semibold text-brand-400 font-mono"><?= esc($upcomingBooking['bus_name']) ?></span>
                                    <div class="w-full h-0.5 bg-gradient-to-r from-brand-500/20 via-brand-400 to-brand-500/20 relative">
                                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-teal-400"></div>
                                    </div>
                                    <span class="text-[9px] text-slate-500 font-mono"><?= date('d M Y', strtotime($upcomingBooking['departure_time'])) ?></span>
                                </div>
                                <div class="text-left">
                                    <p class="text-2xl font-black text-white font-inter"><?= date('H:i', strtotime($upcomingBooking['arrival_time'])) ?></p>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide"><?= esc($upcomingBooking['destination']) ?></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 items-center">
                                <span class="text-xs text-slate-400 mr-1">Kursi:</span>
                                <?php foreach ($upcomingBooking['seats'] as $seat): ?>
                                    <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-teal-300 bg-teal-500/10 border border-teal-500/20 px-2.5 py-1 rounded-xl">
                                        <i data-lucide="armchair" class="w-3.5 h-3.5"></i>
                                        <?= esc($seat['seat_number']) ?> (<?= esc($seat['passenger_name']) ?>)
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Countdown Timer -->
                        <div class="flex flex-col items-center justify-center bg-slate-950/80 border border-white/5 rounded-2xl p-4 min-h-[110px]">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Berangkat Dalam</p>
                            <div class="flex items-center gap-2 text-white font-inter">
                                <div class="text-center">
                                    <span class="text-xl font-black text-brand-400 font-mono" x-text="time.days">00</span>
                                    <p class="text-[8px] text-slate-500 font-medium">Hari</p>
                                </div>
                                <span class="text-slate-700 font-bold -mt-3">:</span>
                                <div class="text-center">
                                    <span class="text-xl font-black text-brand-400 font-mono" x-text="time.hours">00</span>
                                    <p class="text-[8px] text-slate-500 font-medium">Jam</p>
                                </div>
                                <span class="text-slate-700 font-bold -mt-3">:</span>
                                <div class="text-center">
                                    <span class="text-xl font-black text-brand-400 font-mono" x-text="time.minutes">00</span>
                                    <p class="text-[8px] text-slate-500 font-medium">Menit</p>
                                </div>
                                <span class="text-slate-700 font-bold -mt-3">:</span>
                                <div class="text-center">
                                    <span class="text-xl font-black text-teal-400 font-mono" x-text="time.seconds">00</span>
                                    <p class="text-[8px] text-slate-500 font-medium">Detik</p>
                                </div>
                            </div>
                            
                            <a href="<?= base_url('customer/ticket/download/' . $upcomingBooking['id']) ?>" target="_blank"
                               class="mt-4 w-full py-2 px-3 rounded-xl font-bold text-center text-xs text-white bg-gradient-to-r from-brand-600 to-indigo-650 hover:from-brand-500 hover:to-indigo-550 transition-all flex items-center justify-center gap-1.5 shadow-md shadow-brand-600/10">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> E-Tiket PDF
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 2. SEARCH FORM CARD -->
            <div id="search-section" class="glass rounded-3xl p-6 border border-brand-500/10 scroll-mt-20">
                <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-brand-400"></i>
                    Cari Jadwal Tiket Bus
                    <span class="ml-2 text-[10px] font-bold text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded-full border border-brand-500/20 flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-2.5 h-2.5"></i> AI Recommendation
                    </span>
                </h2>
                <form class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4" action="<?= base_url('customer/search') ?>" method="GET">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">
                            <i data-lucide="map-pin" class="w-3 h-3 inline text-brand-400 mr-1"></i> Dari
                        </label>
                        <select name="origin" required class="input-field block w-full px-3 py-3 rounded-xl text-sm">
                            <option value="">Pilih Asal</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= esc($city) ?>"><?= esc($city) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">
                            <i data-lucide="navigation" class="w-3 h-3 inline text-teal-400 mr-1"></i> Ke
                        </label>
                        <select name="destination" required class="input-field block w-full px-3 py-3 rounded-xl text-sm">
                            <option value="">Pilih Tujuan</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= esc($city) ?>"><?= esc($city) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">
                            <i data-lucide="calendar" class="w-3 h-3 inline text-indigo-400 mr-1"></i> Tanggal
                        </label>
                        <input type="date" name="date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"
                               class="input-field block w-full px-3 py-3 rounded-xl text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/15 flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="search" class="w-4 h-4"></i> Temukan
                        </button>
                    </div>
                </form>
            </div>

            <!-- 3. INFORMATIONAL SYSTEM WORKFLOW PROCESS -->
            <div class="glass rounded-3xl p-6 border border-brand-500/10 bg-gradient-to-br from-slate-900 to-slate-950">
                <h3 class="text-sm font-extrabold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="workflow" class="w-4.5 h-4.5 text-brand-400"></i>
                    Alur Transaksi &amp; Boarding Pintar SiTeBus
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex gap-3">
                        <div class="h-8 w-8 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-400 font-bold text-xs flex-shrink-0">1</div>
                        <div>
                            <p class="text-xs font-bold text-white leading-none">Cari Jadwal &amp; Kursi</p>
                            <p class="text-[10px] text-slate-400 mt-1 leading-normal">Pencarian jadwal rute real-time dan visualisasi layout kursi armada bus secara presisi.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="h-8 w-8 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 font-bold text-xs flex-shrink-0">2</div>
                        <div>
                            <p class="text-xs font-bold text-white leading-none">Proteksi Double-Booking</p>
                            <p class="text-[10px] text-slate-400 mt-1 leading-normal">Kursi pesanan dikunci otomatis selama 15 menit agar tidak terjadi tabrakan transaksi.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="h-8 w-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-xs flex-shrink-0">3</div>
                        <div>
                            <p class="text-xs font-bold text-white leading-none">Pembayaran &amp; E-Tiket PDF</p>
                            <p class="text-[10px] text-slate-400 mt-1 leading-normal">Midtrans menerbitkan instruksi instan. E-Tiket PDF diterbitkan lengkap beserta QR code boarding.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="h-8 w-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0">4</div>
                        <div>
                            <p class="text-xs font-bold text-white leading-none">Scan Boarding &amp; Ulasan AI</p>
                            <p class="text-[10px] text-slate-400 mt-1 leading-normal">Petugas terminal memverifikasi QR Code per armada, ditutup ulasan dengan analisis sentimen AI.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. BOOKING HISTORY -->
            <div id="history-section" class="scroll-mt-20">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i data-lucide="history" class="w-5 h-5 text-brand-400"></i>
                        Tiket &amp; Riwayat Pemesanan
                    </h2>
                    <span class="text-xs text-slate-500 bg-slate-800/80 px-3 py-1 rounded-full border border-white/5 font-mono">
                        <?= count($bookings) ?> Pemesanan
                    </span>
                </div>

                <?php if (empty($bookings)): ?>
                    <div class="glass-light rounded-3xl p-12 text-center space-y-4 border border-white/5">
                        <div class="w-16 h-16 rounded-2xl bg-slate-800/80 flex items-center justify-center mx-auto">
                            <i data-lucide="inbox" class="w-8 h-8 text-slate-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Belum Ada Pemesanan</h3>
                            <p class="text-sm text-slate-500 mt-1">Mulai pesan tiket perjalanan Anda sekarang</p>
                        </div>
                        <a href="#search-section" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 transition-all">
                            <i data-lucide="search" class="w-4 h-4"></i> Cari Tiket Sekarang
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($bookings as $b): ?>
                            <?php
                                $isPaid    = $b['payment_status'] === 'paid';
                                $isPending = $b['payment_status'] === 'pending';
                                $isBoarded = $b['ticket_status'] === 'boarded';
                            ?>
                            <div class="glass-light rounded-3xl overflow-hidden border transition-all <?= $isBoarded ? 'border-emerald-500/15' : ($isPaid ? 'border-brand-500/10' : 'border-white/5') ?> hover:border-white/15">
                                <!-- Top Bar -->
                                <div class="px-5 py-3.5 bg-slate-950/40 border-b border-white/5 flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-3">
                                        <?php if ($isPaid && $b['ticket_id']): ?>
                                            <span class="text-xs font-semibold text-slate-400">No. Tiket:</span>
                                            <code class="text-xs font-bold text-teal-400 bg-teal-500/10 px-2.5 py-1 rounded-lg border border-teal-500/20 font-mono font-inter">TKT-<?= esc($b['ticket_id']) ?></code>
                                        <?php else: ?>
                                            <span class="text-xs font-semibold text-slate-400">No. Booking:</span>
                                            <code class="text-xs font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/20 font-mono font-inter"><?= esc($b['booking_code']) ?></code>
                                        <?php endif; ?>
                                        <span class="text-xs text-slate-600">|</span>
                                        <span class="text-[11px] text-slate-500 font-medium font-mono font-inter"><?= date('d M Y H:i', strtotime($b['created_at'])) ?></span>
                                    </div>
                                    <div>
                                        <?php if ($isPaid && $isBoarded): ?>
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                                                <span class="w-1 h-1 rounded-full bg-emerald-400"></span> BOARDED
                                            </span>
                                        <?php elseif ($isPaid): ?>
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-brand-400 bg-brand-500/10 px-2.5 py-1 rounded-full border border-brand-500/20">
                                                <span class="w-1 h-1 rounded-full bg-brand-400 animate-pulse"></span> TIKET AKTIF
                                            </span>
                                        <?php elseif ($isPending): ?>
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20">
                                                <span class="w-1 h-1 rounded-full bg-amber-400"></span> MENUNGGU BAYAR
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 bg-slate-800 px-2.5 py-1 rounded-full border border-white/5">
                                                <?= strtoupper($b['payment_status']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="p-5 flex flex-col md:flex-row gap-5 items-stretch">
                                    <!-- Left detailed route timeline -->
                                    <div class="flex-grow space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="bus" class="w-4.5 h-4.5 text-brand-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-white leading-none"><?= esc($b['bus_name']) ?></p>
                                                <span class="text-[10px] text-slate-400 font-medium capitalize mt-1 inline-block"><?= esc($b['bus_type']) ?></span>
                                            </div>
                                        </div>

                                        <!-- Route Details with Explicit Labels -->
                                        <div class="grid grid-cols-2 gap-4 bg-slate-950/40 p-4 rounded-2xl border border-white/5">
                                            <div>
                                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Keberangkatan</span>
                                                <p class="text-sm font-extrabold text-white truncate"><?= esc($b['origin']) ?></p>
                                                <p class="text-[11px] font-mono text-slate-400 mt-1 font-inter">
                                                    <?= date('d M Y', strtotime($b['departure_time'])) ?> @ <span class="text-white font-bold"><?= date('H:i', strtotime($b['departure_time'])) ?></span>
                                                </p>
                                            </div>
                                            <div class="border-l border-white/5 pl-4">
                                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Tujuan</span>
                                                <p class="text-sm font-extrabold text-white truncate"><?= esc($b['destination']) ?></p>
                                                <p class="text-[11px] font-mono text-slate-400 mt-1 font-inter">
                                                    <?= date('d M Y', strtotime($b['arrival_time'] ?? $b['departure_time'])) ?> @ <span class="text-white font-bold"><?= date('H:i', strtotime($b['arrival_time'] ?? $b['departure_time'])) ?></span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Tabular Passenger List -->
                                        <div class="bg-slate-950/20 p-4 rounded-2xl border border-white/5">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Manifes Penumpang</span>
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left border-collapse">
                                                    <thead>
                                                        <tr class="border-b border-white/5 text-[9px] font-bold text-slate-500 uppercase tracking-wider">
                                                            <th class="pb-1.5">Nama Penumpang</th>
                                                            <th class="pb-1.5 text-right">No. Kursi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-white/5">
                                                        <?php foreach ($b['seats'] as $seat): ?>
                                                            <tr class="text-xs text-slate-300">
                                                                <td class="py-2.5 flex items-center gap-2 font-medium">
                                                                    <div class="w-1.5 h-1.5 rounded-full bg-brand-400"></div>
                                                                    <?= esc($seat['passenger_name']) ?>
                                                                </td>
                                                                <td class="py-2.5 text-right font-mono font-bold text-teal-400">
                                                                    <?= esc($seat['seat_number']) ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Visual Stepper Progress -->
                                        <div class="mt-4 pt-4 border-t border-white/5">
                                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block mb-3">Progress Tiket &amp; Perjalanan</span>
                                            <div class="grid grid-cols-5 gap-2 relative">
                                                <!-- Step 1: Dipesan -->
                                                <div class="text-center space-y-1.5">
                                                    <div class="w-7 h-7 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-[10px] mx-auto shadow-md shadow-brand-500/20">
                                                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <span class="text-[8px] font-extrabold text-slate-300 block leading-none">Dipesan</span>
                                                </div>

                                                <!-- Step 2: Dibayar -->
                                                <div class="text-center space-y-1.5">
                                                    <?php if ($isPaid || $isBoarded): ?>
                                                        <div class="w-7 h-7 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-[10px] mx-auto shadow-md shadow-brand-500/20">
                                                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-slate-300 block leading-none">Dibayar</span>
                                                    <?php elseif ($isPending): ?>
                                                        <div class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-[10px] mx-auto shadow-md shadow-amber-500/20 animate-pulse">
                                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-amber-400 block leading-none">Menunggu</span>
                                                    <?php else: ?>
                                                        <div class="w-7 h-7 rounded-full bg-rose-500/20 border border-rose-500/30 text-rose-400 flex items-center justify-center font-bold text-[10px] mx-auto">
                                                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-rose-450 block leading-none">Gagal</span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Step 3: Tiket Terbit -->
                                                <div class="text-center space-y-1.5">
                                                    <?php if ($isPaid || $isBoarded): ?>
                                                        <div class="w-7 h-7 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-[10px] mx-auto shadow-md shadow-brand-500/20">
                                                            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-slate-300 block leading-none">Terbit</span>
                                                    <?php else: ?>
                                                        <div class="w-7 h-7 rounded-full bg-slate-950 border border-white/5 text-slate-500 flex items-center justify-center font-bold text-[10px] mx-auto">
                                                            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-slate-500 block leading-none">Terbit</span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Step 4: Boarding -->
                                                <div class="text-center space-y-1.5">
                                                    <?php if ($isBoarded): ?>
                                                        <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-[10px] mx-auto shadow-md shadow-emerald-500/20">
                                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-emerald-400 block leading-none">Boarded</span>
                                                    <?php else: ?>
                                                        <div class="w-7 h-7 rounded-full bg-slate-950 border border-white/5 text-slate-500 flex items-center justify-center font-bold text-[10px] mx-auto">
                                                            <i data-lucide="scan" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-slate-500 block leading-none">Boarding</span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Step 5: Ulasan AI -->
                                                <div class="text-center space-y-1.5">
                                                    <?php if ($b['reviewed']): ?>
                                                        <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-[10px] mx-auto shadow-md shadow-emerald-500/20">
                                                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-emerald-400 block leading-none">Ulasan AI</span>
                                                    <?php else: ?>
                                                        <div class="w-7 h-7 rounded-full bg-slate-950 border border-white/5 text-slate-500 flex items-center justify-center font-bold text-[10px] mx-auto">
                                                            <i data-lucide="star" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <span class="text-[8px] font-extrabold text-slate-500 block leading-none">Ulasan AI</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right pricing & actions -->
                                    <div class="flex flex-col justify-between items-start md:items-end gap-4 border-t md:border-t-0 md:border-l border-white/5 pt-4 md:pt-0 md:pl-5 md:min-w-[170px] flex-shrink-0">
                                        <div class="md:text-right">
                                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Total Pembayaran</p>
                                            <p class="text-xl font-black text-white font-inter mt-1">Rp <?= number_format($b['total_price'], 0, ',', '.') ?></p>
                                        </div>

                                        <div class="flex flex-col sm:flex-row md:flex-col gap-2 w-full">
                                            <?php if ($isPending): ?>
                                                <a href="<?= base_url('customer/payment/' . $b['id']) ?>"
                                                   class="w-full py-2.5 px-4 rounded-xl text-center text-xs font-bold bg-amber-500 hover:bg-amber-400 text-white flex items-center justify-center gap-1.5 transition-all shadow-md shadow-amber-500/20">
                                                    <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> Bayar Sekarang
                                                </a>
                                            <?php elseif ($isPaid && $b['ticket_qr']): ?>
                                                <?php if ($isBoarded): ?>
                                                    <span class="w-full py-2 px-3 text-center rounded-xl text-xs font-semibold bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center gap-1">
                                                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Selesai Boarding
                                                    </span>
                                                <?php else: ?>
                                                    <a href="<?= base_url('customer/ticket/download/' . $b['id']) ?>" target="_blank"
                                                       class="w-full py-2.5 px-4 rounded-xl text-center text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/8 flex items-center justify-center gap-1.5 transition-all">
                                                        <i data-lucide="download" class="w-3.5 h-3.5"></i> E-Tiket PDF
                                                    </a>
                                                <?php endif; ?>

                                                <?php if (!$b['reviewed']): ?>
                                                    <button @click="reviewBookingId = <?= $b['id'] ?>; showReviewModal = true; rating = 5; comment = ''"
                                                            class="w-full py-2.5 px-4 rounded-xl text-center text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center gap-1.5 transition-all shadow-md shadow-emerald-600/15">
                                                        <i data-lucide="star" class="w-3.5 h-3.5"></i> Beri Ulasan
                                                    </button>
                                                <?php else: ?>
                                                    <span class="w-full py-2 px-3 text-center rounded-xl text-xs font-semibold bg-slate-800/80 border border-white/5 text-slate-400 flex items-center justify-center gap-1.5">
                                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-400"></i> Sudah Diulas
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- KOLOM KANAN (1/3 width on desktop) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- 1. QUICK LOYALTY PROFILE CARD -->
            <div class="glass rounded-3xl p-6 border border-brand-500/10 bg-gradient-to-br from-slate-900 to-slate-950 relative overflow-hidden">
                <?php 
                    $accentColor = 'from-amber-600 to-yellow-400';
                    $bgLight = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                    if ($tierName === 'Bronze Member') {
                        $accentColor = 'from-orange-600 to-amber-700';
                        $bgLight = 'bg-orange-500/10 text-orange-400 border-orange-500/20';
                    } elseif ($tierName === 'Silver Member') {
                        $accentColor = 'from-slate-400 to-slate-200';
                        $bgLight = 'bg-slate-400/15 text-slate-300 border-slate-400/20';
                    }
                ?>
                <div class="absolute right-0 top-0 w-24 h-24 rounded-full bg-gradient-to-tr from-brand-500/5 to-teal-500/5 blur-xl"></div>
                
                <div class="flex items-center gap-3.5 mb-5">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-tr <?= $accentColor ?> flex items-center justify-center text-white font-extrabold shadow-lg shadow-black/20">
                        <i data-lucide="crown" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Loyalty Tier</p>
                        <h3 class="text-lg font-black text-white"><?= $tierName ?></h3>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center bg-slate-950/40 border border-white/5 rounded-xl p-3">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Total Perjalanan</p>
                            <p class="text-lg font-black text-white font-inter mt-0.5"><?= $boardedCount ?> <span class="text-xs text-slate-400 font-medium font-sans">Trip</span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Loyalty XP</p>
                            <p class="text-lg font-black text-teal-400 font-inter mt-0.5"><?= $xpPoints ?> <span class="text-xs text-slate-500 font-medium font-sans">XP</span></p>
                        </div>
                    </div>
                    
                    <?php if ($progress < 100): ?>
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400">Ke level <span class="text-white font-bold"><?= $nextTierName ?></span></span>
                                <span class="text-brand-400 font-bold"><?= $progress ?>%</span>
                            </div>
                            <div class="w-full bg-slate-950 h-2 rounded-full overflow-hidden border border-white/5">
                                <div class="bg-gradient-to-r from-brand-500 to-teal-400 h-full rounded-full progress-fill animate-on-scroll visible" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-1.5 text-xs text-emerald-400 font-semibold bg-emerald-500/10 border border-emerald-500/20 px-3 py-2 rounded-xl">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            Anda berada di tingkat keanggotaan tertinggi!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. SAVED PASSENGER MANIFEST -->
            <div class="glass rounded-3xl p-6 border border-brand-500/10 bg-gradient-to-br from-slate-900 to-slate-950">
                <h3 class="text-sm font-extrabold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="users" class="w-4.5 h-4.5 text-teal-400"></i>
                    Manifes Penumpang
                </h3>
                
                <?php if (empty($savedPassengers)): ?>
                    <p class="text-xs text-slate-500 py-2 leading-relaxed">Belum ada manifest tersimpan. Nama penumpang otomatis terdaftar setelah Anda memesan tiket.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($savedPassengers as $sp): ?>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/40 border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-7 w-7 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 text-xs font-extrabold uppercase">
                                        <?= strtoupper(substr($sp['passenger_name'], 0, 1)) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-white truncate"><?= esc($sp['passenger_name']) ?></p>
                                        <p class="text-[9px] text-slate-500 font-medium">Penumpang Terdaftar</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-slate-400 bg-slate-800 px-2 py-0.5 rounded-md border border-white/5 font-mono">
                                    <?= $sp['trip_count'] ?> Trip
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 3. ACTIVE PROMO CARD -->
            <div class="glass rounded-3xl p-6 border border-brand-500/10 bg-gradient-to-br from-slate-900 to-slate-950">
                <h3 class="text-sm font-extrabold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="ticket-percent" class="w-4.5 h-4.5 text-brand-400"></i>
                    Kupon Promo Aktif
                </h3>
                
                <?php if (empty($promos)): ?>
                    <p class="text-xs text-slate-500 py-2">Tidak ada kupon promo aktif saat ini.</p>
                <?php else: ?>
                    <div class="space-y-3" x-data="{ copiedCode: null }">
                        <?php foreach ($promos as $promo): ?>
                            <div class="p-3 rounded-2xl bg-slate-950/60 border border-brand-500/10 relative overflow-hidden flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black text-brand-400 font-mono tracking-wider bg-brand-500/10 border border-brand-500/20 px-2.5 py-1 rounded-lg">
                                        <?= esc($promo['code']) ?>
                                    </span>
                                    <button type="button" 
                                            @click="navigator.clipboard.writeText('<?= esc($promo['code']) ?>'); copiedCode = '<?= esc($promo['code']) ?>'; setTimeout(() => copiedCode = null, 2000)"
                                            class="text-[10px] font-bold text-slate-400 hover:text-white transition-colors flex items-center gap-1">
                                        <span x-text="copiedCode === '<?= esc($promo['code']) ?>' ? 'Tersalin!' : 'Salin'"></span>
                                        <i :data-lucide="copiedCode === '<?= esc($promo['code']) ?>' ? 'check' : 'copy'" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <div class="flex justify-between items-baseline mt-1">
                                    <p class="text-[10px] text-slate-400 font-medium">Diskon 
                                        <span class="text-white font-bold">
                                            <?= $promo['discount_type'] === 'percent' ? esc($promo['discount_value']).'%' : 'Rp '.number_format($promo['discount_value'], 0, ',', '.') ?>
                                        </span>
                                    </p>
                                    <p class="text-[8px] text-slate-500 font-mono">Hingga: <?= date('d M Y', strtotime($promo['valid_until'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
        
    </div>

    <!-- ===================== REVIEW MODAL ===================== -->
    <div x-show="showReviewModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="showReviewModal = false"></div>
        <div class="relative glass rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-white/10 space-y-6 animate-on-scroll visible">
            <button @click="showReviewModal = false" class="absolute top-4 right-4 p-1.5 text-slate-500 hover:text-white hover:bg-white/5 rounded-lg transition-all">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            <div class="text-center">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="star" class="w-6 h-6 text-emerald-400"></i>
                </div>
                <h3 class="text-lg font-extrabold text-white">Berikan Ulasan Perjalanan</h3>
                <p class="text-xs text-slate-400 mt-1">Ulasan Anda dianalisis otomatis oleh AI untuk meningkatkan layanan kami</p>
            </div>

            <form action="<?= base_url('customer/review/store') ?>" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <input type="hidden" name="booking_id" :value="reviewBookingId">

                <!-- Star Rating -->
                <div class="text-center space-y-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Rating</label>
                    <div class="flex justify-center gap-2">
                        <template x-for="i in [1,2,3,4,5]">
                            <button type="button" @click="rating = i"
                                    class="text-3xl transition-transform active:scale-95 hover:scale-110">
                                <span :class="i <= rating ? 'text-amber-400' : 'text-slate-700'" x-text="'★'"></span>
                            </button>
                        </template>
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                </div>

                <!-- Comment -->
                <div class="space-y-1.5">
                    <label for="modal-comment" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Komentar</label>
                    <textarea id="modal-comment" name="comment" x-model="comment" rows="4"
                              class="input-field block w-full px-3.5 py-3 rounded-xl text-sm resize-none"
                              placeholder="Ceritakan pengalaman perjalanan Anda..."></textarea>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showReviewModal = false"
                            class="flex-1 py-3 rounded-xl text-sm font-semibold text-slate-400 bg-slate-800 hover:bg-slate-700 border border-white/5 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/15 transition-all">
                        Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<!-- JS Countdown Helper -->
<script>
function countdownTimer(departureTime) {
    return {
        departure: new Date(departureTime).getTime(),
        time: { days: '00', hours: '00', minutes: '00', seconds: '00' },
        timer: null,
        init() {
            this.updateTime();
            this.timer = setInterval(() => {
                this.updateTime();
            }, 1000);
        },
        updateTime() {
            const now = new Date().getTime();
            const distance = this.departure - now;
            if (distance < 0) {
                clearInterval(this.timer);
                this.time = { days: '00', hours: '00', minutes: '00', seconds: '00' };
                return;
            }
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            this.time = {
                days: String(days).padStart(2, '0'),
                hours: String(hours).padStart(2, '0'),
                minutes: String(minutes).padStart(2, '0'),
                seconds: String(seconds).padStart(2, '0')
            };
        }
    };
}
</script>
<?= $this->endSection() ?>
