<div class="container mt-4">
    <h1 class="text-center mb-4">Správa uživatelů</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Jméno</th>
                <th>Příjmení</th>
                <th>E-mail</th>
                <th>Role</th>
                <th>Akce</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['surname']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="/admin/users/edit/<?= htmlspecialchars($user['id']) ?>" class="btn btn-primary btn-sm">Upravit</a>
                        <a href="/admin/users/delete/<?= htmlspecialchars($user['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Opravdu chcete smazat tohoto uživatele?')">Smazat</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>