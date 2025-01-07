<div class="container mt-4">
    <h1 class="text-center mb-4">Upravit článek</h1>
    <form action="/admin/articles/update/<?= htmlspecialchars($article['id']) ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nazev" class="form-label">Název článku</label>
            <input type="text" class="form-control" id="nazev" name="nazev" value="<?= htmlspecialchars($article['nazev']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="kategorie" class="form-label">Kategorie</label>
            <select class="form-select" id="kategorie" name="kategorie">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>" <?= $article['id_kategorie'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['nazev_kategorie']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="nahled_foto" class="form-label">Náhledové foto</label>
            <input type="file" class="form-control" id="nahled_foto" name="nahled_foto" onchange="previewImage(event)">
            <?php if (!empty($article['nahled_foto'])): ?>
                <p class="mt-2">Aktuální obrázek:</p>
                <img src="/uploads/thumbnails/<?= htmlspecialchars($article['nahled_foto']) ?>" alt="Náhled" style="max-width: 150px;">
                <input type="hidden" name="current_foto" value="<?= htmlspecialchars($article['nahled_foto']) ?>">
            <?php endif; ?>
            <div id="preview-container" class="mt-3"></div>
        </div>

        <div class="mb-3">
            <label for="obsah" class="form-label">Obsah článku</label>
            <textarea class="form-control tinymce-editor" id="obsah" name="obsah" rows="10" required><?= htmlspecialchars($article['obsah']) ?></textarea>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="viditelnost" name="viditelnost" value="1" <?= $article['viditelnost'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="viditelnost">Viditelný</label>
        </div>
        <button type="submit" class="btn btn-primary">Uložit změny</button>
    </form>

</div>
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
<!-- TinyMCE konfigurace -->
<script src="https://cdn.tiny.cloud/1/4zya77m9f7cxct4wa90s8vckad17auk31vflx884mx6xu1a3/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: 'textarea',
        plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            'checklist', 'mediaembed', 'casechange', 'export', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'editimage', 'advtemplate', 'ai', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown', 'importword', 'exportword', 'exportpdf'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        tinycomments_mode: 'embedded',
        tinycomments_author: 'Author name',
        mergetags_list: [{
                value: 'First.Name',
                title: 'First Name'
            },
            {
                value: 'Email',
                title: 'Email'
            },
        ],
        ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
    });
</script>