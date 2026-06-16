<?= $this->extend('layout/admin') ?>

<?php
$title = 'Tambah Jadwal Baru - SiTeBus';
$subtitle = 'Atur Penjadwalan Rute & Armada Bus';
?>

<?= $this->section('admin_content') ?>
<div class="max-w-2xl bg-slate-900/60 border border-slate-800/80 p-6 sm:p-8 rounded-3xl shadow-xl">
    <!-- Errors Alert -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-xs space-y-1">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p class="flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-4 h-4"></i> <?= esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('admin/schedule/store') ?>" method="POST" class="space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="route_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Rute Perjalanan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="map-pinned" class="w-4 h-4"></i>
                    </div>
                    <select id="route_id" name="route_id" required
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="">Pilih Rute</option>
                        <?php foreach ($routes as $route): ?>
                            <option value="<?= $route['id'] ?>" <?= old('route_id') == $route['id'] ? 'selected' : '' ?>>
                                <?= esc($route['origin']) ?> - <?= esc($route['destination']) ?> (<?= esc($route['distance_km']) ?> KM)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="bus_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Bus / Armada</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                    </div>
                    <select id="bus_id" name="bus_id" required
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="">Pilih Bus</option>
                        <?php foreach ($buses as $bus): ?>
                            <option value="<?= $bus['id'] ?>" <?= old('bus_id') == $bus['id'] ? 'selected' : '' ?>>
                                <?= esc($bus['name']) ?> (<?= esc($bus['type']) ?> - <?= esc($bus['total_seats']) ?> Kursi)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="departure_time" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Waktu Keberangkatan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <input id="departure_time" name="departure_time" type="datetime-local" required value="<?= old('departure_time') ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
            </div>

            <div>
                <label for="arrival_time" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Waktu Kedatangan (Estimasi)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    </div>
                    <input id="arrival_time" name="arrival_time" type="datetime-local" required value="<?= old('arrival_time') ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="price" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Harga Tiket (Rupiah)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <span class="text-sm font-semibold text-slate-550">Rp</span>
                    </div>
                    <input id="price" name="price" type="number" step="0.01" required value="<?= old('price') ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-semibold"
                        placeholder="Contoh: 120000">
                </div>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status Jadwal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </div>
                    <select id="status" name="status" required
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="scheduled" <?= old('status', 'scheduled') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="ongoing" <?= old('status') === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="completed" <?= old('status') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= old('status') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        <!-- Crew Information Section -->
        <div class="border-t border-slate-800/80 pt-6">
            <h4 class="text-xs font-bold text-brand-400 uppercase tracking-widest mb-4">Informasi Kru Bus (Opsional)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <label for="driver_1" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Sopir Utama (Sopir 1)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input id="driver_1" name="driver_1" type="text" value="<?= old('driver_1') ?>" placeholder="Nama Sopir Utama"
                            class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="driver_2" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Sopir Cadangan (Sopir 2)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input id="driver_2" name="driver_2" type="text" value="<?= old('driver_2') ?>" placeholder="Nama Sopir Cadangan"
                            class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="conductor" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kondektur</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input id="conductor" name="conductor" type="text" value="<?= old('conductor') ?>" placeholder="Nama Kondektur"
                            class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 gap-2 transition-all text-sm">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Simpan Jadwal
            </button>
            <a href="<?= base_url('admin/schedule') ?>" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-slate-400 hover:text-slate-250 hover:bg-slate-850 gap-2 transition-all text-sm border border-slate-800/80">
                Batal
            </a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
