<?php use App\Helpers\CsrfHelper; ?>
<section class="content-section">
    <div class="section-header">
        <h1 class="mb-4 text-center"><i class="fas fa-share-alt me-2"></i>Správa sociálních sítí</h1>
        <div class="text-end mb-3">
            <a href="/admin/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Zpět na uživatele
            </a>
        </div>
    </div>

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

    <!-- Formulář pro přidání nové sociální sítě -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i>Přidat novou sociální síť
        </div>
        <div class="card-body">
            <form action="/admin/social-sites/save" method="POST">
                <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate() ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nazev" class="form-label"><i class="fas fa-tag me-2"></i>Název</label>
                        <input type="text" class="form-control" id="nazev" name="nazev" placeholder="Např. Instagram, Facebook, X (Twitter)" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fa_class" class="form-label"><i class="fas fa-code me-2"></i>Font Awesome třída</label>
                        <input type="text" class="form-control" id="fa_class" name="fa_class" placeholder="Např. fab fa-instagram, fab fa-x-twitter" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i>Použijte Font Awesome ikonu (např. <code>fab fa-instagram</code>).
                        </small>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-action w-100">
                            <i class="fas fa-save me-2"></i>Přidat
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Seznam sociálních sítí -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-2"></i>Dostupné sociální sítě
        </div>
        <div class="card-body">
            <?php if (empty($socials)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Zatím nejsou přidané žádné sociální sítě.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="artikly-thead text-center">
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                <th><i class="fas fa-tag me-1"></i>Název</th>
                                <th><i class="fas fa-code me-1"></i>Font Awesome třída</th>
                                <th><i class="fas fa-eye me-1"></i>Náhled ikony</th>
                                <th><i class="fas fa-cogs me-1"></i>Akce</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php foreach ($socials as $social): ?>
                                <tr>
                                    <td><?= htmlspecialchars($social['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($social['nazev']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($social['fa_class']) ?></code></td>
                                    <td class="align-middle">
                                        <?php if (!empty($social['fa_class'])): ?>
                                            <div class="social-icon-preview" title="<?= htmlspecialchars($social['fa_class']) ?>">
                                                <i class="<?= htmlspecialchars($social['fa_class']) ?>" aria-hidden="true"></i>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="/admin/social-sites/delete/<?= htmlspecialchars($social['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Opravdu chcete smazat sociální síť <?= htmlspecialchars($social['nazev']) ?>? Tato akce může ovlivnit uživatele, kteří tuto síť používají.')">
                                            <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt me-1"></i>Smazat
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.social-icon-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin: 0 auto;
}

.social-icon-preview i {
    font-size: 2rem !important;
    color: #000000 !important;
    display: inline-block !important;
    font-style: normal !important;
    font-weight: normal !important;
    font-variant: normal !important;
    text-rendering: auto !important;
    line-height: 1 !important;
    -webkit-font-smoothing: antialiased !important;
    -moz-osx-font-smoothing: grayscale !important;
}

/* Font Awesome 5+ používá ::before pro ikony */
.social-icon-preview i::before {
    display: inline-block !important;
}
</style>

