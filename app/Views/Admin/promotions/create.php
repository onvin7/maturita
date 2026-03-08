<section class="content-section">
    <div class="section-header">
        <h2><i class="fa-solid fa-bullhorn"></i> Vytvoření nové propagace</h2>
        <a href="/admin/promotions" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Zpět
        </a>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form action="/admin/promotions/store" method="post">
                <?= \App\Helpers\CsrfHelper::formInput() ?>
                <div class="mb-4">
                    <label for="article_id" class="form-label">Článek pro propagaci <span class="text-danger">*</span></label>
                    
                    <div class="input-group mb-3">
                        <input type="text" id="articleSearch" class="form-control" placeholder="Vyhledat článek...">
                        <button type="button" class="btn btn-primary" id="clearSearch">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    
                    <select name="article_id" id="article_id" class="form-select" required>
                        <option value="">Vyberte článek</option>
                        <?php foreach ($articles as $article): ?>
                            <option value="<?= $article['id'] ?>" data-title="<?= htmlspecialchars(strtolower($article['nazev'])) ?>">
                                <?= htmlspecialchars($article['nazev']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        <i class="fa-solid fa-info-circle me-1"></i> Propagace stejného článku může existovat vícekrát, ale ne ve stejný čas.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="start" class="form-label">Datum začátku <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <input type="date" name="start_date" id="start_date" class="form-control" required
                                  min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            <div class="form-text">Datum (min. dnešní datum)</div>
                        </div>
                        <div class="col-md-6">
                            <input type="time" name="start_time" id="start_time" class="form-control" required
                                  value="<?= date('H:i') ?>">
                            <div class="form-text">Čas (HH:MM)</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="end" class="form-label">Datum konce <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <input type="date" name="end_date" id="end_date" class="form-control" required
                                  min="<?= date('Y-m-d') ?>">
                            <div class="form-text">Datum</div>
                        </div>
                        <div class="col-md-6">
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                            <div class="form-text">Čas (HH:MM)</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-action">
                        <i class="fa-solid fa-save me-1"></i> Vytvořit propagaci
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Nastaví minimální koncové datum podle zvoleného datumu začátku
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Inicializace koncového datumu
    endDateInput.value = startDateInput.value;
    endDateInput.min = startDateInput.value;
    
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        
        // Pokud je aktuální koncové datum dřívější než počáteční, aktualizujeme ho
        if (endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });
    
    // Nastaví výchozí čas konce na o 1 hodinu více než čas začátku
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    function setDefaultEndTime() {
        const startTime = startTimeInput.value;
        if (startTime) {
            const [hours, minutes] = startTime.split(':').map(Number);
            let endHours = hours + 1;
            if (endHours >= 24) {
                endHours = 23;
                endTimeInput.value = `${endHours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            } else {
                endTimeInput.value = `${endHours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            }
        }
    }
    
    // Nastavení výchozího času konce při načtení stránky
    setDefaultEndTime();
    
    // Aktualizace času konce při změně času začátku
    startTimeInput.addEventListener('change', setDefaultEndTime);
    
    // Funkce pro vyhledávání článků
    const articleSearch = document.getElementById('articleSearch');
    const articleSelect = document.getElementById('article_id');
    const clearButton = document.getElementById('clearSearch');
    const articleOptions = Array.from(articleSelect.options).slice(1); // Bez první option "Vyberte článek"
    
    articleSearch.addEventListener('input', function() {
        const searchText = this.value.toLowerCase().trim();
        
        // Nejprve odstraníme všechny možnosti kromě první
        while (articleSelect.options.length > 1) {
            articleSelect.remove(1);
        }
        
        // Pokud je vyhledávací pole prázdné, zobrazíme všechny články
        if (searchText === '') {
            articleOptions.forEach(option => {
                articleSelect.add(option.cloneNode(true));
            });
        } else {
            // Jinak filtrujeme podle zadaného textu
            const filteredOptions = articleOptions.filter(option => 
                option.dataset.title.includes(searchText)
            );
            
            filteredOptions.forEach(option => {
                articleSelect.add(option.cloneNode(true));
            });
            
            // Pokud nejsou žádné výsledky, zobrazíme zprávu
            if (filteredOptions.length === 0) {
                const noResultOption = document.createElement('option');
                noResultOption.disabled = true;
                noResultOption.textContent = 'Žádné výsledky nenalezeny';
                articleSelect.add(noResultOption);
            }
        }
    });
    
    // Tlačítko pro vymazání vyhledávání
    clearButton.addEventListener('click', function() {
        articleSearch.value = '';
        // Vyvolá událost input, která znovu naplní select
        articleSearch.dispatchEvent(new Event('input'));
    });
});
</script>