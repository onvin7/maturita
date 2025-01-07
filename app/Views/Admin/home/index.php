<div class="container mt-4">
    <h1 class="text-center mb-4">Admin Dashboard</h1>

    <!-- Nejnovější články -->
    <div class="mb-5">
        <h2 class="mb-3">📄 Nejnovější články</h2>
        <ul class="list-group">
            <?php foreach ($latestArticles as $article): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($article['nazev']) ?></strong>
                        <br>
                        <small class="text-muted">Publikováno: <?= htmlspecialchars($article['datum']) ?></small>
                    </div>
                    <a href="/admin/articles/edit/<?= htmlspecialchars($article['id']) ?>" class="btn btn-sm btn-primary">Upravit</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Nejčtenější články -->
    <div class="mb-5">
        <h2 class="mb-3">🔥 Nejčtenější články za poslední týden</h2>
        <ul class="list-group">
            <?php foreach ($articleViewsData as $article): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <?= htmlspecialchars($article['nazev']) ?>
                    </div>
                    <span class="badge bg-success"><?= htmlspecialchars($article['pocet_zobrazeni'] ?? 0) ?> zobrazení</span>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>

    <!-- Graf zobrazení stránek -->
    <div class="mb-5">
        <h2 class="mb-3">📊 Graf zobrazení stránek</h2>
        <canvas id="pageViewsChart"></canvas>
    </div>

    <!-- Graf zobrazení článků -->
    <div class="mb-5">
        <h2 class="mb-3">📈 Graf zobrazení článků</h2>
        <canvas id="articleViewsChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data pro graf zobrazení stránek
    const pageViewsData = <?= json_encode($pageViewsData) ?>;
    const pageViewsChart = new Chart(document.getElementById('pageViewsChart'), {
        type: 'bar',
        data: {
            labels: pageViewsData.map(data => data.page),
            datasets: [{
                label: 'Zobrazení',
                data: pageViewsData.map(data => data.total_views),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Data pro graf zobrazení článků
    const articleViewsData = <?= json_encode($articleViewsData) ?>;
    const articleViewsChart = new Chart(document.getElementById('articleViewsChart'), {
        type: 'bar',
        data: {
            labels: articleViewsData.map(data => data.nazev),
            datasets: [{
                label: 'Zobrazení',
                data: articleViewsData.map(data => data.total_views),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>