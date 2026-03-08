<?php
use App\Helpers\FlashMessageHelper;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="flash-messages-container">
    <?= FlashMessageHelper::showIfSet('login_error', 'error') ?>
    <?= FlashMessageHelper::showIfSet('login_success', 'success') ?>
</div>
<form method="POST" action="/login/submit/">
    <?= \App\Helpers\CsrfHelper::formInput() ?>
    <div class="container">
        <div class="ohraniceni">
            <div class="logo"><img src="/assets/graphics/logo_text_cyklistickey.png" alt="Cyklistickey logo">
            </div>
            <div class="inputy">
                <div class="input-wrapper">
                    <div class="prvek">
                        <div class="input-group validator-msg-holder js-validated-element-wrapper">
                            <label class="input-group__label" for="email">EMAIL</label>
                            <input id="email" class="form-control input-group__input" name="email" required="" type="email" placeholder="jsem@cyklistickey.cz" />
                        </div>
                    </div>

                    <div class="prvek">
                        <div class="input-group validator-msg-holder js-validated-element-wrapper">
                            <label class="input-group__label" for="password">HESLO</label>
                            <input id="password" class="form-control input-group__input2" name="password" required="" type="password" placeholder="ta čo ja viem" />
                        </div>
                    </div>

                    <input type="submit" value="Přihlásit se">

                    <div class="prvek">
                        <a href="/reset-password">Zapomněl jsi heslo? Nechtěl bych...</a>
                    </div>
                    
                    <div class="prvek">
                        <a href="/register">Nemáš účet? Tak co tady děláš...</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>
