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
        <label for="obsah">Obsah článku</label>
        <textarea name="obsah" id="editor" class="form-control"></textarea>
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
<!-- Place the first <script> tag in your HTML's <head> -->
<script src="https://cdn.tiny.cloud/1/4zya77m9f7cxct4wa90s8vckad17auk31vflx884mx6xu1a3/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

<!-- Place the following <script> and <textarea> tags your HTML's <body> -->
<script>
    tinymce.init({
        selector: 'textarea',
        plugins: [
            // Core editing features
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            // Your account includes a free trial of TinyMCE premium features
            // Try the most popular premium features until Jan 8, 2025:
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