<h1><?= htmlspecialchars($article['nazev']); ?></h1>
<p><?= htmlspecialchars($article['datum']); ?></p>
<p>Kategorie:
    <?php foreach ($categories as $category): ?>
        <?= htmlspecialchars($category['nazev_kategorie']); ?>
    <?php endforeach; ?>
</p>
<p><?= htmlspecialchars($article['nahled_foto']); ?></p>