<?= $this->extend('layout/admin') ?>

<?= $this->section('admin_content') ?>

<div class="space-y-6">
    <!-- AI Recommendation Box (If anomalies exist) -->
    <?php 
        $aiRec = null;
        $activeAnomalies = [];
        foreach ($anomalies as $a) {
            if ($a['key'] === 'ai_recommendation') {
                $aiRec = $a;
            } else {
                $activeAnomalies[] = $a;
            }
        }
    ?>

    <?php if ($aiRec): ?>
        <div class="p-5 bg-indigo-500/10 border border-indigo-500/20 rounded-3xl relative overflow-hidden flex flex-col md:flex-row items-start gap-4">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl -translate-y-6 translate-x-6"></div>
            <div class="p-3 bg-indigo-500/20 text-indigo-400 rounded-2xl border border-indigo-500/30 flex-shrink-0">
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <div class="flex-1 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-400">Analisis Keamanan AI (Gemini)</span>
                    <span class="text-[9px] text-slate-500 font-mono"><?= $aiRec['time'] ?></span>
                </div>
                <h4 class="text-sm font-bold text-white">Rangkuman Risiko &amp; Rekomendasi Tindakan</h4>
                <p class="text-xs text-slate-300 leading-relaxed pt-1.5">
                    "<?= esc($aiRec['details']) ?>"
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Panel -->
    <div class="panel-card p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800/60 pb-4">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 bg-rose-500/10 text-rose-400 rounded-lg border border-rose-500/20">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    </div>
                    Log Temuan Anomali Transaksi
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 ml-9">Daftar transaksi yang memicu alarm keamanan sistem booking.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-extrabold px-3 py-1 rounded-full border <?= count($activeAnomalies) > 0 ? 'text-rose-400 bg-rose-500/10 border-rose-500/20' : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' ?>">
                    <?= count($activeAnomalies) ?> Kasus Aktif
                </span>
                <?php if (session()->get('resolved_anomalies')): ?>
                    <a href="<?= base_url('admin/anomalies/reset') ?>" class="text-[10px] font-bold text-slate-500 hover:text-slate-300 transition-colors flex items-center gap-1">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset Selesai
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($activeAnomalies)): ?>
            <div class="p-10 bg-emerald-500/5 border border-emerald-500/15 rounded-3xl text-center space-y-3 max-w-lg mx-auto">
                <div class="inline-flex p-3 bg-emerald-500/10 text-emerald-400 rounded-2xl border border-emerald-500/20 mb-2">
                    <i data-lucide="shield-check" class="w-8 h-8"></i>
                </div>
                <h4 class="text-sm font-bold text-emerald-400">Sistem Keamanan Aman</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Tidak ada aktivitas fraud, pembelian massal calo, atau pembayaran tertunda yang terdeteksi. Semua transaksi berjalan normal.
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($activeAnomalies as $a): ?>
                    <?php 
                        $styles = [
                            'high'   => ['bg-rose-500/5',  'border-rose-500/20',  'text-rose-400',  '#f43f5e', 'Tinggi'],
                            'medium' => ['bg-amber-500/5', 'border-amber-500/20', 'text-amber-400', '#f59e0b', 'Sedang'],
                        ];
                        $sev = $styles[$a['severity']] ?? $styles['medium'];
                    ?>
                    <div id="card-<?= esc($a['key']) ?>" class="p-4 <?= $sev[0] ?> border <?= $sev[1] ?> rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all">
                        <div class="space-y-2 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: <?= $sev[3] ?>"></span>
                                <span class="text-[10px] font-extrabold <?= $sev[2] ?> uppercase tracking-wider"><?= esc($a['type']) ?></span>
                                <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-500">Tingkat: <?= $sev[4] ?></span>
                                <span class="text-[9px] text-slate-500 font-mono pl-2"><?= $a['time'] ?></span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-200"><?= esc($a['user']) ?> <span class="text-[10px] text-slate-500 font-normal">&middot; <?= esc($a['email']) ?></span></h4>
                            <p class="text-xs text-slate-400 leading-relaxed"><?= esc($a['details']) ?></p>
                        </div>
                        <button onclick="resolveAnomaly('<?= esc($a['key']) ?>')" class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-brand-500/30 hover:bg-brand-500/5 text-[10px] font-bold text-slate-300 hover:text-white transition-all flex items-center gap-1.5 self-end sm:self-center">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Selesaikan
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Custom Toast Notification -->
<div id="toast" class="fixed bottom-5 right-5 z-50 transform translate-y-10 opacity-0 transition-all duration-300 pointer-events-none p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-3 shadow-2xl">
    <div class="h-8 w-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
        <i data-lucide="check" class="w-4 h-4"></i>
    </div>
    <p class="text-xs font-bold text-slate-200" id="toast-message">Notifikasi Berhasil</p>
</div>

<script>
async function resolveAnomaly(key) {
    try {
        const formData = new FormData();
        formData.append('key', key);
        
        const res = await fetch('<?= base_url('admin/anomalies/resolve') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (res.ok) {
            const data = await res.json();
            if (data.status === 'success') {
                // Show toast
                showToast(data.message);
                
                // Animate card removal
                const card = document.getElementById('card-' + key);
                if (card) {
                    card.style.transform = 'scale(0.95)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Reload if empty to show the empty state
                        const remaining = document.querySelectorAll('[id^="card-"]');
                        if (remaining.length === 0) {
                            window.location.reload();
                        }
                    }, 300);
                }
            }
        }
    } catch (err) {
        console.error('Failed to resolve anomaly:', err);
    }
}

function showToast(msg) {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-message');
    if (toast && toastMsg) {
        toastMsg.textContent = msg;
        toast.classList.remove('translate-y-10', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-10', 'opacity-0');
        }, 3000);
    }
}
</script>

<?= $this->endSection() ?>
