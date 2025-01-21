<div class="container mt-4">
    <h1 class="text-center">Správa přístupů</h1>
    <form method="POST" action="/admin/access-control/update" class="mt-4">
        <div class="form-check form-check-inline mb-3">
            <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleAll(this)">
            <label class="form-check-label" for="selectAll">Vybrat vše</label>
        </div>

        <ul class="list-group">
            <!-- Strom pro úrovně uživatelů -->
            <li class="list-group-item">
                <strong>Admin</strong>
                <ul class="list-group mt-2">
                    <li class="list-group-item">
                        <input type="checkbox" name="access[admin][dashboard]" id="admin-dashboard">
                        <label for="admin-dashboard">Dashboard</label>
                    </li>
                    <li class="list-group-item">
                        <input type="checkbox" name="access[admin][users]" id="admin-users">
                        <label for="admin-users">Správa uživatelů</label>
                    </li>
                </ul>
            </li>
            <li class="list-group-item">
                <strong>Admin 2</strong>
                <ul class="list-group mt-2">
                    <li class="list-group-item">
                        <input type="checkbox" name="access[admin2][reports]" id="admin2-reports">
                        <label for="admin2-reports">Reporty</label>
                    </li>
                    <li class="list-group-item">
                        <input type="checkbox" name="access[admin2][settings]" id="admin2-settings">
                        <label for="admin2-settings">Nastavení</label>
                    </li>
                </ul>
            </li>
            <li class="list-group-item">
                <strong>Super Admin</strong>
                <ul class="list-group mt-2">
                    <li class="list-group-item">
                        <input type="checkbox" name="access[superadmin][logs]" id="superadmin-logs">
                        <label for="superadmin-logs">Logy</label>
                    </li>
                    <li class="list-group-item">
                        <input type="checkbox" name="access[superadmin][advanced]" id="superadmin-advanced">
                        <label for="superadmin-advanced">Pokročilé nastavení</label>
                    </li>
                </ul>
            </li>
        </ul>

        <button type="submit" class="btn btn-primary mt-3">Uložit změny</button>
    </form>
</div>

<script>
    // Funkce pro výběr všech checkboxů
    function toggleAll(source) {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = source.checked);
    }
</script>