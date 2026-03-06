<script>
    document.addEventListener('DOMContentLoaded', function() {
        hideLoader();
    });

    function hideLoader() {
        document.getElementById("loader").style.opacity = "0";
        setTimeout(function() {
            document.getElementById("loader").style.display = "none";
        }, 500); // Předpokládá se půlsekundové zpoždění pro plynulý přechod opacity, před skrytím loaderu
    }
</script>
<script>
    // JavaScript a AJAX kód pro detekci kdy uživatel zavře stránku
    window.addEventListener("beforeunload", function(e) {
        // AJAXový požadavek pro zaznamenání odchodu uživatele
        // navigator.sendBeacon('templates/leave.php'); // Soubor neexistuje
    });
</script>
</head>

<body>
    <div id="loader" style="position: fixed; left: 0; top: 0; width: 100%; height: 100%; background-color: #f1f1f1; z-index: 999999999; transition: all .5s; filter: invert(1);">
        <img src="/assets/graphics/loader.gif" alt="Loading..." style="width: 600px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    </div>

    <div class="navbar">
        <a href="/">
            <img loading="lazy" src="/assets/graphics/logo.png" alt="logo">
        </a>
        <div class="inside-nav">
            <ul>
                <?php echo $links; ?>
            </ul>
        </div>
    </div>

    <div class="nav-mobile">
        <a href="/">
            <img loading="lazy" src="/assets/graphics/CYKLISTICKEY.png" alt="logo">
        </a>
        <div id='menu'>
            <div class='menu-line1'></div>
            <div class='menu-line2'></div>
        </div>
    </div>
    <div class='nav-page1'>
        <?php echo $links; ?>
    </div>
    <div class='nav-page2>'></div>

    <script>
        window.onload = () => {
            const $ = document.querySelector.bind(document);
            const $All = document.querySelectorAll.bind(document);
            const menu = $('#menu');
            const navMobile = $('.nav-mobile');
            const navPage1 = $('.nav-page1');
            const menuLine1 = $('.menu-line1');
            const menuLine2 = $('.menu-line2');
            
            menu.onclick = () => {
                menu.classList.toggle('rotate');
                navPage1.classList.toggle('transform');
                menuLine1.classList.toggle('rotate1');
                menuLine2.classList.toggle('rotate2');

                // Check if the rotate class is active and adjust the nav-mobile styles
                if (menu.classList.contains('rotate')) {
                    navMobile.style.backdropFilter = 'blur(0px)';
                    navMobile.style.backgroundColor = 'transparent';
                    navMobile.style.transition = '0.2s';
                } else {
                    // Reset the styles to default when rotate class is not active
                    navMobile.style.backdropFilter = '';
                    navMobile.style.backgroundColor = '';
                    navMobile.style.transition = '0.2s';
                }
            };
        };

        window.addEventListener('scroll', function() {
            var navbar = document.querySelector('.navbar');
            var navMobile = document.querySelector('.nav-mobile');
            var flashnews = document.querySelector('.marquees-wrapper');
            var scrollPosition = window.pageYOffset;
            
            // Výška flashnews
            var flashnewsHeight = flashnews ? flashnews.offsetHeight : 0;
            
            // Plynulý přechod navbaru - maximální pozice scrollu pro plný efekt
            var maxScroll = 45;
            
            if (scrollPosition <= maxScroll) {
                // Výpočet poměru posunutí
                var scrollRatio = scrollPosition / maxScroll;
                
                // Plynulá změna opacity pro flashnews
                if (flashnews) {
                    flashnews.style.opacity = 1 - scrollRatio;
                }
                
                // Plynulá změna pozice pro navbar - přidáme 1px navíc, aby se eliminovala mezera
                if (window.innerWidth > 930) { 
                    // Desktop verze
                    navbar.style.transform = 'translateY(-' + (flashnewsHeight * scrollRatio + 1) + 'px)';
                } else {
                    // Mobilní verze
                    navMobile.style.transform = 'translateY(-' + (flashnewsHeight * scrollRatio + 1) + 'px)';
                }
                
                // Odebíráme třídu scrolled pro jistotu
                navbar.classList.remove('scrolled');
                navMobile.classList.remove('scrolled');
            } else {
                // Pokud jsme za maximální pozicí, zafixujeme navbar nahoře
                if (window.innerWidth > 930) {
                    navbar.style.transform = 'translateY(-' + (flashnewsHeight + 1) + 'px)';
                } else {
                    navMobile.style.transform = 'translateY(-' + (flashnewsHeight + 1) + 'px)';
                }
                
                // Flashnews jsou zcela průhledné
                if (flashnews) {
                    flashnews.style.opacity = '0';
                }
                
                // Přidáme třídu scrolled pro dodatečné styly
                navbar.classList.add('scrolled');
                navMobile.classList.add('scrolled');
            }
        });
    </script>