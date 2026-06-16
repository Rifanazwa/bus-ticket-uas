<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow" x-data="bookingApp()">

    <!-- Error alert -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 flex items-center gap-3 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
        </div>
    <?php endif; ?>

    <!-- Booking Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: Trip Summary + Seat Map -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Trip Summary -->
            <div class="glass-light rounded-2xl p-5 border border-white/5">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Ringkasan Perjalanan</p>
                <div class="flex flex-col sm:flex-row justify-between gap-4">
                    <div>
                        <span class="text-[10px] bg-brand-500/15 border border-brand-500/30 text-brand-400 px-2 py-0.5 rounded font-bold uppercase tracking-wider"><?= esc($schedule['bus_type']) ?></span>
                        <h2 class="text-xl font-extrabold text-white mt-1.5"><?= esc($schedule['bus_name']) ?></h2>
                        <div class="flex items-center gap-2 text-sm text-slate-400 mt-1">
                            <span><?= esc($schedule['origin']) ?></span>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-600"></i>
                            <span><?= esc($schedule['destination']) ?></span>
                        </div>
                    </div>
                    <div class="sm:text-right text-xs text-slate-400 space-y-1.5">
                        <p class="flex sm:justify-end items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-400"></i>
                            Berangkat: <span class="text-white font-semibold ml-1"><?= date('d M Y, H:i', strtotime($schedule['departure_time'])) ?></span>
                        </p>
                        <p class="flex sm:justify-end items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-teal-400"></i>
                            Tiba: <span class="text-white font-semibold ml-1"><?= date('d M Y, H:i', strtotime($schedule['arrival_time'])) ?></span>
                        </p>
                        <p class="flex sm:justify-end items-center gap-1.5">
                            <i data-lucide="bus" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span><?= $schedule['total_seats'] ?> kursi total</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- SEAT MAP -->
            <div class="glass-light rounded-2xl p-5 border border-white/5 space-y-5">
                <div>
                    <h3 class="text-base font-bold text-white">Peta Pemilihan Kursi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Klik kursi untuk memilih/batal pilih. Anda bisa pilih beberapa kursi.</p>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap gap-5 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-slate-800 border border-slate-700 rounded-lg flex items-center justify-center">
                            <i data-lucide="armchair" class="w-4 h-4 text-slate-400"></i>
                        </div>
                        <span class="text-slate-400">Tersedia</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-600 border border-brand-500 rounded-lg flex items-center justify-center">
                            <i data-lucide="armchair" class="w-4 h-4 text-white"></i>
                        </div>
                        <span class="text-slate-400">Dipilih Anda</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-slate-950 border border-slate-800 rounded-lg flex items-center justify-center opacity-50">
                            <i data-lucide="x" class="w-4 h-4 text-slate-600"></i>
                        </div>
                        <span class="text-slate-400">Terisi / Tidak Tersedia</span>
                    </div>
                </div>

                <!-- Bus Container -->
                <div class="flex justify-center overflow-x-auto pb-2">
                    <div class="min-w-[280px] max-w-xs w-full">
                        <!-- Bus Body -->
                        <div class="bg-slate-900 rounded-3xl border-2 border-slate-700 overflow-hidden shadow-2xl shadow-black/40">

                            <!-- Bus Front / Dashboard -->
                            <div class="bg-gradient-to-b from-slate-800 to-slate-900 px-4 py-3 border-b-2 border-slate-700 rounded-t-3xl">
                                <div class="flex items-center justify-between">
                                    <!-- Door (LEFT) -->
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-10 h-5 bg-teal-500/15 border border-teal-500/40 rounded-md flex items-center justify-center">
                                            <i data-lucide="door-open" class="w-3 h-3 text-teal-400"></i>
                                        </div>
                                        <span class="text-[9px] text-teal-400 font-bold uppercase tracking-wider">Pintu</span>
                                    </div>

                                    <!-- Bus Name / Class badge -->
                                    <div class="text-center">
                                        <span class="text-[10px] font-bold text-white/60 uppercase tracking-widest">
                                            <?= strtolower($schedule['bus_type']) === 'ekonomi' ? '2 × 3 SEAT' : '2 × 2 SEAT' ?>
                                        </span>
                                    </div>

                                    <!-- Driver (RIGHT) -->
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-10 h-5 bg-amber-500/15 border border-amber-500/40 rounded-md flex items-center justify-center">
                                            <i data-lucide="steering-wheel" class="w-3 h-3 text-amber-400"></i>
                                        </div>
                                        <span class="text-[9px] text-amber-400 font-bold uppercase tracking-wider">Supir</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Seat Grid -->
                            <div class="p-4">
                                <?php
                                    $busType = strtolower($schedule['bus_type']);
                                    $isEkonomi = ($busType === 'ekonomi');
                                    // Ekonomi: 2 left + aisle + 3 right? Or 2+3?
                                    // Per request: Ekonomi = 2x3, Eksekutif = 2x2
                                    // Layout: left cols | aisle | right cols
                                    $leftCols  = 2;
                                    $rightCols = $isEkonomi ? 3 : 2;
                                    $totalSeats = $schedule['total_seats'];
                                    $seatsPerRow = $leftCols + $rightCols;
                                    $rowCount = ceil($totalSeats / $seatsPerRow);

                                    // Column labels
                                    $leftLabels  = ['A', 'B'];
                                    $rightLabels = $isEkonomi ? ['C', 'D', 'E'] : ['C', 'D'];
                                ?>
                                <!-- Header labels -->
                                <div class="flex items-center gap-1 mb-2 px-0.5">
                                    <?php foreach ($leftLabels as $l): ?>
                                        <div class="flex-1 text-center text-[9px] font-bold text-slate-500 uppercase"><?= $l ?></div>
                                    <?php endforeach; ?>
                                    <div class="w-5 flex-shrink-0"></div>
                                    <?php foreach ($rightLabels as $l): ?>
                                        <div class="flex-1 text-center text-[9px] font-bold text-slate-500 uppercase"><?= $l ?></div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Rows -->
                                <div class="space-y-1.5" id="seat-grid">
                                    <?php
                                    $seatNum = 1;
                                    for ($row = 1; $row <= $rowCount; $row++):
                                        $allCols = array_merge(
                                            array_map(fn($l) => $row . $l, $leftLabels),
                                            array_map(fn($l) => $row . $l, $rightLabels)
                                        );
                                    ?>
                                        <div class="flex items-center gap-1">
                                            <!-- Row number -->
                                            <div class="w-4 text-[9px] font-bold text-slate-600 text-center flex-shrink-0"><?= $row ?></div>
                                            <!-- Left seats -->
                                            <?php foreach ($leftLabels as $l):
                                                $seatCode = $row . $l;
                                                if ($seatNum > $totalSeats) break;
                                                $seatNum++;
                                            ?>
                                                <button type="button"
                                                    data-seat="<?= $seatCode ?>"
                                                    @click="toggleSeat('<?= $seatCode ?>')"
                                                    :class="getSeatClass('<?= $seatCode ?>')"
                                                    class="flex-1 aspect-square rounded-lg flex flex-col items-center justify-center text-[9px] font-bold transition-all duration-150 relative group"
                                                    <?= in_array($seatCode, $bookedSeatNumbers) ? 'disabled' : '' ?>>
                                                    <i data-lucide="armchair" class="w-3.5 h-3.5 mb-0.5"></i>
                                                    <span><?= $seatCode ?></span>
                                                </button>
                                            <?php endforeach; ?>

                                            <!-- Aisle -->
                                            <div class="w-5 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-0.5 h-full bg-slate-800 rounded-full min-h-[32px]"></div>
                                            </div>

                                            <!-- Right seats -->
                                            <?php foreach ($rightLabels as $l):
                                                $seatCode = $row . $l;
                                                if ($seatNum > $totalSeats) break;
                                                $seatNum++;
                                            ?>
                                                <button type="button"
                                                    data-seat="<?= $seatCode ?>"
                                                    @click="toggleSeat('<?= $seatCode ?>')"
                                                    :class="getSeatClass('<?= $seatCode ?>')"
                                                    class="flex-1 aspect-square rounded-lg flex flex-col items-center justify-center text-[9px] font-bold transition-all duration-150 relative group"
                                                    <?= in_array($seatCode, $bookedSeatNumbers) ? 'disabled' : '' ?>>
                                                    <i data-lucide="armchair" class="w-3.5 h-3.5 mb-0.5"></i>
                                                    <span><?= $seatCode ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>

                                <!-- Back label -->
                                <div class="mt-4 pt-3 border-t border-slate-800 text-center text-[9px] text-slate-600 font-bold uppercase tracking-widest">
                                    ← Belakang Bis →
                                </div>
                            </div>
                        </div>

                        <!-- Selected summary below map -->
                        <div class="mt-3 text-center">
                            <span class="text-xs text-slate-500" x-show="selectedSeats.length === 0">Belum ada kursi dipilih</span>
                            <div class="flex flex-wrap justify-center gap-1.5" x-show="selectedSeats.length > 0" x-cloak>
                                <template x-for="s in selectedSeats" :key="s">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-brand-600/20 text-brand-300 border border-brand-500/30">
                                        <i data-lucide="armchair" class="w-2.5 h-2.5"></i>
                                        <span x-text="s"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Passenger Details & Billing -->
        <div class="lg:col-span-1">
            <div class="glass-light rounded-2xl p-5 border border-white/5 sticky top-20 space-y-5">
                <h3 class="text-base font-bold text-white border-b border-white/5 pb-3">Detail Pemesanan</h3>

                <form action="<?= base_url('customer/booking/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="schedule_id" value="<?= esc($schedule['id']) ?>">
                    <input type="hidden" name="selected_seats" :value="selectedSeats.join(',')">

                    <!-- Passenger Details -->
                    <div class="space-y-3 mb-5">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Data Penumpang</p>
                        <template x-if="selectedSeats.length === 0">
                            <div class="p-4 bg-slate-900/80 border border-white/5 rounded-xl text-xs text-slate-500 text-center leading-relaxed">
                                <i data-lucide="armchair" class="w-8 h-8 mx-auto mb-2 text-slate-700"></i>
                                Pilih kursi terlebih dahulu
                            </div>
                        </template>
                        <div class="space-y-2.5 max-h-52 overflow-y-auto pr-1">
                            <template x-for="seat in selectedSeats" :key="seat">
                                <div class="p-3 bg-slate-900/80 border border-white/5 rounded-xl space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-[10px] font-bold text-brand-400 flex items-center gap-1">
                                            <i data-lucide="armchair" class="w-3 h-3"></i> Kursi <span x-text="seat"></span>
                                        </span>
                                        <button type="button" @click="removeSeat(seat)" class="text-[10px] text-rose-400 hover:text-rose-300 transition-colors">Hapus</button>
                                    </div>
                                    <input type="text" :name="`passengers[${seat}]`" required
                                        class="input-field block w-full px-3 py-2 rounded-lg text-xs"
                                        placeholder="Nama Lengkap Penumpang"
                                        x-model="passengerNames[seat]">
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Promo Code -->
                    <div class="space-y-2 mb-5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                            <i data-lucide="tag" class="w-3 h-3 inline text-amber-400"></i> Kode Promo
                        </label>
                        <div class="flex gap-2">
                            <input name="promo_code" id="promo_code" type="text" x-model="promoCode"
                                   class="input-field block w-full px-3 py-2.5 rounded-xl text-xs font-mono tracking-widest"
                                   placeholder="Contoh: AIPROMO" style="text-transform:uppercase">
                            <button type="button" @click="applyPromo()"
                                    class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center">
                                Terapkan
                            </button>
                        </div>
                        <p class="text-[10px] mt-1.5 font-semibold" :class="promoValid ? 'text-emerald-400' : 'text-rose-400'" x-show="promoMessage" x-text="promoMessage" x-cloak></p>
                    </div>

                    <!-- Pricing -->
                    <div class="space-y-2 py-3 border-t border-b border-white/5 mb-5">
                        <div class="flex justify-between text-xs text-slate-400">
                            <span>Harga per kursi</span>
                            <span class="font-mono text-white">Rp <?= number_format($schedule['price'], 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-400">
                            <span>Jumlah kursi</span>
                            <span class="font-bold text-white" x-text="selectedSeats.length + ' kursi'">0 kursi</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-405" x-show="discountAmount > 0" x-cloak>
                            <span>Potongan Promo (<span x-text="appliedPromoCode" class="font-mono font-bold text-amber-400"></span>)</span>
                            <span class="font-mono text-rose-400 font-semibold" x-text="'- ' + formatRupiah(discountAmount)">- Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-white pt-1">
                            <span>Total Harga</span>
                            <span class="text-emerald-400 font-mono" x-text="formatRupiah(finalPrice)">Rp 0</span>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="selectedSeats.length === 0"
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-600/15 flex items-center justify-center gap-2 text-sm transition-all transform hover:-translate-y-0.5 disabled:bg-slate-800 disabled:text-slate-500 disabled:shadow-none disabled:cursor-not-allowed disabled:transform-none disabled:from-slate-800 disabled:to-slate-800">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        Proses Ke Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
const BOOKED_SEATS = <?= json_encode($bookedSeatNumbers) ?>;
const PRICE_PER_SEAT = <?= (int)$schedule['price'] ?>;

function bookingApp() {
    return {
        selectedSeats: <?= json_encode(old('selected_seats') ? explode(',', old('selected_seats')) : []) ?>,
        passengerNames: <?= json_encode(old('passengers') ?? (object)[]) ?>,
        promoCode: '',
        appliedPromoCode: '',
        discountAmount: 0,
        discountPercent: null,
        discountFixed: null,
        promoMessage: '',
        promoValid: false,
        finalPrice: 0,

        init() {
            this.recalculatePrice();
        },

        toggleSeat(code) {
            if (BOOKED_SEATS.includes(code)) return;
            const idx = this.selectedSeats.indexOf(code);
            if (idx > -1) {
                this.selectedSeats.splice(idx, 1);
            } else {
                this.selectedSeats.push(code);
            }
            this.selectedSeats.sort((a, b) => {
                const ra = parseInt(a), rb = parseInt(b);
                if (ra !== rb) return ra - rb;
                return a.slice(-1).localeCompare(b.slice(-1));
            });
            this.recalculatePrice();
            this.$nextTick(() => lucide.createIcons());
        },

        removeSeat(code) {
            const idx = this.selectedSeats.indexOf(code);
            if (idx > -1) this.selectedSeats.splice(idx, 1);
            this.recalculatePrice();
        },

        recalculatePrice() {
            const baseTotal = this.selectedSeats.length * PRICE_PER_SEAT;
            if (this.promoValid) {
                if (this.discountPercent !== null) {
                    this.discountAmount = baseTotal * (this.discountPercent / 100);
                } else if (this.discountFixed !== null) {
                    this.discountAmount = Math.min(this.discountFixed, baseTotal);
                }
            } else {
                this.discountAmount = 0;
            }
            this.finalPrice = baseTotal - this.discountAmount;
        },

        applyPromo() {
            const baseTotal = this.selectedSeats.length * PRICE_PER_SEAT;
            if (baseTotal <= 0) {
                this.promoMessage = 'Pilih kursi terlebih dahulu sebelum menggunakan promo.';
                this.promoValid = false;
                this.discountAmount = 0;
                this.recalculatePrice();
                return;
            }

            if (!this.promoCode.trim()) {
                this.promoMessage = 'Masukkan kode promo Anda.';
                this.promoValid = false;
                this.discountAmount = 0;
                this.recalculatePrice();
                return;
            }

            fetch('<?= base_url('customer/promo/check') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({
                    promo_code: this.promoCode,
                    total_price: baseTotal
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.valid) {
                    this.promoValid = true;
                    this.appliedPromoCode = data.code;
                    this.discountAmount = data.discount_amount;
                    this.discountPercent = data.discount_type === 'percent' ? data.discount_value : null;
                    this.discountFixed = data.discount_type === 'fixed' ? data.discount_value : null;
                    this.promoMessage = 'Kode promo berhasil digunakan!';
                    this.recalculatePrice();
                } else {
                    this.promoValid = false;
                    this.discountAmount = 0;
                    this.promoMessage = data.message || 'Kode promo tidak valid.';
                    this.recalculatePrice();
                }
            })
            .catch(err => {
                console.error(err);
                this.promoValid = false;
                this.promoMessage = 'Terjadi kesalahan saat memeriksa kode promo.';
                this.recalculatePrice();
            });
        },

        getSeatClass(code) {
            if (BOOKED_SEATS.includes(code)) {
                return 'bg-slate-900 border border-slate-800 text-slate-700 cursor-not-allowed opacity-50';
            }
            if (this.selectedSeats.includes(code)) {
                return 'bg-brand-600 border border-brand-400 text-white cursor-pointer shadow-md shadow-brand-600/30 scale-105';
            }
            return 'bg-slate-800 border border-slate-700 text-slate-400 hover:bg-slate-700 hover:border-brand-500/50 hover:text-brand-300 cursor-pointer hover:scale-105';
        },

        formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }
    };
}
</script>
<?= $this->endSection() ?>
