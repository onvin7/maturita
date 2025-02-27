<form action="/admin/articles/store" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="nazev">Název</label>
        <input type="text" name="nazev" id="nazev" class="form-control" placeholder="Název článku" required>
    </div>

    <div class="form-group">
        <label for="category">Kategorie</label>
        <select name="category" id="category" class="form-control">
            <option value="">Vyberte kategorii</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['id']) ?>">
                    <?= htmlspecialchars($category['nazev_kategorie']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="datum">Datum publikování</label>
        <input type="datetime-local" name="datum" id="datum" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="nahled_foto" class="form-label">Náhledové foto</label>
        <input type="file" class="form-control" id="nahled_foto" name="nahled_foto" onchange="previewImage(event)">
        <div id="preview-container" class="mt-3"></div>
    </div>

    <div class="form-group">
        <label for="content" class="form-label">Obsah článku</label>
        <textarea id="editor" name="content"></textarea>
    </div>

    <div class="form-check">
        <input type="checkbox" name="viditelnost" id="viditelnost" class="form-check-input">
        <label for="viditelnost" class="form-check-label">Je příspěvek veřejný?</label>
    </div>

    <div class="form-check">
        <input type="checkbox" name="autor" id="autor" class="form-check-input">
        <label for="autor" class="form-check-label">Zobrazit autora příspěvku?</label>
    </div>

    <button type="submit" class="btn btn-primary">Vytvořit článek</button>
</form>


<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview-container');
                preview.innerHTML = `<img src="${e.target.result}" alt="Náhled" style="max-width: 150px;">`;
            };
            reader.readAsDataURL(file);
        }
    }
</script>
<!-- ✅ TinyMCE + konfigurace -->
<script src="/js/tinymce-config.js"></script>