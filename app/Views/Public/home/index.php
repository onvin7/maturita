<?php
$title = "Cyklistický magazín";
$description = "Nejnovější články o cyklistice.";
include '../app/Views/Public/layouts/header.php';
?>

<!-- Nejnovější články -->
<section class="latest-articles mb-5">
    <h2 class="text-center mb-4">Nejnovější články</h2>
    <div class="row">
        <?php if (!empty($latestArticles)): ?>
            <?php foreach ($latestArticles as $article): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php if (!empty($article['nahled_foto'])): ?>
                            <img src="/uploads/thumbnails/<?= htmlspecialchars($article['nahled_foto']) ?>" class="card-img-top" alt="<?= htmlspecialchars($article['nazev']) ?>">
                        <?php else: ?>
                            <img src="/uploads/thumbnails/default.jpg" class="card-img-top" alt="Výchozí obrázek">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($article['nazev']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted"><?= htmlspecialchars(date('d.m.Y', strtotime($article['datum']))) ?></small>
                            </p>
                            <a href="/article/<?= htmlspecialchars($article['url']) ?>" class="btn btn-primary">Číst více</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Žádné články k zobrazení.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Nejčastější kategorie -->
<section class="top-categories mb-5">
    <h2 class="text-center mb-4">Nejčastější kategorie</h2>
    <div class="row">
        <?php foreach ($topCategories as $category): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($category['nazev_kategorie']) ?></h5>
                        <p class="card-text">
                            <small class="text-muted">Článků: <?= htmlspecialchars($category['pocet_clanku']) ?></small>
                        </p>
                        <a href="/category/<?= htmlspecialchars($category['url']) ?>" class="btn btn-primary">Zobrazit články</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include '../app/Views/Public/layouts/footer.php'; ?>