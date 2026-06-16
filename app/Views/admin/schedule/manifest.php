<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .print-border {
                border-color: #000000 !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8 text-slate-800">

    <!-- Action Buttons (Hidden when printing) -->
    <div class="max-w-4xl mx-auto mb-6 flex justify-between items-center no-print">
        <a href="<?= base_url('admin/schedule') ?>" class="py-2 px-4 rounded-xl font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 shadow-sm flex items-center gap-1.5 transition-all text-xs">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Jadwal
        </a>
        <button onclick="window.print()" class="py-2.5 px-5 rounded-xl font-semibold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md flex items-center gap-1.5 transition-all text-xs">
            <i data-lucide="printer" class="w-4 h-4"></i> Cetak Surat Jalan
        </button>
    </div>

    <!-- Main Manifest Card -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm print:shadow-none print:border-none">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-200 pb-6 mb-6 print:border-black">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2">
                    <span class="bg-indigo-650 text-white px-3 py-1 rounded-xl text-lg font-black tracking-widest no-print">JOSS</span>
                    <span>PO JOSS BUS</span>
                </h1>
                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider mt-1">Intercity Interprovince Transportation Service</p>
            </div>
            <div class="mt-4 sm:mt-0 text-left sm:text-right">
                <span class="inline-block bg-slate-100 text-slate-800 px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase mb-1 no-print">Dokumen Resmi</span>
                <h2 class="text-md font-bold text-slate-900 uppercase">SURAT JALAN & MANIFES</h2>
                <p class="text-xs text-slate-500 font-mono mt-0.5">No: SJ/JB/<?= date('Ymd', strtotime($schedule['departure_time'])) ?>/<?= esc($schedule['id']) ?></p>
            </div>
        </div>

        <!-- Trip & Crew Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-slate-200 pb-6 mb-6 print:border-black">
            <!-- Left: Journey Info -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 print:bg-white print:border-black">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i> Detail Perjalanan
                </h3>
                <table class="w-full text-xs">
                    <tr class="h-7">
                        <td class="text-slate-500 w-1/3">Rute</td>
                        <td class="font-bold text-slate-950">: <?= esc($schedule['origin']) ?> &rarr; <?= esc($schedule['destination']) ?></td>
                    </tr>
                    <tr class="h-7">
                        <td class="text-slate-500">Armada / Tipe</td>
                        <td class="font-bold text-slate-950">: <?= esc($schedule['bus_name']) ?> (<?= esc($schedule['bus_type']) ?>)</td>
                    </tr>
                    <tr class="h-7">
                        <td class="text-slate-500">Kapasitas Kursi</td>
                        <td class="font-bold text-slate-950 font-mono">: <?= esc($schedule['total_seats']) ?> Kursi</td>
                    </tr>
                    <tr class="h-7">
                        <td class="text-slate-500">Berangkat</td>
                        <td class="font-bold text-slate-950 font-mono">: <?= date('d M Y - H:i', strtotime($schedule['departure_time'])) ?> WIB</td>
                    </tr>
                    <tr class="h-7">
                        <td class="text-slate-500">Tiba (Estimasi)</td>
                        <td class="font-bold text-slate-950 font-mono">: <?= date('d M Y - H:i', strtotime($schedule['arrival_time'])) ?> WIB</td>
                    </tr>
                </table>
            </div>

            <!-- Right: Crew Info -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 print:bg-white print:border-black">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i> Kru yang Bertugas
                </h3>
                <table class="w-full text-xs">
                    <tr class="h-7">
                        <td class="text-slate-500 w-1/3">Sopir Utama</td>
                        <td class="font-bold text-slate-950">: <?= esc($schedule['driver_1'] ?? 'Belum Ditugaskan') ?></td>
                    </tr>
                    <tr class="h-7">
                        <td class="text-slate-500">Sopir Cadangan</td>
                        <td class="font-bold text-slate-950">: <?= esc($schedule['driver_2'] ?? 'Belum Ditugaskan') ?></td>
                    </tr>
                    <tr class="h-7">
                        <td class="text-slate-500">Kondektur</td>
                        <td class="font-bold text-slate-950">: <?= esc($schedule['conductor'] ?? 'Belum Ditugaskan') ?></td>
                    </tr>
                    <tr class="h-7">
                        <td class="text-slate-500">Status Tugas</td>
                        <td class="font-bold text-slate-950">
                            : <span class="capitalize"><?= esc($schedule['status']) ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Passenger Manifest Table -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-1.5">
                <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-500"></i> Daftar Manifes Penumpang
            </h3>
            
            <div class="overflow-x-auto border border-slate-200 rounded-2xl print:border-black print:rounded-none">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold print:bg-white print:border-black">
                            <th class="py-3 px-4 text-center w-16">No. Kursi</th>
                            <th class="py-3 px-4">Kode Booking</th>
                            <th class="py-3 px-4">Nama Penumpang</th>
                            <th class="py-3 px-4">Nomor HP</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 print:divide-black">
                        <?php if (empty($passengers)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">
                                    Belum ada manifest penumpang untuk perjalanan ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($passengers as $p): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 px-4 text-center font-bold text-indigo-600 font-mono print:text-black">
                                        <?= esc($p['seat_number']) ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-slate-600 print:text-black">
                                        <?= esc($p['booking_code']) ?>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        <?= esc($p['passenger_name']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 font-mono">
                                        <?= esc($p['passenger_phone']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($p['boarding_status'] === 'boarded'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-150 print:bg-white print:text-black print:border-none">
                                                BOARDED
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-150 print:bg-white print:text-black print:border-none">
                                                BELUM NAIK
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Manifest Summary -->
            <div class="mt-4 flex justify-between items-center text-xs font-semibold text-slate-500 font-mono px-2">
                <span>Total Pemesan: <?= count($passengers) ?> Penumpang</span>
                <span>Boarded: <?= count(array_filter($passengers, function($p) { return $p['boarding_status'] === 'boarded'; })) ?> / Belum Naik: <?= count(array_filter($passengers, function($p) { return $p['boarding_status'] !== 'boarded'; })) ?></span>
            </div>
        </div>

        <!-- Signatures (Tanda Tangan) -->
        <div class="grid grid-cols-3 gap-6 text-center mt-12 pt-8 border-t border-slate-200 print:border-black text-xs">
            <div>
                <p class="text-slate-500 font-semibold uppercase tracking-wider mb-16">Pengawas Terminal</p>
                <p class="font-bold text-slate-900 underline">( ____________________ )</p>
                <p class="text-[10px] text-slate-400 mt-1 font-semibold">Tanda Tangan & Cap</p>
            </div>
            <div>
                <p class="text-slate-500 font-semibold uppercase tracking-wider mb-16">Sopir Utama (Kru 1)</p>
                <p class="font-bold text-slate-900 underline"><?= esc($schedule['driver_1'] ?? '( ____________________ )') ?></p>
                <p class="text-[10px] text-slate-400 mt-1 font-semibold">Tanda Tangan Kru</p>
            </div>
            <div>
                <p class="text-slate-500 font-semibold uppercase tracking-wider mb-16">Administrator PO</p>
                <p class="font-bold text-slate-900 underline">( ____________________ )</p>
                <p class="text-[10px] text-slate-400 mt-1 font-semibold">SiTeBus Administrator</p>
            </div>
        </div>

    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
