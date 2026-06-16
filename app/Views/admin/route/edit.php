<?= $this->extend('layout/admin') ?>

<?php
$title = 'Edit Rute - SiTeBus';
$subtitle = 'Perbarui Jarak & Estimasi Waktu Perjalanan';
?>

<?= $this->section('admin_content') ?>
<div class="w-full bg-slate-900/60 border border-slate-800/80 p-6 sm:p-8 rounded-3xl shadow-xl">
    <!-- Errors Alert -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-xs space-y-1">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p class="flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-4 h-4"></i> <?= esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('admin/route/update/' . $route['id']) ?>" method="POST" class="space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="origin" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kota Asal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <input id="origin" name="origin" type="text" required value="<?= old('origin', $route['origin']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Contoh: Jakarta">
                </div>
            </div>

            <div>
                <label for="destination" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kota Tujuan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="navigation" class="w-4 h-4"></i>
                    </div>
                    <input id="destination" name="destination" type="text" required value="<?= old('destination', $route['destination']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Contoh: Bandung">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="distance_km" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Jarak (KM)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="compass" class="w-4 h-4"></i>
                    </div>
                    <input id="distance_km" name="distance_km" type="number" step="0.01" required value="<?= old('distance_km', $route['distance_km']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Contoh: 150.00">
                </div>
            </div>

            <div>
                <label for="estimated_duration" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Estimasi Durasi (Menit)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                    <input id="estimated_duration" name="estimated_duration" type="number" required value="<?= old('estimated_duration', $route['estimated_duration']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Contoh: 180">
                </div>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 gap-2 transition-all text-sm">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Perbarui Rute
            </button>
            <a href="<?= base_url('admin/route') ?>" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-slate-400 hover:text-slate-250 hover:bg-slate-850 gap-2 transition-all text-sm border border-slate-800/80">
                Batal
            </a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
