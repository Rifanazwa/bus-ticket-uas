<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Main Container -->
<main class="max-w-7xl mx-auto px-4 py-8 flex-grow space-y-6" x-data="scanApp()">
    
    <!-- Top Area: Select Schedule -->
    <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold font-outfit text-white flex items-center gap-2">
                    <div class="p-1.5 bg-brand-500/10 text-brand-400 rounded-lg border border-brand-500/20">
                        <i data-lucide="bus" class="w-4.5 h-4.5"></i>
                    </div>
                    Pilih Perjalanan Bus &amp; Manifes
                </h1>
                <p class="text-xs text-slate-450 mt-1">Pilih jadwal perjalanan hari ini untuk memantau manifest penumpang secara realtime dan mencetak laporan perjalanan.</p>
            </div>
            
            <div class="w-full md:max-w-md flex gap-2">
                <select x-model="selectedScheduleId" @change="loadManifest()" 
                        class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs">
                    <option value="">-- Pilih Jadwal Perjalanan Hari Ini --</option>
                    <?php foreach ($schedules as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= date('H:i', strtotime($s['departure_time'])) ?> WIB | <?= esc($s['origin']) ?> &rarr; <?= esc($s['destination']) ?> (<?= esc($s['bus_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button @click="printReport()" :disabled="!selectedScheduleId"
                        class="py-2.5 px-4 rounded-xl font-bold text-white bg-slate-900 border border-slate-800 hover:border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all text-xs flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State: If no schedule is selected -->
    <div x-show="!selectedScheduleId" class="py-20 bg-slate-900/40 border border-slate-800/80 rounded-3xl text-center space-y-3 max-w-md mx-auto">
        <div class="inline-flex p-3.5 bg-brand-500/10 text-brand-400 rounded-2xl border border-brand-500/20 mb-2">
            <i data-lucide="info" class="w-6 h-6"></i>
        </div>
        <h3 class="text-sm font-bold text-white">Jadwal Belum Dipilih</h3>
        <p class="text-xs text-slate-450 max-w-xs mx-auto leading-relaxed">
            Harap pilih salah satu jadwal keberangkatan bus hari ini pada dropdown di atas untuk membuka portal boarding.
        </p>
    </div>

    <!-- Active Portal: Show when a schedule is selected -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-show="selectedScheduleId" x-cloak>
        
        <!-- Left: Scan Area (5/12 cols) -->
        <div class="lg:col-span-5 bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-6">
            <div class="text-center max-w-xs mx-auto">
                <h2 class="text-md font-bold font-outfit text-white">Alat Pemindai Tiket</h2>
                <p class="text-[10px] text-slate-450 mt-1">Nyalakan kamera atau ketik kode booking tiket untuk verifikasi boarding pass.</p>
            </div>

            <!-- Scanner Camera Screen -->
            <div class="bg-slate-950 border border-slate-850 rounded-2xl p-4 text-center relative overflow-hidden flex flex-col items-center justify-center h-64">
                <!-- Active camera simulator -->
                <div class="flex flex-col items-center justify-center space-y-3" x-show="!cameraActive">
                    <div class="absolute inset-0 border-2 border-dashed border-brand-500/20 rounded-2xl pointer-events-none m-3"></div>
                    <div class="p-3 bg-slate-900 rounded-full text-slate-650 border border-slate-800/60">
                        <i data-lucide="camera-off" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-semibold text-slate-400">Kamera Scanner Tidak Aktif</h4>
                    </div>
                    <button @click="startCamera()" class="py-1 px-2.5 rounded-lg text-[10px] font-bold bg-brand-600 hover:bg-brand-500 text-white transition-all">
                        Aktifkan Kamera
                    </button>
                </div>

                <!-- Actual Scanner View -->
                <div class="w-full h-full relative" x-show="cameraActive" x-cloak>
                    <div id="reader" class="w-full h-full rounded-lg overflow-hidden bg-black"></div>
                    <button @click="stopCamera()" class="absolute bottom-3 right-3 z-10 px-2 py-0.5 rounded bg-slate-950 hover:bg-slate-900 text-[9px] font-bold text-slate-450 border border-slate-800 transition-all">
                        Matikan Kamera
                    </button>
                </div>
            </div>

            <!-- Manual Input Form -->
            <div class="space-y-2">
                <label for="booking_code" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Input Kode Manual</label>
                <div class="flex gap-2">
                    <input id="booking_code" x-model="manualCode" type="text" @keydown.enter.prevent="verifyCode()"
                        class="block w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs font-mono tracking-wider"
                        placeholder="BK-XXXXXXXX ATAU TKT-XXXXXXXX" style="text-transform: uppercase;">
                    <button @click="verifyCode()" class="py-2 px-3.5 rounded-xl font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-md flex items-center gap-1.5 transition-all text-xs flex-shrink-0">
                        <i data-lucide="check" class="w-4 h-4"></i> Verifikasi
                    </button>
                </div>
            </div>

            <!-- Verification Details Box -->
            <div class="border-t border-slate-800/60 pt-4 space-y-4">
                <h3 class="text-xs font-bold text-slate-300">Hasil Scan Tiket</h3>
                
                <!-- State: Empty -->
                <div x-show="!ticketData && !loading && !errorMessage" class="py-8 text-center text-slate-600 space-y-1">
                    <i data-lucide="file-question" class="w-8 h-8 opacity-30 mx-auto"></i>
                    <p class="text-[10px]">Belum ada tiket dipindai.</p>
                </div>

                <!-- State: Loading -->
                <div x-show="loading" class="py-8 text-center text-slate-500 space-y-2" x-cloak>
                    <div class="animate-spin rounded-full h-5 w-5 border-t-2 border-brand-500 border-slate-900 mx-auto"></div>
                    <p class="text-[10px]">Memproses data tiket...</p>
                </div>

                <!-- State: Error -->
                <div x-show="errorMessage && !loading" class="p-4 bg-rose-500/5 border border-rose-500/15 rounded-xl text-center space-y-2" x-cloak>
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 mx-auto"></i>
                    <p class="text-xs font-semibold text-rose-400">Scan Gagal</p>
                    <p class="text-[10px] text-slate-400" x-text="errorMessage"></p>
                </div>

                <!-- State: Success Details -->
                <div x-show="ticketData && !loading" class="bg-slate-950 p-4 border border-slate-850 rounded-2xl space-y-4" x-cloak>
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-extrabold px-2 py-0.5 rounded border uppercase tracking-wider"
                              :class="ticketData.status === 'boarded' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-brand-400 bg-brand-500/10 border-brand-500/20'">
                            <span x-text="ticketData.status === 'boarded' ? 'Sudah Boarding' : 'Belum Boarding'"></span>
                        </span>
                        <span class="font-mono text-[10px] font-bold text-slate-400" x-text="ticketData.qr_code"></span>
                    </div>

                    <div class="space-y-1 text-xs">
                        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Nama Penumpang</p>
                        <p class="text-white font-extrabold" x-text="ticketData.customer_name"></p>
                    </div>

                    <div class="space-y-1 text-xs">
                        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Jadwal Tiket</p>
                        <p class="text-slate-300 font-semibold"><span x-text="ticketData.origin"></span> &rarr; <span x-text="ticketData.destination"></span></p>
                        <p class="text-[10px] text-slate-550" x-text="formatDateTime(ticketData.departure_time)"></p>
                    </div>

                    <!-- Selected Seats -->
                    <div class="space-y-1">
                        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Manifes Kursi</p>
                        <div class="flex gap-1 flex-wrap pt-0.5">
                            <template x-for="seat in seats" :key="seat.id">
                                <span class="px-2 py-0.5 bg-brand-500/10 text-brand-400 font-bold border border-brand-500/20 rounded font-mono text-[10px]" x-text="seat.seat_number"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2">
                        <template x-if="ticketData.status !== 'boarded'">
                            <button @click="confirmBoarding()"
                                class="w-full py-2.5 px-4 rounded-xl font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-md flex items-center justify-center gap-1.5 transition-all text-xs">
                                <i data-lucide="check" class="w-4 h-4"></i> Konfirmasi Boarding
                            </button>
                        </template>
                        <template x-if="ticketData.status === 'boarded'">
                            <button disabled
                                class="w-full py-2.5 px-4 rounded-xl font-bold text-slate-500 bg-slate-900 border border-slate-800 cursor-not-allowed flex items-center justify-center gap-1.5 text-xs">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i> Boarding Terkonfirmasi
                            </button>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right: Trip Manifest Panel (7/12 cols) -->
        <div class="lg:col-span-7 bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-6">
            
            <!-- Trip Header Info -->
            <div x-show="scheduleData" class="space-y-4" x-cloak>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-slate-950 border border-slate-850 rounded-2xl space-y-1">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Unit Armada Bus</span>
                        <h4 class="text-xs font-bold text-white" x-text="scheduleData.bus_name"></h4>
                        <p class="text-[10px] text-slate-500 font-mono" x-text="scheduleData.bus_type.toUpperCase()"></p>
                    </div>
                    <div class="p-3 bg-slate-950 border border-slate-850 rounded-2xl space-y-1">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Kru Perjalanan</span>
                        <p class="text-[10px] text-slate-300 font-semibold">Sopir: <span x-text="scheduleData.driver_1_name || '-'"></span></p>
                        <p class="text-[10px] text-slate-300 font-semibold">Kondektur: <span x-text="scheduleData.conductor_name || '-'"></span></p>
                    </div>
                </div>
            </div>

            <!-- Manifest List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Manifes Penumpang Aktif</h3>
                    <span class="text-[9px] text-slate-500 font-bold" x-text="`${manifestList.length} Penumpang`"></span>
                </div>

                <!-- Manifest loading -->
                <div x-show="manifestLoading" class="py-20 text-center space-y-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-brand-500 border-slate-900 mx-auto"></div>
                    <p class="text-[10px] text-slate-500">Memuat manifes penumpang...</p>
                </div>

                <!-- Manifest table -->
                <div x-show="!manifestLoading && manifestList.length > 0" class="overflow-x-auto rounded-xl border border-slate-850" x-cloak>
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="bg-slate-950 text-slate-500 text-[9px] uppercase tracking-widest font-bold border-b border-slate-850">
                                <th class="px-3 py-2.5 text-center">Kursi</th>
                                <th class="px-3 py-2.5">Nama Penumpang</th>
                                <th class="px-3 py-2.5 text-center">Kode Booking</th>
                                <th class="px-3 py-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="m in manifestList" :key="m.seat_number">
                                <tr class="border-b border-slate-850/50 table-row">
                                    <td class="px-3 py-2.5 text-center font-bold font-mono text-white" x-text="m.seat_number"></td>
                                    <td class="px-3 py-2.5 font-bold text-slate-200" x-text="m.passenger_name"></td>
                                    <td class="px-3 py-2.5 text-center font-mono text-slate-400 text-[10px]" x-text="m.booking_code"></td>
                                    <td class="px-3 py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded text-[8px] font-extrabold uppercase border"
                                              :class="m.boarding_status === 'boarded' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-rose-455 bg-rose-500/5 border-rose-500/15'">
                                            <span x-text="m.boarding_status === 'boarded' ? 'Boarded' : 'Belum'"></span>
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty Manifest State -->
                <div x-show="!manifestLoading && manifestList.length === 0" class="py-16 text-center text-slate-550 bg-slate-950/40 rounded-2xl border border-slate-850" x-cloak>
                    <i data-lucide="users" class="w-8 h-8 opacity-25 mx-auto mb-2"></i>
                    <p class="text-xs font-semibold text-slate-400">Belum Ada Penumpang</p>
                    <p class="text-[10px] text-slate-500 max-w-xs mx-auto mt-0.5">Tidak ada kursi yang dipesan untuk jadwal perjalanan bus ini.</p>
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

        // Trip Manifest State
        selectedScheduleId: '',
        scheduleData: null,
        manifestList: [],
        manifestLoading: false,

        startCamera() {
            this.cameraActive = true;
            this.ticketData = null;
            this.errorMessage = '';
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
                    
                    // Update manifest list dynamically in real-time
                    if (this.selectedScheduleId && res.schedule_id == this.selectedScheduleId) {
                        this.manifestList.forEach(m => {
                            if (m.ticket_id == this.ticketData.id) {
                                m.boarding_status = 'boarded';
                            }
                        });
                    } else if (this.selectedScheduleId) {
                        // Re-fetch manifest if it belongs to different schedule
                        this.loadManifest();
                    }
                    
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    this.errorMessage = res.message;
                }
            })
            .catch(err => {
                this.errorMessage = 'Gagal mengonfirmasi boarding.';
            });
        },

        loadManifest() {
            if (!this.selectedScheduleId) {
                this.scheduleData = null;
                this.manifestList = [];
                return;
            }

            this.manifestLoading = true;
            fetch('<?= base_url("petugas/scan/manifest") ?>/' + this.selectedScheduleId)
            .then(res => res.json())
            .then(res => {
                this.manifestLoading = false;
                if (res.status === 'success') {
                    this.scheduleData = res.schedule;
                    this.manifestList = res.manifest;
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    alert(res.message);
                }
            })
            .catch(err => {
                this.manifestLoading = false;
                console.error('Failed to load manifest:', err);
            });
        },

        printReport() {
            if (!this.selectedScheduleId) return;
            window.open('<?= base_url("petugas/scan/print") ?>/' + this.selectedScheduleId, '_blank');
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
