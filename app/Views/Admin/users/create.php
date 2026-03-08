<?php
use App\Helpers\FlashMessageHelper;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="flash-messages-container">
    <?= FlashMessageHelper::showIfSet('registration_error', 'error') ?>
    <?= FlashMessageHelper::showIfSet('login_success', 'success') ?>
</div>
<div class="ohraniceni new">
    <div class="logo register">
        <img src="/assets/graphics/logo_text_cyklistickey.png" alt="Cyklistickey logo">
    </div>
    <div class="inputy">
        <form method="POST" action="/register/submit" enctype="application/x-www-form-urlencoded">
                <?= \App\Helpers\CsrfHelper::formInput() ?>
                <div class="prvek">
                    <span class="form-title">Registrace</span>
                </div>
                <div class="prvek">
                    <div class="input-group validator-msg-holder js-validated-element-wrapper">
                        <label class="input-group__label" for="email">EMAIL</label>
                        <input id="email" class="form-control input-group__input" name="email" required="" type="email"
                            placeholder="jsem@cyklistickey.cz" />
                    </div>
                </div>
                <div class="prvek">
                    <div class="input-group validator-msg-holder js-validated-element-wrapper">
                        <label class="input-group__label" for="name">JMÉNO</label>
                        <input id="name" class="form-control input-group__input" name="name" required="" type="text"
                            placeholder="Jsem" />
                    </div>
                </div>
                <div class="prvek">
                    <div class="input-group validator-msg-holder js-validated-element-wrapper">
                        <label class="input-group__label" for="surname">PŘÍJMENÍ</label>
                        <input id="surname" class="form-control input-group__input" name="surname" required=""
                            type="text" placeholder="Cyklistickey" />
                    </div>
                </div>
                <div class="prvek">
                    <div class="input-group validator-msg-holder js-validated-element-wrapper">
                        <label class="input-group__label" for="heslo">HESLO</label>
                        <input id="heslo" class="form-control input-group__input" name="heslo" required=""
                            type="password" placeholder="heslo1234" />
                    </div>
                </div>
                <div class="prvek">
                    <div class="input-group validator-msg-holder js-validated-element-wrapper">
                        <label class="input-group__label" for="confirm_heslo">POTVRDIT HESLO</label>
                        <input id="confirm_heslo" class="form-control input-group__input" name="confirm_heslo"
                            required="" type="password" placeholder="heslo1234" />
                    </div>
                </div>

                <input type="submit" value="Registrovat">

            <div class="prvek">
                <a href="/login">Zpět na přihlášení</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="/register/submit"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Register form submit event triggered');
            console.log('Form method:', form.method);
            console.log('Form action:', form.action);
            
            // Zkontrolujeme HTML5 validaci
            if (!form.checkValidity()) {
                console.log('HTML5 validation failed');
                // Necháme browser zobrazit default validační hlášky
                return;
            }
            
            // Kontrola shody hesel
            const password = document.getElementById('heslo').value;
            const confirmPassword = document.getElementById('confirm_heslo').value;
            
            if (password !== confirmPassword) {
                console.log('Password mismatch');
                e.preventDefault();
                // Odstraníme existující chybové zprávy
                const container = document.querySelector('.flash-messages-container');
                if (container) {
                    const existingAlerts = container.querySelectorAll('.alert');
                    existingAlerts.forEach(alert => alert.remove());
                    
                    // Vytvoříme novou flash zprávu
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger alert-dismissible fade show';
                    errorMsg.setAttribute('role', 'alert');
                    errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Hesla se neshodují! <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>';
                    
                    // Vložíme zprávu do kontejneru nahoře
                    container.appendChild(errorMsg);
                }
                return false;
            }
            
            console.log('Form is valid, submitting...');
            // Necháme formulář se odeslat normálně
        });
    }
});
</script>