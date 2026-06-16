<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Tiket — <?= esc($booking['booking_code']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .ticket-card {
            width: 380px;
            margin: 5px auto;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            overflow: hidden;
        }
        .header-bg {
            background: #1e1b4b;
            color: #ffffff;
            padding: 16px 20px;
            text-align: center;
        }
        .header-title {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #ffffff;
            margin: 0;
        }
        .header-subtitle {
            font-size: 8px;
            color: #a5b4fc;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .bus-type-badge {
            display: inline-block;
            background: rgba(99, 102, 241, 0.25);
            border: 1px solid rgba(165, 180, 252, 0.4);
            color: #c7d2fe;
            font-size: 8.5px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 12px;
            margin-top: 6px;
            text-transform: uppercase;
        }
        .route-section {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 20px;
        }
        .city-code {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
        }
        .city-name {
            font-size: 8.5px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
        }
        .time-label {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .date-label {
            font-size: 8px;
            color: #64748b;
        }
        .duration-container {
            text-align: center;
            vertical-align: middle;
            padding: 0 5px;
        }
        .duration-line {
            height: 1.5px;
            background: #6366f1;
            margin: 4px auto;
            width: 70%;
        }
        .duration-badge {
            display: inline-block;
            background-color: #e0e7ff;
            color: #4f46e5;
            font-size: 8px;
            font-weight: bold;
            padding: 1.5px 5px;
            border-radius: 6px;
            text-transform: uppercase;
        }
        .info-section {
            padding: 12px 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-cell {
            width: 50%;
            padding: 5px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .info-label {
            font-size: 7.5px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .info-value {
            font-size: 10px;
            font-weight: bold;
            color: #1e293b;
        }
        .info-code {
            font-family: monospace;
            font-size: 10.5px;
            font-weight: bold;
            color: #4338ca;
        }
        .manifest-section {
            padding: 0 20px 12px 20px;
        }
        .manifest-title {
            font-size: 8.5px;
            font-weight: bold;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .manifest-table {
            width: 100%;
            border-collapse: collapse;
        }
        .manifest-cell-name {
            font-size: 9.5px;
            font-weight: bold;
            color: #334155;
            padding: 5px 4px;
        }
        .manifest-cell-seat {
            width: 55px;
            text-align: right;
            padding: 5px 4px;
        }
        .seat-badge {
            background-color: #4338ca;
            color: #ffffff;
            font-family: monospace;
            font-size: 9px;
            font-weight: bold;
            padding: 1.5px 5.5px;
            border-radius: 4px;
            display: inline-block;
        }
        .dashed-line {
            border-top: 2px dashed #cbd5e1;
            margin: 2px 20px;
        }
        .qr-section {
            background-color: #fafbfc;
            text-align: center;
            padding: 14px 20px;
        }
        .lunas-stamp {
            display: inline-block;
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 3.5px 12px;
            border-radius: 6px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .qr-img {
            width: 110px;
            height: 110px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 3px;
            background: #ffffff;
            margin: 0 auto;
        }
        .qr-text {
            font-family: monospace;
            font-size: 9.5px;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        .footer-bg {
            background-color: #1e1b4b;
            color: rgba(255, 255, 255, 0.5);
            font-size: 7.5px;
            text-align: center;
            padding: 8px 20px;
            line-height: 1.4;
        }
        .footer-bg strong {
            color: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>
<body>

<div class="ticket-card">
    <!-- HEADER -->
    <div class="header-bg">
        <h1 class="header-title">E-Boarding Pass</h1>
        <div class="header-subtitle">SiTeBus — Sistem Informasi Ticketing Bus</div>
        <div class="bus-type-badge"><?= esc($booking['bus_type']) ?> CLASS</div>
    </div>

    <!-- ROUTE -->
    <div class="route-section">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 35%; text-align: left; vertical-align: top;">
                    <div class="city-code"><?= esc(strtoupper(substr($booking['origin'], 0, 3))) ?></div>
                    <div class="city-name"><?= esc($booking['origin']) ?></div>
                    <div class="time-label"><?= date('H:i', strtotime($booking['departure_time'])) ?></div>
                    <div class="date-label"><?= date('d M Y', strtotime($booking['departure_time'])) ?></div>
                </td>
                <td class="duration-container" style="width: 30%; vertical-align: middle;">
                    <div class="duration-badge"><?= esc($booking['estimated_duration'] ?? 'Langsung') ?></div>
                    <div class="duration-line"></div>
                </td>
                <td style="width: 35%; text-align: right; vertical-align: top;">
                    <div class="city-code"><?= esc(strtoupper(substr($booking['destination'], 0, 3))) ?></div>
                    <div class="city-name"><?= esc($booking['destination']) ?></div>
                    <div class="time-label"><?= date('H:i', strtotime($booking['arrival_time'])) ?></div>
                    <div class="date-label"><?= date('d M Y', strtotime($booking['arrival_time'])) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- DETAILS -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-cell" style="padding-right: 10px;">
                    <div class="info-label">Kode Booking</div>
                    <div class="info-code"><?= esc($booking['booking_code']) ?></div>
                </td>
                <td class="info-cell" style="padding-left: 10px;">
                    <div class="info-label">Armada Bus</div>
                    <div class="info-value"><?= esc($booking['bus_name']) ?></div>
                </td>
            </tr>
            <tr>
                <td class="info-cell" style="padding-right: 10px;">
                    <div class="info-label">Nama Pemesan</div>
                    <div class="info-value"><?= esc($booking['customer_name']) ?></div>
                </td>
                <td class="info-cell" style="padding-left: 10px;">
                    <div class="info-label">No. Telepon</div>
                    <div class="info-value"><?= esc($booking['customer_phone'] ?? '-') ?></div>
                </td>
            </tr>
            <tr>
                <td class="info-cell" style="padding-right: 10px;">
                    <div class="info-label">Email Pemesan</div>
                    <div class="info-value" style="font-size: 9px; word-break: break-all;"><?= esc($booking['customer_email'] ?? '-') ?></div>
                </td>
                <td class="info-cell" style="padding-left: 10px;">
                    <div class="info-label">Metode Pembayaran</div>
                    <div class="info-value text-capitalize"><?= esc($payment['method'] ?? 'Online Payment') ?></div>
                </td>
            </tr>
            <tr>
                <td class="info-cell" style="padding-right: 10px;">
                    <div class="info-label">ID Transaksi</div>
                    <div class="info-value font-mono" style="font-size: 8.5px;"><?= esc($payment['transaction_id'] ?? 'TRX-'.$booking['booking_code']) ?></div>
                </td>
                <td class="info-cell" style="padding-left: 10px;">
                    <div class="info-label">Total Pembayaran</div>
                    <div class="info-value" style="color: #059669; font-size: 11px;">Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- MANIFEST -->
    <div class="manifest-section">
        <div class="manifest-title">Manifes Penumpang</div>
        <table class="manifest-table">
            <?php foreach ($seats as $seat): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td class="manifest-cell-name">
                        <span style="color: #6366f1; margin-right: 4px;">•</span> <?= esc($seat['passenger_name']) ?>
                    </td>
                    <td class="manifest-cell-seat">
                        <span class="seat-badge"><?= esc($seat['seat_number']) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- CUT LINE -->
    <div class="dashed-line"></div>

    <!-- QR CODE -->
    <div class="qr-section">
        <div>
            <span class="lunas-stamp">LUNAS / PAID</span>
        </div>
        <div style="font-size: 8px; color: #6366f1; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 6px;">QR Boarding Pass</div>
        <img class="qr-img" src="<?= $base64Qr ?>" alt="QR Code Boarding Pass">
        <div class="qr-text"><?= esc($ticket['qr_code']) ?></div>
    </div>

    <!-- FOOTER -->
    <div class="footer-bg">
        <strong>PENTING:</strong> Tunjukkan QR Code ini kepada petugas terminal untuk verifikasi boarding. &nbsp;•&nbsp;
        Hadir minimal <strong>30 menit</strong> sebelum keberangkatan. &nbsp;•&nbsp;
        Tiket digital ini diterbitkan secara sah dan dilindungi enkripsi SiTeBus.
    </div>
</div>

</body>
</html>
