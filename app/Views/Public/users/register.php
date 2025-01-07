<div class="container mt-4">
    <h1 class="text-center mb-4">Registrace</h1>
    <form action="/register/store" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Jméno</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="surname" class="form-label">Příjmení</label>
            <input type="text" class="form-control" id="surname" name="surname" required>
        </div>
        <div class="mb-3">
            <label for="popis" class="form-label">Popis</label>
            <textarea class="form-control" id="popis" name="popis"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Registrovat</button>
    </form>
</div>