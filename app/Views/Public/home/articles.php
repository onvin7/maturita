<h1>Articles in Category: <?= htmlspecialchars($category['nazev_kategorie']) ?></h1>
<ul>
    <?php foreach ($articles as $article): ?>
        <li>
            <a href="?controller=Home&action=detail&id=<?= $article['id'] ?>">
                <?= htmlspecialchars($article['nazev']) ?>
            </a>
            <p><?= htmlspecialchars($article['datum']) ?></p>
        </li>
    <?php endforeach; ?>
</ul>
<a href="?controller=Home&action=index">Back to Home</a>
