PRIORITNÍ PLÁN ÚKOLŮ (aktuální pořadí)

1) Bezpečnost a citlivá konfigurace
- [ ] Přesunout DB přístupové údaje z web/migrate_db.php do bezpečné konfigurace (.env / config) a odstranit je z repa
- [ ] Zajistit, že žádné secret klíče a přístupy nejsou v commitované historii
- [ ] Zkontrolovat logování, aby nelogovalo citlivé údaje

2) GDPR – Cookie Consent a gating trackingu
- [ ] Implementovat cookie banner (souhlas/nesouhlas, uložení do localStorage/cookie)
- [ ] Načítat Meta Pixel a Google Analytics pouze po souhlasu (gating v app/Views/Web/layouts/base.php)
- [ ] Aktualizovat privacy policy, doplnit informaci o anonymizaci IP v Click Trackingu

3) SEO – uvedení do provozu (Search Console, News)
- [ ] Ověřit doménu v Google Search Console
- [ ] Odeslat sitemap.xml do Search Console (a upravit robots.txt podle reálných sitemap)
- [ ] Doplňit skutečné GA ID do web/config/seo_config.json
- [ ] Základní monitoring indexace (1–2 týdny), řešení chyb v SC

4) Events systém (závody) – administrace + data
- [x] Upravit veřejné view sekce Events (rozdělení na Bav se sportem a Archiv)
- [x] Migrovat existující závody (cyklistickey/bezeckey) do DB
- [x] Upravit veřejné view, aby četlo data z DB (ne static PHP)
- [ ] Připravit admin CRUD pro závody (model, controller, views, routes)
- [ ] Volitelně odstranit staré race soubory po migraci

5) Ads management (reklamy)
- [ ] Doplnit chybějící komponenty (model, controller, views, upload validace, frekvence, fallback)
- [ ] Odkomentovat položku “Reklamy” v admin navbaru po dokončení
- [ ] Přidat přepínač pro Google Ads kód (v adminu, zobrazení v článcích)
- [ ] Ošetřit mazání (soubor + DB), access control

6) Migrace databáze – dokončení a validace
- [ ] Spustit kroky 1–10 v web/migrate_db.php postupně s limity
- [ ] Validovat integritu (počty, vazby, obsah), dořešit chybějící HTML a velké obsahy
- [ ] Dokončit migraci obrázků/uživatelů/audio a ověřit výstupy

7) CSRF ochrana v admin formulářích
- [ ] Vložit CSRF hidden token do všech admin formulářů (create/edit atd.)
- [ ] Validovat tokeny v kontrolerech u POST metod

8) Editor článků – vylepšení
- [x] Možnost přidat zdroj (autor/web) k obrázkům a galeriím
- [ ] LanguageTool API integrace (pokročilá gramatika volitelně)
- [ ] Frontend CSS pro galerie obrázků (2–4 vedle sebe) i mimo editor
- [ ] Volba “Otevřít odkaz v novém okně” s target="_blank" + rel="noopener"

9) Sitemap ekosystém – konsolidace
- [ ] Implementovat images/news sitemapy (nebo upravit robots.txt, aby odkazoval jen na existující sitemapy)
- [ ] Pravidelné generování a cache (kontrola velikosti, limitů)

10) RSS feed (SEO rozšíření)
- [ ] Vytvořit web/rss.php (generace posledních článků)
- [ ] Přidat odkaz do robots.txt a nahrát do Search Console (volitelně)

11) Veřejná viditelnost uživatelů – rozšíření
- [ ] Aktualizovat metody create() / createUser() v App\Models\User pro konzistenci public_visible
- [ ] UI v adminu: jasné přepínání viditelnosti a výchozí hodnoty

12) Text-to-Speech + automatizace (do budoucna)
- [ ] Vybrat TTS službu, navrhnout pipeline generace a uložení
- [ ] Přidat UI tlačítko “Přehrát audio” v článku, backfill pro existující obsah
- [ ] (Později) automatizace publikování/AI obsah

Poznámky:
- Pořadí je zvoleno tak, aby minimalizovalo rizika (bezpečnost, GDPR) a rychle uvedlo web do indexace (SEO), následně dokončilo klíčové doménové části (Events, Ads), a teprve pak rozšiřovalo funkcionality.
- Každý blok lze řešit iterativně (MVP → rozšíření), s průběžnou validací v produkčním prostředí.