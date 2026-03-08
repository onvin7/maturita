<?php
use App\Helpers\CsrfHelper;

$pageTitle = 'Správa Tracking Kódů';
$pageDescription = 'Správa Meta Pixel a Google Analytics tracking kódů';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Správa Tracking Kódů</h1>
                <div>
                    <button type="button" class="btn btn-info" onclick="testTracking()">
                        <i class="fas fa-test-tube"></i> Testovat Tracking
                    </button>
                </div>
            </div>

            <!-- Zobrazení chyb a úspěchů -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Konfigurace Tracking Kódů</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/admin/tracking/update">
                                <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate() ?>">
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enabled" name="enabled" 
                                               <?= ($trackingConfig['enabled'] ?? false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enabled">
                                            <strong>Povolit tracking</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Zapne/vypne všechny tracking kódy na webu
                                    </small>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <label for="meta_pixel_id" class="form-label">
                                        <i class="fab fa-facebook text-primary"></i> Meta Pixel ID
                                    </label>
                                    <input type="text" class="form-control" id="meta_pixel_id" name="meta_pixel_id" 
                                           value="<?= htmlspecialchars($trackingConfig['meta_pixel_id'] ?? '') ?>"
                                           placeholder="Např: 123456789012345">
                                    <small class="form-text text-muted">
                                        ID vašeho Meta Pixel účtu z Facebook Business Manager
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="google_analytics_id" class="form-label">
                                        <i class="fab fa-google text-success"></i> Google Analytics ID
                                    </label>
                                    <input type="text" class="form-control" id="google_analytics_id" name="google_analytics_id" 
                                           value="<?= htmlspecialchars($trackingConfig['google_analytics_id'] ?? '') ?>"
                                           placeholder="Např: G-XXXXXXXXXX">
                                    <small class="form-text text-muted">
                                        ID vašeho Google Analytics účtu (formát: G-XXXXXXXXXX)
                                    </small>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Uložit Konfiguraci
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Nápověda</h5>
                        </div>
                        <div class="card-body">
                            <h6>Meta Pixel</h6>
                            <p class="small">
                                1. Jděte do <a href="https://business.facebook.com" target="_blank">Facebook Business Manager</a><br>
                                2. Vytvořte nový Pixel<br>
                                3. Zkopírujte Pixel ID<br>
                                4. Vložte ho do pole výše
                            </p>

                            <h6>Google Analytics</h6>
                            <p class="small">
                                1. Jděte do <a href="https://analytics.google.com" target="_blank">Google Analytics</a><br>
                                2. Vytvořte nový datový zdroj<br>
                                3. Zkopírujte Measurement ID<br>
                                4. Vložte ho do pole výše
                            </p>

                            <h6>Testování</h6>
                            <p class="small">
                                Použijte tlačítko "Testovat Tracking" pro ověření, že jsou kódy správně nakonfigurovány.
                            </p>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Stav Tracking</h5>
                        </div>
                        <div class="card-body">
                            <div id="tracking-status">
                                <p class="text-muted">Klikněte na "Testovat Tracking" pro zobrazení stavu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testTracking() {
    fetch('/admin/tracking/test')
        .then(response => response.json())
        .then(data => {
            let statusHtml = '';
            
            // Meta Pixel status
            if (data.meta_pixel.valid && data.meta_pixel.enabled) {
                statusHtml += '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Meta Pixel: Aktivní</div>';
            } else if (data.meta_pixel.valid && !data.meta_pixel.enabled) {
                statusHtml += '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Meta Pixel: Nakonfigurován, ale vypnutý</div>';
            } else {
                statusHtml += '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Meta Pixel: Nenakonfigurován</div>';
            }
            
            // Google Analytics status
            if (data.google_analytics.valid && data.google_analytics.enabled) {
                statusHtml += '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Google Analytics: Aktivní</div>';
            } else if (data.google_analytics.valid && !data.google_analytics.enabled) {
                statusHtml += '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Google Analytics: Nakonfigurován, ale vypnutý</div>';
            } else {
                statusHtml += '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Google Analytics: Nenakonfigurován</div>';
            }
            
            document.getElementById('tracking-status').innerHTML = statusHtml;
        })
        .catch(error => {
            document.getElementById('tracking-status').innerHTML = 
                '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Chyba při testování</div>';
        });
}
</script>
