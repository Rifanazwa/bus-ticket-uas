<?= $this->extend('layout/admin') ?>

<?= $this->section('admin_content') ?>

<div class="space-y-6">
    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Average Rating Widget -->
        <div class="panel-card p-6 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Peringkat Rata-Rata</h3>
                <div class="p-2 bg-amber-500/10 text-amber-400 rounded-lg border border-amber-500/20">
                    <i data-lucide="star" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="space-y-1">
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold text-white font-mono"><?= $avgRating ?></span>
                    <span class="text-sm font-semibold text-slate-500">/ 5.0</span>
                </div>
                <div class="flex items-center gap-0.5">
                    <?php 
                        $stars = round($avgRating);
                        for ($i = 1; $i <= 5; $i++) {
                            $color = $i <= $stars ? 'text-amber-400 fill-amber-400' : 'text-slate-700';
                            echo '<i data-lucide="star" class="w-4 h-4 ' . $color . '"></i>';
                        }
                    ?>
                </div>
                <p class="text-[10px] text-slate-500 pt-1">Berdasarkan total <?= $totalReviews ?> ulasan terdaftar.</p>
            </div>
        </div>

        <!-- Sentiment Breakdown Widget -->
        <div class="panel-card p-6 flex flex-col justify-between space-y-4 col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Distribusi Sentimen AI</h3>
                <span class="flex items-center gap-1 text-[9px] font-bold text-brand-400 bg-brand-500/10 border border-brand-500/20 px-2 py-0.5 rounded-full">
                    <i data-lucide="sparkles" class="w-3 h-3"></i> Gemini AI Classified
                </span>
            </div>
            
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3 bg-emerald-500/5 border border-emerald-500/15 rounded-2xl text-center space-y-1">
                    <p class="text-2xl font-extrabold text-emerald-400 font-mono"><?= $sentimentCounts['positive'] ?></p>
                    <p class="text-[10px] font-semibold text-slate-400">Positif</p>
                    <p class="text-[9px] text-slate-500"><?= $totalReviews > 0 ? round(($sentimentCounts['positive'] / $totalReviews) * 100) : 0 ?>% ulasan</p>
                </div>
                <div class="p-3 bg-slate-800/40 border border-slate-700/30 rounded-2xl text-center space-y-1">
                    <p class="text-2xl font-extrabold text-slate-300 font-mono"><?= $sentimentCounts['neutral'] ?></p>
                    <p class="text-[10px] font-semibold text-slate-400">Netral</p>
                    <p class="text-[9px] text-slate-500"><?= $totalReviews > 0 ? round(($sentimentCounts['neutral'] / $totalReviews) * 100) : 0 ?>% ulasan</p>
                </div>
                <div class="p-3 bg-rose-500/5 border border-rose-500/15 rounded-2xl text-center space-y-1">
                    <p class="text-2xl font-extrabold text-rose-400 font-mono"><?= $sentimentCounts['negative'] ?></p>
                    <p class="text-[10px] font-semibold text-slate-400">Negatif</p>
                    <p class="text-[9px] text-slate-500"><?= $totalReviews > 0 ? round(($sentimentCounts['negative'] / $totalReviews) * 100) : 0 ?>% ulasan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- AI CRM Insight Box -->
    <div class="p-5 bg-indigo-500/10 border border-indigo-500/20 rounded-3xl relative overflow-hidden flex flex-col md:flex-row items-start gap-4">
        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl -translate-y-6 translate-x-6"></div>
        <div class="p-3 bg-indigo-500/20 text-indigo-400 rounded-2xl border border-indigo-500/30 flex-shrink-0">
            <i data-lucide="sparkles" class="w-6 h-6"></i>
        </div>
        <div class="flex-1 space-y-1">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-400">AI CRM Summary (Gemini)</span>
            <h4 class="text-sm font-bold text-white">Analisis Kepuasan Pelanggan &amp; Tindakan Operasional</h4>
            <p class="text-xs text-slate-300 leading-relaxed pt-1">
                "<?= esc($aiSummary) ?>"
            </p>
        </div>
    </div>

    <!-- Filter & List Section -->
    <div class="panel-card p-6 space-y-6">
        <!-- Header & Filters -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-800/60 pb-5">
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <div class="p-1.5 bg-brand-500/10 text-brand-400 rounded-lg border border-brand-500/20">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                    </div>
                    Log Review &amp; Sentimen Ulasan Penumpang
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 ml-9">Ulasan asli dari penumpang yang telah melakukan keberangkatan bus.</p>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="<?= base_url('admin/reviews') ?>" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rating:</label>
                    <select name="rating" onchange="this.form.submit()" class="pl-3 pr-8 py-1.5 text-xs input-field bg-slate-900 border border-slate-800 rounded-xl focus:border-brand-500 focus:outline-none text-slate-300">
                        <option value="">Semua</option>
                        <option value="5" <?= $ratingFilter === '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4" <?= $ratingFilter === '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ (4)</option>
                        <option value="3" <?= $ratingFilter === '3' ? 'selected' : '' ?>>⭐⭐⭐ (3)</option>
                        <option value="2" <?= $ratingFilter === '2' ? 'selected' : '' ?>>⭐⭐ (2)</option>
                        <option value="1" <?= $ratingFilter === '1' ? 'selected' : '' ?>>⭐ (1)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Sentimen:</label>
                    <select name="sentiment" onchange="this.form.submit()" class="pl-3 pr-8 py-1.5 text-xs input-field bg-slate-900 border border-slate-800 rounded-xl focus:border-brand-500 focus:outline-none text-slate-300">
                        <option value="">Semua</option>
                        <option value="positive" <?= $sentimentFilter === 'positive' ? 'selected' : '' ?>>Positif</option>
                        <option value="neutral" <?= $sentimentFilter === 'neutral' ? 'selected' : '' ?>>Netral</option>
                        <option value="negative" <?= $sentimentFilter === 'negative' ? 'selected' : '' ?>>Negatif</option>
                    </select>
                </div>

                <?php if ($ratingFilter || $sentimentFilter): ?>
                    <a href="<?= base_url('admin/reviews') ?>" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-[10px] font-bold text-slate-400 hover:text-slate-200 transition-all">
                        Reset Filter
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Review Grid/List -->
        <?php if (empty($reviews)): ?>
            <div class="p-10 bg-slate-900/50 border border-slate-800/40 rounded-3xl text-center space-y-2 max-w-sm mx-auto">
                <i data-lucide="message-square-dashed" class="w-8 h-8 text-slate-600 mx-auto"></i>
                <h4 class="text-xs font-bold text-slate-400">Tidak ada ulasan ditemukan</h4>
                <p class="text-[10px] text-slate-500">Coba ubah filter atau lakukan transaksi E2E untuk menambah review.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($reviews as $r): ?>
                    <?php 
                        $styles = [
                            'positive' => ['text-emerald-400 bg-emerald-500/10 border-emerald-500/20', 'Positif'],
                            'neutral'  => ['text-slate-300 bg-slate-800 border-slate-700/30', 'Netral'],
                            'negative' => ['text-rose-400 bg-rose-500/10 border-rose-500/20', 'Negatif'],
                        ];
                        $sev = $styles[$r['sentiment']] ?? $styles['neutral'];
                    ?>
                    <div class="p-4 bg-slate-950/40 border border-slate-800/40 rounded-2xl space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <!-- Left: User details & rating -->
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-xs font-extrabold text-white"><?= esc($r['customer_name']) ?></h4>
                                    <span class="text-[10px] text-slate-500 font-semibold font-mono">&middot; <?= esc($r['customer_email']) ?></span>
                                </div>
                                <div class="flex items-center gap-0.5">
                                    <?php 
                                        for ($i = 1; $i <= 5; $i++) {
                                            $color = $i <= $r['rating'] ? 'text-amber-400 fill-amber-400' : 'text-slate-800';
                                            echo '<i data-lucide="star" class="w-3 h-3 ' . $color . '"></i>';
                                        }
                                    ?>
                                </div>
                            </div>
                            <!-- Right: Sentiment badge & route details -->
                            <div class="flex items-center gap-2 self-start sm:self-center">
                                <span class="text-[9px] font-extrabold px-2 py-0.5 rounded border uppercase tracking-wider <?= $sev[0] ?>">
                                    <?= $sev[1] ?>
                                </span>
                                <span class="text-[9px] text-slate-500 font-mono"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?> WIB</span>
                            </div>
                        </div>

                        <!-- Review Comment -->
                        <p class="text-xs text-slate-300 leading-relaxed font-medium">
                            "<?= esc($r['comment']) ?>"
                        </p>

                        <!-- Trip Meta details -->
                        <div class="flex items-center gap-3 pt-2 border-t border-slate-900 flex-wrap text-[9px] text-slate-500">
                            <span class="flex items-center gap-1"><i data-lucide="bus" class="w-3 h-3"></i> Armada: <?= esc($r['bus_name']) ?></span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i> Rute: <?= esc($r['origin']) ?> &rarr; <?= esc($r['destination']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
