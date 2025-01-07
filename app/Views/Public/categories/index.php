<?php
$title = "Kategorie";
$description = "Seznam všech kategorií článků.";
include '../app/Views/Public/layouts/header.php';
?>

<div class="container mt-4">
    <h1 class="text-center mb-4">Kategorie</h1>
    <div class="row">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6">
                    <div class="card mb-3 h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($category['nazev_kategorie']) ?></h5>
                            <a href="/category/<?= htmlspecialchars($category['url']) ?>" class="btn btn-primary">Zobrazit články</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Žádné kategorie k zobrazení.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../app/Views/Public/layouts/footer.php'; ?>