<section class="content-section">
    <div class="section-header">
        <h1 class="mb-4 text-center"><i class="fas fa-users me-2"></i>Správa uživatelů</h1>
        <div class="text-end mb-3">
            <a href="/admin/social-sites" class="btn btn-action">
                <i class="fas fa-share-alt me-2"></i>Správa sociálních sítí
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="mb-4">
        <form action="/admin/users" method="GET" class="card">
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="filter" class="form-control" placeholder="Hledat uživatele..." value="<?= htmlspecialchars($_GET['filter'] ?? '') ?>">
                    <button type="submit" class="btn btn-action">Filtrovat</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Výpis uživatelů -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="artikly-thead text-center">
                <tr>
                    <th>
                        <a href="?sort_by=id&amp;order=<?= ($sortBy === 'id' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            <i class="fas fa-hashtag me-1"></i>ID
                            <span><?= ($sortBy === 'id') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=name&amp;order=<?= ($sortBy === 'name' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            <i class="fas fa-user me-1"></i>Jméno
                            <span><?= ($sortBy === 'name') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=surname&amp;order=<?= ($sortBy === 'surname' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            <i class="fas fa-user me-1"></i>Příjmení
                            <span><?= ($sortBy === 'surname') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=email&amp;order=<?= ($sortBy === 'email' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            <i class="fas fa-envelope me-1"></i>E-mail
                            <span><?= ($sortBy === 'email') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=role&amp;order=<?= ($sortBy === 'role' && $order === 'ASC') ? 'DESC' : 'ASC' ?>" class="text-white text-decoration-none">
                            <i class="fas fa-user-tag me-1"></i>Role
                            <span><?= ($sortBy === 'role') ? ($order === 'ASC' ? '⬆' : '⬇') : '' ?></span>
                        </a>
                    </th>
                    <th><i class="fas fa-cogs me-1"></i>Akce</th>
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
                                case 0:
                                    echo '<span class="badge bg-success"><i class="fas fa-user me-1"></i>Uživatel</span>';
                                    break;
                                case 1:
                                    echo '<span class="badge bg-info text-dark"><i class="fas fa-user-shield me-1"></i>Moderátor</span>';
                                    break;
                                case 2:
                                    echo '<span class="badge bg-warning text-dark"><i class="fas fa-user-edit me-1"></i>Editor</span>';
                                    break;
                                case 3:
                                    echo '<span class="badge bg-danger"><i class="fas fa-user-tie me-1"></i>Administrátor</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-secondary"><i class="fas fa-user-slash me-1"></i>Neznámá role</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="/admin/users/edit/<?= htmlspecialchars($user['id']) ?>" class="btn btn-sm btn-primary me-1">
                                <i class="fas fa-edit me-1"></i>Upravit
                            </a>
                            <form action="/admin/users/delete/<?= htmlspecialchars($user['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Opravdu chcete smazat tohoto uživatele?')">
                                <?= \App\Helpers\CsrfHelper::formInput() ?>
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash-alt me-1"></i>Smazat
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>