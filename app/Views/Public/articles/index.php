<h1>Články</h1>
<ul>
    <?php foreach ($articles as $article): ?>
        <li>
            <h2><a href="/articles/view/<?= htmlspecialchars($article['id']); ?>"><?= htmlspecialchars($article['url']); ?></a></h2>
            <p><?= htmlspecialchars($article['datum']); ?></p>
        </li>
    <?php endforeach; ?>
</ul>