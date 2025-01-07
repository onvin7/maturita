<?php
$title = "Kategorie - " . htmlspecialchars($category['nazev_kategorie']);
$description = "Články v kategorii " . htmlspecialchars($category['nazev_kategorie']) . ".";
include '../app/Views/Public/layouts/header.php';
?>

<div class="container mt-4">
    <h1 class="text-center mb-4"><?= htmlspecialchars($category['nazev_kategorie']) ?></h1>

    <?php if (!empty($articles)): ?>
        <div class="row">
            <?php foreach ($articles as $article): ?>
                <div class="col-md-4">
                    <div class="card mb-3 h-100">
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
        </div>
    <?php else: ?>
        <p class="text-center">V této kategorii nejsou žádné články.</p>
    <?php endif; ?>
</div>

<?php include '../app/Views/Public/layouts/footer.php'; ?>