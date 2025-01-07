<div class="container mt-4">
    <h1 class="text-center mb-4">Správa článků</h1>

    <!-- Filtrování -->
    <form action="/admin/articles" method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="filter" class="form-control" placeholder="Hledat články..." value="<?= htmlspecialchars($_GET['filter'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrovat</button>
            </div>
        </div>
    </form>

    <!-- Tabulka článků -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle border rounded shadow">
            <thead class="table-dark rounded-top">
                <tr>
                    <th>
                        <a href="?sort_by=id&amp;order=<?= ($sortBy === 'id' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none d-flex justify-content-between align-items-center">
                            <span>ID</span>
                            <span><?= ($sortBy === 'id') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=nazev&amp;order=<?= (($_GET['sort_by'] ?? '') === 'nazev' && ($_GET['order'] ?? 'DESC') === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none d-flex justify-content-between align-items-center">
                            <span>Název</span>
                            <span><?= ($_GET['sort_by'] ?? '') === 'nazev' ? (($_GET['order'] ?? 'DESC') === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=datum&amp;order=<?= (($_GET['sort_by'] ?? '') === 'datum' && ($_GET['order'] ?? 'DESC') === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none d-flex justify-content-between align-items-center">
                            <span>Datum</span>
                            <span><?= ($_GET['sort_by'] ?? 'datum') === 'datum' ? (($_GET['order'] ?? 'DESC') === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=viditelnost&amp;order=<?= (($_GET['sort_by'] ?? '') === 'viditelnost' && ($_GET['order'] ?? 'DESC') === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none d-flex justify-content-between align-items-center">
                            <span>Viditelnost</span>
                            <span><?= ($_GET['sort_by'] ?? '') === 'viditelnost' ? (($_GET['order'] ?? 'DESC') === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=user_id&amp;order=<?= (($_GET['sort_by'] ?? '') === 'user_id' && ($_GET['order'] ?? 'DESC') === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none d-flex justify-content-between align-items-center">
                            <span>Autor</span>
                            <span><?= ($_GET['sort_by'] ?? '') === 'user_id' ? (($_GET['order'] ?? 'DESC') === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=pocet_zobrazeni&amp;order=<?= (($_GET['sort_by'] ?? '') === 'pocet_zobrazeni' && ($_GET['order'] ?? 'DESC') === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none d-flex justify-content-between align-items-center">
                            <span>Zobrazení</span>
                            <span><?= ($_GET['sort_by'] ?? '') === 'pocet_zobrazeni' ? (($_GET['order'] ?? 'DESC') === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $maxViews = max(array_column($articles, 'pocet_zobrazeni'));
                $minViews = min(array_column($articles, 'pocet_zobrazeni'));

                function getHslColor($value, $min, $max)
                {
                    if ($max === $min) {
                        return 'hsl(60, 100%, 50%)'; // Pokud jsou všechny hodnoty stejné, použijeme žlutou
                    }

                    // Normalizace hodnoty mezi 0 a 1
                    $normalized = ($value - $min) / ($max - $min);

                    // Interpolace Hue mezi červenou (0°) a zelenou (120°)
                    $hue = 120 * $normalized; // Červená (0°) -> Zelená (120°)

                    return "hsl(" . intval($hue) . ", 100%, 50%)";
                }


                foreach ($articles as $article):
                    $color = getHslColor($article['pocet_zobrazeni'], $minViews, $maxViews);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($article['id']) ?></td>
                        <td><?= htmlspecialchars($article['nazev']) ?></td>
                        <td><?= htmlspecialchars(date('d.m.Y', strtotime($article['datum']))) ?></td>
                        <td>
                            <span class="badge <?= $article['viditelnost'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $article['viditelnost'] ? 'Viditelný' : 'Skrytý' ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($article['autor_jmeno'] . ' ' . $article['autor_prijmeni']) ?></td>
                        <td>
                            <span style="background-color: <?= $color ?>; color: #000; padding: 4px 8px; border-radius: 4px;">
                                <?= htmlspecialchars($article['pocet_zobrazeni'] ?? 0) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/admin/articles/edit/<?= htmlspecialchars($article['id']) ?>" class="btn btn-sm btn-primary">Upravit</a>
                            <a href="/admin/articles/delete/<?= htmlspecialchars($article['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete smazat tento článek?')">Smazat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>