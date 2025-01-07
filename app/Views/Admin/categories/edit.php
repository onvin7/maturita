<div class="container mt-4">
    <h1 class="text-center mb-4"><?= isset($category) ? 'Upravit kategorii' : 'Přidat novou kategorii' ?></h1>
    <form action="<?= isset($category) ? '/admin/categories/update/' . htmlspecialchars($category['id']) : '/admin/categories/store' ?>" method="POST">
        <div class="mb-3">
            <label for="nazev_kategorie" class="form-label">Název kategorie</label>
            <input type="text" class="form-control" id="nazev_kategorie" name="nazev_kategorie" value="<?= htmlspecialchars($category['nazev_kategorie'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary"><?= isset($category) ? 'Uložit změny' : 'Vytvořit kategorii' ?></button>
        <a href="/admin/categories" class="btn btn-secondary">Zpět</a>
    </form>
</div>