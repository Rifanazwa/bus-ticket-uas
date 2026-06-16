# Digitalisasi Tiket Bus berbasis AI — Project Akhir CodeIgniter 4

## 1. Deskripsi Singkat
Aplikasi web untuk memesan tiket bus secara online, dilengkapi fitur AI untuk rekomendasi rute, prediksi okupansi, chatbot customer service, dan deteksi penipuan/anomali pemesanan. Dibangun dengan CodeIgniter 4 sebagai backend utama.

---

## 2. Tech Stack

### Backend
- **CodeIgniter 4** (PHP 8.1+)
- **MySQL 8.x** sebagai database utama
- **Composer** untuk dependency management

### Frontend
- **Blade-like CI4 View** + **Tailwind CSS** (atau Bootstrap 5)
- **Alpine.js / Vanilla JS** untuk interaktivitas (kalender, pemilihan kursi)
- **Chart.js** untuk dashboard admin (grafik penjualan, okupansi)

### AI / Integrasi Eksternal
- **Gemini API / OpenAI API** (via LiteLLM atau langsung) untuk:
  - Chatbot CS otomatis
  - Rekomendasi rute & jadwal
  - Analisis sentimen ulasan penumpang
- **Python microservice (opsional, Flask/FastAPI)** untuk model prediksi okupansi & deteksi anomali (jika butuh ML model sendiri, bukan cuma LLM)

### Payment & Notifikasi
- **Midtrans / Xendit** untuk payment gateway
- **WhatsApp Gateway (Fonnte/Wablas/Baileys)** atau **Email (SMTP)** untuk notifikasi e-ticket

### Tools Pendukung
- **DomPDF / TCPDF** — generate e-ticket PDF
- **QR Code Generator (endroid/qr-code)** — QR tiket untuk scan boarding
- **Git + GitHub** untuk version control

---

## 3. Daftar Fitur

### A. Fitur User (Penumpang)
1. Registrasi & login (email/HP, optional Google OAuth)
2. Pencarian tiket (kota asal, tujuan, tanggal, jumlah penumpang)
3. **AI Rekomendasi**: saran rute alternatif/jadwal terbaik berdasarkan histori & harga
4. Pemilihan kursi interaktif (seat map)
5. Pemesanan & pembayaran online (payment gateway)
6. E-ticket otomatis (PDF + QR Code) dikirim via email/WA
7. Riwayat pemesanan & status tiket
8. Pembatalan/refund tiket (dengan kebijakan tertentu)
9. **AI Chatbot** untuk FAQ, cek status pesanan, bantuan pemesanan
10. Ulasan & rating perjalanan setelah selesai

### B. Fitur Admin/Operator
1. Dashboard statistik (total penjualan, okupansi per rute, pendapatan)
2. CRUD data bus, armada, sopir, rute, jadwal, harga
3. Manajemen kursi per armada (layout kursi custom)
4. Manajemen pemesanan (konfirmasi, refund, cetak manifest)
5. **AI Prediksi Okupansi**: prediksi tingkat keterisian bus untuk jadwal mendatang berdasarkan data historis
6. **AI Deteksi Anomali**: deteksi pola pemesanan mencurigakan (misal banyak akun beli kursi sama, indikasi reseller/bot)
7. Laporan keuangan (harian, bulanan, per rute)
8. Manajemen promo/diskon/voucher
9. **Analisis sentimen ulasan** otomatis (positif/negatif/netral) dari AI

### C. Fitur Petugas Lapangan (opsional)
1. Scan QR tiket untuk validasi boarding (via kamera HP/web)
2. Update status tiket → "boarded"

---

## 4. Struktur Modul CI4 (Controller)

```
app/Controllers/
├── Auth/                  # Login, Register, Logout
├── Customer/
│   ├── Home.php
│   ├── Search.php
│   ├── Booking.php
│   ├── Payment.php
│   ├── Ticket.php
│   ├── Chatbot.php
├── Admin/
│   ├── Dashboard.php
│   ├── Bus.php
│   ├── Route.php
│   ├── Schedule.php
│   ├── Booking.php
│   ├── Report.php
│   ├── Promo.php
├── Api/
│   ├── AiRecommendation.php
│   ├── AiPrediction.php
│   ├── AiAnomaly.php
│   └── AiSentiment.php
└── Petugas/
    └── Scan.php
```

---

## 5. Struktur Database (MySQL)

### Tabel Utama

**users**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| name | VARCHAR(100) | |
| email | VARCHAR(100) UNIQUE | |
| phone | VARCHAR(20) | |
| password | VARCHAR(255) | |
| role | ENUM('customer','admin','petugas') | default customer |
| created_at | TIMESTAMP | |

**buses** (armada)
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| code | VARCHAR(20) | nomor lambung |
| name | VARCHAR(100) | nama PO/armada |
| type | VARCHAR(50) | Executive, VIP, Ekonomi |
| seat_layout | JSON | konfigurasi kursi (baris, kolom, no kursi) |
| total_seats | INT | |
| created_at | TIMESTAMP | |

**routes**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| origin | VARCHAR(100) | |
| destination | VARCHAR(100) | |
| distance_km | DECIMAL(8,2) | |
| estimated_duration | INT | dalam menit |

**schedules**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| route_id | INT FK -> routes | |
| bus_id | INT FK -> buses | |
| departure_time | DATETIME | |
| arrival_time | DATETIME | |
| price | DECIMAL(10,2) | |
| status | ENUM('scheduled','ongoing','completed','cancelled') | |

**bookings**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| booking_code | VARCHAR(20) UNIQUE | |
| user_id | INT FK -> users | |
| schedule_id | INT FK -> schedules | |
| total_price | DECIMAL(10,2) | |
| payment_status | ENUM('pending','paid','failed','refunded') | |
| booking_status | ENUM('active','completed','cancelled') | |
| created_at | TIMESTAMP | |

**booking_seats**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| booking_id | INT FK -> bookings | |
| seat_number | VARCHAR(10) | |
| passenger_name | VARCHAR(100) | |

**tickets**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| booking_id | INT FK -> bookings | |
| qr_code | VARCHAR(255) | |
| status | ENUM('issued','boarded','expired') | |
| issued_at | TIMESTAMP | |

**payments**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| booking_id | INT FK -> bookings | |
| method | VARCHAR(50) | |
| amount | DECIMAL(10,2) | |
| transaction_id | VARCHAR(100) | dari payment gateway |
| status | ENUM('pending','success','failed') | |
| paid_at | TIMESTAMP NULL | |

**reviews**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| booking_id | INT FK -> bookings | |
| user_id | INT FK -> users | |
| rating | TINYINT | 1-5 |
| comment | TEXT | |
| sentiment | ENUM('positive','neutral','negative') | hasil AI |
| created_at | TIMESTAMP | |

**promos**
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| code | VARCHAR(30) UNIQUE | |
| discount_type | ENUM('percent','fixed') | |
| discount_value | DECIMAL(10,2) | |
| valid_from | DATE | |
| valid_until | DATE | |
| usage_limit | INT | |

**chat_logs** (AI Chatbot)
| Field | Type | Keterangan |
|---|---|---|
| id | INT PK AI | |
| user_id | INT FK -> users NULL | |
| session_id | VARCHAR(50) | |
| message | TEXT | |
| response | TEXT | |
| created_at | TIMESTAMP | |

---

## 6. Detail Integrasi Fitur AI

### a. AI Chatbot Customer Service
- Endpoint: `POST /api/chatbot`
- Kirim pesan user + context (data jadwal, FAQ) ke LLM API
- Simpan log ke `chat_logs`
- Bisa pakai prompt system: "Kamu adalah asisten layanan tiket bus, jawab dengan ramah dan ringkas"

### b. AI Rekomendasi Rute/Jadwal
- Berdasarkan input pencarian + histori booking user
- LLM diberi data jadwal tersedia + preferensi (harga, waktu) → output rekomendasi terurut

### c. AI Prediksi Okupansi
- Ambil data historis booking per rute & jadwal
- Bisa pakai pendekatan sederhana: regresi linear / moving average via PHP, atau kirim data ke LLM untuk analisis pola
- Output: persentase prediksi okupansi untuk jadwal mendatang (membantu admin atur harga dinamis)

### d. AI Deteksi Anomali
- Deteksi: multiple booking dari 1 device/IP dalam waktu singkat, pola pembelian kursi berurutan oleh akun berbeda
- Bisa pakai rule-based + LLM untuk analisis pola mencurigakan, tampil sebagai alert di dashboard admin

### e. AI Analisis Sentimen Ulasan
- Saat user submit review, kirim teks ke LLM API
- Hasil sentiment disimpan di kolom `sentiment` tabel `reviews`
- Dashboard admin tampilkan ringkasan sentimen per rute/armada

---

## 7. Library/Package CI4 yang Disarankan

```bash
composer require endroid/qr-code      # generate QR code tiket
composer require dompdf/dompdf        # generate e-ticket PDF
composer require guzzlehttp/guzzle    # request ke AI API & payment gateway
```

---

## 8. Alur Sistem (High Level)

1. User cari tiket → sistem tampilkan jadwal + rekomendasi AI
2. User pilih jadwal & kursi → checkout
3. Sistem buat record `bookings` (status pending) → redirect ke payment gateway
4. Payment gateway callback (webhook) → update `payments` & `bookings` jadi paid
5. Sistem generate `tickets` (QR code + PDF) → kirim ke email/WA user
6. Hari-H, petugas scan QR → update status `boarded`
7. Setelah selesai, user kasih review → AI proses sentimen
8. Admin pantau dashboard: okupansi, prediksi AI, anomali, laporan keuangan

---

## 9. Saran Timeline Pengerjaan (contoh 8-10 minggu)

| Minggu | Fokus |
|---|---|
| 1 | Setup project, database, autentikasi |
| 2 | CRUD master data (bus, rute, jadwal) |
| 3 | Fitur pencarian & seat map |
| 4 | Booking flow + payment gateway |
| 5 | E-ticket (PDF, QR) + notifikasi |
| 6 | Integrasi AI chatbot & rekomendasi |
| 7 | AI prediksi okupansi & deteksi anomali |
| 8 | Dashboard admin & laporan |
| 9 | Testing, fix bug, UI polish |
| 10 | Dokumentasi & presentasi |

---

## 10. Catatan Tambahan
- Untuk fitur AI, jika tidak ingin pakai API berbayar terus-menerus, bisa pakai free-tier (Gemini API ada free tier yang cukup besar)
- Seat map bisa disimpan sebagai JSON di tabel `buses`, lalu di-render dinamis via JS
- Pastikan validasi server-side untuk semua input booking (cegah double booking kursi yang sama)
