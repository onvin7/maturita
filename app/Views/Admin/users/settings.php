<section class="content-section">
    
    <div class="section-header">
        <h2 class="mb-4"><i class="fas fa-cog me-2"></i>Nastavení účtu</h2>
        <div class="text-end">
            <button type="button" class="btn btn-secondary" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i>Zpět</button>
        </div>
    </div>
    <form action="/admin/settings/update" method="POST" enctype="multipart/form-data">
        <?= \App\Helpers\CsrfHelper::formInput() ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header"><i class="fas fa-user me-2"></i>Osobní údaje</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-signature me-2"></i>Jméno:</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-signature me-2"></i>Příjmení:</label>
                            <input type="text" class="form-control" name="surname" value="<?= htmlspecialchars($user['surname']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope me-2"></i>Email:</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="profil_foto" class="form-label"><i class="fas fa-camera me-2"></i>Profilová fotka</label>
                            <input type="file" class="form-control" id="profil_foto" name="profil_foto" accept="image/jpeg, image/png, image/gif" onchange="previewImage(event)">
                            
                            <div id="photo-previews" class="mt-3">
                                <div class="preview-box" id="existing-preview">
                                    <?php if (!empty($user['profil_foto'])): ?>
                                        <div class="text-center">
                                            <p class="mb-2 text-muted small">Aktuální fotka:</p>
                                            <img src="/uploads/users/thumbnails/<?= htmlspecialchars($user['profil_foto']) ?>" alt="Náhled" class="img-thumbnail shadow-sm" style="max-width: 100%; max-height: 200px;">
                                            <input type="hidden" name="current_foto" value="<?= htmlspecialchars($user['profil_foto']) ?>">
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small text-center">Uživatel nemá nahranou žádnou profilovou fotku</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div id="preview-container" class="mt-3 text-center"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-pen me-2"></i>Popis:</label>
                            <textarea id="editor" name="description" lang="cs" spellcheck="true"><?= htmlspecialchars($user['popis']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header"><i class="fas fa-share-alt me-2"></i>Sociální sítě</div>
                    <div class="card-body" id="social-links">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Poznámka:</strong> Zadejte <strong>celý odkaz</strong> na váš profil (např. <code>https://www.instagram.com/vas_profil</code>), ne pouze uživatelské jméno.
                        </div>
                        <?php foreach ($social_links as $social): ?>
                            <div class="d-flex mb-2 social-entry align-items-center">
                                <select class="form-select me-2 social-select" name="social_id[]" required>
                                    <option value="">Vyber sociální síť</option>
                                    <?php foreach ($available_socials as $site): ?>
                                        <option value="<?= $site['id'] ?>" data-icon="<?= htmlspecialchars($site['fa_class']) ?>" <?= isset($social['social_id']) && (int)$social['social_id'] == (int)$site['id'] ? 'selected="selected"' : '' ?>>
                                            <?php echo htmlspecialchars($site['nazev']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" class="form-control me-2" name="link[]" placeholder="https://www.instagram.com/vas_profil" value="<?= htmlspecialchars($social['link']) ?>" required>
                                <button type="button" class="btn btn-danger remove-social"><i class="fas fa-times"></i></button>
                            </div>
                        <?php endforeach; ?>

                        <button type="button" class="btn btn-success mt-3" id="add-social"><i class="fas fa-plus me-2"></i>Přidat sociální síť</button>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        <i class="fas fa-arrow-left me-2"></i>Zpět
                    </button>
                    <button type="submit" class="btn btn-action">
                        <i class="fas fa-save me-2"></i>Uložit vše
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<style>
    #editor {
        min-height: 300px;
    }
    
    .tox-tinymce {
        border-radius: 0.25rem !important;
    }

    /* Styly pro náhledy fotek */
    #photo-previews {
        position: relative;
    }
    
    .preview-box, #preview-container {
        transition: all 0.3s ease;
    }
    
    /* Styling pro dropdown sociálních sítí */
    .social-select {
        font-size: 1rem;
        padding: 0.625rem 0.875rem;
        line-height: 1.6;
        min-height: 42px;
    }
    
    .social-select option {
        padding: 0.625rem 0.875rem;
        font-size: 1rem;
        line-height: 1.6;
    }
    
    /* Vylepšení pro lepší vzhled */
    .social-entry {
        align-items: center;
    }
    
    .social-entry .form-select {
        flex: 0 0 auto;
        width: auto;
        min-width: 200px;
    }
    
    .social-entry .form-control {
        flex: 1 1 auto;
    }
    
</style>

<!-- TinyMCE + konfigurace -->
<!-- TinyMCE je načten v base.php -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Počkáme na inicializaci TinyMCE
        setTimeout(function() {
            const editorElement = document.querySelector('.tox-tinymce');
            if (editorElement) {
                editorElement.style.height = '500px';
                const editArea = document.querySelector('.tox-edit-area');
                if (editArea) {
                    editArea.style.height = '430px';
                }
            }
        }, 1000);
    });
</script>

<!-- Existující script pro správu sociálních sítí -->
<script>
    function filterAvailableSocials() {
        let selectedSocials = [...document.querySelectorAll('select[name="social_id[]"]')]
            .map(select => select.value)
            .filter(val => val !== ''); // Pouze neprázdné hodnoty
        
        document.querySelectorAll('select[name="social_id[]"]').forEach(select => {
            const currentValue = select.value;
            select.querySelectorAll('option').forEach(option => {
                // Pokud je option vybraná v jiném selectu a není to aktuální select, disable
                // ALE nikdy nedisable aktuálně vybranou option
                if (selectedSocials.includes(option.value) && option.value !== "" && option.value !== currentValue) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });
        });
    }

    document.getElementById('add-social')?.addEventListener('click', function() {
        let container = document.getElementById('social-links');
        let existingSocials = [...document.querySelectorAll('select[name="social_id[]"]')]
            .map(select => select.value);

        let availableSocials = <?php echo json_encode($available_socials); ?>;

        let div = document.createElement('div');
        div.classList.add('d-flex', 'mb-2', 'social-entry', 'align-items-center');

        let select = document.createElement('select');
        select.classList.add('form-select', 'me-2', 'social-select');
        select.name = 'social_id[]';
        select.onchange = filterAvailableSocials;

        let defaultOption = document.createElement('option');
        defaultOption.value = "";
        defaultOption.text = "Vyber sociální síť";
        select.appendChild(defaultOption);

        availableSocials.forEach(site => {
            let option = document.createElement('option');
            option.value = site.id;
            option.textContent = site.nazev;
            option.setAttribute('data-icon', site.fa_class || '');
            if (existingSocials.includes(site.id.toString())) {
                option.disabled = true;
            }
            select.appendChild(option);
        });

        let input = document.createElement('input');
        input.type = 'text';
        input.classList.add('form-control', 'me-2');
        input.name = 'link[]';
        input.placeholder = 'https://www.instagram.com/vas_profil';
        input.required = true;

        let button = document.createElement('button');
        button.type = 'button';
        button.classList.add('btn', 'btn-danger', 'remove-social');
        button.innerHTML = '<i class="fas fa-times"></i>';
        button.addEventListener('click', function(event) {
            event.preventDefault();
            div.remove();
            filterAvailableSocials();
        });

        div.appendChild(select);
        div.appendChild(input);
        div.appendChild(button);
        
        // Přidat event listener
        select.addEventListener('change', function() {
            filterAvailableSocials();
        });
        
        // Vložíme nový řádek před tlačítko
        container.insertBefore(div, document.getElementById('add-social'));
        filterAvailableSocials();
    });

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-social') || 
            event.target.closest('.remove-social')) {
            event.preventDefault();
            let socialEntry = event.target.closest('.social-entry');
            if (socialEntry) {
                socialEntry.remove();
                filterAvailableSocials();
            }
        }
    });

    filterAvailableSocials();
    
    
    // Před odesláním formuláře - odstranit pouze prázdné řádky a zkontrolovat disabled optiony
    document.querySelector('form')?.addEventListener('submit', function(e) {
        // Nejdřív povolit všechny disabled optiony, aby se odeslaly
        document.querySelectorAll('select[name="social_id[]"] option').forEach(option => {
            option.disabled = false;
        });
        
        const socialEntries = document.querySelectorAll('.social-entry');
        
        socialEntries.forEach((entry) => {
            const select = entry.querySelector('select[name="social_id[]"]');
            const input = entry.querySelector('input[name="link[]"]');
            
            if (select && input) {
                const socialId = select.value;
                const link = input.value.trim();
                
                // Pokud je select prázdný NEBO link prázdný, odstranit celý řádek
                if (!socialId || socialId === '' || !link || link === '') {
                    entry.remove();
                }
            }
        });
    });
</script>

<!-- Přidání JavaScriptu pro náhled fotografií -->
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('preview-container');
        const existingPreview = document.querySelector('.preview-box');
        
        if (file) {
            // Skryj existující náhled
            if (existingPreview) {
                existingPreview.style.display = 'none';
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="p-3 rounded border text-center">
                        <p class="mb-2 text-success">Nová fotka:</p>
                        <div class="image-preview-container">
                            <img src="${e.target.result}" alt="Náhled" class="img-thumbnail shadow-sm" style="max-width: 100%; max-height: 300px;">
                        </div>
                    </div>`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
            // Znovu zobraz existující náhled
            if (existingPreview) {
                existingPreview.style.display = 'block';
            }
        }
    }
</script>