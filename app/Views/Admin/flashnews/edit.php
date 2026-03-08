<?php
use App\Helpers\CsrfHelper;

$title = 'Upravit Flash News';
$css = ['admin'];

// Zajištění, že jsou proměnné definované
$flashNews = $flashNews ?? [];
$csrfToken = CsrfHelper::generate();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Upravit Flash News</h1>
                <a href="/admin/flashnews" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Zpět na seznam
                </a>
            </div>

            <!-- Error/Success zprávy -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informace o Flash News</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/admin/flashnews/update">
                                <input type="hidden" name="id" value="<?= $flashNews['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                
                                <div class="mb-3">
                                    <label for="title" class="form-label">Název <span class="text-danger">*</span></label>
                                    <textarea class="form-control" 
                                              id="title" 
                                              name="title" 
                                              rows="3" 
                                              maxlength="500" 
                                              required
                                              placeholder="Zadejte název flash news..."><?= htmlspecialchars($flashNews['title']) ?></textarea>
                                    <div class="form-text">
                                        <span id="charCount"><?= strlen($flashNews['title']) ?></span>/500 znaků
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Typ</label>
                                            <select class="form-select" id="type" name="type">
                                                <option value="custom" <?= $flashNews['type'] === 'custom' ? 'selected' : '' ?>>Vlastní</option>
                                                <option value="news" <?= $flashNews['type'] === 'news' ? 'selected' : '' ?>>Novinky</option>
                                                <option value="tech" <?= $flashNews['type'] === 'tech' ? 'selected' : '' ?>>Technologie</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label">Pořadí</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="sort_order" 
                                                   name="sort_order" 
                                                   value="<?= $flashNews['sort_order'] ?>"
                                                   min="0">
                                            <div class="form-text">Nižší číslo = vyšší priorita</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               <?= $flashNews['is_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            Aktivní (zobrazit na webu)
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Uložit změny
                                    </button>
                                    <a href="/admin/flashnews" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Zrušit
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informace o záznamu</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8"><?= $flashNews['id'] ?></dd>
                                
                                <dt class="col-sm-4">Vytvořeno:</dt>
                                <dd class="col-sm-8">
                                    <?= date('d.m.Y H:i', strtotime($flashNews['created_at'])) ?>
                                    <?php if ($flashNews['created_by_name']): ?>
                                        <br><small class="text-muted">od <?= htmlspecialchars($flashNews['created_by_name']) ?></small>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4">Aktualizováno:</dt>
                                <dd class="col-sm-8"><?= date('d.m.Y H:i', strtotime($flashNews['updated_at'])) ?></dd>
                                
                                <dt class="col-sm-4">Stav:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-<?= $flashNews['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $flashNews['is_active'] ? 'Aktivní' : 'Neaktivní' ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Nápověda</h5>
                        </div>
                        <div class="card-body">
                            <h6>Typy Flash News:</h6>
                            <ul class="list-unstyled">
                                <li><span class="badge bg-dark me-2">Vlastní</span> Vlastní obsah</li>
                                <li><span class="badge bg-info me-2">Novinky</span> Cyklistické novinky</li>
                                <li><span class="badge bg-secondary me-2">Technologie</span> Technické novinky</li>
                            </ul>
                            
                            <h6 class="mt-3">Pořadí:</h6>
                            <p class="small text-muted">
                                Flash news se zobrazují podle pořadí (nižší číslo = vyšší priorita). 
                                Pokud mají stejné pořadí, zobrazí se podle data vytvoření.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const charCount = document.getElementById('charCount');
    
    // Počítadlo znaků
    titleInput.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        
        if (count > 450) {
            charCount.classList.add('text-warning');
        } else {
            charCount.classList.remove('text-warning');
        }
        
        if (count >= 500) {
            charCount.classList.add('text-danger');
        } else {
            charCount.classList.remove('text-danger');
        }
    });
});
</script>
