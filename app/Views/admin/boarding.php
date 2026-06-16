<?= $this->extend('layout/admin') ?>

<?= $this->section('admin_content') ?>

<main class="space-y-6" x-data="boardingMonitorApp()">
    
    <!-- Top Selector Card -->
    <div class="panel-card p-6 shadow-2xl space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 bg-brand-500/10 text-brand-400 rounded-lg border border-brand-500/20">
                        <i data-lucide="scan-line" class="w-4 h-4"></i>
                    </div>
                    Monitoring Boarding Penumpang
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 ml-9">Pantau manifest penumpang yang melakukan boarding secara realtime dari scanner petugas terminal.</p>
            </div>
            
            <div class="w-full lg:max-w-md flex gap-2 items-center">
                <?php if ($isCrew && count($schedules) === 1): ?>
                    <div class="flex-1 px-4 py-2.5 bg-brand-500/10 border border-brand-500/20 text-brand-400 rounded-xl font-semibold text-xs flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>Jadwal Tugas: <?= date('H:i', strtotime($schedules[0]['departure_time'])) ?> WIB | <?= esc($schedules[0]['origin']) ?> &rarr; <?= esc($schedules[0]['destination']) ?></span>
                    </div>
                <?php else: ?>
                    <select x-model="selectedScheduleId" @change="handleScheduleChange()" 
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs">
                        <option value="">-- Pilih Rute Keberangkatan Hari Ini --</option>
                        <?php foreach ($schedules as $s): ?>
                            <option value="<?= $s['id'] ?>">
                                <?= date('H:i', strtotime($s['departure_time'])) ?> WIB | <?= esc($s['origin']) ?> &rarr; <?= esc($s['destination']) ?> (<?= esc($s['bus_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <button @click="printReport()" :disabled="!selectedScheduleId"
                        class="py-2.5 px-4 rounded-xl font-bold text-white bg-slate-900 border border-slate-800 hover:border-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all text-xs flex items-center gap-1.5 flex-shrink-0">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Laporan
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <?php if ($isCrew && empty($schedules)): ?>
        <div class="py-20 bg-slate-900/40 border border-slate-850 rounded-3xl text-center space-y-3 max-w-sm mx-auto">
            <div class="inline-flex p-3 bg-rose-500/10 text-rose-400 rounded-2xl border border-rose-500/20 mb-1">
                <i data-lucide="calendar-off" class="w-5 h-5"></i>
            </div>
            <h4 class="text-xs font-bold text-white">Tidak Ada Jadwal Tugas</h4>
            <p class="text-[10px] text-slate-500 leading-relaxed max-w-xs mx-auto">
                Anda tidak memiliki jadwal perjalanan atau penugasan tugas hari ini. Silakan hubungi admin terminal jika terjadi kesalahan.
            </p>
        </div>
    <?php else: ?>
        <div x-show="!selectedScheduleId" class="py-20 bg-slate-900/40 border border-slate-850 rounded-3xl text-center space-y-3 max-w-sm mx-auto">
            <div class="inline-flex p-3 bg-brand-500/10 text-brand-400 rounded-2xl border border-brand-500/20 mb-1">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <h4 class="text-xs font-bold text-white">Jadwal Belum Dipilih</h4>
            <p class="text-[10px] text-slate-500 leading-relaxed max-w-xs mx-auto">
                Silakan pilih jadwal rute bus hari ini pada pilihan di atas untuk memulai monitoring manifest secara realtime.
            </p>
        </div>
    <?php endif; ?>

    <!-- Active Monitoring Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-show="selectedScheduleId" x-cloak>
        
        <!-- Left: Trip details & Crew (4/12) -->
        <div class="lg:col-span-4 bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                <i data-lucide="truck" class="w-4 h-4 text-brand-400"></i> Detail Armada &amp; Kru
            </h3>

            <div x-show="scheduleData" class="space-y-4 text-xs" x-cloak>
                <div class="bg-slate-950 p-4 border border-slate-850 rounded-2xl space-y-2">
                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Armada Bus</span>
                    <h4 class="text-xs font-bold text-white" x-text="scheduleData.bus_name"></h4>
                    <p class="text-[10px] text-slate-500" x-text="`Tipe: ${scheduleData.bus_type} · ${scheduleData.total_seats} Kursi`"></p>
                </div>

                <div class="bg-slate-950 p-4 border border-slate-850 rounded-2xl space-y-3">
                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Kru Bertugas</span>
                    
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="p-1 bg-slate-900 rounded text-slate-400"><i data-lucide="user" class="w-3.5 h-3.5"></i></div>
                            <div>
                                <p class="text-[9px] text-slate-500 uppercase font-semibold">Sopir Utama</p>
                                <p class="text-slate-300 font-bold" x-text="scheduleData.driver_1_name || '-'"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="p-1 bg-slate-900 rounded text-slate-400"><i data-lucide="user" class="w-3.5 h-3.5"></i></div>
                            <div>
                                <p class="text-[9px] text-slate-500 uppercase font-semibold">Sopir Cadangan</p>
                                <p class="text-slate-300 font-bold" x-text="scheduleData.driver_2_name || '-'"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="p-1 bg-slate-900 rounded text-slate-400"><i data-lucide="user" class="w-3.5 h-3.5"></i></div>
                            <div>
                                <p class="text-[9px] text-slate-500 uppercase font-semibold">Kondektur</p>
                                <p class="text-slate-300 font-bold" x-text="scheduleData.conductor_name || '-'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Passenger Manifest List (8/12) -->
        <div class="lg:col-span-8 bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-3xl p-6 shadow-2xl space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-brand-400"></i> Manifes Kursi &amp; Boarding Status
                </h3>
                
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 dot-pulse"></div>
                    <span class="text-[9px] text-slate-500 font-semibold">Realtime Monitoring Aktif</span>
                </div>
            </div>

            <!-- Loader -->
            <div x-show="manifestLoading" class="py-20 text-center space-y-2">
                <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-brand-500 border-slate-900 mx-auto"></div>
                <p class="text-[10px] text-slate-500">Menghubungkan ke manifest...</p>
            </div>

            <!-- Table -->
            <div x-show="!manifestLoading && manifestList.length > 0" class="overflow-x-auto rounded-xl border border-slate-850" x-cloak>
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="bg-slate-950 text-slate-500 text-[9px] uppercase tracking-widest font-bold border-b border-slate-850">
                            <th class="px-4 py-3 text-center">Kursi</th>
                            <th class="px-4 py-3">Nama Penumpang</th>
                            <th class="px-4 py-3 text-center">Kode Booking</th>
                            <th class="px-4 py-3 text-center">Status Boarding</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="m in manifestList" :key="m.seat_number">
                            <tr class="border-b border-slate-850/50 table-row">
                                <td class="px-4 py-3 text-center font-bold font-mono text-white" x-text="m.seat_number"></td>
                                <td class="px-4 py-3 font-bold text-slate-200" x-text="m.passenger_name"></td>
                                <td class="px-4 py-3 text-center font-mono text-slate-400 text-[10px]" x-text="m.booking_code"></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded text-[8px] font-extrabold uppercase border"
                                          :class="m.boarding_status === 'boarded' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-slate-400 bg-slate-900 border-slate-800'">
                                        <span x-text="m.boarding_status === 'boarded' ? 'Boarded' : 'Belum Boarding'"></span>
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
                <p class="text-xs font-semibold text-slate-400">Belum Ada Reservasi</p>
                <p class="text-[10px] text-slate-500 max-w-xs mx-auto mt-0.5">Tidak ada kursi yang dipesan untuk jadwal perjalanan bus ini hari ini.</p>
            </div>
        </div>

    </div>

</main>

<script>
function boardingMonitorApp() {
    return {
        selectedScheduleId: '<?= ($isCrew && count($schedules) >= 1) ? $schedules[0]['id'] : '' ?>',
        scheduleData: null,
        manifestList: [],
        manifestLoading: false,
        pollingInterval: null,

        init() {
            if (this.selectedScheduleId) {
                this.handleScheduleChange();
            }
        },

        handleScheduleChange() {
            // Clear existing polling
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }

            if (!this.selectedScheduleId) {
                this.scheduleData = null;
                this.manifestList = [];
                return;
            }

            // Load immediately
            this.loadManifest(true);

            // Set up polling every 5 seconds for real-time updates
            this.pollingInterval = setInterval(() => {
                this.loadManifest(false);
            }, 5000);
        },

        loadManifest(showLoader = false) {
            if (!this.selectedScheduleId) return;

            if (showLoader) {
                this.manifestLoading = true;
            }

            fetch('<?= base_url("admin/boarding/manifest") ?>/' + this.selectedScheduleId)
            .then(res => res.json())
            .then(res => {
                this.manifestLoading = false;
                if (res.status === 'success') {
                    this.scheduleData = res.schedule;
                    this.manifestList = res.manifest;
                    this.$nextTick(() => lucide.createIcons());
                }
            })
            .catch(err => {
                this.manifestLoading = false;
                console.error('Failed to poll boarding manifest:', err);
            });
        },

        printReport() {
            if (!this.selectedScheduleId) return;
            window.open('<?= base_url("admin/boarding/print") ?>/' + this.selectedScheduleId, '_blank');
        }
    };
}
</script>

<?= $this->endSection() ?>
