<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ✅ Výchozí SEO hodnoty (přepíší se, pokud kontroler nastaví jiné) -->
    <?php
    $defaultTitle = "Cyklistický magazín – Novinky, závody a technika";
    $defaultDescription = "Sledujte nejnovější zprávy, tréninkové tipy, technické novinky a rozhovory ze světa cyklistiky.";
    $defaultOgImage = "https://www.cyklistickey.cz/assets/graphics/logo_text_cyklistickey.png";
    $defaultOgUrl = "https://www.cyklistickey.cz";
    ?>

    <!-- ✅ Dynamické SEO (pokud není nastavena proměnná, použije se výchozí hodnota) -->
    <?php
    use App\Helpers\SEOHelper;
    $seoTitle = SEOHelper::generateTitle($title ?? null, null, $keywords ?? []);
    $seoDescription = SEOHelper::generateDescription(null, $description ?? null, $keywords ?? []);
    $seoKeywords = !empty($keywords) ? SEOHelper::generateKeywords(null, $keywords) : SEOHelper::generateKeywords();
    $robotsMeta = SEOHelper::generateRobotsMeta();
    $canonicalUrlFinal = $canonicalUrl ?? SEOHelper::generateCanonicalUrl($canonicalPath ?? '');
    ?>
    <title><?= htmlspecialchars($seoTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seoKeywords) ?>">
    <meta name="robots" content="<?= htmlspecialchars($robotsMeta) ?>">
    <meta name="author" content="<?= htmlspecialchars(SEOHelper::getConfig()['site']['author']) ?>">

    <!-- Open Graph pro sociální sítě -->
    <?php
    $ogData = SEOHelper::generateOpenGraph(
        $ogTitle ?? $seoTitle,
        $ogDescription ?? $seoDescription,
        $ogImage ?? $defaultOgImage,
        $ogType ?? 'website',
        $ogUrl ?? $canonicalUrlFinal
    );
    $twitterData = SEOHelper::generateTwitterCard(
        $ogTitle ?? $seoTitle,
        $ogDescription ?? $seoDescription,
        $ogImage ?? $defaultOgImage
    );
    ?>
    <meta property="og:title" content="<?= htmlspecialchars($ogData['og:title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogData['og:description']) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogData['og:type']) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($ogData['og:url']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogData['og:image']) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($ogData['og:site_name']) ?>">
    <meta property="og:locale" content="<?= htmlspecialchars($ogData['og:locale']) ?>">
    <?php if (isset($ogImage) && $ogImage): ?>
    <meta property="og:image:width" content="<?= htmlspecialchars(SEOHelper::getConfig()['defaults']['image_width'] ?? '1200') ?>">
    <meta property="og:image:height" content="<?= htmlspecialchars(SEOHelper::getConfig()['defaults']['image_height'] ?? '630') ?>">
    <meta property="og:image:alt" content="<?= htmlspecialchars($seoTitle) ?>">
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= htmlspecialchars($twitterData['twitter:card']) ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($twitterData['twitter:title']) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($twitterData['twitter:description']) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($twitterData['twitter:image']) ?>">
    <?php if (!empty($twitterData['twitter:site'])): ?>
    <meta name="twitter:site" content="<?= htmlspecialchars($twitterData['twitter:site']) ?>">
    <?php endif; ?>
    <?php if (!empty($twitterData['twitter:creator'])): ?>
    <meta name="twitter:creator" content="<?= htmlspecialchars($twitterData['twitter:creator']) ?>">
    <?php endif; ?>
    <?php if (isset($ogImage) && $ogImage): ?>
    <meta name="twitter:image:alt" content="<?= htmlspecialchars($seoTitle) ?>">
    <?php endif; ?>
    
    <?php if (isset($articlePublishedTime)): ?>
    <meta property="article:published_time" content="<?= htmlspecialchars($articlePublishedTime) ?>">
    <?php endif; ?>
    <?php if (isset($articleModifiedTime)): ?>
    <meta property="article:modified_time" content="<?= htmlspecialchars($articleModifiedTime) ?>">
    <?php endif; ?>
    <?php if (isset($articleAuthor)): ?>
    <meta property="article:author" content="<?= htmlspecialchars($articleAuthor) ?>">
    <?php endif; ?>
    <?php if (isset($articleSection)): ?>
    <meta property="article:section" content="<?= htmlspecialchars($articleSection) ?>">
    <?php endif; ?>
    <?php if (isset($articleTags) && is_array($articleTags) && !empty($articleTags)): ?>
        <?php foreach ($articleTags as $tag): ?>
    <meta property="article:tag" content="<?= htmlspecialchars($tag) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ✅ Canonical URL -->
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrlFinal) ?>">
    
    <!-- hreflang tags -->
    <?php
    $hreflangData = SEOHelper::generateHreflangData($canonicalUrlFinal);
    foreach ($hreflangData as $lang => $url):
    ?>
    <link rel="alternate" hreflang="<?= htmlspecialchars($lang) ?>" href="<?= htmlspecialchars($url) ?>">
    <?php endforeach; ?>
    
    <!-- Preconnect pro externí zdroje -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tiny.cloud">
    <link rel="preconnect" href="https://connect.facebook.net">

    <!-- Favicon -->
    <link id="favicon" rel="icon" href="/assets/graphics/icon.ico" type="image/x-icon">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.5.1/css/all.css" crossorigin="anonymous" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">
    
    <script src="https://cdn.tiny.cloud/1/4zya77m9f7cxct4wa90s8vckad17auk31vflx884mx6xu1a3/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <?php
        $metaPixelId = \App\Helpers\TrackingHelper::getMetaPixelId();
        $trackingEnabled = \App\Helpers\TrackingHelper::isTrackingEnabled();

        if ($trackingEnabled && $metaPixelId && $metaPixelId !== 'YOUR_META_PIXEL_ID'):
    ?>
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?= htmlspecialchars($metaPixelId, ENT_QUOTES, 'UTF-8'); ?>');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=<?= rawurlencode($metaPixelId); ?>&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    <?php endif; ?>

    <?php
        if ($trackingEnabled) {
            echo \App\Helpers\TrackingHelper::generateGoogleAnalytics();
        }
    ?>

    <!-- ✅ Structured Data (JSON-LD) -->
    <?php if (isset($structuredData)): ?>
        <?php if (is_array($structuredData) && isset($structuredData[0])): ?>
            <!-- Multiple structured data -->
            <?php foreach ($structuredData as $schema): ?>
                <script type="application/ld+json">
                    <?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
                </script>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Single structured data -->
            <script type="application/ld+json">
                <?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        let originalTitle = <?php echo json_encode($title ?? $defaultTitle); ?>; // Bezpečně uloží původní titulek
        let newTitle = "\u{1F525} " + <?php echo json_encode($title ?? $defaultTitle); ?>; // Nový titulek s emoji
        let isChangingTitle = false; // Stav, jestli se má titulek měnit
        let titleInterval; // Proměnná pro interval
        let favicon = document.querySelector('#favicon');

        // Funkce pro změnu titulku
        function changeTitle() {
            document.title = document.title === originalTitle ? newTitle : originalTitle;
        }

        function changeFavicon(src) {
            if (favicon) {
                favicon.href = src;
            }
        }

        // Když uživatel opustí stránku
        window.onblur = function() {
            changeFavicon('/assets/graphics/logo2.png'); // Změní ikonu
            changeTitle(); // Okamžitě změní titulek při prvním blur
            if (!isChangingTitle) {
                titleInterval = setInterval(changeTitle, 2000); // Mění titulek každé 2 vteřiny
                isChangingTitle = true;
            }
        };

        // Když se uživatel vrátí na stránku
        window.onfocus = function() {
            changeFavicon('/assets/graphics/icon.ico'); // Vrátí původní ikonu
            if (isChangingTitle) {
                clearInterval(titleInterval); // Zastaví změnu titulku
                document.title = originalTitle; // Obnoví původní titulek
                isChangingTitle = false;
            }
        };
    </script>

    <link rel="stylesheet" href="/css/navbar-web.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="/css/breadcrumbs.css">

    <?php if (isset($css) && is_array($css)): ?>
        <?php foreach ($css as $i): ?>
            <link rel="stylesheet" href="/css/<?php echo $i; ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($script) && is_array($script)): ?>
        <?php foreach ($script as $i): ?>
            <link rel="stylesheet" href="/js/<?php echo $i; ?>.js">
        <?php endforeach; ?>
    <?php endif; ?>

</head>

<body>
    <?php
    function generateLinks($links)
    {
        $html = '';
        foreach ($links as $link) {
            $target = isset($link['target']) ? ' target="' . $link['target'] . '"' : '';
            $html .= '<li><a href="' . $link['url'] . '"' . $target . '>' . $link['text'] . '</a></li>';
        }
        return $html;
    }

    $links = generateLinks([
        ['url' => '/', 'text' => 'DOMŮ'],
        ['url' => 'https://www.cycli.cz/vyhledavani?controller=search&s=cyklistickey', 'text' => 'ESHOP', 'target' => '_blank'],
        ['url' => '/categories/', 'text' => 'KATEGORIE'],
        ['url' => '/authors/', 'text' => 'REDAKCE'],
        ['url' => '/events', 'text' => 'EVENTS'],
        ['url' => '/kontakt/', 'text' => 'O NÁS']
    ]);
    
    $footerLinks = generateLinks([
        ['url' => '/', 'text' => 'DOMŮ'],
        ['url' => 'https://www.cycli.cz/vyhledavani?controller=search&s=cyklistickey', 'text' => 'ESHOP', 'target' => '_blank'],
        ['url' => '/categories/', 'text' => 'KATEGORIE'],
        ['url' => '/authors/', 'text' => 'REDAKCE'],
        ['url' => '/events', 'text' => 'EVENTS'],
        ['url' => '/kontakt/', 'text' => 'O NÁS'],
        ['url' => '/appka', 'text' => 'APPKA']
    ]);
     
    include 'flash.php';

    include 'header.php';?>

    <main>
        <?php include $view; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <script async type="application/javascript" src="https://news.google.com/swg/js/v1/swg-basic.js"></script>

</body>

</html>