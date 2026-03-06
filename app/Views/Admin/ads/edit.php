<section class="content-section">
    <div class="section-header">
        <h2><i class="fa-solid fa-ad"></i> Úprava reklamy</h2>
        <a href="/admin/ads" class="btn btn-secondary">
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
            <form action="/admin/ads/update/<?= $ad['id'] ?>" method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="nazev" class="form-label">Název reklamy <span class="text-danger">*</span></label>
                    <input type="text" name="nazev" id="nazev" class="form-control" required 
                           value="<?= htmlspecialchars($ad['nazev']) ?>"
                           placeholder="Např. Banner Cycli.cz">
                    <div class="form-text">Název pro identifikaci reklamy v adminu</div>
                </div>

                <div class="mb-4">
                    <label for="obrazek" class="form-label">Obrázek reklamy</label>
                    <div class="mb-2">
                        <img src="/uploads/ads/<?= htmlspecialchars($ad['obrazek']) ?>" 
                             alt="<?= htmlspecialchars($ad['nazev']) ?>" 
                             style="max-width: 300px; max-height: 200px; object-fit: contain; border: 1px solid #ddd; padding: 5px;">
                    </div>
                    <input type="file" name="obrazek" id="obrazek" class="form-control" accept="image/*">
                    <div class="form-text">Nahrajte nový obrázek pouze pokud chcete změnit současný. Podporované formáty: JPEG, PNG, GIF, WebP</div>
                    <div id="imagePreview" class="mt-2" style="display: none;">
                        <img id="previewImg" src="" alt="Náhled" style="max-width: 300px; max-height: 200px; object-fit: contain;">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="odkaz" class="form-label">Odkaz (URL) <span class="text-danger">*</span></label>
                    <input type="url" name="odkaz" id="odkaz" class="form-control" required 
                           value="<?= htmlspecialchars($ad['odkaz']) ?>"
                           placeholder="https://www.example.com">
                    <div class="form-text">URL adresa, na kterou má reklama odkazovat</div>
                </div>

                <?php
                $start = new DateTime($ad['zacatek']);
                $end = new DateTime($ad['konec']);
                ?>

                <div class="mb-4">
                    <label for="start" class="form-label">Datum začátku <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <input type="date" name="start_date" id="start_date" class="form-control" required
                                  value="<?= $start->format('Y-m-d') ?>">
                            <div class="form-text">Datum</div>
                        </div>
                        <div class="col-md-6">
                            <input type="time" name="start_time" id="start_time" class="form-control" required
                                  value="<?= $start->format('H:i') ?>">
                            <div class="form-text">Čas (HH:MM)</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="end" class="form-label">Datum konce <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <input type="date" name="end_date" id="end_date" class="form-control" required
                                  value="<?= $end->format('Y-m-d') ?>">
                            <div class="form-text">Datum</div>
                        </div>
                        <div class="col-md-6">
                            <input type="time" name="end_time" id="end_time" class="form-control" required
                                  value="<?= $end->format('H:i') ?>">
                            <div class="form-text">Čas (HH:MM)</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="frekvence" class="form-label">Frekvence zobrazování</label>
                    <input type="number" name="frekvence" id="frekvence" class="form-control" 
                           value="<?= $ad['frekvence'] ?>" min="1" required>
                    <div class="form-text">Jak často se má reklama zobrazovat (1 = vždy, vyšší hodnoty = méně často)</div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="aktivni" id="aktivni" value="1" 
                               <?= $ad['aktivni'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="aktivni">
                            Aktivní
                        </label>
                    </div>
                    <div class="form-text">Aktivní reklamy se zobrazují v článcích</div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="vychozi" id="vychozi" value="1"
                               <?= $ad['vychozi'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="vychozi">
                            Výchozí reklama
                        </label>
                    </div>
                    <div class="form-text">Výchozí reklama se zobrazí, pokud nejsou žádné aktivní reklamy v daném časovém rozsahu</div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-action">
                        <i class="fa-solid fa-save me-1"></i> Uložit změny
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Náhled obrázku
    const imageInput = document.getElementById('obrazek');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Nastaví minimální koncové datum podle zvoleného datumu začátku
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
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
    
    // Aktualizace času konce při změně času začátku
    startTimeInput.addEventListener('change', setDefaultEndTime);
});
</script>


