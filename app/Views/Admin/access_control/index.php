<div class="container mt-4">
    <h1 class="text-center mb-4">Správa přístupů</h1>
    <div class="d-flex justify-content-end mb-3">
    </div>

    <form action="/admin/access-control/update" method="POST">
        <table class="table table-bordered table-striped">
            <thead class="table-dark text-center">
                <tr>
                    <th>Stránka</th>
                    <th>Role 1</th>
                    <th>Role 2</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $currentGroup = null;
                $pages = $pages ?? []; // Zajištění existence proměnné
                ?>

                <?php foreach ($pages as $page): ?>
                    <?php
                    // Získání první části URI jako skupiny
                    $group = explode('/', $page['page'])[0];
                    if ($group !== $currentGroup):
                        $currentGroup = $group;
                    ?>
                        <!-- Oddíl pro skupinu -->
                        <tr class="table-primary">
                            <td colspan="3" class="fw-bold text-uppercase text-center py-2">
                                <?= htmlspecialchars($currentGroup) ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <!-- Výpis stránky -->
                    <tr>
                        <td><?= htmlspecialchars($page['page']) ?></td>
                        <td class="text-center">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" style="transform: scale(1.5); margin: 10px; background-color: <?= $page['role_1'] ? '#28a745' : '#dc3545'; ?>;" name="role_1[<?= htmlspecialchars($page['page']) ?>]" <?= $page['role_1'] ? 'checked' : '' ?> onchange="this.style.backgroundColor = this.checked ? '#28a745' : '#dc3545';">
                            </label>
                        </td>
                        <td class="text-center">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" style="transform: scale(1.5); margin: 10px; background-color: <?= $page['role_2'] ? '#28a745' : '#dc3545'; ?>;" name="role_2[<?= htmlspecialchars($page['page']) ?>]" <?= $page['role_2'] ? 'checked' : '' ?> onchange="this.style.backgroundColor = this.checked ? '#28a745' : '#dc3545';">
                            </label>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Uložit změny</button>
    </form>
</div>