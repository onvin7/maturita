<div class="container mt-4">
    <h1 class="text-center mb-4"><?= isset($user) ? 'Upravit uživatele' : 'Přidat nového uživatele' ?></h1>
    <form action="<?= isset($user) ? '/admin/users/update/' . htmlspecialchars($user['id']) : '/admin/users/store' ?>" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Jméno</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="surname" class="form-label">Příjmení</label>
            <input type="text" class="form-control" id="surname" name="surname" value="<?= htmlspecialchars($user['surname'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="0" <?= isset($user['role']) && $user['role'] == 0 ? 'selected' : '' ?>>Uživatel</option>
                <option value="1" <?= isset($user['role']) && $user['role'] == 1 ? 'selected' : '' ?>>Redaktor</option>
                <option value="2" <?= isset($user['role']) && $user['role'] == 2 ? 'selected' : '' ?>>Editor</option>
                <option value="3" <?= isset($user['role']) && $user['role'] == 3 ? 'selected' : '' ?>>Administrátor</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="popis" class="form-label">Popis</label>
            <textarea class="form-control" id="popis" name="popis"><?= htmlspecialchars($user['popis'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?= isset($user) ? 'Uložit změny' : 'Přidat uživatele' ?></button>
        <a href="/admin/users" class="btn btn-secondary">Zpět</a>
    </form>
</div>