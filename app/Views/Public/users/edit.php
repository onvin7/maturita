<h1>Edit User</h1>
<form method="post">
    <!-- Email -->
    <label for="email">Email:</label>
    <input
        type="email"
        name="email"
        id="email"
        value="<?= htmlspecialchars($user['email'] ?? '') ?>"
        required>

    <!-- Heslo (ponech prázdné, pokud neměníš) -->
    <label for="password">Password (leave blank to keep unchanged):</label>
    <input
        type="password"
        name="password"
        id="password">

    <!-- Jméno -->
    <label for="name">Name:</label>
    <input
        type="text"
        name="name"
        id="name"
        value="<?= htmlspecialchars($user['name'] ?? '') ?>"
        required>

    <!-- Příjmení -->
    <label for="surname">Surname:</label>
    <input
        type="text"
        name="surname"
        id="surname"
        value="<?= htmlspecialchars($user['surname'] ?? '') ?>"
        required>

    <!-- Role (Admin nebo ne) -->
    <label for="role">Admin (1 = Yes, 0 = No):</label>
    <input
        type="number"
        name="role"
        id="role"
        value="<?= htmlspecialchars($user['role'] ?? 0) ?>"
        required>

    <!-- Odeslat -->
    <button type="submit">Save</button>
</form>