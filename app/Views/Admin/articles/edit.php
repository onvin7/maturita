<section class="content-section">
    <div class="section-header">
        <h2>Upravit článek</h2>
    </div>
    
    <form action="/admin/articles/update/<?= htmlspecialchars($article['id']) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" class="form-control" id="id" name="id" value="<?= htmlspecialchars($article['id']) ?>" required>
        
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="nazev" class="form-label">Název článku</label>
                    <input type="text" class="form-control" id="nazev" name="nazev" value="<?= htmlspecialchars($article['nazev']) ?>" required>
                </div>

                <!-- URL adresa (slug) se generuje/aktualizuje automaticky na pozadí -->

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
                                           <?php 
                                                if (isset($article_categories) && is_array($article_categories)) {
                                                    foreach ($article_categories as $art_cat) {
                                                        if ($art_cat['id_kategorie'] == $category['id']) {
                                                            echo 'checked';
                                                            break;
                                                        }
                                                    }
                                                }
                                           ?>
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
                    <input type="datetime-local" class="form-control" id="datum_publikace" name="datum_publikace" 
                           value="<?= date('Y-m-d\TH:i', strtotime($article['datum'])) ?>">
                </div>
                <div class="mb-4">
                    <label for="nahled_foto" class="form-label">Náhledové foto</label>
                    <input type="file" class="form-control" id="nahled_foto" name="nahled_foto" onchange="previewImage(event)" accept="image/jpeg, image/png, image/gif">
                    
                    <div class="mt-3 preview-box">
                        <?php if (!empty($article['nahled_foto'])): ?>
                            <div class="text-center">
                                <p class="mb-2 text-muted small">Aktuální obrázek:</p>
                                <img src="../../../uploads/thumbnails/male/<?= htmlspecialchars($article['nahled_foto']) ?>" alt="Náhled" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                                <input type="hidden" name="current_foto" value="<?= htmlspecialchars($article['nahled_foto']) ?>">
                            </div>
                        <?php else: ?>
                            <p class="text-muted small text-center">Článek nemá nahraný žádný obrázek</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="preview-container" class="mt-3 text-center"></div>
                </div>
                
                <div class="mb-4">
                    <label for="audio_file" class="form-label">Zvuková stopa</label>
                    <?php 
                    // Relativní cesta od kořene webu
                    $audioPath = "/uploads/audio/" . $article['id'] . ".mp3";
                    
                    // Absolutní cesta k souboru
                    $documentRoot = realpath(__DIR__ . "/../../../../web");
                    $fullAudioPath = $documentRoot . "/uploads/audio/" . $article['id'] . ".mp3";
                    
                    // Kontrola, zda soubor existuje
                    $audioExists = file_exists($fullAudioPath) && is_file($fullAudioPath);
                    
                    // Debug informace pro administrátory
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <div class="small text-muted mb-2">
                        <strong>Debug info:</strong> Kontroluje se existence souboru: <?= $fullAudioPath ?>
                        (<?= $audioExists ? 'Soubor nalezen' : 'Soubor nenalezen' ?>)
                    </div>
                    <?php endif; 
                    
                    if ($audioExists): 
                    ?>
                    <div class="audio-container p-3 bg-light border rounded mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong class="text-primary"><i class="fa-solid fa-music me-2"></i>Aktuální zvuková stopa</strong>
                            <span class="badge bg-success">Nahráno</span>
                        </div>
                        <audio controls class="w-100 mb-2">
                            <source src="<?= $audioPath ?>" type="audio/mpeg">
                            Váš prohlížeč nepodporuje přehrávání audia.
                        </audio>
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" class="form-control" id="audio_file" name="audio_file" accept="audio/mpeg, audio/mp3" onchange="handleAudioChange(event)">
                    <div class="mt-2" id="audio-info-text">
                        <p class="text-muted small">
                        <?php if (!$audioExists): ?>
                            Článek zatím nemá zvukovou stopu. Nahrajte MP3 soubor.
                        <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="viditelnost" name="viditelnost" value="1" <?php echo $article['viditelnost'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="viditelnost">Viditelný článek</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label for="editor" class="form-label">Obsah článku</label>
            <textarea id="editor" name="content" lang="cs" spellcheck="true"><?= $article['obsah'] ?? '' ?></textarea>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="/admin/articles" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Zpět na seznam
            </a>
            <button type="submit" class="btn btn-action">
                <i class="fas fa-save me-1"></i> Uložit změny
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
            preview.innerHTML = '';
        }
    }
    
    function handleAudioChange(event) {
        const file = event.target.files[0];
        if (file) {
            // Skrýt existující audio přehrávač (pokud existuje)
            const audioContainer = document.querySelector('.audio-container');
            if (audioContainer) {
                audioContainer.style.display = 'none';
            }
            
            // Změnit informační text
            const infoText = document.getElementById('audio-info-text').querySelector('p');
            infoText.innerHTML = `<span class="text-success"><i class="fa-solid fa-check me-1"></i>Nový audio soubor: <strong>${file.name}</strong> bude nahrán po uložení</span>`;
        }
    }
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

<script>
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

// Inicializace textu s počtem vybraných kategorií při načtení stránky
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCategories();
});
</script>
<!-- Varování před zavřením stránky při neuložených změnách -->
<script src="/js/article-unsaved-warning.js"></script>