<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SiTeBus — Pesan Tiket Bus Online' ?></title>
    <meta name="description" content="Pesan tiket bus online termudah di Indonesia. Dilengkapi AI rekomendasi rute, chatbot CS, dan prediksi harga.">

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
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease forwards',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                        'counter': 'counter 2s ease-out forwards',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
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
        
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: #0a0f1e;
            color: #e2e8f0;
        }

        /* Gradient backgrounds */
        .bg-mesh {
            background-color: #0a0f1e;
            background-image:
                radial-gradient(at 20% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 55%),
                radial-gradient(at 80% 10%, rgba(20, 184, 166, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 80%, rgba(79, 70, 229, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* Glass card */
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.12);
        }

        .glass-light {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(148, 163, 184, 0.08);
        }

        /* Bus card hover */
        .bus-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bus-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.12);
            border-color: rgba(99, 102, 241, 0.3);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a0f1e; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }

        /* Animate on scroll */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Gradient text */
        .text-gradient {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 40%, #4f46e5 70%, #14b8a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Input focus glow */
        .input-field {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: #e2e8f0;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(99, 102, 241, 0.6);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .input-field option {
            background: #0f172a;
            color: #e2e8f0;
        }

        /* Progress bar animation */
        .progress-fill {
            transition: width 1s ease-in-out;
        }

        /* Shimmer loading */
        .shimmer {
            background: linear-gradient(90deg, #1e293b 25%, #2d3f59 37%, #1e293b 63%);
            background-size: 400% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 100% 0; }
            100% { background-position: -100% 0; }
        }
    </style>
</head>
<body class="bg-mesh min-h-screen flex flex-col" x-data="{ mobileMenuOpen: false }">

    <!-- ===================== NAVBAR ===================== -->
    <nav class="sticky top-0 z-50 glass border-b border-white/5" 
         x-data="{ scrolled: false }" 
         @scroll.window="scrolled = window.scrollY > 20"
         :class="scrolled ? 'shadow-xl shadow-black/30' : ''">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="<?= base_url('/') ?>" class="flex items-center gap-2.5 group">
                    <div class="flex items-center justify-center h-9 w-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 text-white shadow-lg shadow-brand-600/25 group-hover:shadow-brand-600/40 transition-all">
                        <i data-lucide="bus" class="w-4.5 h-4.5"></i>
                    </div>
                    <span class="text-lg font-bold text-white">SiTe<span class="text-brand-400 font-medium">Bus</span></span>
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-1">
                    <?php if (session()->get('isLoggedIn') && session()->get('userRole') === 'customer'): ?>
                        <a href="<?= base_url('customer/home') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">Beranda</a>
                        <a href="<?= base_url('customer/home#search-section') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">Cari Tiket</a>
                        <a href="<?= base_url('customer/home#history-section') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">Tiket Saya</a>
                    <?php else: ?>
                        <a href="<?= base_url('/') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">Beranda</a>
                        <a href="<?= base_url('/#search-section') ?>" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">Cari Tiket</a>
                    <?php endif; ?>
                </div>

                <!-- Desktop Auth -->
                <div class="hidden md:flex items-center gap-3">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <?php $role = session()->get('userRole'); ?>
                        <?php if ($role === 'admin'): ?>
                            <a href="<?= base_url('admin/dashboard') ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-all">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 inline -mt-0.5"></i> Admin Panel
                            </a>
                        <?php elseif ($role === 'petugas'): ?>
                            <a href="<?= base_url('petugas/scan') ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-500/10 text-teal-400 border border-teal-500/20 hover:bg-teal-500/20 transition-all mr-2">
                                <i data-lucide="scan" class="w-3.5 h-3.5 inline -mt-0.5"></i> Scan Boarding
                            </a>
                            <a href="<?= base_url('admin/boarding') ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 hover:bg-indigo-500/20 transition-all">
                                <i data-lucide="scan-line" class="w-3.5 h-3.5 inline -mt-0.5"></i> Portal Boarding
                            </a>
                        <?php endif; ?>
                        <div class="flex items-center gap-2 pl-3 border-l border-white/10">
                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-md">
                                <?= strtoupper(substr(session()->get('userName'), 0, 1)) ?>
                            </div>
                            <div class="hidden lg:block">
                                <p class="text-xs font-semibold text-white leading-none"><?= esc(session()->get('userName')) ?></p>
                                <p class="text-[10px] text-slate-400 capitalize mt-0.5"><?= esc(session()->get('userRole')) ?></p>
                            </div>
                            <a href="<?= base_url('logout') ?>" class="ml-1 p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all" title="Keluar">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-all">Masuk</a>
                        <a href="<?= base_url('register') ?>" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-md shadow-brand-600/20 transition-all">
                            Daftar Gratis
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" x-cloak></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-white/5 bg-slate-950/95 backdrop-blur-xl">
            <div class="max-w-7xl mx-auto px-4 py-4 space-y-1">
                <?php if (session()->get('isLoggedIn') && session()->get('userRole') === 'customer'): ?>
                    <a href="<?= base_url('customer/home') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                        <i data-lucide="home" class="w-4 h-4"></i> Beranda
                    </a>
                    <a href="<?= base_url('customer/home#search-section') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                        <i data-lucide="search" class="w-4 h-4"></i> Cari Tiket
                    </a>
                    <a href="<?= base_url('customer/home#history-section') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                        <i data-lucide="ticket" class="w-4 h-4"></i> Tiket Saya
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('/') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                        <i data-lucide="home" class="w-4 h-4"></i> Beranda
                    </a>
                    <a href="<?= base_url('/#search-section') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                        <i data-lucide="search" class="w-4 h-4"></i> Cari Tiket
                    </a>
                    <?php if (session()->get('isLoggedIn') && session()->get('userRole') === 'admin'): ?>
                        <a href="<?= base_url('admin/dashboard') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-amber-400 hover:text-white hover:bg-white/5 transition-all">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin Panel
                        </a>
                    <?php elseif (session()->get('isLoggedIn') && session()->get('userRole') === 'petugas'): ?>
                        <a href="<?= base_url('petugas/scan') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-teal-400 hover:text-white hover:bg-white/5 transition-all">
                            <i data-lucide="scan" class="w-4 h-4"></i> Scan Boarding
                        </a>
                        <a href="<?= base_url('admin/boarding') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-indigo-400 hover:text-white hover:bg-white/5 transition-all">
                            <i data-lucide="scan-line" class="w-4 h-4"></i> Portal Boarding
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (session()->get('isLoggedIn')): ?>
                    <div class="px-3 py-2 text-xs text-slate-500 border-t border-white/5 mt-2">Masuk sebagai: <span class="text-white font-semibold"><?= esc(session()->get('userName')) ?></span></div>
                    <a href="<?= base_url('logout') ?>" class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-rose-400 hover:bg-rose-500/10 transition-all">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                    </a>
                <?php else: ?>
                    <div class="pt-2 flex flex-col gap-2 border-t border-white/5 mt-2">
                        <a href="<?= base_url('login') ?>" class="w-full py-2.5 text-center rounded-xl text-sm font-semibold text-white border border-white/10 hover:bg-white/5 transition-all">Masuk</a>
                        <a href="<?= base_url('register') ?>" class="w-full py-2.5 text-center rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-brand-600 to-indigo-600 shadow-md">Daftar Gratis</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ===================== TOAST NOTIFICATION ===================== -->
    <div x-data="{
            show: false,
            message: '',
            type: 'success',
            init() {
                <?php if (session()->getFlashdata('success')): ?>
                    this.showToast('<?= addslashes(session()->getFlashdata('success')) ?>', 'success');
                <?php elseif (session()->getFlashdata('error')): ?>
                    this.showToast('<?= addslashes(session()->getFlashdata('error')) ?>', 'error');
                <?php endif; ?>
            },
            showToast(msg, type) {
                this.message = msg;
                this.type = type;
                this.show = true;
                setTimeout(() => { this.show = false; }, 4500);
            }
         }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-20 right-5 z-50 max-w-sm w-full glass-light border shadow-2xl rounded-2xl p-4 flex items-start gap-3"
         :class="type === 'success' ? 'border-emerald-500/30' : 'border-rose-500/30'"
         x-cloak>
        <div class="flex-shrink-0 mt-0.5">
            <template x-if="type === 'success'">
                <div class="p-1.5 bg-emerald-500/15 rounded-lg text-emerald-400">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </template>
            <template x-if="type === 'error'">
                <div class="p-1.5 bg-rose-500/15 rounded-lg text-rose-400">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </template>
        </div>
        <div class="flex-grow min-w-0">
            <p class="text-sm font-semibold text-white" x-text="type === 'success' ? 'Berhasil!' : 'Terjadi Kesalahan'"></p>
            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed" x-text="message"></p>
        </div>
        <button @click="show = false" class="flex-shrink-0 text-slate-500 hover:text-slate-300 transition-colors">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- ===================== MAIN CONTENT ===================== -->
    <?= $this->renderSection('content') ?>

    <!-- ===================== FOOTER ===================== -->
    <footer class="mt-auto border-t border-white/5 bg-slate-950/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center justify-center h-9 w-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 text-white shadow-md">
                            <i data-lucide="bus" class="w-4.5 h-4.5"></i>
                        </div>
                        <span class="text-lg font-bold text-white">SiTe<span class="text-brand-400">Bus</span></span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-xs">
                        Platform pemesanan tiket bus online termudah di Indonesia, dilengkapi teknologi AI untuk pengalaman perjalanan yang lebih cerdas.
                    </p>
                    <div class="flex items-center gap-1">
                        <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[10px] font-semibold text-emerald-400">Sistem Online 24/7</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Layanan</h4>
                    <ul class="space-y-2.5">
                        <li><a href="<?= base_url('/') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="<?= base_url('search') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">Cari Tiket</a></li>
                        <li><a href="<?= base_url('login') ?>" class="text-sm text-slate-400 hover:text-white transition-colors">Masuk / Daftar</a></li>
                    </ul>
                </div>

                <!-- Info -->
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Fitur AI</h4>
                    <ul class="space-y-2.5">
                        <li class="flex items-center gap-2 text-sm text-slate-400"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand-400"></i> Rekomendasi Rute</li>
                        <li class="flex items-center gap-2 text-sm text-slate-400"><i data-lucide="message-circle" class="w-3.5 h-3.5 text-brand-400"></i> Chatbot CS 24/7</li>
                        <li class="flex items-center gap-2 text-sm text-slate-400"><i data-lucide="bar-chart-3" class="w-3.5 h-3.5 text-brand-400"></i> Prediksi Harga</li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-500">© <?= date('Y') ?> SiTeBus. Hak cipta dilindungi.</p>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Pembayaran aman & terenkripsi via Midtrans
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================== AI CHATBOT (Customer Only) ===================== -->
    <?php if (session()->get('isLoggedIn') && session()->get('userRole') === 'customer'): ?>
    <div x-data="chatbotWidget()" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        <!-- Chat Window -->
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="w-80 sm:w-96 h-[500px] rounded-3xl shadow-2xl shadow-brand-900/30 flex flex-col mb-4 overflow-hidden border border-white/10"
             style="background: rgba(10,15,30,0.97); backdrop-filter: blur(20px);">

            <!-- Chat Header -->
            <div class="p-4 flex items-center justify-between border-b border-white/10 flex-shrink-0" style="background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 bg-white/15 rounded-xl flex items-center justify-center">
                        <i data-lucide="bot" class="w-4.5 h-4.5 text-white"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white leading-none">AIBus Assistant</h4>
                        <span class="text-[10px] text-indigo-200 flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Online & siap membantu
                        </span>
                    </div>
                </div>
                <button @click="open = false" class="text-white/60 hover:text-white transition-colors p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Chat Body -->
            <div x-ref="msgContainer" class="flex-1 p-4 overflow-y-auto space-y-3 text-sm">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex" :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'">
                        <template x-if="msg.sender === 'bot'">
                            <div class="h-6 w-6 rounded-full bg-brand-600/20 border border-brand-500/30 flex items-center justify-center mr-2 flex-shrink-0 mt-1">
                                <i data-lucide="bot" class="w-3 h-3 text-brand-400"></i>
                            </div>
                        </template>
                        <div class="max-w-[78%] px-3.5 py-2.5 rounded-2xl text-xs leading-relaxed"
                             :class="msg.sender === 'user'
                                ? 'bg-brand-600 text-white rounded-tr-sm'
                                : 'bg-slate-800 text-slate-200 rounded-tl-sm border border-white/5'">
                            <span x-html="formatMessage(msg.text)"></span>
                        </div>
                    </div>
                </template>

                <!-- Typing Indicator -->
                <div x-show="loading" class="flex justify-start items-end gap-2" x-cloak>
                    <div class="h-6 w-6 rounded-full bg-brand-600/20 border border-brand-500/30 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="bot" class="w-3 h-3 text-brand-400"></i>
                    </div>
                    <div class="bg-slate-800 border border-white/5 px-4 py-3 rounded-2xl rounded-tl-sm flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 animate-bounce"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 animate-bounce" style="animation-delay:0.15s"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 animate-bounce" style="animation-delay:0.3s"></span>
                    </div>
                </div>
            </div>

            <!-- Chat Footer -->
            <form @submit.prevent="sendMessage()" class="p-3 border-t border-white/5 flex gap-2 flex-shrink-0" style="background: rgba(15,23,42,0.9);">
                <input type="text" x-model="inputText" :disabled="loading"
                       class="flex-1 px-3.5 py-2.5 bg-slate-800/80 border border-white/10 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500/60 text-xs transition-all"
                       placeholder="Tanyakan jadwal, harga, atau promo...">
                <button type="submit" :disabled="loading || !inputText.trim()"
                        class="p-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-md shadow-brand-600/20">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <!-- Floating Button -->
        <button @click="toggleChat()"
                class="h-14 w-14 rounded-2xl text-white shadow-xl transition-all transform hover:scale-105 active:scale-95 relative border"
                :class="open ? 'bg-slate-800 border-white/10 shadow-black/30' : 'bg-gradient-to-tr from-brand-600 to-indigo-500 border-brand-500/30 shadow-brand-600/30'">
            <div class="flex items-center justify-center">
                <i data-lucide="message-circle" class="w-6 h-6" x-show="!open"></i>
                <i data-lucide="chevron-down" class="w-6 h-6" x-show="open" x-cloak></i>
            </div>
            <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-emerald-500 border-2 border-slate-950 flex items-center justify-center text-[9px] font-bold text-white" x-show="!open">AI</span>
        </button>
    </div>

    <script>
    function chatbotWidget() {
        return {
            open: false,
            loading: false,
            inputText: '',
            messages: [{
                id: 1,
                sender: 'bot',
                text: 'Halo! Saya SiTeBus Helper — asisten pintar SiTeBus 🚌 Saya bisa bantu cek jadwal, harga tiket, promo aktif, atau cara booking. Ada yang bisa saya bantu?'
            }],
            toggleChat() {
                this.open = !this.open;
                if (this.open) this.scrollToBottom();
            },
            formatMessage(text) {
                if (!text) return '';
                // Escape HTML characters to prevent XSS
                let escaped = text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
                
                // Convert markdown bold **text** to <strong>text</strong>
                escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                // Convert markdown lists starting with * or - to styled list items
                let lines = escaped.split('\n');
                let formattedLines = lines.map(line => {
                    let trimmed = line.trim();
                    if (trimmed.startsWith('* ') || trimmed.startsWith('- ')) {
                        return '<div class="pl-4 relative before:content-[\'•\'] before:absolute before:left-1 before:text-brand-400">' + trimmed.substring(2) + '</div>';
                    }
                    return line;
                });
                
                return formattedLines.join('<br>');
            },
            sendMessage() {
                let text = this.inputText.trim();
                if (!text || this.loading) return;
                this.messages.push({ id: Date.now(), sender: 'user', text });
                this.inputText = '';
                this.loading = true;
                this.scrollToBottom();

                let formData = new FormData();
                formData.append('message', text);

                fetch('<?= base_url("customer/chatbot/send") ?>', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(r => {
                        this.loading = false;
                        this.messages.push({
                            id: Date.now() + 1,
                            sender: 'bot',
                            text: r.status === 'success' ? r.response : 'Maaf, terjadi kendala teknis. Silakan coba lagi.'
                        });
                        this.scrollToBottom();
                    })
                    .catch(() => {
                        this.loading = false;
                        this.messages.push({ id: Date.now() + 1, sender: 'bot', text: 'Koneksi gagal. Pastikan Anda terhubung ke internet.' });
                        this.scrollToBottom();
                    });
            },
            scrollToBottom() {
                this.$nextTick(() => {
                    const el = this.$refs.msgContainer;
                    if (el) el.scrollTop = el.scrollHeight;
                    lucide.createIcons();
                });
            }
        };
    }
    </script>
    <?php endif; ?>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Animate on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

        // Smooth scroll and focus on search input when clicking hash links
        function handleHashScroll() {
            const hash = window.location.hash;
            if (hash === '#search-section') {
                const target = document.getElementById('search-section');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    setTimeout(() => {
                        const firstInput = target.querySelector('select, input');
                        if (firstInput) {
                            firstInput.focus();
                            firstInput.classList.add('ring-2', 'ring-brand-500');
                            setTimeout(() => firstInput.classList.remove('ring-2', 'ring-brand-500'), 1500);
                        }
                    }, 500);
                }
            } else if (hash === '#history-section') {
                const target = document.getElementById('history-section');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        }

        window.addEventListener('hashchange', handleHashScroll);
        window.addEventListener('DOMContentLoaded', handleHashScroll);
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            handleHashScroll();
        }
    </script>
</body>
</html>
