<?= $this->extend('layout/admin') ?>

<?php
$title = 'Manajemen Jadwal - SiTeBus';
$subtitle = 'Daftar Jadwal Keberangkatan Bus';
?>

<?= $this->section('admin_actions') ?>
<a href="<?= base_url('admin/schedule/create') ?>" class="py-2.5 px-4 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 flex items-center gap-2 transition-all text-sm">
    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Jadwal
</a>
<?= $this->endSection() ?>

<?= $this->section('admin_content') ?>
<div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-900/20 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                    <th class="py-4 px-6">Rute Perjalanan</th>
                    <th class="py-4 px-6">Bus / Armada</th>
                    <th class="py-4 px-6 text-center">Waktu Keberangkatan</th>
                    <th class="py-4 px-6 text-center">Waktu Kedatangan</th>
                    <th class="py-4 px-6 text-right">Harga Tiket</th>
                    <th class="py-4 px-6 text-center">Status</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-sm">
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Belum ada jadwal keberangkatan. Silakan tambahkan baru.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $sched): ?>
                        <tr class="hover:bg-slate-900/20 transition-colors">
                            <td class="py-4 px-6 text-slate-200 font-semibold">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-350"><?= esc($sched['origin']) ?></span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-500"></i>
                                    <span class="text-slate-200"><?= esc($sched['destination']) ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="text-slate-300 font-semibold"><?= esc($sched['bus_name']) ?></span>
                                    <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold"><?= esc($sched['bus_type']) ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center text-slate-350 font-mono">
                                <?= date('d M Y, H:i', strtotime($sched['departure_time'])) ?>
                            </td>
                            <td class="py-4 px-6 text-center text-slate-350 font-mono">
                                <?= date('d M Y, H:i', strtotime($sched['arrival_time'])) ?>
                            </td>
                            <td class="py-4 px-6 text-right text-emerald-400 font-bold font-mono">
                                Rp <?= number_format($sched['price'], 0, ',', '.') ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <?php if ($sched['status'] === 'scheduled'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20">Scheduled</span>
                                <?php elseif ($sched['status'] === 'ongoing'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Ongoing</span>
                                <?php elseif ($sched['status'] === 'completed'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Completed</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= base_url('admin/schedule/edit/' . $sched['id']) ?>" class="p-1.5 bg-slate-850 hover:bg-slate-800 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-800" title="Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <a href="<?= base_url('admin/schedule/delete/' . $sched['id']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white rounded-lg transition-colors border border-rose-500/20" title="Hapus">
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
