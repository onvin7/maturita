<?php
use App\Helpers\FlashMessageHelper;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Bezpečné získání tokenu z URL a zobrazení ve formuláři
$token = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';
?>
<div class="flash-messages-container">
    <?php if (empty($token)): ?>
        <?= FlashMessageHelper::display('error', 'Chybí token pro reset hesla!') ?>
    <?php endif; ?>
    <?= FlashMessageHelper::showIfSet('reset_error', 'error') ?>
    <?= FlashMessageHelper::showIfSet('reset_success', 'success') ?>
</div>
<div class="ohraniceni new">
    <div class="logo">
        <img src="/assets/graphics/logo_text_cyklistickey.png" alt="Cyklistickey logo">
    </div>
    <div class="inputy">
        <form method="POST" action="/reset-password/save" class="input-wrapper">
            <?= \App\Helpers\CsrfHelper::formInput() ?>
            <input type="hidden" name="token" value="<?= $token ?>">
            <div class="prvek" style="margin-top: 10px;">
                <span class="form-title">Obnova hesla</span>
            </div>
            <div class="prvek">
                <div class="input-group validator-msg-holder js-validated-element-wrapper">
                    <label class="input-group__label" for="new_password">NOVÉ HESLO</label>
                    <input id="new_password" class="form-control input-group__input" name="new_password" required="" type="password" placeholder="Nové heslo" />
                </div>
            </div>
            <div class="prvek">
                <div class="input-group validator-msg-holder js-validated-element-wrapper">
                    <label class="input-group__label" for="confirm_password">POTVRDIT HESLO</label>
                    <input id="confirm_password" class="form-control input-group__input" name="confirm_password" required="" type="password" placeholder="Potvrdit heslo" />
                </div>
            </div>
            <input type="submit" value="Změnit heslo">
            <div class="prvek">
                <a href="/login">Zpět na přihlášení</a>
            </div>
        </form>
    </div>
</div>

<!-- ✅ Jednoduchá JS validace pro kontrolu hesel -->
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            // Odstraníme existující chybové zprávy
            const container = document.querySelector('.flash-messages-container');
            if (container) {
                const existingAlerts = container.querySelectorAll('.alert');
                existingAlerts.forEach(alert => alert.remove());
                
                // Vytvoříme novou alert zprávu
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger alert-dismissible fade show';
                errorMsg.setAttribute('role', 'alert');
                errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Hesla se neshodují! <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>';
                
                // Vložíme zprávu do kontejneru nahoře
                container.appendChild(errorMsg);
            }
        }
    });
</script>