<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="min-h-[calc(100vh-64px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-4xl">
        <div class="grid lg:grid-cols-2 gap-0 rounded-3xl overflow-hidden shadow-2xl shadow-black/40 border border-white/8">

            <!-- Left Panel: Branding -->
            <div class="hidden lg:flex flex-col justify-between p-10 relative overflow-hidden" style="background: linear-gradient(135deg, #134e4a 0%, #0f766e 40%, #0d9488 100%);">
                <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-teal-400/10 rounded-full blur-[60px] pointer-events-none"></div>

                <div>
                    <div class="flex items-center gap-2.5 mb-8">
                        <div class="h-10 w-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center">
                            <i data-lucide="bus" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-white">SiTe<span class="text-teal-200 font-medium">Bus</span></span>
                    </div>
                    <h2 class="text-3xl font-extrabold text-white leading-tight mb-4">Bergabung &<br>Mulai Perjalanan</h2>
                    <p class="text-teal-100 text-sm leading-relaxed">Buat akun gratis dalam 30 detik dan nikmati kemudahan pesan tiket bus kapan saja, di mana saja.</p>
                </div>

                <div class="space-y-3 relative z-10">
                    <?php $perks = [
                        'Gratis tanpa biaya pendaftaran',
                        'Akses ke semua jadwal & rute',
                        'Rekomendasi rute AI personal',
                        'Notifikasi promo eksklusif',
                    ]; ?>
                    <?php foreach ($perks as $p): ?>
                        <div class="flex items-center gap-3 text-sm text-teal-100">
                            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="check" class="w-3 h-3 text-white"></i>
                            </div>
                            <?= $p ?>
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

                <div class="mb-7">
                    <h1 class="text-2xl font-extrabold text-white">Buat Akun Baru</h1>
                    <p class="text-slate-400 text-sm mt-1">Sudah punya akun? <a href="<?= base_url('login') ?>" class="text-brand-400 hover:text-brand-300 font-semibold transition-colors">Masuk di sini</a></p>
                </div>

                <!-- Error alerts -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-4 flex items-center gap-3 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-sm">
                        <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-sm space-y-1">
                        <div class="flex items-center gap-2 font-semibold mb-1"><i data-lucide="alert-circle" class="w-4 h-4"></i> Periksa kembali:</div>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <p class="text-xs pl-6">· <?= esc($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-4" action="<?= base_url('register') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <input id="name" name="name" type="text" required value="<?= old('name') ?>"
                                class="input-field block w-full pl-10 pr-4 py-3 rounded-xl text-sm"
                                placeholder="Nama Lengkap Anda">
                        </div>
                    </div>

                    <!-- Email + Phone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required value="<?= old('email') ?>"
                                    class="input-field block w-full pl-10 pr-3 py-3 rounded-xl text-sm"
                                    placeholder="email@domain.com">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label for="phone" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">No. HP</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </div>
                                <input id="phone" name="phone" type="tel" required value="<?= old('phone') ?>"
                                    class="input-field block w-full pl-10 pr-3 py-3 rounded-xl text-sm"
                                    placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Kata Sandi</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input id="password" name="password" :type="show ? 'text' : 'password'" required
                                class="input-field block w-full pl-10 pr-10 py-3 rounded-xl text-sm"
                                placeholder="Minimal 6 karakter">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition-colors">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5">
                        <label for="password_confirm" class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Konfirmasi Kata Sandi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                            </div>
                            <input id="password_confirm" name="password_confirm" type="password" required
                                class="input-field block w-full pl-10 pr-4 py-3 rounded-xl text-sm"
                                placeholder="Ulangi kata sandi">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-bold text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-xl shadow-brand-600/20 transition-all transform hover:-translate-y-0.5 active:scale-[0.99] mt-2">
                        <i data-lucide="user-plus" class="w-4 h-4 inline mr-2 -mt-0.5"></i>
                        Buat Akun Gratis
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:initialized', () => lucide.createIcons());
</script>
<?= $this->endSection() ?>
