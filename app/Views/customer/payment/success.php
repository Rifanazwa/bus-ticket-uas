<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<main class="min-h-[calc(100vh-64px)] flex flex-col justify-center items-center px-4 py-12">
    <!-- Confetti particles (pure CSS animation) -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <?php for ($i = 0; $i < 12; $i++): ?>
            <div class="absolute w-2 h-2 rounded-full opacity-70 animate-bounce"
                 style="
                    left: <?= rand(5, 95) ?>%;
                    top: <?= rand(-10, 30) ?>%;
                    background: <?= ['#6366f1', '#14b8a6', '#f59e0b', '#ec4899', '#10b981'][rand(0, 4)] ?>;
                    animation-delay: <?= rand(0, 20) / 10 ?>s;
                    animation-duration: <?= rand(15, 30) / 10 ?>s;
                 "></div>
        <?php endfor; ?>
    </div>

    <div class="relative z-10 glass rounded-3xl p-8 sm:p-10 max-w-lg w-full text-center shadow-2xl shadow-black/30 border border-white/8 space-y-6">

        <!-- Success Icon -->
        <div class="flex justify-center">
            <div class="relative flex items-center justify-center h-24 w-24 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <i data-lucide="check-circle-2" class="w-12 h-12"></i>
                <div class="absolute inset-0 rounded-full bg-emerald-500/10 animate-ping opacity-50"></div>
            </div>
        </div>

        <div>
            <h1 class="text-2xl font-extrabold text-white">Pembayaran Berhasil! 🎉</h1>
            <p class="text-sm text-slate-400 mt-2 leading-relaxed">
                Transaksi Anda telah berhasil diproses. E-Tiket dengan QR Code boarding pass telah diterbitkan.
            </p>
        </div>

        <!-- Steps info -->
        <div class="p-4 bg-slate-900/80 rounded-2xl border border-white/5 text-left space-y-3">
            <p class="text-xs font-bold text-white flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-brand-400"></i>
                Apa selanjutnya?
            </p>
            <div class="space-y-2.5">
                <?php $steps = [
                    ['icon' => 'ticket',         'color' => 'brand',   'text' => 'E-Tiket PDF resmi Anda dengan QR Code sudah siap diunduh.'],
                    ['icon' => 'scan-line',       'color' => 'teal',    'text' => 'Tunjukkan QR Code kepada petugas terminal saat boarding.'],
                    ['icon' => 'clock',           'color' => 'amber',   'text' => 'Hadir di terminal minimal 30 menit sebelum keberangkatan.'],
                ]; ?>
                <?php foreach ($steps as $i => $step): ?>
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-lg bg-<?= $step['color'] ?>-500/10 border border-<?= $step['color'] ?>-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="<?= $step['icon'] ?>" class="w-3 h-3 text-<?= $step['color'] ?>-400"></i>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed"><?= $step['text'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3 pt-1">
            <?php if (isset($bookingId) && $bookingId): ?>
                <a href="<?= base_url('customer/ticket/download/' . $bookingId) ?>" target="_blank"
                   class="w-full py-3.5 px-6 rounded-2xl font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-xl shadow-emerald-600/20 flex items-center justify-center gap-2 text-sm transition-all transform hover:-translate-y-0.5">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Unduh E-Tiket PDF Sekarang
                </a>
            <?php endif; ?>
            <a href="<?= base_url('customer/home') ?>"
               class="w-full py-3 px-6 rounded-2xl font-semibold text-slate-300 border border-white/10 hover:bg-white/5 flex items-center justify-center gap-2 text-sm transition-all">
                <i data-lucide="home" class="w-4 h-4"></i>
                Lihat Semua Tiket Saya
            </a>
        </div>

    </div>
</main>
<?= $this->endSection() ?>
