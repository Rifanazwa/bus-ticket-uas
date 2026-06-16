<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>


<main class="max-w-xl mx-auto px-4 py-12 flex-grow flex flex-col justify-center">
    <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        
        <div class="text-center">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-brand-500/10 text-brand-400 mb-3">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-bold font-outfit text-white">Selesaikan Pembayaran</h1>
            <p class="text-xs text-slate-450 mt-1">Sisa waktu pembayaran Anda terbatas. Silakan bayar melalui Snap Midtrans.</p>
        </div>

        <!-- Booking details card -->
        <div class="p-4 bg-slate-950 rounded-2xl border border-slate-850 space-y-3">
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-500">Kode Booking</span>
                <span class="font-mono font-bold text-slate-200"><?= esc($booking['booking_code']) ?></span>
            </div>
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-500">Rute Perjalanan</span>
                <span class="font-semibold text-slate-200"><?= esc($booking['origin']) ?> ke <?= esc($booking['destination']) ?></span>
            </div>
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-500">Kursi Dipilih</span>
                <span class="font-semibold text-brand-400">
                    <?php 
                        $seatNums = array_map(function($s) { return $s['seat_number']; }, $seats);
                        echo implode(', ', $seatNums);
                    ?>
                </span>
            </div>
            <div class="flex justify-between items-center text-xs border-t border-slate-850 pt-2.5">
                <span class="text-slate-400 font-semibold">Total Pembayaran</span>
                <span class="font-mono font-bold text-lg text-emerald-400">Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Warning / Error if API key is not set -->
        <?php if (strpos($snapToken, 'MOCK-SNAP-TOKEN') === 0): ?>
            <div class="bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 p-4 rounded-xl text-xs space-y-2">
                <div class="flex items-center gap-1.5 font-semibold">
                    <i data-lucide="info" class="w-4 h-4"></i> Mode Simulasi (Sandbox Mock)
                </div>
                <p class="leading-relaxed">Midtrans Server Key belum dikonfigurasi di file `.env`. Anda dapat menguji alur sistem dengan menekan tombol simulasi sukses di bawah ini.</p>
            </div>
            
            <div class="space-y-3">
                <button type="button" id="pay-button" class="w-full py-3 px-4 rounded-xl font-bold text-white bg-slate-800 hover:bg-slate-750 flex items-center justify-center gap-2 transition-all text-sm border border-slate-700">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-slate-400"></i> Buka Snap Midtrans (Dummy)
                </button>
                <button type="button" id="simulate-success" class="w-full py-3 px-4 rounded-xl font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-600/10 flex items-center justify-center gap-2 transition-all text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Simulasi Bayar Sukses (Cepat)
                </button>
            </div>
        <?php else: ?>
            <div>
                <button type="button" id="pay-button" class="w-full py-3.5 px-4 rounded-xl font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-600/10 flex items-center justify-center gap-2 transition-all text-sm transform hover:scale-[1.01]">
                    <i data-lucide="credit-card" class="w-4 h-4"></i> Bayar Sekarang via Midtrans
                </button>
            </div>
        <?php endif; ?>

        <div class="text-center">
            <a href="<?= base_url('customer/home') ?>" class="text-xs text-slate-500 hover:text-slate-400 transition-colors">Batal & Kembali ke Beranda</a>
        </div>

    </div>
</main>

<!-- Midtrans Snap JS Library -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= $clientKey ?>"></script>
<script>
    const payButton = document.getElementById('pay-button');
    const simulateBtn = document.getElementById('simulate-success');
    const snapToken = '<?= $snapToken ?>';

    payButton.addEventListener('click', function () {
        if (snapToken.startsWith('MOCK-SNAP-TOKEN')) {
            alert('Menggunakan Token Uji Coba: ' + snapToken + '\n\nSilakan klik tombol "Simulasi Bayar Sukses" untuk memproses status berhasil.');
            return;
        }

        snap.pay(snapToken, {
            onSuccess: function (result) {
                // Redirect to success page
                window.location.href = '<?= base_url("customer/payment/success") ?>';
            },
            onPending: function (result) {
                alert("Menunggu pembayaran Anda!");
            },
            onError: function (result) {
                alert("Pembayaran gagal!");
            },
            onClose: function () {
                alert('Anda menutup popup tanpa menyelesaikan pembayaran.');
            }
        });
    });

    if (simulateBtn) {
        simulateBtn.addEventListener('click', function() {
            // We can simulate webhook callback by making a POST request to our local webhook API,
            // or perform a direct DB update via simulating webhook success,
            // then redirecting to customer/payment/success page.
            
            // To make it simple and reliable during offline development,
            // we will send a POST fetch to api/payment/webhook, passing simulated Midtrans payload:
            fetch('<?= base_url("api/payment/webhook") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: '<?= esc($booking["booking_code"]) ?>',
                    transaction_status: 'settlement',
                    payment_type: 'qris',
                    transaction_id: 'mock-trans-id-' + Math.random().toString(36).substr(2, 9)
                })
            })
            .then(res => {
                window.location.href = '<?= base_url("customer/payment/success") ?>';
            })
            .catch(err => {
                alert('Gagal menyimulasikan pembayaran.');
            });
        });
    }
</script>
<?= $this->endSection() ?>
