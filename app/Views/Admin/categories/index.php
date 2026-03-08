<section class="content-section">
    <div class="section-header">
        <h2><i class="fa-solid fa-folder"></i> Správa kategorií</h2>
        <a href="/admin/categories/create" class="btn btn-action">
            <i class="fa-solid fa-plus"></i> Vytvořit novou kategorii</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="/admin/categories" method="GET">
                <div class="input-group">
                    <input type="text" name="filter" class="form-control" placeholder="Hledat kategorie..." value="<?= htmlspecialchars($_GET['filter'] ?? '') ?>">
                    <button type="submit" class="btn btn-action">
                        <i class="fa-solid fa-magnifying-glass"></i> Filtrovat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="artikly-thead text-center">
                <tr>
                    <th>
                        <a href="?sort_by=id&amp;order=<?= ($sortBy === 'id' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            <span>ID</span>
                            <span><?= ($sortBy === 'id') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=nazev_kategorie&amp;order=<?= ($sortBy === 'nazev_kategorie' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            <span>Název Kategorie</span>
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
                            <a href="/admin/categories/edit/<?= htmlspecialchars($category['id']) ?>" class="btn btn-sm btn-primary me-1">
                                <i class="fa-solid fa-pen"></i> Upravit
                            </a>
                            <form action="/admin/categories/delete/<?= htmlspecialchars($category['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Opravdu chcete smazat tuto kategorii?')">
                                <?= \App\Helpers\CsrfHelper::formInput() ?>
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa-solid fa-trash"></i> Smazat
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>