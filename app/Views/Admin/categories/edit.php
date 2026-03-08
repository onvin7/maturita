<section class="content-section">
    <div class="section-header">
        <h2><?= isset($category) ? 'Upravit kategorii' : 'Přidat novou kategorii' ?></h2>
    </div>
    
    <form action="<?= isset($category) ? '/admin/categories/update/' . htmlspecialchars($category['id']) : '/admin/categories/store' ?>" method="POST">
        <?= \App\Helpers\CsrfHelper::formInput() ?>
        <div class="mb-3">
            <label for="nazev_kategorie" class="form-label">Název kategorie</label>
            <input type="text" class="form-control" id="nazev_kategorie" name="nazev_kategorie" value="<?= htmlspecialchars($category['nazev_kategorie'] ?? '') ?>" required>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-action"><?= isset($category) ? 'Uložit změny' : 'Vytvořit kategorii' ?></button>
            <a href="/admin/categories" class="btn btn-secondary">Zpět na seznam</a>
        </div>
    </form>
</section>