<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Main Container -->
<main class="max-w-5xl mx-auto px-4 py-8 flex-grow" x-data="scanApp()">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Left: Scan Area -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="text-center max-w-sm mx-auto">
                <h1 class="text-xl font-bold font-outfit text-white">Validasi Tiket Boarding</h1>
                <p class="text-xs text-slate-450 mt-1">Gunakan pemindai kamera atau input kode booking/tiket secara manual untuk melakukan validasi boarding pass penumpang.</p>
            </div>

            <!-- Scanner Camera Screen -->
            <div class="bg-slate-950 border border-slate-850 rounded-2xl p-4 text-center relative overflow-hidden flex flex-col items-center justify-center h-72">
                <!-- Active camera simulator -->
                <div class="flex flex-col items-center justify-center space-y-3" x-show="!cameraActive">
                    <div class="absolute inset-0 border-2 border-dashed border-brand-500/20 rounded-2xl pointer-events-none m-3"></div>
                    <div class="p-3.5 bg-slate-900 rounded-full text-slate-600 border border-slate-800/60">
                        <i data-lucide="camera-off" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400">Kamera Scanner Tidak Aktif</h4>
                        <p class="text-[10px] text-slate-550 max-w-xs mx-auto mt-0.5">Nyalakan kamera untuk melakukan pemindaian secara langsung dari webcam/HP.</p>
                    </div>
                    <button @click="startCamera()" class="py-1.5 px-3 rounded-lg text-xs font-semibold bg-brand-600 hover:bg-brand-500 text-white transition-all">
                        Aktifkan Kamera
                    </button>
                </div>

                <!-- Actual Scanner View -->
                <div class="w-full h-full relative" x-show="cameraActive" x-cloak>
                    <div id="reader" class="w-full h-full rounded-lg overflow-hidden bg-black"></div>
                    <button @click="stopCamera()" class="absolute bottom-3 right-3 z-10 px-2.5 py-1 rounded bg-slate-950 hover:bg-slate-900 text-[10px] font-bold text-slate-450 border border-slate-800 transition-all">
                        Matikan Kamera
                    </button>
                </div>
            </div>

            <!-- Manual Input Form -->
            <div class="space-y-2">
                <label for="booking_code" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Input Kode Manual</label>
                <div class="flex gap-2">
                    <input id="booking_code" x-model="manualCode" type="text" @keydown.enter.prevent="verifyCode()"
                        class="block w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs font-mono tracking-wider"
                        placeholder="Contoh: BK-XXXXXXXX atau TKT-XXXXXXXX" style="text-transform: uppercase;">
                    <button @click="verifyCode()" class="py-2.5 px-4 rounded-xl font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-md flex items-center gap-1.5 transition-all text-xs flex-shrink-0">
                        <i data-lucide="check" class="w-4 h-4"></i> Verifikasi
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Verification Details -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
            <h2 class="text-md font-bold text-white font-outfit border-b border-slate-800 pb-3 flex items-center gap-2">
                <i data-lucide="info" class="w-5 h-5 text-brand-400"></i> Detail Boarding Pass
            </h2>

            <!-- State: Empty -->
            <div x-show="!ticketData && !loading && !errorMessage" class="h-64 flex flex-col items-center justify-center text-slate-500 text-center space-y-2">
                <i data-lucide="file-question" class="w-12 h-12 opacity-35"></i>
                <h4 class="text-xs font-semibold text-slate-400">Belum Ada Tiket yang Diverifikasi</h4>
                <p class="text-[10px] text-slate-550 max-w-xs">Scan QR Code penumpang atau masukkan kode manual untuk menampilkan manifes penumpang.</p>
            </div>

            <!-- State: Error -->
            <div x-show="errorMessage && !loading" class="h-64 flex flex-col items-center justify-center text-rose-400 text-center space-y-3" x-cloak>
                <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-full text-rose-450">
                    <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-rose-400">Validasi Gagal</h4>
                    <p class="text-[10px] text-slate-400 max-w-xs mx-auto mt-1 leading-relaxed" x-text="errorMessage"></p>
                </div>
                <button @click="errorMessage = ''; manualCode = ''" class="py-1.5 px-3 rounded-lg text-[10px] font-bold bg-slate-800 hover:bg-slate-750 text-slate-300 border border-white/5 transition-all">
                    Ulangi Pemindaian
                </button>
            </div>

            <!-- State: Loading -->
            <div x-show="loading" class="h-64 flex flex-col items-center justify-center text-slate-500 space-y-3" x-cloak>
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-brand-500 border-slate-900"></div>
                <p class="text-xs text-slate-400">Memverifikasi kode tiket...</p>
            </div>

            <!-- State: Success Details -->
            <div x-show="ticketData && !loading" class="space-y-6" x-cloak>
                
                <!-- Warning Banner -->
                <div x-show="warningMessage" class="p-4 bg-amber-500/10 border border-amber-500/25 rounded-2xl text-amber-450 text-xs flex items-start gap-2.5" x-cloak>
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-amber-300 block text-xs">Peringatan Boarding (Salah Armada)</span>
                        <p class="mt-1 leading-relaxed text-[11px]" x-text="warningMessage"></p>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="flex justify-between items-center p-3 rounded-2xl border"
                    :class="ticketData.status === 'boarded' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-450' : 'bg-brand-500/10 border-brand-500/20 text-brand-400'">
                    <div class="flex items-center gap-2 text-xs font-semibold">
                        <i :data-lucide="ticketData.status === 'boarded' ? 'check-circle' : 'ticket'" class="w-4 h-4"></i>
                        <span x-text="ticketData.status === 'boarded' ? 'SUDAH BOARDING (CONFIRMED)' : 'BELUM BOARDING (READY)'"></span>
                    </div>
                    <span class="font-mono text-xs font-bold" x-text="ticketData.qr_code"></span>
                </div>

                <!-- Manifest details -->
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-850">
                        <span class="text-slate-550 uppercase tracking-wider text-[9px] font-semibold block">Penumpang / Pemesan</span>
                        <span class="text-white font-bold block mt-1" x-text="ticketData.customer_name"></span>
                        <span class="text-[10px] text-slate-550" x-text="ticketData.customer_phone"></span>
                    </div>
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-850">
                        <span class="text-slate-555 uppercase tracking-wider text-[9px] font-semibold block">Rute & Bus</span>
                        <span class="text-white font-bold block mt-1"><span x-text="ticketData.origin"></span> &rarr; <span x-text="ticketData.destination"></span></span>
                        <span class="text-[10px] text-slate-550" x-text="`${ticketData.bus_name} (${ticketData.bus_type})`"></span>
                    </div>
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-850 col-span-2">
                        <span class="text-slate-555 uppercase tracking-wider text-[9px] font-semibold block">Waktu Keberangkatan</span>
                        <span class="text-white font-bold block mt-1" x-text="formatDateTime(ticketData.departure_time)"></span>
                    </div>
                </div>

                <!-- Selected Seats Detail -->
                <div class="space-y-2">
                    <span class="text-slate-500 uppercase tracking-wider text-[9px] font-semibold block">Manifes Kursi Penumpang</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="seat in seats" :key="seat.id">
                            <div class="p-2.5 bg-slate-950 border border-slate-850 rounded-xl flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="px-2 py-0.5 bg-brand-500/10 text-brand-400 font-bold border border-brand-500/20 rounded font-mono" x-text="seat.seat_number"></div>
                                    <span class="text-slate-300 font-semibold" x-text="seat.passenger_name"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-4 border-t border-slate-800/60">
                    <template x-if="ticketData.status !== 'boarded'">
                        <button @click="confirmBoarding()"
                            class="w-full py-3.5 px-4 rounded-xl font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-600/10 flex items-center justify-center gap-2 transition-all text-sm transform hover:scale-[1.01]">
                            <i data-lucide="check-square" class="w-4 h-4"></i> Konfirmasi Boarding Penumpang
                        </button>
                    </template>
                    <template x-if="ticketData.status === 'boarded'">
                        <button disabled
                            class="w-full py-3.5 px-4 rounded-xl font-bold text-slate-550 bg-slate-800 cursor-not-allowed border border-slate-750 flex items-center justify-center gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-450"></i> Penumpang Sudah Boarding
                        </button>
                    </template>
                </div>

            </div>

        </div>

    </div>
</main>

<!-- html5-qrcode webcam library -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
function scanApp() {
    return {
        manualCode: '',
        cameraActive: false,
        loading: false,
        ticketData: null,
        seats: [],
        html5QrCode: null,
        errorMessage: '',
        warningMessage: '',

        startCamera() {
            this.cameraActive = true;
            this.ticketData = null;
            this.errorMessage = '';
            this.warningMessage = '';
            this.$nextTick(() => {
                this.html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: 200 };
                
                this.html5QrCode.start(
                    { facingMode: "environment" }, 
                    config, 
                    (decodedText) => {
                        this.manualCode = decodedText;
                        this.verifyCode();
                        this.stopCamera();
                    },
                    (errorMessage) => {
                        // ignore failures to keep scanning
                    }
                ).catch(err => {
                    this.errorMessage = "Gagal mengakses kamera. Pastikan memberikan izin akses kamera.";
                    this.cameraActive = false;
                });
            });
        },

        stopCamera() {
            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().then(() => {
                    this.cameraActive = false;
                    this.html5QrCode = null;
                }).catch(err => {
                    this.cameraActive = false;
                });
            } else {
                this.cameraActive = false;
            }
        },

        verifyCode() {
            let codeVal = this.manualCode.trim();
            if (!codeVal) return;

            this.loading = true;
            this.ticketData = null;
            this.errorMessage = '';
            this.warningMessage = '';
            
            let formData = new FormData();
            formData.append('booking_code', codeVal);

            fetch('<?= base_url("petugas/scan/verify") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                this.loading = false;
                if (res.status === 'success') {
                    this.ticketData = res.ticket;
                    this.seats = res.seats;
                    this.warningMessage = res.warning || '';
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    this.errorMessage = res.message;
                }
            })
            .catch(err => {
                this.loading = false;
                this.errorMessage = 'Gagal menghubungkan ke server.';
            });
        },

        confirmBoarding() {
            if (!this.ticketData) return;

            let formData = new FormData();
            formData.append('ticket_id', this.ticketData.id);

            fetch('<?= base_url("petugas/scan/confirm") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    this.ticketData.status = 'boarded';
                    alert(res.message);
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    this.errorMessage = res.message;
                }
            })
            .catch(err => {
                this.errorMessage = 'Gagal mengonfirmasi boarding.';
            });
        },

        formatDateTime(dateTimeStr) {
            const date = new Date(dateTimeStr);
            const options = { 
                day: 'numeric', month: 'short', year: 'numeric', 
                hour: '2-digit', minute: '2-digit',
                timeZone: 'Asia/Jakarta',
                hour12: false
            };
            const formatted = date.toLocaleString('id-ID', options);
            return formatted + ' WIB';
        }
    };
}
</script>
<?= $this->endSection() ?>
