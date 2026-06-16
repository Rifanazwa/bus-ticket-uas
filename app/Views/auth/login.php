<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-4xl">
        <div class="grid lg:grid-cols-2 gap-0 rounded-3xl overflow-hidden shadow-2xl shadow-black/40 border border-white/8">

            <!-- Left Panel: Branding -->
            <div class="hidden lg:flex flex-col justify-between p-10 relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);">
                <!-- BG decorative -->
                <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-indigo-500/15 rounded-full blur-[60px] pointer-events-none"></div>
                <div class="absolute top-10 left-10 w-32 h-32 bg-white/5 rounded-full blur-[40px] pointer-events-none"></div>

                <div>
                    <div class="flex items-center gap-2.5 mb-8">
                        <div class="h-10 w-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <i data-lucide="bus" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-white">SiTe<span class="text-indigo-200 font-medium">Bus</span></span>
                    </div>

                    <h2 class="text-3xl font-extrabold text-white leading-tight mb-4">
                        Perjalanan Cerdas<br>Dimulai dari Sini
                    </h2>
                    <p class="text-indigo-200 text-sm leading-relaxed">
                        Gabung bersama ribuan penumpang yang sudah menikmati kemudahan pesan tiket bus dengan teknologi AI terdepan.
                    </p>
                </div>

                <!-- Features list -->
                <div class="space-y-3 relative z-10">
                    <?php $feats = [
                        ['icon' => 'sparkles',      'text' => 'Rekomendasi rute terbaik oleh AI'],
                        ['icon' => 'shield-check',  'text' => 'Pembayaran aman via Midtrans'],
                        ['icon' => 'ticket',        'text' => 'E-Tiket PDF + QR boarding pass'],
                        ['icon' => 'message-circle','text' => 'Chatbot CS aktif 24/7'],
                    ]; ?>
                    <?php foreach ($feats as $f): ?>
                        <div class="flex items-center gap-3 text-sm text-indigo-100">
                            <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="<?= $f['icon'] ?>" class="w-3.5 h-3.5 text-indigo-200"></i>
                            </div>
                            <?= $f['text'] ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Panel: Form -->
            <div class="glass p-8 sm:p-10 flex flex-col justify-center">
                <!-- Mobile logo -->
                <div class="lg:hidden flex items-center gap-2 mb-6">
                    <div class="h-8 w-8 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center">
                        <i data-lucide="bus" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="text-base font-bold text-white">SiTe<span class="text-brand-400">Bus</span></span>
                </div>

                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-white">Masuk ke Akun</h1>
                    <p class="text-slate-400 text-sm mt-1">Belum punya akun? <a href="<?= base_url('register') ?>" class="text-brand-400 hover:text-brand-300 font-semibold transition-colors">Daftar gratis</a></p>
                </div>

                <!-- Error alerts -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-5 flex items-center gap-3 p-4 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-sm">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-sm space-y-1">
                        <div class="flex items-center gap-2 font-semibold mb-1"><i data-lucide="alert-circle" class="w-4 h-4"></i> Periksa kembali:</div>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <p class="text-xs pl-6">· <?= esc($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-5" action="<?= base_url('login') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required value="<?= old('email') ?>"
                                class="input-field block w-full pl-10 pr-4 py-3 rounded-xl text-sm"
                                placeholder="nama@email.com">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Kata Sandi</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required
                                class="input-field block w-full pl-10 pr-10 py-3 rounded-xl text-sm"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition-colors">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-xl shadow-brand-600/20 transition-all transform hover:-translate-y-0.5 active:scale-[0.99]">
                        Masuk ke Akun
                    </button>
                </form>

                <!-- Demo accounts -->
                <div class="mt-8 pt-6 border-t border-white/5">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Akun Demo</p>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <?php $demos = [
                            ['role' => 'Customer', 'color' => 'brand', 'email' => 'customer@bus.com', 'pass' => 'user123'],
                            ['role' => 'Admin',    'color' => 'amber', 'email' => 'admin@bus.com',    'pass' => 'admin123'],
                            ['role' => 'Petugas',  'color' => 'teal',  'email' => 'petugas@bus.com',  'pass' => 'petugas123'],
                        ]; ?>
                        <?php foreach ($demos as $d): ?>
                            <div class="p-2.5 bg-slate-900/80 border border-white/5 rounded-xl space-y-0.5 hover:border-white/10 transition-all">
                                <p class="font-bold text-<?= $d['color'] ?>-400"><?= $d['role'] ?></p>
                                <p class="text-slate-400 text-[10px] leading-tight"><?= $d['email'] ?></p>
                                <p class="text-slate-500 text-[10px] font-mono"><?= $d['pass'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Re-initialize icons after Alpine updates
document.addEventListener('alpine:initialized', () => lucide.createIcons());
</script>
<?= $this->endSection() ?>
