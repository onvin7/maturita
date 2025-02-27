<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Obnova hesla</h3>
                </div>
                <div class="card-body">
                    <form action="/reset-password" method="post">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']); ?>">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nové heslo</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Potvrďte nové heslo</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Změnit heslo</button>
                    </form>
                    <div class="text-center mt-3 d-flex justify-content-center align-items-center">
                        <a href="/login" class="btn btn-link">Zpět na přihlášení</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Jednoduchá JS validace pro kontrolu hesel -->
<script>
    function validatePasswords() {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorDiv = document.getElementById('password-error');

        if (password !== confirmPassword) {
            errorDiv.style.display = 'block';
            return false; // Zabrání odeslání formuláře
        } else {
            errorDiv.style.display = 'none';
            return true; // Povolení odeslání formuláře
        }
    }
</script>