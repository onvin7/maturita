<?php
use App\Helpers\FlashMessageHelper;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="flash-messages-container">
    <?= FlashMessageHelper::showIfSet('reset_error', 'error') ?>
    <?= FlashMessageHelper::showIfSet('reset_success', 'success') ?>
    <?= FlashMessageHelper::showIfSet('reset_info', 'info') ?>
</div>
    <div class="ohraniceni">
        <div class="logo">
            <img src="/assets/graphics/logo_text_cyklistickey.png" alt="Cyklistickey logo">
        </div>
        <div class="inputy">
            <form method="POST" action="/reset-password/submit" class="input-wrapper">
                <?= \App\Helpers\CsrfHelper::formInput() ?>
                <div class="prvek">
                    <span class="form-title">Reset hesla</span>
                </div>
                <div class="prvek">
                    <div class="input-group validator-msg-holder js-validated-element-wrapper">
                        <label class="input-group__label" for="email">Email</label>
                        <input id="email" class="form-control input-group__input" name="email" required="" type="email" placeholder="jsem@cyklistickey.cz" />
                    </div>
                </div>
                <input type="submit" value="Resetovat heslo">
                <div class="prvek">
                    <a href="/login">Zpět na přihlášení</a>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="/reset-password/submit"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Reset password form submit event triggered');
            console.log('Form method:', form.method);
            console.log('Form action:', form.action);
            console.log('Email value:', document.getElementById('email').value);
            
            // Zkontrolujeme HTML5 validaci
            if (!form.checkValidity()) {
                console.log('HTML5 validation failed');
                // Necháme browser zobrazit default validační hlášky
                return;
            }
            
            console.log('Form is valid, submitting...');
            // Necháme formulář se odeslat normálně
        });
    }
});
</script>
