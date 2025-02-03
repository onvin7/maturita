<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Registrace</h3>
                </div>
                <div class="card-body">
                    <form action="/register/submit" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Zadejte váš e-mail" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Jméno</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Zadejte vaše jméno" required>
                        </div>
                        <div class="mb-3">
                            <label for="surname" class="form-label">Příjmení</label>
                            <input type="text" name="surname" id="surname" class="form-control" placeholder="Zadejte vaše příjmení" required>
                        </div>
                        <div class="mb-3">
                            <label for="heslo" class="form-label">Heslo</label>
                            <input type="password" name="heslo" id="heslo" class="form-control" placeholder="Zadejte vaše heslo" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_heslo" class="form-label">Potvrzení hesla</label>
                            <input type="password" name="confirm_heslo" id="confirm_heslo" class="form-control" placeholder="Potvrďte vaše heslo" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrovat</button>
                    </form>
                    <div class="text-center mt-3 d-flex justify-content-center align-items-center">
                        <a href="/login" class="btn btn-link">Zpět na přihlášení</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>