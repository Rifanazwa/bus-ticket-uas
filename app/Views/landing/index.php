<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- ===================== HERO SECTION ===================== -->
<section class="relative overflow-hidden min-h-[90vh] flex flex-col justify-center pt-8 pb-16">
    <!-- Animated background blobs -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-32 -left-32 w-[600px] h-[600px] bg-brand-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute -bottom-20 right-0 w-[500px] h-[500px] bg-indigo-600/8 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay:2s"></div>
        <div class="absolute top-1/2 left-1/3 w-[300px] h-[300px] bg-teal-500/5 rounded-full blur-[80px]"></div>
    </div>

    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#6366f1 1px, transparent 1px), linear-gradient(90deg, #6366f1 1px, transparent 1px); background-size: 60px 60px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <!-- Left: Text Content -->
            <div class="space-y-8">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/25 text-brand-300 text-xs font-semibold animate-on-scroll">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"></span>
                    Ditenagai Kecerdasan Buatan Gemini AI
                </div>

                <!-- Headline -->
                <div class="space-y-4 animate-on-scroll" style="animation-delay:0.1s">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-[1.1] tracking-tight">
                        Pesan Tiket Bus<br>
                        <span class="text-gradient">Lebih Cerdas</span><br>
                        Lebih Mudah
                    </h1>
                    <p class="text-base sm:text-lg text-slate-400 leading-relaxed max-w-lg">
                        Sistem reservasi bus berbasis AI pertama di Indonesia. Rekomendasi rute otomatis, harga terbaik, dan proses booking dalam hitungan menit.
                    </p>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-wrap gap-3 animate-on-scroll" style="animation-delay:0.2s">
                    <a href="<?= base_url('register') ?>" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-xl shadow-brand-600/25 transition-all transform hover:-translate-y-0.5 hover:shadow-brand-600/40">
                        <i data-lucide="ticket" class="w-4.5 h-4.5"></i>
                        Mulai Pesan Sekarang
                    </a>
                    <a href="#search-section" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl font-semibold text-slate-300 border border-white/10 hover:bg-white/5 hover:text-white transition-all">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Cari Tiket Dulu
                    </a>
                </div>

                <!-- Trust indicators -->
                <div class="flex flex-wrap items-center gap-6 animate-on-scroll" style="animation-delay:0.3s">
                    <div class="flex items-center gap-2 text-slate-400 text-sm">
                        <div class="p-1.5 bg-emerald-500/10 rounded-lg">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                        </div>
                        <span>Pembayaran Aman</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-400 text-sm">
                        <div class="p-1.5 bg-brand-500/10 rounded-lg">
                            <i data-lucide="zap" class="w-4 h-4 text-brand-400"></i>
                        </div>
                        <span>Konfirmasi Instan</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-400 text-sm">
                        <div class="p-1.5 bg-amber-500/10 rounded-lg">
                            <i data-lucide="headphones" class="w-4 h-4 text-amber-400"></i>
                        </div>
                        <span>CS AI 24/7</span>
                    </div>
                </div>
            </div>

            <!-- Right: Search Card -->
            <div class="animate-on-scroll" style="animation-delay:0.15s" id="search-section">
                <div class="glass rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/30 border border-white/8">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="p-2 bg-brand-500/15 rounded-xl">
                            <i data-lucide="search" class="w-4 h-4 text-brand-400"></i>
                        </div>
                        <h2 class="text-lg font-bold text-white">Cari Tiket Bus</h2>
                        <span class="ml-auto text-xs text-slate-500"><?= date('d M Y') ?></span>
                    </div>

                    <form action="<?= base_url('search') ?>" method="GET" class="space-y-4">
                        <!-- Asal & Tujuan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                                    <i data-lucide="map-pin" class="w-3 h-3 inline text-brand-400 mr-1"></i> Dari
                                </label>
                                <select name="origin" required class="input-field block w-full px-3 py-3 rounded-xl text-sm">
                                    <option value="">Pilih Kota Asal</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= esc($city) ?>"><?= esc($city) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                                    <i data-lucide="navigation" class="w-3 h-3 inline text-teal-400 mr-1"></i> Ke
                                </label>
                                <select name="destination" required class="input-field block w-full px-3 py-3 rounded-xl text-sm">
                                    <option value="">Pilih Kota Tujuan</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= esc($city) ?>"><?= esc($city) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                                <i data-lucide="calendar" class="w-3 h-3 inline text-indigo-400 mr-1"></i> Tanggal Keberangkatan
                            </label>
                            <input type="date" name="date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"
                                   class="input-field block w-full px-3 py-3 rounded-xl text-sm">
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="w-full py-3.5 px-6 rounded-2xl font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-600/20 transition-all flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            Temukan Bus Terbaik
                        </button>
                    </form>

                    <!-- Quick route shortcuts -->
                    <div class="mt-4 pt-4 border-t border-white/5">
                        <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider mb-2">Rute Populer</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (array_slice($routes, 0, 3) as $r): ?>
                                <a href="<?= base_url('search?origin=' . urlencode($r['origin']) . '&destination=' . urlencode($r['destination']) . '&date=' . date('Y-m-d')) ?>"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-800/80 hover:bg-slate-700/80 text-slate-300 border border-white/5 transition-all">
                                    <i data-lucide="route" class="w-3 h-3 text-brand-400"></i>
                                    <?= esc($r['origin']) ?> → <?= esc($r['destination']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce opacity-40">
        <span class="text-[10px] text-slate-500 uppercase tracking-widest">Scroll</span>
        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500"></i>
    </div>
</section>

<!-- ===================== STATS SECTION ===================== -->
<section class="py-16 border-y border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php $stats = [
                ['icon' => 'ticket',    'value' => number_format($totalTickets),  'label' => 'Tiket Terjual',  'color' => 'brand'],
                ['icon' => 'route',     'value' => $totalRoutes . '+',            'label' => 'Rute Aktif',     'color' => 'indigo'],
                ['icon' => 'bus',       'value' => $totalBuses . '+',             'label' => 'Armada Bus',     'color' => 'teal'],
                ['icon' => 'star',      'value' => '4.9',                         'label' => 'Rating Kepuasan','color' => 'amber'],
            ]; ?>
            <?php foreach ($stats as $i => $stat): ?>
                <div class="glass-light rounded-2xl p-5 text-center space-y-2 animate-on-scroll hover:border-brand-500/20 transition-all" style="animation-delay:<?= $i * 0.08 ?>s">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-<?= $stat['color'] ?>-500/10 border border-<?= $stat['color'] ?>-500/20 mb-1">
                        <i data-lucide="<?= $stat['icon'] ?>" class="w-5 h-5 text-<?= $stat['color'] ?>-400"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-white font-inter"><?= $stat['value'] ?></p>
                    <p class="text-xs text-slate-400 font-medium"><?= $stat['label'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== HOW IT WORKS ===================== -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 animate-on-scroll">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-400 text-xs font-semibold mb-4">
                <i data-lucide="list-ordered" class="w-3.5 h-3.5"></i> Cara Pemesanan
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Booking dalam <span class="text-gradient">3 Langkah Mudah</span></h2>
            <p class="mt-3 text-slate-400 max-w-lg mx-auto text-sm">Tidak perlu antri, tidak perlu repot. Pesan tiket bus Anda secara online kapan saja dan di mana saja.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Connecting line (desktop) -->
            <div class="hidden md:block absolute top-10 left-1/4 right-1/4 h-0.5 bg-gradient-to-r from-brand-600/40 via-indigo-500/40 to-teal-500/40"></div>

            <?php $steps = [
                ['num' => '01', 'icon' => 'search', 'title' => 'Cari Jadwal', 'desc' => 'Masukkan kota asal, tujuan, dan tanggal keberangkatan. AI kami langsung menampilkan jadwal terbaik.', 'color' => 'brand'],
                ['num' => '02', 'icon' => 'armchair', 'title' => 'Pilih Kursi', 'desc' => 'Pilih kursi favorit Anda melalui peta kursi interaktif visual. Harga tampil transparan tanpa biaya tersembunyi.', 'color' => 'indigo'],
                ['num' => '03', 'icon' => 'credit-card', 'title' => 'Bayar & Terima Tiket', 'desc' => 'Bayar via transfer bank, e-wallet, atau QRIS. E-tiket PDF langsung dikirim dengan QR Code boarding.', 'color' => 'teal'],
            ]; ?>
            <?php foreach ($steps as $i => $step): ?>
                <div class="relative glass-light rounded-3xl p-6 sm:p-8 space-y-4 animate-on-scroll" style="animation-delay:<?= $i * 0.12 ?>s">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-<?= $step['color'] ?>-500/10 border border-<?= $step['color'] ?>-500/20 flex items-center justify-center">
                            <i data-lucide="<?= $step['icon'] ?>" class="w-5 h-5 text-<?= $step['color'] ?>-400"></i>
                        </div>
                        <span class="text-4xl font-black text-white/5 font-inter"><?= $step['num'] ?></span>
                    </div>
                    <h3 class="text-lg font-bold text-white"><?= $step['title'] ?></h3>
                    <p class="text-sm text-slate-400 leading-relaxed"><?= $step['desc'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== AI FEATURES ===================== -->
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-600/5 rounded-full blur-[140px]"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6 animate-on-scroll">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-300 text-xs font-semibold">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Teknologi AI Terdepan
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                    AI yang Bekerja Keras<br><span class="text-gradient">untuk Perjalanan Anda</span>
                </h2>
                <p class="text-slate-400 leading-relaxed">
                    Kami mengintegrasikan Gemini AI ke dalam setiap aspek platform — mulai dari rekomendasi rute hingga analisis sentimen ulasan penumpang secara real-time.
                </p>

                <div class="space-y-4">
                    <?php $features = [
                        ['icon' => 'route', 'color' => 'brand', 'title' => 'Rekomendasi Rute Cerdas', 'desc' => 'AI menganalisis jadwal, harga, dan kelas bus untuk merekomendasikan pilihan terbaik berdasarkan preferensi Anda.'],
                        ['icon' => 'message-circle', 'color' => 'teal', 'title' => 'Chatbot CS Aktif 24/7', 'desc' => 'Asisten AI siap menjawab pertanyaan seputar jadwal, harga, dan cara pemesanan kapan pun Anda butuhkan.'],
                        ['icon' => 'bar-chart-3', 'color' => 'indigo', 'title' => 'Prediksi Harga & Okupansi', 'desc' => 'Sistem memprediksi ketersediaan kursi dan fluktuasi harga agar Anda bisa memilih waktu pemesanan terbaik.'],
                    ]; ?>
                    <?php foreach ($features as $i => $feat): ?>
                        <div class="flex items-start gap-4 animate-on-scroll" style="animation-delay:<?= $i * 0.08 ?>s">
                            <div class="w-10 h-10 rounded-xl bg-<?= $feat['color'] ?>-500/10 border border-<?= $feat['color'] ?>-500/20 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="<?= $feat['icon'] ?>" class="w-4.5 h-4.5 text-<?= $feat['color'] ?>-400"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm"><?= $feat['title'] ?></h4>
                                <p class="text-xs text-slate-400 mt-0.5 leading-relaxed"><?= $feat['desc'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Feature showcase card -->
            <div class="animate-on-scroll" style="animation-delay:0.2s">
                <div class="glass rounded-3xl p-6 space-y-4 border border-white/8">
                    <!-- Mock AI recommendation card -->
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-white">Rekomendasi AI Hari Ini</span>
                        <span class="flex items-center gap-1 text-[10px] text-brand-400 font-semibold bg-brand-500/10 px-2 py-1 rounded-full border border-brand-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"></span> Live
                        </span>
                    </div>

                    <?php foreach (array_slice($routes, 0, 3) as $i => $r): ?>
                        <div class="flex items-center justify-between p-3.5 rounded-2xl <?= $i === 0 ? 'bg-brand-600/10 border border-brand-500/20' : 'bg-slate-800/50 border border-white/5' ?> transition-all">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-slate-800 rounded-xl <?= $i === 0 ? 'bg-brand-500/15' : '' ?>">
                                    <i data-lucide="bus" class="w-4 h-4 <?= $i === 0 ? 'text-brand-400' : 'text-slate-500' ?>"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5 text-sm font-bold <?= $i === 0 ? 'text-white' : 'text-slate-300' ?>">
                                        <?= esc($r['origin']) ?>
                                        <i data-lucide="arrow-right" class="w-3 h-3 text-slate-500"></i>
                                        <?= esc($r['destination']) ?>
                                    </div>
                                    <?php if ($r['min_price']): ?>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Mulai <span class="font-bold text-emerald-400">Rp <?= number_format($r['min_price'], 0, ',', '.') ?></span></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($i === 0): ?>
                                <span class="text-[10px] bg-brand-600 text-white px-2 py-1 rounded-full font-bold">⭐ Best</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <a href="<?= base_url('search') ?>" class="mt-2 w-full flex items-center justify-center gap-2 py-3 rounded-2xl font-semibold text-sm text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 transition-all shadow-md shadow-brand-600/20">
                        <i data-lucide="search" class="w-4 h-4"></i> Lihat Semua Jadwal
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== PROMOS ===================== -->
<?php if (!empty($promos)): ?>
<section class="py-16 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <div class="animate-on-scroll">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold mb-3">
                    <i data-lucide="tag" class="w-3.5 h-3.5"></i> Promo Spesial
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Penawaran Terbatas</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <?php foreach ($promos as $i => $promo): ?>
                <div class="relative glass-light rounded-2xl p-5 overflow-hidden border border-amber-500/10 hover:border-amber-500/25 transition-all group animate-on-scroll" style="animation-delay:<?= $i * 0.1 ?>s">
                    <div class="absolute -right-6 -top-6 w-20 h-20 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/15 transition-all"></div>
                    <div class="relative">
                        <div class="flex items-start justify-between mb-3">
                            <div class="p-2 bg-amber-500/10 rounded-xl">
                                <i data-lucide="tag" class="w-4 h-4 text-amber-400"></i>
                            </div>
                            <span class="text-xs font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20">
                                <?= $promo['discount_type'] === 'percent' ? $promo['discount_value'] . '%' : 'Rp ' . number_format($promo['discount_value'], 0, ',', '.') ?>
                            </span>
                        </div>
                        <p class="font-mono text-lg font-bold text-white tracking-wider"><?= esc($promo['code']) ?></p>
                        <p class="text-xs text-slate-400 mt-1">Berlaku hingga <?= date('d M Y', strtotime($promo['valid_until'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== TESTIMONIALS ===================== -->
<?php if (!empty($reviews)): ?>
<section class="py-20 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 animate-on-scroll">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold mb-4">
                <i data-lucide="star" class="w-3.5 h-3.5"></i> Ulasan Penumpang
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Dipercaya Ribuan <span class="text-gradient">Penumpang</span></h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($reviews as $i => $review): ?>
                <div class="glass-light rounded-2xl p-5 space-y-3 animate-on-scroll hover:border-emerald-500/15 transition-all" style="animation-delay:<?= $i * 0.08 ?>s">
                    <div class="flex items-center gap-0.5">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i data-lucide="star" class="w-3.5 h-3.5 <?= $s <= $review['rating'] ? 'text-amber-400 fill-amber-400' : 'text-slate-700' ?>" style="fill: <?= $s <= $review['rating'] ? '#f59e0b' : 'transparent' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed italic">"<?= esc(substr($review['comment'] ?? 'Layanan sangat memuaskan!', 0, 100)) ?><?= strlen($review['comment'] ?? '') > 100 ? '...' : '' ?>"</p>
                    <div class="flex items-center gap-2 pt-1 border-t border-white/5">
                        <div class="h-7 w-7 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white text-[10px] font-bold">
                            <?= strtoupper(substr($review['user_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-white"><?= esc($review['user_name']) ?></p>
                            <p class="text-[10px] text-slate-500">Penumpang Terverifikasi</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== CTA SECTION ===================== -->
<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass rounded-3xl p-10 sm:p-16 text-center relative overflow-hidden animate-on-scroll border border-brand-500/15 shadow-2xl shadow-brand-900/20">
            <!-- Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-brand-900/40 via-indigo-950/60 to-slate-950/80 pointer-events-none"></div>
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-brand-500/10 rounded-full blur-[80px] pointer-events-none"></div>

            <div class="relative space-y-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-500/15 border border-brand-500/25 mx-auto">
                    <i data-lucide="rocket" class="w-7 h-7 text-brand-400"></i>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white">Siap Memulai Perjalanan?</h2>
                <p class="text-slate-400 text-base max-w-md mx-auto leading-relaxed">
                    Daftar gratis dan dapatkan akses ke semua fitur AI premium: rekomendasi rute, chatbot CS, dan notifikasi promo eksklusif.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="<?= base_url('register') ?>" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-xl shadow-brand-600/25 transition-all transform hover:-translate-y-0.5">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        Daftar Gratis Sekarang
                    </a>
                    <a href="<?= base_url('login') ?>" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl font-semibold text-slate-300 border border-white/10 hover:bg-white/5 hover:text-white transition-all">
                        Sudah punya akun? Masuk
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
