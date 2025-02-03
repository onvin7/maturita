<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Reset hesla</h3>
                </div>
                <div class="card-body">
                    <form action="/reset-password/submit" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Zadejte váš e-mail" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Resetovat heslo</button>
                    </form>
                    <div class="text-center mt-3 d-flex justify-content-center align-items-center">
                        <a href="/login" class="btn btn-link">Zpět na přihlášení</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>