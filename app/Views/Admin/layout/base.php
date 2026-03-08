<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= \App\Helpers\CsrfHelper::generate() ?>">
    <title><?= htmlspecialchars($adminTitle ?? 'Admin Panel - Cyklistickey magazín') ?></title>
    <?php if (!isset($disableBootstrap) || !$disableBootstrap): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.5.1/css/all.css" crossorigin="anonymous" />
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <link href="/css/admin-dashboard.css" rel="stylesheet">

    <?php if (isset($css) && is_array($css)): ?>
        <?php foreach ($css as $i): ?>
            <link rel="stylesheet" href="/css/<?php echo $i; ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/l1vyo5rc4lr9bndoweby2luoq845e7lw20i4gb1rtwn0xify/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    
    <!-- SpellChecker pro kontrolu pravopisu -->
    <script src="/js/spellchecker.js"></script>
    
    <!-- Jednoduchá TinyMCE konfigurace s vestavěnou kontrolou pravopisu -->
    <script src="/js/tinymce-simple.js"></script>

</head>

<body>
    <?php if (!isset($disableNavbar) || !$disableNavbar): ?>
        <?php include 'navbar.php'; ?>
    <?php endif; ?>
    <?php if (isset($useFullWidth) && $useFullWidth): ?>
        <?php include $view; ?>
    <?php else: ?>
        <div class="container">
            <?php include $view; ?>
        </div>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>