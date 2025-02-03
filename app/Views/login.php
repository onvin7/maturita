<?php
$disableNavbar = true;
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Přihlášení</h3>
                </div>
                <div class="card-body">
                    <form action="/login" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Heslo</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <div class="text-start">
                                <a href="/reset-password" class="btn-link">Zapomněli jste heslo?</a>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Přihlásit se</button>
                    </form>
                    <div class="text-center mt-3 d-flex justify-content-center align-items-center">
                        <p class="mb-0 me-2">Nemáš účet?</p>
                        <a href="/register" class="btn btn-success">Vytvoř si ho!</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>