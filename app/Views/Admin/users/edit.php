<?php
use App\Helpers\CSRFHelper;
?>
<section class="content-section">
    <div class="section-header">
        <h1 class="text-center mb-4"><i class="fas fa-user-edit me-2"></i><?= isset($user) ? 'Upravit uživatele' : 'Přidat nového uživatele' ?></h1>
        <div class="text-end">
            <a href="/admin/users" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Zpět</a>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    <form action="<?= isset($user) ? '/admin/users/update/' . htmlspecialchars($user['id']) : '/admin/users/store' ?>" method="POST">
        <?= \App\Helpers\CsrfHelper::formInput() ?>
        <div class="mb-3">
                            <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label"><i class="fas fa-signature me-2"></i>Jméno</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="surname" class="form-label"><i class="fas fa-signature me-2"></i>Příjmení</label>
                            <input type="text" class="form-control" id="surname" name="surname" value="<?= htmlspecialchars($user['surname'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label"><i class="fas fa-user-tag me-2"></i>Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="0" <?= isset($user['role']) && (int)$user['role'] === 0 ? 'selected' : '' ?>>Uživatel</option>
                                <option value="1" <?= isset($user['role']) && $user['role'] == 1 ? 'selected' : '' ?>>Moderátor</option>
                                <option value="2" <?= isset($user['role']) && $user['role'] == 2 ? 'selected' : '' ?>>Editor</option>
                                <option value="3" <?= isset($user['role']) && $user['role'] == 3 ? 'selected' : '' ?>>Administrátor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-eye me-2"></i>Veřejná viditelnost</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="public_visible" name="public_visible" value="1" role="switch" <?= isset($user['public_visible']) && (int)$user['public_visible'] === 1 ? 'checked' : (!isset($user['public_visible']) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="public_visible">
                                    Zobrazit uživatele v sekci redakce
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Pokud je vypnuto, uživatel nebude zobrazen v sekci redakce, ale jeho články a profil zůstanou přístupné.
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="editor" class="form-label"><i class="fas fa-pen me-2"></i>Popis</label>
                            <textarea id="editor" name="popis" lang="cs" spellcheck="true"><?= htmlspecialchars($user['popis'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/admin/users" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Zpět na seznam
                            </a>
                            <button type="submit" class="btn btn-action">
                                <i class="fas fa-save me-2"></i><?= isset($user) ? 'Uložit změny' : 'Přidat uživatele' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    #editor {
        min-height: 400px;
    }
    
    .tox-tinymce {
        border-radius: 0.25rem !important;
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
                editorElement.style.height = '750px';
                const editArea = document.querySelector('.tox-edit-area');
                if (editArea) {
                    editArea.style.height = '680px';
                }
            }
        }, 1000);
    });
</script>