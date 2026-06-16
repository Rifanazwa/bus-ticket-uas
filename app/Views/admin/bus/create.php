<?= $this->extend('layout/admin') ?>

<?php
$title = 'Tambah Bus Baru - SiTeBus';
$subtitle = 'Definisikan Armada & Konfigurasi Kursi';
?>

<?= $this->section('admin_content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="seatBuilder()">
    <!-- Form Card -->
    <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-3xl shadow-xl lg:col-span-1 h-fit">
        <h3 class="text-md font-bold text-white font-outfit mb-4">Informasi Armada</h3>

        <!-- Errors Alert -->
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="mb-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 p-3 rounded-xl text-xs space-y-1">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p class="flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> <?= esc($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/bus/store') ?>" method="POST" @submit="serializeLayout()">
            <?= csrf_field() ?>

            <!-- Hidden input for serialized JSON layout -->
            <input type="hidden" name="seat_layout" :value="JSON.stringify(layout)">

            <div class="space-y-4">
                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">BUS ID</label>
                    <input id="code" name="code" type="text" required value="<?= old('code') ?>"
                        class="block w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm font-mono"
                        placeholder="EX-001">
                </div>

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Bus / PO</label>
                    <input id="name" name="name" type="text" required value="<?= old('name') ?>"
                        class="block w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                        placeholder="Nusantara Jaya (Executive)">
                </div>

                <div>
                    <label for="type" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kelas Bus</label>
                    <select id="type" name="type" required @change="changePreset($el.value)"
                        class="block w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="Eksekutif">Eksekutif (40 Kursi, 2-2)</option>
                        <option value="Bisnis">Bisnis (24 Kursi, 2-1)</option>
                        <option value="Ekonomi">Ekonomi (48 Kursi, 2-2)</option>
                    </select>
                </div>

                <div>
                    <label for="total_seats" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kapasitas Kursi Aktif</label>
                    <input id="total_seats" name="total_seats" type="number" readonly :value="totalActiveSeats"
                        class="block w-full px-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-400 focus:outline-none text-sm font-bold">
                    <p class="text-[10px] text-slate-500 mt-1">Dihitung otomatis dari jumlah kursi aktif di grid builder.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Petugas Lapangan Armada</label>
                    <div class="space-y-2 bg-slate-950 border border-slate-800 rounded-xl p-3 max-h-40 overflow-y-auto">
                        <?php if (empty($officers)): ?>
                            <p class="text-xs text-slate-500 italic">Belum ada petugas terdaftar</p>
                        <?php else: ?>
                            <?php foreach ($officers as $officer): ?>
                                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer hover:text-white transition-colors">
                                    <input type="checkbox" name="officers[]" value="<?= $officer['id'] ?>"
                                        class="rounded border-slate-800 text-brand-600 focus:ring-brand-500 bg-slate-900 w-4 h-4">
                                    <span><?= esc($officer['name']) ?> (<?= esc($officer['email']) ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Pilih petugas yang akan ditugaskan untuk scan tiket di armada bus ini.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/10 flex items-center justify-center gap-2 transition-all text-sm">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Simpan Bus
                    </button>
                    <a href="<?= base_url('admin/bus') ?>" class="w-full mt-2 py-3 px-4 rounded-xl font-semibold text-slate-400 hover:text-slate-250 hover:bg-slate-850 flex items-center justify-center gap-2 transition-all text-sm border border-slate-800/80">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Layout Builder Card -->
    <div class="bg-slate-900/60 border border-slate-800/80 p-6 rounded-3xl shadow-xl lg:col-span-2 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
            <div>
                <h3 class="text-md font-bold text-white font-outfit">Visual Seat Layout Builder</h3>
                <p class="text-xs text-slate-500">Sesuaikan baris, kolom, dan klik kursi untuk mengedit tipe (Kursi, Lorong, Kosong).</p>
            </div>
            
            <div class="flex gap-2">
                <button @click="addRow()" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-850 hover:bg-slate-800 text-slate-300 border border-slate-800 flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Baris
                </button>
                <button @click="removeRow()" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-850 hover:bg-slate-800 text-slate-300 border border-slate-800 flex items-center gap-1">
                    <i data-lucide="minus" class="w-3.5 h-3.5"></i> Baris
                </button>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 text-xs">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-brand-600 border border-brand-500 rounded-lg flex items-center justify-center text-white text-[9px] font-bold">1A</div>
                <span class="text-slate-450">Kursi Aktif</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-slate-950 border border-slate-850 rounded-lg flex items-center justify-center text-slate-500 text-[10px]"><i data-lucide="footprints" class="w-3 h-3"></i></div>
                <span class="text-slate-450">Lorong / Gang</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-slate-900 border border-slate-850/60 rounded-lg"></div>
                <span class="text-slate-450">Ruang Kosong (No Seat)</span>
            </div>
        </div>

        <!-- Bus Map Container -->
        <div class="bg-slate-950 rounded-2xl p-6 border border-slate-850 flex justify-center overflow-x-auto min-h-[300px]">
            <div class="flex flex-col items-center gap-2 bg-slate-900/10 border border-slate-800/40 p-4 rounded-xl max-w-sm w-full">
                <!-- Driver Section -->
                <div class="w-full flex justify-between items-center border-b border-slate-800 pb-3 mb-3 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                    <span>Depan (Pintu)</span>
                    <div class="flex items-center gap-1 bg-slate-950 border border-slate-850 py-1 px-2.5 rounded-lg text-[10px]">
                        <i data-lucide="circle-dot" class="w-3.5 h-3.5 text-rose-500"></i> Supir
                    </div>
                </div>

                <!-- Grid -->
                <div class="grid gap-2 w-full" :style="`grid-template-columns: repeat(${cols}, minmax(0, 1fr))`">
                    <template x-for="(cell, index) in layout" :key="index">
                        <div class="aspect-square flex items-center justify-center rounded-lg cursor-pointer transition-all duration-150 relative group"
                            :class="getCellClass(cell)"
                            @click="toggleCell(index)">
                            
                            <!-- Display content -->
                            <template x-if="cell.type === 'seat'">
                                <span class="text-xs font-bold font-outfit" x-text="cell.number"></span>
                            </template>
                            <template x-if="cell.type === 'aisle'">
                                <i data-lucide="footprints" class="w-3.5 h-3.5 opacity-30 text-slate-400"></i>
                            </template>
                            
                            <!-- Overlay tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1.5 hidden group-hover:block bg-slate-950 text-[9px] py-0.5 px-1.5 rounded border border-slate-800 pointer-events-none z-10 whitespace-nowrap">
                                Click to toggle type
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Back Section -->
                <div class="w-full border-t border-slate-800 pt-3 mt-3 text-center text-xs text-slate-550 font-semibold uppercase tracking-wider">
                    Belakang
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function seatBuilder() {
    return {
        rows: 10,
        cols: 5,
        layout: [],
        preset: 'Eksekutif',
        
        init() {
            this.changePreset('Eksekutif');
        },
        
        changePreset(type) {
            this.preset = type;
            this.layout = [];
            if (type === 'Bisnis') {
                this.rows = 8;
                this.cols = 4;
                for (let r = 1; r <= this.rows; r++) {
                    this.layout.push({row: r, col: 'A', number: r + 'A', type: 'seat'});
                    this.layout.push({row: r, col: 'B', number: r + 'B', type: 'seat'});
                    this.layout.push({row: r, col: 'aisle', number: null, type: 'aisle'});
                    this.layout.push({row: r, col: 'C', number: r + 'C', type: 'seat'});
                }
            } else if (type === 'Eksekutif') {
                this.rows = 10;
                this.cols = 5;
                for (let r = 1; r <= this.rows; r++) {
                    this.layout.push({row: r, col: 'A', number: r + 'A', type: 'seat'});
                    this.layout.push({row: r, col: 'B', number: r + 'B', type: 'seat'});
                    this.layout.push({row: r, col: 'aisle', number: null, type: 'aisle'});
                    this.layout.push({row: r, col: 'C', number: r + 'C', type: 'seat'});
                    this.layout.push({row: r, col: 'D', number: r + 'D', type: 'seat'});
                }
            } else if (type === 'Ekonomi') {
                this.rows = 12;
                this.cols = 5;
                for (let r = 1; r <= this.rows; r++) {
                    this.layout.push({row: r, col: 'A', number: r + 'A', type: 'seat'});
                    this.layout.push({row: r, col: 'B', number: r + 'B', type: 'seat'});
                    this.layout.push({row: r, col: 'aisle', number: null, type: 'aisle'});
                    this.layout.push({row: r, col: 'C', number: r + 'C', type: 'seat'});
                    this.layout.push({row: r, col: 'D', number: r + 'D', type: 'seat'});
                }
            }
            this.$nextTick(() => lucide.createIcons());
        },

        get totalActiveSeats() {
            return this.layout.filter(c => c.type === 'seat').length;
        },

        getCellClass(cell) {
            if (cell.type === 'seat') {
                return 'bg-brand-600 border border-brand-500 hover:bg-brand-500 hover:border-brand-400 text-white';
            }
            if (cell.type === 'aisle') {
                return 'bg-slate-950 hover:bg-slate-900 border border-slate-900 border-dashed text-slate-600';
            }
            return 'bg-slate-900 border border-slate-850/60 hover:bg-slate-850';
        },

        toggleCell(index) {
            let cell = this.layout[index];
            if (cell.type === 'seat') {
                cell.type = 'aisle';
                cell.number = null;
            } else if (cell.type === 'aisle') {
                cell.type = 'empty';
                cell.number = null;
            } else {
                cell.type = 'seat';
                // Find column label based on active columns index
                let r = cell.row;
                // Count active seat types in this row to label appropriately
                let rowSeats = this.layout.filter(c => c.row === r);
                let seatIndexInRow = rowSeats.indexOf(cell);
                let colLabel = String.fromCharCode(65 + seatIndexInRow); // A, B, C, D...
                cell.col = colLabel;
                cell.number = r + colLabel;
            }
            // Re-render labels for all seats in the row to maintain order (e.g. 1A, 1B, 1C)
            this.recalculateSeatLabels();
        },

        recalculateSeatLabels() {
            for (let r = 1; r <= this.rows; r++) {
                let rowCells = this.layout.filter(c => c.row === r);
                let seatCount = 0;
                rowCells.forEach(cell => {
                    if (cell.type === 'seat') {
                        let label = String.fromCharCode(65 + seatCount);
                        cell.col = label;
                        cell.number = r + label;
                        seatCount++;
                    } else {
                        cell.number = null;
                    }
                });
            }
            this.$nextTick(() => lucide.createIcons());
        },

        addRow() {
            this.rows++;
            let r = this.rows;
            let colNames = ['A', 'B', 'aisle', 'C', 'D'];
            if (this.preset === 'Bisnis') {
                colNames = ['A', 'B', 'aisle', 'C'];
            }
            colNames.forEach(col => {
                if (col === 'aisle') {
                    this.layout.push({row: r, col: 'aisle', number: null, type: 'aisle'});
                } else {
                    this.layout.push({row: r, col: col, number: r + col, type: 'seat'});
                }
            });
            this.recalculateSeatLabels();
        },

        removeRow() {
            if (this.rows > 1) {
                // remove last row cells
                this.layout = this.layout.filter(c => c.row < this.rows);
                this.rows--;
            }
        },

        serializeLayout() {
            // hidden input will be updated automatically via Alpine reactive value
        }
    };
}
</script>
<?= $this->endSection() ?>
