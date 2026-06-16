<?= $this->extend('layout/admin') ?>

<?php
$title = 'Manajemen Bus - SiTeBus';
$subtitle = 'Daftar Armada Bus PO';
?>

<?= $this->section('admin_actions') ?>
<div class="flex flex-wrap items-center gap-2">
    <!-- Template Button -->
    <a href="<?= base_url('admin/bus/template') ?>" class="py-2 px-3 rounded-xl font-semibold text-slate-350 bg-slate-850 hover:bg-slate-800 hover:text-white border border-slate-800 shadow flex items-center gap-1.5 transition-all text-xs">
        <i data-lucide="download" class="w-3.5 h-3.5"></i> Template CSV
    </a>
    
    <!-- Export Button -->
    <a href="<?= base_url('admin/bus/export') ?>" class="py-2 px-3 rounded-xl font-semibold text-slate-350 bg-slate-850 hover:bg-slate-800 hover:text-white border border-slate-800 shadow flex items-center gap-1.5 transition-all text-xs">
        <i data-lucide="file-output" class="w-3.5 h-3.5"></i> Export CSV
    </a>

    <!-- Import Trigger Button -->
    <button onclick="openImportModal()" class="py-2 px-3 rounded-xl font-semibold text-slate-355 bg-slate-850 hover:bg-slate-800 hover:text-white border border-slate-800 shadow flex items-center gap-1.5 transition-all text-xs">
        <i data-lucide="file-input" class="w-3.5 h-3.5"></i> Import CSV
    </button>

    <!-- Create Button -->
    <a href="<?= base_url('admin/bus/create') ?>" class="py-2 px-3.5 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 flex items-center gap-1.5 transition-all text-xs">
        <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Bus
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('admin_content') ?>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-2xl text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
            <div><?= esc(session()->getFlashdata('success')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-5 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-2xl text-xs flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <div><?= esc(session()->getFlashdata('error')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-5 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-4 rounded-2xl text-xs space-y-2">
            <div class="flex items-center gap-2 font-bold">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                <span>Gagal Mengimport Beberapa Baris:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-1 max-h-36 overflow-y-auto font-mono text-[11px]">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

<div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
    <!-- Table Header / Search Bar -->
    <div class="p-5 border-b border-slate-800/80 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-white">Daftar Unit Armada</h3>
            <p class="text-xs text-slate-500 mt-0.5">Kelola unit operasional PO Bus.</p>
        </div>
        
        <form action="<?= base_url('admin/bus') ?>" method="GET" class="w-full md:w-80 flex gap-2">
            <div class="relative w-full">
                <input type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Cari unit PO atau BUS ID..."
                    class="block w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-655 focus:outline-none focus:ring-2 focus:ring-brand-500 text-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-600">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
            </div>
            <button type="submit" class="py-2 px-4 rounded-xl font-semibold text-white bg-slate-800 hover:bg-slate-750 text-xs transition-colors border border-slate-700/80">
                Cari
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?= base_url('admin/bus') ?>" class="py-2 px-3 rounded-xl font-semibold text-slate-400 hover:text-slate-250 bg-slate-950 border border-slate-800 text-xs transition-colors flex items-center justify-center">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-800 bg-slate-900/20 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                    <th class="py-4 px-6">BUS ID</th>
                    <th class="py-4 px-6">Nama PO / Armada</th>
                    <th class="py-4 px-6">Kelas Bus</th>
                    <th class="py-4 px-6">Petugas Lapangan</th>
                    <th class="py-4 px-6 text-center">Kapasitas Kursi</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-sm">
                <?php if (empty($buses)): ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                             Belum ada data bus. Silakan tambahkan baru.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($buses as $bus): ?>
                        <tr class="hover:bg-slate-900/20 transition-colors">
                            <td class="py-4 px-6 font-mono font-semibold text-brand-400"><?= esc($bus['code']) ?></td>
                            <td class="py-4 px-6 text-slate-200 font-semibold"><?= esc($bus['name']) ?></td>
                            <td class="py-4 px-6">
                                <?php if ($bus['type'] === 'Bisnis'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Bisnis</span>
                                <?php elseif ($bus['type'] === 'Eksekutif'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20">Eksekutif</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Ekonomi</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <?php if (empty($bus['officers'])): ?>
                                    <span class="text-xs text-slate-550 italic">Belum ditugaskan</span>
                                <?php else: ?>
                                    <div class="flex flex-col gap-1">
                                        <?php foreach ($bus['officers'] as $officer): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[11px] font-semibold w-fit">
                                                <i data-lucide="user" class="w-3 h-3"></i> <?= esc($officer['name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-center text-slate-350"><?= esc($bus['total_seats']) ?> Kursi</td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= base_url('admin/bus/edit/' . $bus['id']) ?>" class="p-1.5 bg-slate-850 hover:bg-slate-800 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-800" title="Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDelete('<?= base_url('admin/bus/delete/' . $bus['id']) ?>', 'Apakah Anda yakin ingin menghapus bus ini? Menghapus armada akan secara otomatis menghapus semua jadwal keberangkatan, manifes, dan data transaksi terkait bus ini.')" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white rounded-lg transition-colors border border-rose-500/20" title="Hapus">
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
    <!-- Pagination -->
    <?php if (isset($pager)): ?>
        <?= $pager->links('buses', 'tailwind_pagination') ?>
    <?php endif; ?>
</div>

<!-- Import CSV Modal -->
<div id="import-modal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeImportModal()"></div>
    
    <!-- Modal Content -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-3xl w-full max-w-md relative z-10 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4 mb-4">
            <h3 class="text-md font-bold text-white flex items-center gap-2">
                <i data-lucide="file-input" class="w-5 h-5 text-brand-400"></i> Import Bulk Armada
            </h3>
            <button onclick="closeImportModal()" class="text-slate-500 hover:text-slate-350 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form action="<?= base_url('admin/bus/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <p class="text-xs text-slate-400 leading-relaxed">
                    Unggah file CSV (.csv) untuk menambah banyak armada bus sekaligus. Pastikan struktur kolom sesuai dengan template (code, name, type).
                </p>
                
                <div class="border-2 border-dashed border-slate-800 hover:border-brand-500/50 rounded-2xl p-6 text-center cursor-pointer transition-colors relative" id="drop-zone">
                    <input type="file" name="csv_file" id="csv_file" required accept=".csv" class="absolute inset-0 opacity-0 cursor-pointer">
                    <div class="space-y-2">
                        <div class="h-10 w-10 rounded-xl bg-slate-950 border border-slate-850 flex items-center justify-center mx-auto text-slate-400">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                        </div>
                        <p class="text-xs font-semibold text-slate-300" id="file-name-label">Pilih file CSV atau drag ke sini</p>
                        <p class="text-[10px] text-slate-500">Maksimal 2MB (Hanya CSV)</p>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeImportModal()" class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-slate-400 hover:text-slate-250 hover:bg-slate-850 border border-slate-800 text-xs transition-all text-center">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 text-xs transition-all text-center flex items-center justify-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i> Mulai Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openImportModal() {
        const modal = document.getElementById('import-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        lucide.createIcons();
    }
    
    function closeImportModal() {
        const modal = document.getElementById('import-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('csv_file');
        const fileNameLabel = document.getElementById('file-name-label');
        const dropZone = document.getElementById('drop-zone');

        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                if (fileInput.files.length > 0) {
                    fileNameLabel.textContent = fileInput.files[0].name;
                    fileNameLabel.classList.remove('text-slate-300');
                    fileNameLabel.classList.add('text-brand-400', 'font-bold');
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
