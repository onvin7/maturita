<footer style="background-image: url('/assets/graphics/pozadi-footer.webp');">
    <div class="left-side">
        <div class="kontakt">
            <h2>kontaktujte nás</h2>
            <div class="radek">
                <a href="tel: +420 608 644 786"><i class="fa-solid fa-phone"></i>+420 608 644 786</a>
            </div>
            <div class="radek">
                <a href="mailto: jsem@cyklistickey.cz"><i class="fa-solid fa-envelope"></i>jsem@cyklistickey.cz</a>
            </div>
        </div>
        <div class="newsletter">
            <h2>odebírejte newsletter</h2>
            <div class="cist-clanek">
                <input type="email" placeholder="jsem@cyklistickey.cz" name="email">
            </div>
            <button>odebírat</button>
        </div>

    </div>
    <div class="right-side">
        <div class="site">
            <h2>spojte se s námi</h2>
            <div class="ikony">
                <a href="https://www.instagram.com/cyklistickey/" target="blank_"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@cyklistickey" target="blank_"><i class="fa-brands fa-tiktok"></i></a>
                <a href="https://www.youtube.com/@cyklistickey" target="blank_"><i class="fa-brands fa-youtube"></i></a>
                <a href="https://www.facebook.com/profile.php?id=100094700727442" target="blank_"><i class="fa-brands fa-facebook"></i></a>
            </div>
        </div>
        <div class="menu">
            <h2>menu</h2>
            <ul>
                <?php echo $footerLinks; ?>
            </ul>
        </div>

    </div>
</footer>

<div class="mobile-footer">
    <div class="site">
        <h2>spojte se s námi</h2>
        <div class="ikony">
            <a href="https://www.instagram.com/cyklistickey/" target="blank_"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@cyklistickey" target="blank_"><i class="fa-brands fa-tiktok"></i></a>
            <a href="https://www.youtube.com/@cyklistickey" target="blank_"><i class="fa-brands fa-youtube"></i></a>
            <a href="https://www.facebook.com/profile.php?id=100094700727442" target="blank_"><i class="fa-brands fa-facebook"></i></a>
        </div>
    </div>
    <hr>
    <div class="newsletter">
        <h2>odebírejte newsletter</h2>
        <div class="cist-clanek">
            <input type="email" placeholder="jsem@cyklistickey.cz" name="email">
        </div>
        <button>odebírat</button>
    </div>
    <hr>
    <div class="kontakt">
        <h2>kontaktujte nás</h2>
        <div class="radky">
            <div class="radek">
                <a href="tel: +420 608 644 786"><i class="fa-solid fa-phone"></i>+420 608 644 786</a>
            </div>
            <div class="radek">
                <a href="mailto: jsem@cyklistickey.cz"><i class="fa-solid fa-envelope"></i>jsem@cyklistickey.cz</a>
            </div>
        </div>
    </div>
    <hr>
    <div class="menu">
        <h2>menu</h2>
        <ul>
            <?php echo $footerLinks; ?>
        </ul>
    </div>
</div>
<div class="container-prava">
    <div class="prava">
        <p>Všechna práva vyhrazena. &copy; <?= date('Y') ?> Ondřej Vincenc</p>
    </div>
    <div class="prava">

        <p>
            <a href="/obchodni-podminky">Podmínky užívání</a> | <a href="/ochrana-osobnich-udaju">Ochrana osobních údajů</a>
        </p>

    </div>
</div>
