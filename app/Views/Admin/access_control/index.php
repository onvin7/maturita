<section class="content-section">
    <div class="section-header">
        <h2><i class="fa-solid fa-shield-halved"></i> Správa přístupů</h2>
    </div>

    <form action="/admin/access-control/update" method="POST">
        <?= \App\Helpers\CsrfHelper::formInput() ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="artikly-thead text-center">
                    <tr>
                        <th>Stránka</th>
                        <th>Pisatel článků</th>
                        <th>Správce pisatelů</th>
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
                            <tr>
                                <td colspan="3" class="group-header">
                                    <i class="fa-solid fa-folder-open">&nbsp;</i>&nbsp;&nbsp;<?= htmlspecialchars(strtoupper($currentGroup)) ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <!-- Výpis stránky -->
                        <tr>
                            <td class="page-item">
                               <?= htmlspecialchars($page['page']) ?>
                            </td>
                            <td class="text-center">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="role_1[<?= htmlspecialchars($page['page']) ?>]" <?= $page['role_1'] ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="custom-checkbox">
                                    <input type="checkbox" name="role_2[<?= htmlspecialchars($page['page']) ?>]" <?= $page['role_2'] ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-action">
                <i class="fa-solid fa-save"></i> Uložit změny
            </button>
        </div>
    </form>
</section>