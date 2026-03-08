<section class="content-section">
    <div class="section-header">
        <h2>Vytvořit nový článek</h2>
    </div>

    <form action="/admin/articles/store" method="POST" enctype="multipart/form-data">
        <?= \App\Helpers\CsrfHelper::formInput() ?>
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="nazev" class="form-label">Název článku</label>
                    <input type="text" class="form-control" id="nazev" name="nazev" placeholder="Zadejte název článku" required>
                </div>

                <!-- URL adresa (slug) se generuje automaticky na pozadí -->

                <div class="mb-3">
                    <label for="kategorie" class="form-label">Kategorie</label>
                    <div class="category-select-container">
                        <div class="category-select-header" onclick="toggleCategorySelect()">
                            <span id="selected-categories-text">Vyberte kategorie</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="category-select-options" id="category-options">
                            <?php foreach ($categories as $category): ?>
                                <div class="category-option">
                                    <input type="checkbox" 
                                           id="category_<?= htmlspecialchars($category['id']) ?>" 
                                           name="kategorie[]" 
                                           value="<?= htmlspecialchars($category['id']) ?>"
                                           onchange="updateSelectedCategories()">
                                    <label for="category_<?= htmlspecialchars($category['id']) ?>">
                                        <?= htmlspecialchars($category['nazev_kategorie']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="datum_publikace" class="form-label">Datum publikace</label>
                    <input type="datetime-local" class="form-control" id="datum_publikace" name="datum_publikace" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                
                <div class="mb-4">
                    <label for="nahled_foto" class="form-label">Náhledové foto</label>
                    <input type="file" class="form-control" id="nahled_foto" name="nahled_foto" onchange="previewImage(event)" accept="image/jpeg, image/png, image/gif">
                    <div id="preview-container" class="mt-3 text-center preview-box"></div>
                </div>
                 
                <div class="mb-4">
                    <label for="audio_file" class="form-label">Zvuková stopa</label>
                    <input type="file" class="form-control" id="audio_file" name="audio_file" accept="audio/mpeg, audio/mp3" onchange="handleAudioChange(event)">
                    <div id="audio-info-text" class="mt-2 d-flex align-items-center text-muted small">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        <span>Podporované formáty: MP3 (max. 10 MB)</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="viditelnost" name="viditelnost" value="1" checked>
                        <label class="form-check-label" for="viditelnost">Viditelný článek</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label for="editor" class="form-label">Obsah článku</label>
            <textarea id="editor" name="content" lang="cs" spellcheck="true"></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="/admin/articles" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Zpět
            </a>
            <button type="submit" class="btn btn-action">
                <i class="fas fa-save me-1"></i> Uložit článek
            </button>
        </div>
    </form>
</section>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('preview-container');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="p-3 rounded border text-center">
                        <div class="image-preview-container">
                            <img src="${e.target.result}" alt="Náhled" class="img-thumbnail shadow-sm" style="max-width: 100%; max-height: 300px;">
                        </div>
                    </div>`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `
                <div class="p-3 rounded border">
                    <span class="badge bg-secondary mb-2"><i class="fa-solid fa-image-slash me-1"></i> Bez obrázku</span>
                </div>`;
        }
    }

    function handleAudioChange(event) {
        const file = event.target.files[0];
        if (file) {
            // Změnit informační text
            const infoText = document.getElementById('audio-info-text');
            infoText.innerHTML = `
                <div class="p-3 rounded border mt-2">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fa-solid fa-file-audio text-success fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 text-success"><i class="fa-solid fa-check me-1"></i> Audio soubor připraven</h6>
                            <p class="text-muted small mb-0">${file.name} <span class="badge bg-secondary ms-2">${(file.size / (1024*1024)).toFixed(2)} MB</span></p>
                        </div>
                    </div>
                </div>`;
        }
    }

    function toggleCategorySelect() {
        const options = document.getElementById('category-options');
        options.style.display = options.style.display === 'none' ? 'block' : 'none';
    }

    function updateSelectedCategories() {
        const checkboxes = document.querySelectorAll('input[name="kategorie[]"]:checked');
        const textElement = document.getElementById('selected-categories-text');
        
        if (checkboxes.length === 0) {
            textElement.textContent = 'Kategorie nevybrány';
        } else {
            const selectedCategories = Array.from(checkboxes).map(checkbox => {
                return checkbox.nextElementSibling.textContent.trim();
            });
            textElement.textContent = selectedCategories.join(', ');
        }
    }

    // Zavřít dropdown při kliknutí mimo
    document.addEventListener('click', function(event) {
        const container = document.querySelector('.category-select-container');
        const options = document.getElementById('category-options');
        
        if (!container.contains(event.target) && options.style.display === 'block') {
            options.style.display = 'none';
        }
    });
</script>
<!-- Nastavení výšky editoru -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Počkáme na inicializaci TinyMCE
        setTimeout(function() {
            const editorElement = document.querySelector('.tox-tinymce');
            if (editorElement) {
                editorElement.style.height = '830px';
                const editArea = document.querySelector('.tox-edit-area');
                if (editArea) {
                    editArea.style.height = '760px';
                }
            }
        }, 1000);
    });
</script>
<!-- Varování před zavřením stránky při neuložených změnách -->
<script src="/js/article-unsaved-warning.js"></script>