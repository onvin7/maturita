<div class="container mt-4">
    <h1 class="text-center">Nejčtenější články</h1>
    <table class="table table-striped table-hover mt-4">
        <thead class="table-dark">
            <tr>
                <th>Název článku</th>
                <th>Celkový počet zobrazení</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topArticles as $article): ?>
                <tr>
                    <td><?= htmlspecialchars($article['nazev']); ?></td>
                    <td><?= htmlspecialchars($article['total_views']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/admin/statistics" class="btn btn-secondary mt-3">Zpět na statistiky</a>
</div>