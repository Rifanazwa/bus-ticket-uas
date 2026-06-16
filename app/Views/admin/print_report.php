<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perjalanan - <?= esc($schedule['origin']) ?> ke <?= esc($schedule['destination']) ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            background-color: #fff;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px double #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 11px;
        }
        .info-grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-card {
            border: 1px solid #000;
            padding: 10px;
        }
        .info-card h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            text-transform: uppercase;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .signatures {
            display: grid;
            grid-template-cols: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
            text-align: center;
        }
        .sig-box {
            height: 70px;
        }
        @media print {
            body {
                margin: 10px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Header -->
    <div class="header">
        <h1>Laporan Jalan &amp; Manifes Penumpang</h1>
        <p>Sistem Manajemen Tiket Bus — SiTeBus AI</p>
        <p>Tanggal Cetak: <?= date('d M Y H:i') ?> WIB</p>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <!-- Rencana Perjalanan -->
        <div class="info-card">
            <h3>Detail Perjalanan</h3>
            <div class="info-row">
                <span class="info-label">Rute:</span>
                <span><?= esc($schedule['origin']) ?> &rarr; <?= esc($schedule['destination']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Jarak:</span>
                <span><?= esc($schedule['distance_km']) ?> km</span>
            </div>
            <div class="info-row">
                <span class="info-label">Keberangkatan:</span>
                <span><?= date('d M Y, H:i', strtotime($schedule['departure_time'])) ?> WIB</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estimasi Tiba:</span>
                <span><?= date('d M Y, H:i', strtotime($schedule['arrival_time'])) ?> WIB</span>
            </div>
        </div>

        <!-- Armada & Kru -->
        <div class="info-card">
            <h3>Armada &amp; Kru</h3>
            <div class="info-row">
                <span class="info-label">Nama Bus:</span>
                <span><?= esc($schedule['bus_name']) ?> (<?= esc(ucfirst($schedule['bus_type'])) ?>)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kapasitas:</span>
                <span><?= esc($schedule['total_seats']) ?> Kursi</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sopir Utama:</span>
                <span><?= esc($schedule['driver_1_name'] ?: 'Belum Ditugaskan') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Sopir Cadangan:</span>
                <span><?= esc($schedule['driver_2_name'] ?: '-') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Konduktor:</span>
                <span><?= esc($schedule['conductor_name'] ?: 'Belum Ditugaskan') ?></span>
            </div>
        </div>
    </div>

    <!-- Passenger Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 12%;" class="text-center">Kursi</th>
                <th style="width: 35%;">Nama Penumpang</th>
                <th style="width: 18%;" class="text-center">Kode Booking</th>
                <th style="width: 18%;" class="text-center">Kode Tiket</th>
                <th style="width: 12%;" class="text-center">Boarding</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($manifest)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Belum ada penumpang terdaftar untuk perjalanan ini.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($manifest as $index => $m): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td class="text-center" style="font-weight: bold; font-family: monospace;"><?= esc($m['seat_number']) ?></td>
                        <td style="font-weight: bold;"><?= esc($m['passenger_name']) ?></td>
                        <td class="text-center" style="font-family: monospace;"><?= esc($m['booking_code']) ?></td>
                        <td class="text-center" style="font-family: monospace;"><?= esc($m['qr_code']) ?></td>
                        <td class="text-center" style="font-weight: bold;">
                            <?= $m['boarding_status'] === 'boarded' ? 'SUDAH' : 'BELUM' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signatures section -->
    <div class="signatures">
        <div>
            <p>Sopir Utama</p>
            <div class="sig-box"></div>
            <p>( __________________ )</p>
        </div>
        <div>
            <p>Konduktor</p>
            <div class="sig-box"></div>
            <p>( __________________ )</p>
        </div>
        <div>
            <p>Petugas Terminal</p>
            <div class="sig-box"></div>
            <p>( __________________ )</p>
        </div>
    </div>

</body>
</html>
