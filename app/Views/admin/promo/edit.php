<?= $this->extend('layout/admin') ?>

<?php
$title = 'Edit Promo/Voucher - SiTeBus';
$subtitle = 'Perbarui Detail Diskon & Batasan Voucher';
?>

<?= $this->section('admin_content') ?>
<div class="max-w-xl bg-slate-900/60 border border-slate-800/80 p-6 sm:p-8 rounded-3xl shadow-xl">
    <!-- Errors Alert -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-xl text-xs space-y-1">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <p class="flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-4 h-4"></i> <?= esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('admin/promo/update/' . $promo['id']) ?>" method="POST" class="space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="code" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kode Voucher</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="ticket-percent" class="w-4 h-4"></i>
                    </div>
                    <input id="code" name="code" type="text" required value="<?= old('code', $promo['code']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-mono font-bold tracking-wider"
                        placeholder="Contoh: MUDIK10" style="text-transform: uppercase;">
                </div>
            </div>

            <div>
                <label for="discount_type" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tipe Diskon</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="percent" class="w-4 h-4"></i>
                    </div>
                    <select id="discount_type" name="discount_type" required
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="percent" <?= old('discount_type', $promo['discount_type']) === 'percent' ? 'selected' : '' ?>>Persentase (%)</option>
                        <option value="fixed" <?= old('discount_type', $promo['discount_type']) === 'fixed' ? 'selected' : '' ?>>Nominal Tetap (Rupiah)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="discount_value" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nilai Diskon</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="coins" class="w-4 h-4"></i>
                    </div>
                    <input id="discount_value" name="discount_value" type="number" step="0.01" required value="<?= old('discount_value', $promo['discount_value']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-semibold"
                        placeholder="Contoh: 10 atau 15000">
                </div>
            </div>

            <div>
                <label for="usage_limit" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Batas Penggunaan (Limit)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                    <input id="usage_limit" name="usage_limit" type="number" required value="<?= old('usage_limit', $promo['usage_limit']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-mono"
                        placeholder="Contoh: 100">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="valid_from" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Mulai Berlaku</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <input id="valid_from" name="valid_from" type="date" required value="<?= old('valid_from', $promo['valid_from']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
            </div>

            <div>
                <label for="valid_until" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Berakhir Berlaku</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    </div>
                    <input id="valid_until" name="valid_until" type="date" required value="<?= old('valid_until', $promo['valid_until']) ?>"
                        class="block w-full pl-10 pr-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 gap-2 transition-all text-sm">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Perbarui Promo
            </button>
            <a href="<?= base_url('admin/promo') ?>" class="w-full sm:w-auto flex justify-center items-center py-2.5 px-6 rounded-xl font-semibold text-slate-400 hover:text-slate-250 hover:bg-slate-850 gap-2 transition-all text-sm border border-slate-800/80">
                Batal
            </a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
