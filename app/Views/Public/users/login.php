<h1>Přihlášení</h1>
<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error); ?></p>
<?php endif; ?>
<form method="POST">
    <label for="email">E-mail:</label>
    <input type="email" id="email" name="email" required>

    <label for="heslo">Heslo:</label>
    <input type="password" id="heslo" name="heslo" required>

    <button type="submit">Přihlásit</button>
</form>