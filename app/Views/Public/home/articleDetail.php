<?php
$title = htmlspecialchars($article['nazev']);
$description = substr(strip_tags($article['obsah']), 0, 160) . "...";
include '../app/Views/Public/layouts/header.php';
?>

<div class="container mt-4">
    <h1 class="text-center mb-4"><?= htmlspecialchars($article['nazev']) ?></h1>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <?php if (!empty($article['nahled_foto'])): ?>
                <img src="/uploads/thumbnails/velke/<?= htmlspecialchars($article['nahled_foto']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($article['nazev']) ?>">
            <?php endif; ?>

            <p class="text-muted"><small><?= htmlspecialchars(date('d.m.Y', strtotime($article['datum']))) ?></small></p>

            <div class="article-content">
                <?= $article['obsah'] ?>
            </div>
        </div>
    </div>
</div>

<?php include '../app/Views/Public/layouts/footer.php'; ?>