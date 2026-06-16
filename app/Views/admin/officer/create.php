<?= $this->extend('layout/admin') ?>

<?php
$title = 'Tambah Petugas Baru - SiTeBus';
$subtitle = 'Daftar Akun Baru Petugas Terminal & Armada';
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

    <form action="<?= base_url('admin/officer/store') ?>" method="POST" class="space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <input id="name" name="name" type="text" required value="<?= old('name') ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Contoh: Budi Santoso">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input id="email" name="email" type="email" required value="<?= old('email') ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Contoh: budi@sitebus.com">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="phone" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">No. Telepon</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </div>
                    <input id="phone" name="phone" type="text" required value="<?= old('phone') ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Contoh: 08123456789">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input id="password" name="password" type="password" required
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Minimal 6 karakter">
                </div>
            </div>
        </div>

        <div>
            <label for="bus_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Penugasan Bus / Armada</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                </div>
                <select id="bus_id" name="bus_id"
                    class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="">Belum Ditugaskan</option>
                    <?php foreach ($buses as $bus): ?>
                        <option value="<?= $bus['id'] ?>" <?= old('bus_id') == $bus['id'] ? 'selected' : '' ?>>
                            <?= esc($bus['name']) ?> (<?= esc($bus['code']) ?> - <?= esc($bus['type']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 gap-2 transition-all text-sm">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Simpan Petugas
            </button>
            <a href="<?= base_url('admin/officer') ?>" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-slate-400 hover:text-slate-250 hover:bg-slate-850 gap-2 transition-all text-sm border border-slate-800/80">
                Batal
            </a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
