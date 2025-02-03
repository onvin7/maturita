<div class="container mt-4">
    <h1 class="mb-4 text-center">Správa uživatelů</h1>

    <div class="row mb-4 align-items-center justify-content-end">
        <div class="col-md-6">
            <form action="/admin/users" method="GET">
                <div class="input-group">
                    <input type="text" name="filter" class="form-control" placeholder="Hledat uživatele..." value="<?= htmlspecialchars($_GET['filter'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary">Filtrovat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Výpis uživatelů -->
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
                        <a href="?sort_by=name&amp;order=<?= ($sortBy === 'name' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            Jméno
                            <span><?= ($sortBy === 'name') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=surname&amp;order=<?= ($sortBy === 'surname' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            Příjmení
                            <span><?= ($sortBy === 'surname') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=email&amp;order=<?= ($sortBy === 'email' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            E-mail
                            <span><?= ($sortBy === 'email') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=role&amp;order=<?= ($sortBy === 'role' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            Role
                            <span><?= ($sortBy === 'role') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['surname']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php
                            switch ($user['role']) {
                                case 1:
                                    echo '<span class="badge bg-info text-dark">Moderátor</span>';
                                    break;
                                case 2:
                                    echo '<span class="badge bg-warning text-dark">Admin</span>';
                                    break;
                                case 3:
                                    echo '<span class="badge bg-danger">Superadmin</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-secondary">Uživatel</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="/admin/users/edit/<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-primary me-1">Upravit</a>
                            <a href="/admin/users/delete/<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Opravdu chcete smazat tohoto uživatele?')">Smazat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>