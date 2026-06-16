<?php $pager->setSurroundCount(1) ?>

<nav class="flex items-center justify-between border-t border-slate-800/80 px-4 py-3 sm:px-6 bg-slate-900/40 rounded-b-2xl">
    <div class="flex flex-1 justify-between sm:hidden">
        <?php if ($pager->hasPreviousPage()) : ?>
            <a href="<?= $pager->getPreviousPage() ?>" class="relative inline-flex items-center rounded-xl border border-slate-800 bg-slate-950 px-4 py-2 text-xs font-medium text-slate-400 hover:bg-slate-900 transition-colors">
                Sebelumnya
            </a>
        <?php else : ?>
            <span class="relative inline-flex items-center rounded-xl border border-slate-800/40 bg-slate-950/40 px-4 py-2 text-xs font-medium text-slate-650 cursor-not-allowed">
                Sebelumnya
            </span>
        <?php endif ?>

        <?php if ($pager->hasNextPage()) : ?>
            <a href="<?= $pager->getNextPage() ?>" class="relative inline-flex items-center rounded-xl border border-slate-800 bg-slate-950 px-4 py-2 text-xs font-medium text-slate-400 hover:bg-slate-900 transition-colors">
                Selanjutnya
            </a>
        <?php else : ?>
            <span class="relative inline-flex items-center rounded-xl border border-slate-800/40 bg-slate-950/40 px-4 py-2 text-xs font-medium text-slate-650 cursor-not-allowed">
                Selanjutnya
            </span>
        <?php endif ?>
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-xs text-slate-400">
                Menampilkan halaman <span class="font-semibold text-white"><?= $pager->getCurrentPageNumber() ?></span> dari <span class="font-semibold text-white"><?= $pager->getPageCount() ?></span>
            </p>
        </div>
        <div>
            <nav class="isolate inline-flex -space-x-px rounded-xl shadow-sm gap-1" aria-label="Pagination">
                <!-- First Page -->
                <?php if ($pager->getCurrentPageNumber() > 1) : ?>
                    <a href="<?= $pager->getFirst() ?>" class="relative inline-flex items-center rounded-lg p-2 bg-slate-950 text-slate-400 border border-slate-850 hover:bg-slate-900 hover:text-white transition-colors">
                        <span class="sr-only">First</span>
                        <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                    </a>
                <?php endif ?>

                <!-- Previous Page -->
                <?php if ($pager->hasPreviousPage()) : ?>
                    <a href="<?= $pager->getPreviousPage() ?>" class="relative inline-flex items-center rounded-lg p-2 bg-slate-950 text-slate-400 border border-slate-850 hover:bg-slate-900 hover:text-white transition-colors">
                        <span class="sr-only">Previous</span>
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                <?php endif ?>

                <!-- Number Links -->
                <?php foreach ($pager->links() as $link) : ?>
                    <a href="<?= $link['uri'] ?>" class="relative inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold border transition-all <?= $link['active'] ? 'bg-brand-600 border-brand-500 text-white shadow-lg shadow-brand-600/15' : 'bg-slate-950 border-slate-850 text-slate-400 hover:bg-slate-900 hover:text-white' ?>">
                        <?= $link['title'] ?>
                    </a>
                <?php endforeach ?>

                <!-- Next Page -->
                <?php if ($pager->hasNextPage()) : ?>
                    <a href="<?= $pager->getNextPage() ?>" class="relative inline-flex items-center rounded-lg p-2 bg-slate-950 text-slate-400 border border-slate-850 hover:bg-slate-900 hover:text-white transition-colors">
                        <span class="sr-only">Next</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                <?php endif ?>

                <!-- Last Page -->
                <?php if ($pager->getCurrentPageNumber() < $pager->getPageCount()) : ?>
                    <a href="<?= $pager->getLast() ?>" class="relative inline-flex items-center rounded-lg p-2 bg-slate-950 text-slate-400 border border-slate-850 hover:bg-slate-900 hover:text-white transition-colors">
                        <span class="sr-only">Last</span>
                        <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                    </a>
                <?php endif ?>
            </nav>
        </div>
    </div>
</nav>
