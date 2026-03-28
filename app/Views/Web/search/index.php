<div class="nadpis">
    <h1>VYHLEDÁVÁNÍ</h1>
    <h2>VYHLEDÁVÁNÍ</h2>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="/search" method="GET" class="mb-4 search-form-large">
                <div class="input-group input-group-lg">
                    <input type="text" name="q" class="form-control search-input" placeholder="Hledat článek..." value="<?= htmlspecialchars($query) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary search-btn" type="submit" style="display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (!empty($query) && !empty($results)): ?>
        <h3 class="text-center text-muted h4" style="font-size: 1.1rem; margin-top: 10px;">
            Nalezeno <?= (int) ($totalResults ?? count($results)) ?> výsledků
            <?php if (!empty($totalPages) && $totalPages > 1): ?>
                <span class="search-sep">|</span>
                Strana <?= (int) ($page ?? 1) ?> / <?= (int) $totalPages ?>
            <?php endif; ?>
        </h3>
    <?php endif; ?>
</div>

<?php if (!empty($query)): ?>
    <?php if (empty($results)): ?>
        <div class="container text-center py-5 mb-5">
            <div style="font-size: 4rem; color: rgba(255, 255, 255, 0.2); margin-bottom: 20px; overflow: hidden;">
                <i style="overflow: hidden;" class="far fa-folder-open"></i>
            </div>
            <h3 class="mb-3" style="color: white; font-weight: 300;">Nic jsme nenašli</h3>
            <p class="text-muted" style="font-size: 1.1rem;">
                Pro výraz <span style="color: #f1008d;">"<?= htmlspecialchars($query) ?>"</span> nemáme žádný článek.<br>
                Zkuste zjednodušit hledání nebo zkontrolujte překlepy.
            </p>
        </div>
    <?php else: ?>
        <div class="container-clanky" style="padding-top: 0; margin-top: 0;">
            <?php foreach ($results as $article): ?>
                <a href="/clanek/<?= htmlspecialchars($article['url']) ?>">
                    <div class="card">
                        <img loading="lazy" src="/uploads/thumbnails/male/<?= !empty($article['nahled_foto']) ? htmlspecialchars($article['nahled_foto']) : 'noimage.png' ?>" alt="<?= htmlspecialchars($article['nazev']) ?>">
                        <div class="card-body">
                            <div class="card-content-left">
                                <h5><?= $article['nazev_highlighted'] ?></h5>
                                
                                <span class="datum">
                                    <i class="far fa-calendar-alt me-1"></i> <?= \App\Helpers\TimeHelper::getRelativeTime($article['datum']) ?>
                                    <?php if (!empty($article['autor_jmeno'])): ?>
                                        <span class="mx-2">|</span>
                                        <i class="far fa-user me-1"></i> <?= htmlspecialchars($article['autor_jmeno'] . ' ' . $article['autor_prijmeni']) ?>
                                    <?php endif; ?>
                                </span>
                                
                                <div class="clanek-excerpt">
                                    <?= $article['snippet'] ?>
                                </div>
                            </div>
                            
                            <div class="card-content-right">
                                <div class="kategorie">
                                    <?php if (!empty($article['kategorie'])): ?>
                                        <?php foreach ($article['kategorie'] as $kategorie): ?>
                                            <span class="tag-kategorie" data-url="/category/<?= htmlspecialchars($kategorie['url']) ?>/">
                                                <p><?= htmlspecialchars($kategorie['nazev_kategorie']) ?></p>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <span class="read-more">Číst článek</span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <?php
                $currentPage = (int) ($page ?? 1);
                $currentPage = max(1, $currentPage);
                $pagesTotal = (int) $totalPages;

                $buildSearchUrl = function (int $p) use ($query) {
                    return '/search?' . http_build_query(['q' => $query, 'page' => $p]);
                };

                $windowStart = max(1, $currentPage - 2);
                $windowEnd = min($pagesTotal, $currentPage + 2);
            ?>
            <nav class="search-pagination" aria-label="Stránkování výsledků">
                <a class="search-page-link<?= $currentPage <= 1 ? ' is-disabled' : '' ?>" href="<?= $currentPage <= 1 ? '#' : htmlspecialchars($buildSearchUrl($currentPage - 1)) ?>" aria-disabled="<?= $currentPage <= 1 ? 'true' : 'false' ?>">&laquo;</a>

                <a class="search-page-link<?= $currentPage === 1 ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl(1)) ?>" <?= $currentPage === 1 ? 'aria-current="page"' : '' ?>>1</a>

                <?php if ($windowStart > 2): ?>
                    <span class="search-page-ellipsis">…</span>
                <?php endif; ?>

                <?php for ($p = max(2, $windowStart); $p <= min($pagesTotal - 1, $windowEnd); $p++): ?>
                    <a class="search-page-link<?= $p === $currentPage ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl($p)) ?>" <?= $p === $currentPage ? 'aria-current="page"' : '' ?>><?= (int) $p ?></a>
                <?php endfor; ?>

                <?php if ($windowEnd < $pagesTotal - 1): ?>
                    <span class="search-page-ellipsis">…</span>
                <?php endif; ?>

                <a class="search-page-link<?= $currentPage === $pagesTotal ? ' is-active' : '' ?>" href="<?= htmlspecialchars($buildSearchUrl($pagesTotal)) ?>" <?= $currentPage === $pagesTotal ? 'aria-current="page"' : '' ?>><?= (int) $pagesTotal ?></a>

                <a class="search-page-link<?= $currentPage >= $pagesTotal ? ' is-disabled' : '' ?>" href="<?= $currentPage >= $pagesTotal ? '#' : htmlspecialchars($buildSearchUrl($currentPage + 1)) ?>" aria-disabled="<?= $currentPage >= $pagesTotal ? 'true' : 'false' ?>">&raquo;</a>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Přidání event listeneru na všechny kategorie tagy
    const kategorieTags = document.querySelectorAll('.tag-kategorie');
    
    kategorieTags.forEach(tag => {
        tag.addEventListener('click', function(e) {
            // Zastavení propagace události, aby se neaktivoval nadřazený odkaz
            e.stopPropagation();
            e.preventDefault();
            
            // Získání URL z data atributu
            const url = this.getAttribute('data-url');
            
            // Přesměrování na URL kategorie
            if (url) {
                window.location.href = url;
            }
        });
    });
});
</script>
