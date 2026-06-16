<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel — SiTeBus' ?></title>
    <meta name="description" content="Panel administrasi SiTeBus — Kelola jadwal, armada, promo, dan analitik bisnis.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        inter: ['Inter', 'sans-serif'],
                        outfit: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                        slate: {
                            850: '#172033',
                            925: '#0d1526',
                            950: '#0a0f1e',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: #0a0f1e;
            color: #e2e8f0;
            margin: 0;
        }

        /* Sidebar */
        .admin-sidebar {
            background: linear-gradient(180deg, #0d1117 0%, #0a0f1e 100%);
            border-right: 1px solid rgba(99, 102, 241, 0.1);
        }

        /* Top bar */
        .admin-topbar {
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }

        /* Nav item active */
        .nav-item-active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
            color: #818cf8;
            border-left: 3px solid #6366f1;
        }

        .nav-item {
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            background: rgba(99, 102, 241, 0.07);
            color: #e2e8f0;
            border-left-color: rgba(99, 102, 241, 0.3);
        }

        /* Stat cards */
        .stat-card {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(99, 102, 241, 0.1);
            transition: all 0.25s ease;
        }
        .stat-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.1);
        }

        /* Panel card */
        .panel-card {
            background: rgba(13, 17, 23, 0.8);
            border: 1px solid rgba(30, 41, 59, 0.8);
            border-radius: 1.25rem;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #0a0f1e; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }

        /* Sidebar overlay on mobile */
        .sidebar-overlay {
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
        }

        /* Badge pulse */
        .dot-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Content animation */
        .admin-content {
            animation: fadeIn 0.35s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Input style */
        .input-field {
            background: rgba(10, 15, 30, 0.8);
            border: 1px solid rgba(30, 41, 59, 0.9);
            color: #e2e8f0;
            border-radius: 0.75rem;
            transition: border-color 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Table row hover */
        .table-row {
            transition: background 0.15s;
        }
        .table-row:hover {
            background: rgba(99, 102, 241, 0.04);
        }

        /* Tooltip */
        .tooltip { position: relative; }
        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 110%;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: #e2e8f0;
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
            border: 1px solid rgba(99,102,241,0.2);
        }
    </style>
</head>
<body x-data="{ sidebarOpen: false }">

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen"
     x-cloak
     @click="sidebarOpen = false"
     class="fixed inset-0 z-30 sidebar-overlay md:hidden">
</div>

<!-- Layout Wrapper -->
<div class="min-h-screen flex">

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="admin-sidebar w-64 flex-shrink-0 flex flex-col fixed top-0 left-0 h-full z-40 transition-transform duration-300"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

        <!-- Logo Area -->
        <div class="h-16 flex items-center px-5 gap-3 border-b border-slate-800/60 flex-shrink-0">
            <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-brand-600/30 flex-shrink-0">
                <i data-lucide="bus" class="w-4.5 h-4.5 text-white"></i>
            </div>
            <div>
                <span class="text-sm font-extrabold font-outfit text-white tracking-tight">SiTe<span class="text-brand-400">Bus</span></span>
                <p class="text-[9px] text-slate-500 font-medium uppercase tracking-widest -mt-0.5">Admin Console</p>
            </div>
            <!-- Close button mobile -->
            <button @click="sidebarOpen = false" class="ml-auto md:hidden text-slate-500 hover:text-slate-300 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5">

            <?php if (session()->get('userRole') === 'admin'): ?>
                <p class="px-3 text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-2">Menu Utama</p>

                <a href="<?= base_url('admin/dashboard') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= service('router')->getMatchedRoute()[0] === 'admin/dashboard' ? 'nav-item-active' : 'text-slate-400' ?>">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Dashboard</span>
                    <?php if(service('router')->getMatchedRoute()[0] === 'admin/dashboard'): ?>
                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-400 dot-pulse"></span>
                    <?php endif; ?>
                </a>

                <a href="<?= base_url('admin/bus') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= strpos(service('router')->getMatchedRoute()[0], 'admin/bus') === 0 ? 'nav-item-active' : 'text-slate-400' ?>">
                    <i data-lucide="truck" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Data Armada Bus</span>
                </a>

                <a href="<?= base_url('admin/route') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= strpos(service('router')->getMatchedRoute()[0], 'admin/route') === 0 ? 'nav-item-active' : 'text-slate-400' ?>">
                    <i data-lucide="map-pinned" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Data Rute</span>
                </a>

                <a href="<?= base_url('admin/officer') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= strpos(service('router')->getMatchedRoute()[0], 'admin/officer') === 0 ? 'nav-item-active' : 'text-slate-400' ?>">
                    <i data-lucide="users" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Data Petugas</span>
                </a>

                <a href="<?= base_url('admin/schedule') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= strpos(service('router')->getMatchedRoute()[0], 'admin/schedule') === 0 ? 'nav-item-active' : 'text-slate-400' ?>">
                    <i data-lucide="calendar-days" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Jadwal Keberangkatan</span>
                </a>

                <a href="<?= base_url('admin/promo') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= strpos(service('router')->getMatchedRoute()[0], 'admin/promo') === 0 ? 'nav-item-active' : 'text-slate-400' ?>">
                    <i data-lucide="ticket-percent" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Voucher &amp; Promo</span>
                </a>

                <!-- Divider -->
                <div class="pt-4 mt-3 border-t border-slate-800/50">
                    <p class="px-3 text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-2">AI Analytics</p>

                    <a href="<?= base_url('admin/predictions') ?>"
                       class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= service('router')->getMatchedRoute()[0] === 'admin/predictions' ? 'nav-item-active' : 'text-slate-400' ?>">
                        <i data-lucide="trending-up" class="w-4 h-4 flex-shrink-0 text-indigo-400"></i>
                        <span>Prediksi Okupansi</span>
                        <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">AI</span>
                    </a>

                    <a href="<?= base_url('admin/anomalies') ?>"
                       class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= service('router')->getMatchedRoute()[0] === 'admin/anomalies' ? 'nav-item-active' : 'text-slate-400' ?>">
                        <i data-lucide="shield-alert" class="w-4 h-4 flex-shrink-0 text-rose-400"></i>
                        <span>Deteksi Anomali</span>
                    </a>

                    <a href="<?= base_url('admin/reviews') ?>"
                       class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= service('router')->getMatchedRoute()[0] === 'admin/reviews' ? 'nav-item-active' : 'text-slate-400' ?>">
                        <i data-lucide="message-circle" class="w-4 h-4 flex-shrink-0 text-emerald-400"></i>
                        <span>Sentimen Review</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="pt-4 mt-3 border-t border-slate-800/50">
                    <p class="px-3 text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-2">Operasional</p>
                    <a href="<?= base_url('admin/boarding') ?>"
                       class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= service('router')->getMatchedRoute()[0] === 'admin/boarding' ? 'nav-item-active' : 'text-slate-400' ?>">
                        <i data-lucide="scan-line" class="w-4 h-4 flex-shrink-0 text-teal-400"></i>
                        <span>Portal Boarding Petugas</span>
                    </a>
                    <a href="<?= base_url('customer/home') ?>"
                       class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl text-slate-400">
                        <i data-lucide="globe" class="w-4 h-4 flex-shrink-0"></i>
                        <span>Lihat Situs Pelanggan</span>
                    </a>
                </div>
            <?php elseif (session()->get('userRole') === 'petugas'): ?>
                <p class="px-3 text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-2">Menu Petugas</p>
                
                <a href="<?= base_url('petugas/scan') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl text-slate-400">
                    <i data-lucide="scan" class="w-4 h-4 flex-shrink-0 text-teal-400"></i>
                    <span>Scan Tiket Boarding</span>
                </a>
                
                <a href="<?= base_url('admin/boarding') ?>"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 text-xs font-semibold rounded-xl <?= service('router')->getMatchedRoute()[0] === 'admin/boarding' ? 'nav-item-active' : 'text-slate-400' ?>">
                    <i data-lucide="scan-line" class="w-4 h-4 flex-shrink-0 text-teal-400"></i>
                    <span>Portal Boarding</span>
                </a>
            <?php endif; ?>
        </nav>

        <!-- User Profile Footer -->
        <div class="p-3 border-t border-slate-800/60 flex-shrink-0">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-900/50 border border-slate-800/50 hover:border-slate-700/70 transition-colors">
                <div class="h-8 w-8 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white font-extrabold text-xs flex-shrink-0 shadow">
                    <?= strtoupper(substr(session()->get('userName') ?? 'A', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-200 truncate"><?= esc(session()->get('userName') ?? 'Admin') ?></p>
                    <p class="text-[9px] text-slate-500 truncate"><?= esc(session()->get('userRole') ?? 'Administrator') ?></p>
                </div>
                <a href="<?= base_url('logout') ?>"
                   class="tooltip flex-shrink-0 p-1.5 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all"
                   data-tooltip="Logout">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64">

        <!-- Top Bar -->
        <header class="admin-topbar h-16 flex items-center justify-between px-5 md:px-8 flex-shrink-0 sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <!-- Hamburger (mobile) -->
                <button @click="sidebarOpen = true"
                        class="md:hidden p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-800/50 rounded-xl transition-colors">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <!-- Breadcrumb -->
                <div class="flex items-center gap-2">
                    <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-500">
                        <i data-lucide="home" class="w-3.5 h-3.5"></i>
                        <span>Admin</span>
                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    </div>
                    <h1 class="text-sm font-bold text-white"><?= $title ?? 'Dashboard' ?></h1>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2">
                <!-- Clock -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900/60 border border-slate-800/60 text-xs text-slate-400 font-mono">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-brand-400"></i>
                    <span id="admin-clock">--:--</span>
                    <span class="text-slate-600">WIB</span>
                </div>

                <!-- Notification Bell -->
                <button class="relative p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-800/50 rounded-xl transition-colors">
                    <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-brand-500 border-2 border-slate-950 dot-pulse"></span>
                </button>

                <!-- User chip -->
                <div class="hidden md:flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-900/60 border border-slate-800/60 hover:border-slate-700 transition-colors cursor-default">
                    <div class="h-6 w-6 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white font-bold text-[10px]">
                        <?= strtoupper(substr(session()->get('userName') ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="leading-none">
                        <p class="text-xs font-semibold text-slate-200"><?= esc(session()->get('userName') ?? 'Admin') ?></p>
                        <p class="text-[9px] text-slate-500 mt-0.5"><?= esc(session()->get('userRole') ?? 'Administrator') ?></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Intro Banner -->
        <div class="px-5 md:px-8 pt-6 pb-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-white font-outfit"><?= $subtitle ?? 'Kelola Data' ?></h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <?= date('l, d F Y') ?> &nbsp;•&nbsp; <span class="text-brand-400 font-semibold">SiTeBus Admin Console</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <?= $this->renderSection('admin_actions') ?>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <main class="flex-1 px-5 md:px-8 py-5 space-y-6 admin-content">
            <?= $this->renderSection('admin_content') ?>
        </main>

        <!-- Footer -->
        <footer class="px-5 md:px-8 py-4 border-t border-slate-800/40 flex items-center justify-between">
            <p class="text-[10px] text-slate-600">© <?= date('Y') ?> SiTeBus — Admin Console. All rights reserved.</p>
            <span class="text-[10px] text-slate-700 font-mono">v1.0.0 • CI4</span>
        </footer>
    </div>
</div>

<!-- Custom Delete Confirmation Modal -->
<div x-data="{ open: false, url: '', message: '' }"
     x-show="open"
     x-cloak
     @open-delete-modal.window="open = true; url = $event.detail.url; message = $event.detail.message; setTimeout(() => lucide.createIcons(), 50)"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="open = false"></div>
    
    <!-- Modal Dialog -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-3xl w-full max-w-md relative z-10 shadow-2xl transition-all"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="scale-95"
         x-transition:enter-end="scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="scale-100"
         x-transition:leave-end="scale-95">
        
        <div class="flex items-start gap-4">
            <div class="h-10 w-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 flex-shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Konfirmasi Hapus</h3>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed" x-text="message"></p>
            </div>
        </div>
        
        <div class="flex gap-3 pt-6 mt-4 border-t border-slate-800/80">
            <button @click="open = false" 
                    class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-slate-400 hover:text-slate-200 hover:bg-slate-850 border border-slate-800 text-xs transition-all text-center">
                Batal
            </button>
            <a :href="url" 
               class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-white bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-650/15 text-xs transition-all text-center flex items-center justify-center gap-1.5">
                <i data-lucide="check" class="w-4 h-4"></i> Ya, Hapus
            </a>
        </div>
    </div>
</div>

<script>
    // Global function to trigger custom delete modal
    function confirmDelete(url, message) {
        window.dispatchEvent(new CustomEvent('open-delete-modal', {
            detail: { url: url, message: message }
        }));
    }

    // Init icons after DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        lucide.createIcons();

        // Live clock
        function updateClock() {
            const now = new Date();
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Jakarta', hour12: false };
            const timeStr = now.toLocaleTimeString('id-ID', options);
            const el = document.getElementById('admin-clock');
            if (el) el.textContent = timeStr;
        }
        updateClock();
        setInterval(updateClock, 1000);
    });
</script>
</body>
</html>
