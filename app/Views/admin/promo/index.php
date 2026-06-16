<?= $this->extend('layout/admin') ?>

<?php
$title = 'Manajemen Promo & Voucher - SiTeBus';
$subtitle = 'Daftar Kode Promo & Diskon Tiket';
?>

<?= $this->section('admin_actions') ?>
<a href="<?= base_url('admin/promo/create') ?>" class="py-2.5 px-4 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 flex items-center gap-2 transition-all text-sm">
    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Promo
</a>
<?= $this->endSection() ?>

<?= $this->section('admin_content') ?>
<div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-900/20 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                    <th class="py-4 px-6">Kode Promo</th>
                    <th class="py-4 px-6 text-center">Tipe Diskon</th>
                    <th class="py-4 px-6 text-right">Nilai Diskon</th>
                    <th class="py-4 px-6 text-center">Berlaku Dari</th>
                    <th class="py-4 px-6 text-center">Berlaku Hingga</th>
                    <th class="py-4 px-6 text-center">Sisa Limit</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-sm">
                <?php if (empty($promos)): ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Belum ada data promo. Silakan tambahkan baru.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($promos as $promo): ?>
                        <tr class="hover:bg-slate-900/20 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-brand-400 tracking-wider">
                                <span class="bg-brand-500/10 border border-brand-500/20 px-2.5 py-1 rounded-lg">
                                    <?= esc($promo['code']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center text-slate-200">
                                <?php if ($promo['discount_type'] === 'percent'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-500/10 text-indigo-400">Persentase</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-500/10 text-emerald-400">Nominal Tetap</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-right font-semibold font-mono text-slate-200">
                                <?php 
                                    if ($promo['discount_type'] === 'percent') {
                                        echo number_format($promo['discount_value'], 0) . ' %';
                                    } else {
                                        echo 'Rp ' . number_format($promo['discount_value'], 0, ',', '.');
                                    }
                                ?>
                            </td>
                            <td class="py-4 px-6 text-center text-slate-350 font-mono">
                                <?= date('d M Y', strtotime($promo['valid_from'])) ?>
                            </td>
                            <td class="py-4 px-6 text-center text-slate-350 font-mono">
                                <?= date('d M Y', strtotime($promo['valid_until'])) ?>
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-semibold">
                                <?php if ($promo['usage_limit'] <= 5): ?>
                                    <span class="text-rose-450"><?= esc($promo['usage_limit']) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-300"><?= esc($promo['usage_limit']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= base_url('admin/promo/edit/' . $promo['id']) ?>" class="p-1.5 bg-slate-850 hover:bg-slate-800 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-800" title="Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDelete('<?= base_url('admin/promo/delete/' . $promo['id']) ?>', 'Apakah Anda yakin ingin menghapus promo ini? Kode diskon ini tidak akan bisa digunakan lagi oleh pelanggan.')" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white rounded-lg transition-colors border border-rose-500/20" title="Hapus">
                                         <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
