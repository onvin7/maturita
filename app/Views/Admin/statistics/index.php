<div class="container mt-4">
    <h1 class="text-center">Statistiky článků</h1>
    <table class="table table-striped table-hover mt-4">
        <thead class="table-dark">
            <tr>
                <th>Název článku</th>
                <th>Celkový počet zobrazení</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articleViews as $article): ?>
                <tr>
                    <td><?= htmlspecialchars($article['nazev']); ?></td>
                    <td><?= htmlspecialchars($article['total_views']); ?></td>
                    <td><a href="/admin/statistics/view/<?= htmlspecialchars($article['id']); ?>" class="btn btn-primary btn-sm">Zobrazit</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>