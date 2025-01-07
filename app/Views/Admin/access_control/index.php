<div class="container mt-4">
    <h1 class="text-center">Správa přístupů</h1>
    <form method="POST" action="/admin/access-control/update" class="mt-4">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Stránka</th>
                    <th>Role požadovaná pro přístup</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= htmlspecialchars($role['page']); ?></td>
                        <td>
                            <select name="access[<?= htmlspecialchars($role['page']); ?>]" class="form-select">
                                <option value="1" <?= $role['role_required'] == 1 ? 'selected' : ''; ?>>Admin</option>
                                <option value="2" <?= $role['role_required'] == 2 ? 'selected' : ''; ?>>Admin 2</option>
                                <option value="3" <?= $role['role_required'] == 3 ? 'selected' : ''; ?>>Super Admin</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary mt-3">Uložit změny</button>
    </form>
</div>