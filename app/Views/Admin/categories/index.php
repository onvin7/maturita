<div class="container mt-4">
    <h1 class="mb-4 text-center">Správa kategorií</h1>

    <div class="row mb-4 align-items-center">
        <div class="col-md-6 text-start">
            <a href="/admin/categories/create" class="btn btn-success">Vytvořit novou kategorii</a>
        </div>
        <div class="col-md-6">
            <form action="/admin/categories" method="GET">
                <div class="input-group">
                    <input type="text" name="filter" class="form-control" placeholder="Hledat kategorie..." value="<?= htmlspecialchars($_GET['filter'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary">Filtrovat</button>
                </div>
            </form>

        </div>
    </div>

    <!-- Výpis kategorií -->
    <div class="table-responsive">

        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark text-center">
                <tr>
                    <th>
                        <a href="?sort_by=id&amp;order=<?= ($sortBy === 'id' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            ID
                            <span><?= ($sortBy === 'id') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=nazev_kategorie&amp;order=<?= ($sortBy === 'nazev_kategorie' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            Název Kategorie
                            <span><?= ($sortBy === 'nazev_kategorie') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>

                    <th>Akce</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['id']) ?></td>
                        <td><?= htmlspecialchars($category['nazev_kategorie']) ?></td>
                        <td>
                            <a href="/admin/categories/edit/<?= htmlspecialchars($category['id']) ?>" class="btn btn-sm btn-primary me-1">Upravit</a>
                            <a href="/admin/categories/delete/<?= htmlspecialchars($category['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete smazat tuto kategorii?')">Smazat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>