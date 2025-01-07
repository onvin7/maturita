<div class="container mt-4">
    <h1 class="text-center">Detail statistik článku</h1>
    <table class="table table-bordered table-hover mt-4">
        <thead class="table-dark">
            <tr>
                <th>Datum</th>
                <th>Počet zobrazení</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articleViews as $view): ?>
                <tr>
                    <td><?= htmlspecialchars($view['datum']); ?></td>
                    <td><?= htmlspecialchars($view['pocet']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/admin/statistics" class="btn btn-secondary mt-3">Zpět na statistiky</a>
</div>