<section class="content-section">
    <div class="section-header">
        <h2><i class="fa-solid fa-ad"></i> Správa reklam</h2>
        <a href="/admin/ads/create" class="btn btn-action">
            <i class="fa-solid fa-plus"></i> Vytvořit reklamu
        </a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (empty($ads)): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-info-circle me-2"></i> Momentálně nejsou žádné reklamy.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Náhled</th>
                        <th>Název</th>
                        <th>Odkaz</th>
                        <th>Začátek</th>
                        <th>Konec</th>
                        <th>Stav</th>
                        <th>Frekvence</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ads as $ad): 
                        $start = new DateTime($ad['zacatek']);
                        $end = new DateTime($ad['konec']);
                        $now = new DateTime();
                        
                        $isActive = $ad['aktivni'] == 1;
                        $isCurrent = $start <= $now && $end >= $now;
                        $isUpcoming = $start > $now;
                        $isPast = $end < $now;
                    ?>
                        <tr>
                            <td>
                                <img src="/uploads/ads/<?= htmlspecialchars($ad['obrazek']) ?>" 
                                     alt="<?= htmlspecialchars($ad['nazev']) ?>" 
                                     style="max-width: 100px; max-height: 60px; object-fit: contain;">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($ad['nazev']) ?></strong>
                                <?php if ($ad['vychozi']): ?>
                                    <span class="badge bg-primary ms-1">Výchozí</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= htmlspecialchars($ad['odkaz']) ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 200px;">
                                    <?= htmlspecialchars($ad['odkaz']) ?>
                                </a>
                            </td>
                            <td><?= $start->format('d.m.Y H:i') ?></td>
                            <td><?= $end->format('d.m.Y H:i') ?></td>
                            <td>
                                <?php if ($isCurrent && $isActive): ?>
                                    <span class="badge bg-success">Aktivní</span>
                                <?php elseif ($isUpcoming): ?>
                                    <span class="badge bg-warning">Nadcházející</span>
                                <?php elseif ($isPast): ?>
                                    <span class="badge bg-secondary">Ukončeno</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Neaktivní</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $ad['frekvence'] ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/admin/ads/edit/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary" title="Upravit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <a href="/admin/ads/toggle-active/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-<?= $isActive ? 'warning' : 'success' ?>" title="<?= $isActive ? 'Deaktivovat' : 'Aktivovat' ?>">
                                        <i class="fa-solid fa-<?= $isActive ? 'eye-slash' : 'eye' ?>"></i>
                                    </a>
                                    <?php if (!$ad['vychozi']): ?>
                                        <a href="/admin/ads/set-default/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-info" title="Nastavit jako výchozí">
                                            <i class="fa-solid fa-star"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/admin/ads/delete/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Opravdu chcete smazat tuto reklamu?')" title="Smazat">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>


