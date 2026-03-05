<?php

use App\Helpers\TextHelper;

?>

<div class="container-zobrazit">

    <div id="lightbox">
        <button id="lightbox-close">&times;</button>
        <div id="lightbox-counter"></div>
        <div id="lightbox-content">
            <button id="lightbox-prev" class="lightbox-btn">&lt;</button>
            <img loading="lazy" src="#" id="lightbox-img" alt="Fullscreen image">
            <button id="lightbox-next" class="lightbox-btn">&gt;</button>
        </div>
        <div id="lightbox-thumbnails"></div>
    </div>

    <div class="foto-header">
        <img loading="lazy" class='parallax-image' width='100%' src='/uploads/thumbnails/velke/<?php echo !empty($article['nahled_foto']) ? $article['nahled_foto'] : 'noimage.png'; ?>' alt='<?php echo htmlspecialchars($article["nazev"], ENT_QUOTES); ?>'>
    </div>

    <div class="text">
        <div class="categories">
            <div class="output">
                <?php
                if (isset($article['kategorie']) && is_array($article['kategorie'])) {
                    foreach ($article['kategorie'] as $kategorie) {
                ?>
                    <div class='kategorie'>
                        <a id='kategorie' href='/category/<?php echo $kategorie['url']; ?>/'>
                            <p><?php echo htmlspecialchars($kategorie['nazev_kategorie']); ?></p>
                        </a>
                    </div>
                <?php 
                    }
                } 
                ?>
            </div>
        </div>

        <h1><?php echo htmlspecialchars($article["nazev"], ENT_QUOTES); ?></h1>
        <h3><?php echo \App\Helpers\TimeHelper::getRelativeTime($article["datum"], true); ?></h3>

        <?php if (isset($audioUrl) && $audioUrl): ?>
            <div class="prehravac">
                <audio controls>
                    <source src='<?php echo $audioUrl; ?>' type='audio/mpeg'>
                    Váš prohlížeč nepodporuje prvek audio.
                </audio>
            </div>
                    
        <?php endif; ?>

        <div class="text-editor">
            <?php
            if (isset($article['obsah'])) {
                // Zpracování embedů (Instagram, Twitter, Facebook, TikTok, YouTube)
                echo TextHelper::processEmbeds($article['obsah']);
            } else {
                include $emptyArticlePath;
            }
            ?>
        </div>
        <script>
            // Oprava relativních cest k obrázkům
            document.addEventListener('DOMContentLoaded', function() {
                const contentImages = document.querySelectorAll('.text-editor img');
                
                contentImages.forEach(function(img) {
                    const src = img.getAttribute('src');
                    
                    // Hledání /uploads/articles/ v cestě
                    if (src && src.includes('/uploads/articles/')) {
                        // Získáme část cesty začínající /uploads/articles/
                        const newSrc = '/uploads/articles/' + src.split('/uploads/articles/')[1];
                        img.setAttribute('src', newSrc);
                    }
                });
                
                // Načíst Instagram embed script, pokud je v obsahu Instagram embed
                const instagramEmbeds = document.querySelectorAll('.instagram-media, [data-instgrm-permalink], blockquote[data-instgrm-permalink], blockquote.instagram-media');
                if (instagramEmbeds.length > 0) {
                    // Zkontrolovat, jestli už není script načtený
                    const existingScript = document.querySelector('script[src*="instagram.com/embed.js"]');
                    if (!existingScript) {
                        const script = document.createElement('script');
                        script.src = 'https://www.instagram.com/embed.js';
                        script.async = true;
                        document.head.appendChild(script);
                        
                        // Počkat na načtení scriptu a pak inicializovat embed
                        script.onload = function() {
                            // Počkat ještě chvíli, aby Instagram script měl čas se inicializovat
                            setTimeout(function() {
                                if (window.instgrm && window.instgrm.Embeds) {
                                    window.instgrm.Embeds.process();
                                }
                            }, 500);
                        };
                    } else {
                        // Pokud už script existuje, zkusit znovu inicializovat po chvíli
                        setTimeout(function() {
                            if (window.instgrm && window.instgrm.Embeds) {
                                window.instgrm.Embeds.process();
                            }
                        }, 500);
                    }
                }
                
                // Načíst Twitter widgets script, pokud je v obsahu Twitter embed
                if (document.querySelector('.twitter-tweet')) {
                    if (!document.querySelector('script[src*="platform.twitter.com/widgets.js"]')) {
                        const script = document.createElement('script');
                        script.src = 'https://platform.twitter.com/widgets.js';
                        script.async = true;
                        script.charset = 'utf-8';
                        document.head.appendChild(script);
                    } else {
                        // Pokud už script existuje, zkusit znovu inicializovat
                        if (window.twttr && window.twttr.widgets) {
                            window.twttr.widgets.load();
                        }
                    }
                }

                // Načíst Facebook SDK, pokud je v obsahu Facebook embed
                if (document.querySelector('.fb-post, .fb-video')) {
                    // Přidat fb-root element, pokud neexistuje
                    if (!document.getElementById('fb-root')) {
                        const fbRoot = document.createElement('div');
                        fbRoot.id = 'fb-root';
                        document.body.prepend(fbRoot);
                    }

                    if (!document.getElementById('facebook-jssdk')) {
                        const script = document.createElement('script');
                        script.id = 'facebook-jssdk';
                        script.src = "https://connect.facebook.net/cs_CZ/sdk.js#xfbml=1&version=v18.0";
                        script.async = true;
                        script.defer = true;
                        script.crossOrigin = "anonymous";
                        document.head.appendChild(script);
                    } else {
                        if (window.FB) {
                            window.FB.XFBML.parse();
                        }
                    }
                }

                // Načíst TikTok embed script
                if (document.querySelector('.tiktok-embed')) {
                    if (!document.querySelector('script[src*="tiktok.com/embed.js"]')) {
                        const script = document.createElement('script');
                        script.src = 'https://www.tiktok.com/embed.js';
                        script.async = true;
                        document.head.appendChild(script);
                    }
                }

                // Načíst Reddit embed script
                if (document.querySelector('.reddit-card')) {
                    if (!document.querySelector('script[src*="embed.reddit.com/widgets.js"]')) {
                        const script = document.createElement('script');
                        script.src = 'https://embed.reddit.com/widgets.js';
                        script.async = true;
                        script.charset = 'UTF-8';
                        document.head.appendChild(script);
                    }
                }

                // Načíst Pinterest script
                if (document.querySelector('[data-pin-do]')) {
                    if (!document.querySelector('script[src*="assets.pinterest.com/js/pinit.js"]')) {
                        const script = document.createElement('script');
                        script.src = '//assets.pinterest.com/js/pinit.js';
                        script.async = true;
                        script.defer = true;
                        document.head.appendChild(script);
                    }
                }
            });
        </script>
    </div>
</div>
<script>
    $(document).ready(function() {
        var parallax = -0.3;
        var $parallaxImages = $(".parallax-image");
        var original_offsets = $parallaxImages.map(function() {
            return $(this).offset().top;
        });

        function updateParallax() {
            if ($(window).width() > 930) {
                var dy = $(window).scrollTop();
                $parallaxImages.each(function(i, el) {
                    var original_offset = original_offsets[i];
                    $(el).css("top", (original_offset + dy * parallax) + "px");
                });
            } else {
                $parallaxImages.each(function(i, el) {
                    $(el).css("top", original_offsets[i] + "px");
                });
            }
        }

        $(window).scroll(updateParallax);
        $(window).resize(updateParallax).trigger('resize');
    });

</script>


<div class="container-autor">
    <div class="text">
        <div class="image">
            <img loading="lazy" src="/uploads/users/thumbnails/<?php echo !empty($author['profil_foto']) ? $author['profil_foto'] : 'noimage.png'; ?>" alt="<?php echo htmlspecialchars($author['name'] . ' ' . $author['surname']); ?>">
        </div>
        <div class="container-popis">
            <div class="popis">
                <div>
                    <h5><?php echo isset($author["name"]) ? htmlspecialchars($author["name"], ENT_QUOTES) : ''; ?>&nbsp;<?php echo isset($author["surname"]) ? htmlspecialchars($author["surname"], ENT_QUOTES) : ''; ?>
                    </h5>
                    <h6><a href="mailto:<?php echo isset($author["email"]) ? htmlspecialchars($author["email"], ENT_QUOTES) : ''; ?>"><?php echo isset($author["email"]) ? htmlspecialchars($author["email"], ENT_QUOTES) : ''; ?></a>
                    </h6>
                </div>
                <p><?php echo isset($author['popis']) && is_string($author['popis']) ? TextHelper::truncate($author['popis'], 220) : ''; ?></p>
            </div>
        </div>
        <div class="odkaz">
            <a href="/user/<?php echo (isset($author['name']) ? TextHelper::generateFriendlyUrl($author['name']) : '') . "-" . (isset($author['surname']) ? TextHelper::generateFriendlyUrl($author['surname']) : ''); ?>/">
                <p>Zobrazit profil</p><i class="fa-solid fa-angle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="container-autor-mobile">
    <div class="text">
        <div class="fotka-text">
            <div>
                <div class="img" style="background-image: url('/uploads/users/thumbnails/<?php echo isset($author["profil_foto"]) && !empty($author["profil_foto"]) ? $author["profil_foto"] : 'noimage.png'; ?>')">
                </div>
            </div>
            <div class="container-popis">
                <div class="popis">
                    <div>
                        <h5><?php echo isset($author["name"]) ? htmlspecialchars($author["name"], ENT_QUOTES) : ''; ?><br><?php echo isset($author["surname"]) ? htmlspecialchars($author["surname"], ENT_QUOTES) : ''; ?>
                        </h5>
                        <h6><a href="mailto:<?php echo isset($author["email"]) ? htmlspecialchars($author["email"], ENT_QUOTES) : ''; ?>"><?php echo isset($author["email"]) ? htmlspecialchars($author["email"], ENT_QUOTES) : ''; ?></a>
                        </h6>
                    </div>

                </div>
            </div>
        </div>
        <p><?php echo isset($author['popis']) && is_string($author['popis']) ? TextHelper::truncate($author['popis'], 220) : ''; ?></p>
        <div class="odkaz">
            <a href="/user/<?php echo (isset($author['name']) ? TextHelper::generateFriendlyUrl($author['name']) : '') . "-" . (isset($author['surname']) ? TextHelper::generateFriendlyUrl($author['surname']) : ''); ?>/">
                <p>Zobrazit profil</p><i class="fa-solid fa-angle-right"></i>
            </a>
        </div>
    </div>
</div>



<div class="container-clanky">
    <?php if (is_array($relatedArticles) && !empty($relatedArticles)): ?>
        <?php foreach ($relatedArticles as $result): ?>
            <a href="/article/<?php echo ($result['url']); ?>/">
                <div class="card">
                    <img loading="lazy" src="/uploads/thumbnails/male/<?php echo !empty($result["nahled_foto"]) ? htmlspecialchars($result["nahled_foto"]) : 'noimage.png'; ?>" alt="<?php echo htmlspecialchars($result["nazev"]); ?>">
                    <div class="card-body">
                        <div class="kategorie">
                            <?php if (isset($result['kategorie']) && is_array($result['kategorie'])): ?>
                                <?php foreach ($result['kategorie'] as $kategorie): ?>
                                    <a href="/category/<?php echo htmlspecialchars($kategorie["url"]); ?>/">
                                        <p><?php echo htmlspecialchars($kategorie["nazev_kategorie"]); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="/articles/<?php echo $result['url']; ?>/">
                            <h5><?php echo htmlspecialchars($result["nazev"]); ?></h5>
                        </a>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    // Tracking času na stránce a scroll depth pro link clicks
    (function() {
        const pageLoadTime = Date.now();
        let maxScrollDepth = 0;
        let viewportWidth = window.innerWidth || document.documentElement.clientWidth;
        let viewportHeight = window.innerHeight || document.documentElement.clientHeight;
        
        // Trackování scroll depth
        function updateScrollDepth() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const documentHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrollPercent = documentHeight > 0 ? Math.round((scrollTop / documentHeight) * 100) : 0;
            maxScrollDepth = Math.max(maxScrollDepth, scrollPercent);
        }
        
        // Trackování změny velikosti viewportu
        function updateViewport() {
            viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            viewportHeight = window.innerHeight || document.documentElement.clientHeight;
        }
        
        // Přidání tracking parametrů ke všem tracking odkazům
        function enhanceTrackingLinks() {
            const trackingLinks = document.querySelectorAll('a[href^="/track/"]');
            
            trackingLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const timeOnPage = Math.round((Date.now() - pageLoadTime) / 1000); // v sekundách
                    const currentScrollDepth = maxScrollDepth;
                    
                    // Získáme původní href
                    const originalHref = link.getAttribute('href');
                    
                    // Přidáme tracking parametry
                    const separator = originalHref.includes('?') ? '&' : '?';
                    const enhancedHref = originalHref + separator + 
                        'time=' + encodeURIComponent(timeOnPage) + 
                        '&scroll=' + encodeURIComponent(currentScrollDepth) +
                        '&vw=' + encodeURIComponent(viewportWidth) +
                        '&vh=' + encodeURIComponent(viewportHeight);
                    
                    // Nastavíme nový href
                    link.setAttribute('href', enhancedHref);
                });
            });
        }
        
        // Inicializace
        window.addEventListener('scroll', updateScrollDepth, { passive: true });
        window.addEventListener('resize', updateViewport, { passive: true });
        
        // Po načtení DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', enhanceTrackingLinks);
        } else {
            enhanceTrackingLinks();
        }
        
        // Periodické aktualizace scroll depth (pro případ, že uživatel scrolluje rychle)
        setInterval(updateScrollDepth, 100);
    })();
</script>