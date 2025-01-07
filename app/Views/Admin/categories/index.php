<div class="container mt-4">
    <h1 class="text-center mb-4">Správa kategorií</h1>
    <a href="/admin/categories/create" class="btn btn-success mb-3">Přidat novou kategorii</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Název kategorie</th>
                <th>URL</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['id']) ?></td>
                    <td><?= htmlspecialchars($category['nazev_kategorie']) ?></td>
                    <td><?= htmlspecialchars($category['url']) ?></td>
                    <td>
                        <a href="/admin/categories/edit/<?= htmlspecialchars($category['id']) ?>" class="btn btn-primary btn-sm">Upravit</a>
                        <a href="/admin/categories/delete/<?= htmlspecialchars($category['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Opravdu chcete smazat tuto kategorii?')">Smazat</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>