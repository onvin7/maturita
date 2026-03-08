# TODO - Seznam úkolů a poznámek

---

## 🚀 ČÁST 1 - SPUŠTĚNÍ WEBU

**Cíl:** Dokončit všechny úkoly v této sekci, aby web mohl být spuštěn do produkce.

### 1. Migrace databáze - Převést ze staré databáze všechny data do nové databáze
- [ ] **Převést veškerá data ze staré databáze do nové struktury**
  - Převést ze staré databáze všechny data do nové databáze
  - Migrovat kategorie (nejdřív, protože na ně odkazují články)
  - Migrovat uživatele (s převodem admin→role)
  - Migrovat články (načíst obsah z HTML souborů `clanek_{id}.html`)
  - Migrovat vazby (`kategorie_clanku` → `clanky_kategorie`)
  - Migrovat propagace (`datum` → `zacatek`/`konec`)
  - Migrovat statistiky (`views_clanku`)
  - Migrovat reset hesel (pouze nevypršelé tokeny)
  - Zpracovat soubory (fotky, audio)
- [ ] **Otestovat migraci**
  - Ověřit, že všechna data jsou správně přenesena ze staré DB do nové DB
  - Zkontrolovat integritu dat
  - Otestovat funkčnost po migraci
  - Ověřit, že žádná data nechybí
- [ ] **Připravit dílčí migrační skripty po částech**
  - Vymyslet a nastavit způsob, jak migrovat jen vybrané entity (kategorie, uživatelé, články, vazby) samostatně
  - Pro každou část mít vlastní soubor/skript, který lze spustit odděleně
- **Soubor:** `web/migrate_db.php` (existuje, ale potřebuje dokončit/testovat)

### 2. Přesměrování starých URL (301) - Opravit staré odkazy/kolínky
- [x] **Implementovat 301 redirecty pro staré URL (kolínky)** - ✅ HOTOVO (Nutná kontrola)
  - Opravit staré odkazy/kolínky, aby se přesměrovávaly na novou strukturu
  - Zachovat SEO hodnotu starých odkazů z Google
  - Zachovat funkčnost starých sdílených odkazů
  - Mapovat staré URL na nové struktury
- [x] **Přidat redirecty do PHP routing** - ✅ HOTOVO (Nutná kontrola)
  - Staré URL struktury → nové URL struktury (/race → /events)
  - Všechny důležité stránky mají správný routing
  - RedirectHelper vytvořen v `app/Helpers/RedirectHelper.php`
  - Redirecty přidány do `web/index.php` (řádky 88-94)
- **Poznámka:** Redirecty jsou implementovány přes PHP routing, ne přes `.htaccess`

### 3. Odkaz na e-shop (hlavička + patička)
- [x] **Odkaz na e-shop v hlavičce** - ✅ HOTOVO
  - Odkaz je v `app/Views/Web/layouts/base.php` (řádek 235)
  - URL: `https://www.cycli.cz/vyhledavani?controller=search&s=cyklistickey`
  - Otevírá se v novém okně (`target="_blank"`)
- [x] **Odkaz na e-shop v patičce** - ✅ HOTOVO
  - Odkaz je v `app/Views/Web/layouts/base.php` (řádek 244)
  - Stejná URL jako v hlavičce

### 4. Odkaz na mobilní aplikaci (patička)
- [x] **Odkaz na mobilní aplikaci v patičce** - ✅ HOTOVO
  - Odkaz je v `app/Views/Web/layouts/base.php` (řádek 249)
  - URL: `/appka`
  - Stránka existuje: `app/Views/Web/home/appka.php`

### 5. Odstranit popup
- [x] **Zkontrolovat a odstranit rušivé popupy** - ✅ HOTOVO
  - UX zlepšení, odstranění rušivého elementu
  - Ověřeno: V kódu nebyly nalezeny žádné popupy v `app/Views/Web`
  - Žádné modální okna nebo popupy, které by rušily uživatelský zážitek

### 6. Přejmenování „race" na „events"
- [x] **Frontend routing** - ✅ HOTOVO
  - Route `/events` existuje v `web/index.php` (řádek 97)
  - Route `/events/(\d+)/([^/]+)` pro detail eventu (řádek 98)
- [x] **Backend metody** - ✅ HOTOVO
  - Metody `race()`, `raceCyklistickey()`, `raceBezeckey()` nyní obsahují 301 redirecty na `/events`
  - Metoda `events()` existuje a funguje
  - Staré metody zachovány pro zpětnou kompatibilitu s redirecty

### 7. Zobrazit historické + aktuální eventy v sekci Events
- [x] **Základní implementace** - ✅ HOTOVO
  - Metoda `events()` v `HomeController` zobrazuje eventy
  - View `app/Views/Web/events/index.php` existuje
- [x] **V events dát všechny historické eventy + aktuální** - ✅ HOTOVO
  - Zobrazují se historické eventy (2022, 2023) i aktuální (2024)
  - Přehledná sekce se starými i novými závody
  - Správně seřazené podle roku (nejnovější nahoře)
  - Jednotlivé závody + seriály závodů (CYCLI kritérium)

### 8. Flash news - Dát do kupy flash news aby fungovaly
- [x] **Správa Flash News v adminu** - ✅ HOTOVO
  - Controller: `app/Controllers/Admin/FlashNewsJSONAdminController.php`
  - Model: `app/Models/FlashNewsJSONSimple.php`
  - Views: `app/Views/Admin/flashnews/` (index, create, edit)
  - CRUD operace jsou implementovány
  - Dát do kupy flash news aby fungovaly správně
- [x] **Zobrazení Flash News na webu** - ✅ HOTOVO
  - View: `app/Views/Web/layouts/flash.php`
  - Zobrazuje se na všech stránkách (marquee efekt)
  - Správné zobrazování a administrace
  - Flash news fungují a zobrazují se správně

### 9. Automatický výběr kategorie při vytváření článku
- [x] **Přidat automaticky vybranou kategorii při vytváření článku** - ✅ HOTOVO
  - Když autor nevybere kategorii, automaticky se vybere kategorie "Aktuality" (ID: 1)
  - Upraveno v `app/Controllers/Admin/ArticleAdminController.php` (metoda `store`)
  - Každý článek má nyní alespoň jednu kategorii
- **Implementace:** Pokud `empty($postData['kategorie'])`, automaticky se přiřadí kategorie ID 1 ("Aktuality")

### 10. Odstranit kategorii „nevybráno"
- [x] **Odstranit kategorii "Nevybráno" z databáze** - ✅ HOTOVO (podle uživatele)
  - Úklid taxonomie, méně chyb
  - Ověřit, že žádné články nejsou přiřazeny k této kategorii
  - Pokud jsou, přesunout je do jiné kategorie nebo odstranit vazby
- **Poznámka:** Uživatel potvrdil, že je hotovo. Kategorie "Nevybráno" (id: 6, url: 'nevybrano') by již neměla existovat v databázi

### 11. Počítání prokliků v článku
- [x] **Click tracking systém** - ✅ HOTOVO (Nutná kontrola)
  - Controller: `app/Controllers/Web/LinkTrackingController.php`
  - Model: `app/Models/LinkClickEvent.php`
  - Helper: `app/Helpers/LinkTrackingHelper.php`
  - Tabulka: `link_click_events` (detailní tracking)
  - Tabulka: `link_clicks` (agregované statistiky)
  - Admin rozhraní: `app/Controllers/Admin/LinkClicksAdminController.php`
- [x] **Zjištění, na co lidi opravdu klikají** - ✅ HOTOVO (Nutná kontrola)
  - Sledování všech kliků na odkazy v článcích
  - Detailní metriky (IP, User Agent, Geolokace, čas, scroll, atd.)
- **Návrhy na vylepšení (volitelné):**
  - Asynchronní geolokace (neblokuje redirect)
  - Agregace dat pro rychlejší dotazy
  - Dashboard s grafy (Chart.js)
  - Export dat do CSV/Excel
  - Filtrování botů (přeskočit ukládání)
  - Rate limiting na IP adresu

### 12. Gramatická kontrola v editoru
- [x] **Základní kontrola pravopisu** - ✅ HOTOVO
  - SpellChecker (hunspell) implementován v `web/js/tinymce-config.js`
  - Tlačítka pro kontrolu pravopisu a odstranění zvýraznění
  - Klávesová zkratka Ctrl+Shift+S
- [ ] **Pokročilá gramatická kontrola** - ⚠️ ČÁSTEČNĚ
  - LanguageTool API pro pokročilou gramatickou kontrolu (volitelné vylepšení)
  - Současný stav: základní kontrola pravopisu funguje, pokročilá gramatika chybí
- **Soubor:** `web/js/tinymce-config.js` (základní kontrola je implementována)

### 13. Kompletní SEO (titles, popisky, AI modely, helm modely, indexace Google)
- [x] **Základní SEO implementace** - ✅ HOTOVO (Nutná kontrola)
  - SEOHelper: `app/Helpers/SEOHelper.php`
  - Structured Data pro všechny typy stránek
  - Open Graph a Twitter Cards
  - Canonical URL
  - Meta tagy (title, description, keywords)
  - Sitemap (hlavní, images, news)
  - Robots.txt optimalizace
- [ ] **Indexace Google** - ⚠️ ČÁSTEČNĚ
  - Udělat kompletní SEO, aby to vyšlo indexovat od Googlu
  - **Chybí:** Ověřit web v Google Search Console
  - **Chybí:** Odeslat sitemapy do Google Search Console
  - **Chybí:** Přidat do Google News Publisher Center
  - Zkontrolovat, že všechny stránky jsou indexovatelné
- [ ] **AI modely pro SEO** - ⚠️ ČÁSTEČNĚ
  - AISEOHelper existuje: `app/Helpers/AISEOHelper.php`
  - AISEOHelper je volitelně používán v SEOHelper (pokud existuje)
  - **Chybí:** Aktivní použití AISEOHelper ve všech kontrolerech pro optimalizaci
  - **Chybí:** Plná integrace pro všechny AI modely
- [x] **Helm modely (HTML head)** - ✅ HOTOVO (Nutná kontrola)
  - Kompletní SEO meta tagy v `app/Views/Web/layouts/base.php`
  - SEO pro všechny helm modely, aby to šlo najít co nejlíp

### 14. Ochrana & Soukromí (CSRF, GDPR, Config)
- [x] **CSRF Ochrana** - ✅ HOTOVO
  - Implementován `CsrfHelper` s metodami `generate`, `verify`, `formInput`
  - Ochrana přidána do `BaseAdminController` a specifických kontrolerů (Article, Category, User, Login)
  - Formuláře v adminu (create/edit) obsahují skrytý input s tokenem
- [x] **Bezpečná konfigurace** - ✅ HOTOVO
  - Vytvořen `config/db_credentials.php` (přidán do .gitignore)
  - Hesla odstraněna z `config/db.php` a `web/migrate_db.php`
  - Použití konstant pro připojení k DB
- [x] **GDPR Cookie Banner & Gating** - ✅ HOTOVO
  - Implementován `web/js/cookie-consent.js` pro správu souhlasů
  - Banner přidán do `app/Views/Web/layouts/base.php`
  - Tracking skripty (Pixel, GA) v `TrackingHelper` upraveny na `type="text/plain"`
  - Skripty se spouští až po udělení souhlasu
  - Respektuje preference uživatele (localStorage)

### 15. Integrace Meta Pixelu a SEO (jako v realitách)
- [x] **Meta Pixel (Facebook Pixel) tracking** - ✅ HOTOVO
  - Meta Pixel ID nastaveno v `web/config/seo_config.json` (1295970118998945)
  - Meta Pixel kód vložen do `app/Views/Web/layouts/base.php` (řádek 121-139)
  - Automatické trackování PageView na všech stránkách
  - TrackingHelper podporuje Meta Pixel generování
  - **Doporučení:** Ověřit funkčnost v Facebook Events Manager a pomocí Facebook Pixel Helper
  - **Doporučení:** Přidat custom eventy (ViewContent, Lead) pro lepší tracking
- [x] **SEO implementace (jako v realitách)** - ✅ HOTOVO
  - Kompletní SEO meta tagy
  - Structured Data
  - Sitemapy
  - Google Search Console připraveno (ale chybí ověření)
  - Stejné jako máš v realitách
- [x] **SEO konfigurace** - ✅ HOTOVO
  - SEO nastavení v `web/config/seo_config.json`
  - Stejné jako máš v realitách

---

## 🚀 ČÁST 2 - DO BUDOUCNA

**Cíl:** Rozšíření funkcionalit webu pro budoucí vylepšení. Tyto úkoly nejsou nutné pro spuštění webu, ale přidají hodnotu do budoucna.

### 1. Text to Speech pro články
- [ ] **Do budoucna přidat text to speech pro články**
  - Přidat text to speech pro články
  - Umožnit uživatelům poslouchat články místo čtení
  - Integrace text-to-speech API nebo služby
  - Přidat tlačítko "Přehrát audio" u každého článku
  - Možnosti: Web Speech API, Google Text-to-Speech, nebo jiná služba
- **Poznámka:** Do budoucna, není nutné pro spuštění webu

### 2. Automatizace na nové vydání - Video chat, AI generování článků
- [ ] **Do budoucna udělat automatizaci na nové vydání**
  - Automatizace na nové vydání
  - Video chat integrace pro rozhovory s autory/závodníky
  - AI napíše článek na web automaticky
  - Automatické generování obsahu z video rozhovorů
  - Možnosti:
    - Integrace s AI API (OpenAI, Claude, atd.) pro generování článků
    - Video chat pro rozhovory s autory/závodníky
    - Automatické publikování při novém vydání
    - Automatické generování obsahu z video rozhovorů
- **Poznámka:** Do budoucna, není nutné pro spuštění webu

---

## 🔴 Kritické úkoly

### 1. Veřejná viditelnost uživatelů
- [x] **Spustit SQL migraci** - ✅ HOTOVO
  - Soubor: `config/add_public_visible_column.sql`
  - Přidá sloupec `public_visible TINYINT(1) DEFAULT 1` do tabulky `users`
  - SQL migrace byla spuštěna

### 2. SEO - Google Search Console a Google News
- [ ] **Ověřit web v Google Search Console**
  - Přihlásit se do [Google Search Console](https://search.google.com/search-console)
  - Přidat property: `https://www.cyklistickey.cz` (nebo `bicenc.cyklistickey.cz` pokud ještě běží na testu)
  - Ověřit vlastnictví webu (doporučeno HTML tag metoda)
- [ ] **Odeslat všechny 3 sitemapy v Search Console:**
  - `https://www.cyklistickey.cz/sitemap.xml`
  - `https://www.cyklistickey.cz/sitemap-images.xml`
  - `https://www.cyklistickey.cz/sitemap-news.xml`
- [ ] **Přidat web do Google News Publisher Center**
  - Jít na [Google News Publisher Center](https://publishers.google.com/)
  - Přihlásit se se stejným Google účtem jako Search Console
  - Přidat web jako zdroj zpráv
  - Vyplnit informace: Název, URL, Jazyk (Čeština), Kategorie (Sport, Lifestyle), Popis
- [ ] **Zkontrolovat Google Analytics ID**
  - Zkontrolovat, zda je Google Analytics ID v `web/config/seo_config.json`
  - Pokud není, doplnit skutečné ID (nahradit "YOUR_GA_ID")
- [ ] **Testování sitemapů**
  - Otevřít v prohlížeči a zkontrolovat validitu XML všech 3 sitemapů
  - Zkontrolovat, že všechny URL jsou správné (https://, bez duplicit)
  - Zkontrolovat lastmod datumy
  - Zkontrolovat, že obrázky v image sitemap jsou přístupné
- [ ] **Monitoring**
  - Počkat 1-2 dny a zkontrolovat, zda Google našel všechny URL v Search Console
  - Zkontrolovat případné chyby a opravit je
  - Nastavit pravidelnou kontrolu Search Console (1x týdně)
  - Počkat 1-2 týdny na indexaci v Google News

### 3. Správa reklam
- [ ] **Odkomentovat menu** - Odkomentovat položku "Reklamy" v admin navbar (`app/Views/Admin/layout/navbar.php` - řádky 102-106)
- [ ] **Test upload obrázků** - Otestovat nahrávání obrázků reklam, validaci formátů (JPEG, PNG, GIF, WebP), maximální velikost souborů
- [ ] **Test zobrazení reklam** - Otestovat zobrazení v článcích (pozice po 2. odstavci a na konci), časové rozsahy
- [ ] **Test výchozí reklama** - Otestovat, že se zobrazí když nejsou aktivní reklamy, zkontrolovat že může být pouze jedna
- [ ] **Test frekvence** - Otestovat vážený výběr reklam podle frekvence (nižší frekvence = častěji)
- [ ] **Access Control** - Zkontrolovat, zda je potřeba přidat správu reklam do Access Control
- [ ] **DB migrace** - Vytvořit SQL migrační skript pro vytvoření tabulky `reklamy` (pro produkční nasazení)
- [ ] **Test mazání** - Otestovat, že se při mazání reklamy smaže i obrázek z disku
- [ ] **Reklamy v článcích** - Bannery/kódy vkládané přímo do obsahu článků (jako na starém webu)
- [ ] **Celoplošné reklamy** - Systém pro globální reklamy (header, footer, atd.) - NUTNÁ KONZULTACE S DOMINIKEM
- [ ] **Google Ads v článcích** - Místo cycle banneru dát Google Ads, ideálně v adminu nastavení
  - Přidat možnost vložit Google Ads kód místo banneru
  - Nastavení v admin panelu pro přepínání mezi bannery a Google Ads
- [ ] **Google Ads možnost pro Google** - Obecná integrace Google Ads (možná jiná než v článcích)

**Status:** Implementováno, ale zakomentováno v menu. Všechny součásti jsou hotové:
- ✅ Databázová tabulka `reklamy` v `config/db.sql`
- ✅ Model `app/Models/Ad.php`
- ✅ Controller `app/Controllers/Admin/AdAdminController.php`
- ✅ Views: `app/Views/Admin/ads/index.php`, `create.php`, `edit.php`
- ✅ Routes v `admin/index.php`
- ✅ Zobrazení reklam v `app/Views/Web/articles/article.php`
- ✅ Načítání reklam v `app/Controllers/Web/ArticleController.php`
- ✅ Upload adresář `web/uploads/ads/`

---

## 🟡 Důležité úkoly

### 4. Events systém (závody)
- [x] **Upravit sekci Závody** - ✅ HOTOVO
  - Zobrazit informaci: "Naše závody jsou nově pořádány pod hlavičkou našeho nového projektu Bav se sportem. Veškeré infomace najdeš zde."
  - Rozdělení na Hero sekci (Bav se sportem) a Archiv závodů (historie)
  - Admin rozhraní a migrace se ruší/odkládá.

### 5. Click Tracking - GDPR
- [x] **Implementovat anonymizaci IP adres** - ✅ HOTOVO
  - Anonymizace posledního oktetu pro IPv4 (192.168.1.123 → 192.168.1.0)
  - Anonymizace posledních 80 bitů pro IPv6 (poslední 5 hexadecimálních skupin)
  - IPAnonymizerHelper vytvořen: `app/Helpers/IPAnonymizerHelper.php`
  - LinkTrackingController upraven: `app/Controllers/Web/LinkTrackingController.php`
  - IP adresy se anonymizují před uložením do databáze
  - Geolokace stále používá původní IP pro přesnější výsledky
- [ ] **Cookie Consent Banner pro GDPR** - ⚠️ POTŘEBA DODĚLAT
  - Vytvořit cookie consent banner (informace o cookies, souhlas/nesouhlas)
  - Blokovat tracking skripty (Meta Pixel, Google Analytics) dokud uživatel nesouhlasí
  - Ukládat preferenci uživatele do cookie/localStorage
  - Implementovat JavaScript pro načtení trackingu až po souhlasu
  - Aktualizovat privacy policy o informace o anonymizaci IP adres v Click Trackingu

### 6. Migrace databáze
- [ ] **Poznámka:** Detailní úkoly migrace jsou v ČÁSTI 1, úkol 1 - "Migrace databáze - Převést ze staré databáze všechny data do nové databáze"
- [ ] Tento úkol je duplicitní s ČÁSTÍ 1, úkol 1 - viz tam pro detailní seznam úkolů

### 7. Audio soubory
- [x] Vytvořit skript `rename_audio_fuzzy.py` s fuzzy matching algoritmem
- [ ] **Spustit testovací režim** - `python rename_audio_fuzzy.py --limit 50 --dry-run`
- [ ] Ověřit výsledky testovacího režimu a upravit thresholdy pokud je potřeba
- [ ] Spustit produkční přejmenování - `python rename_audio_fuzzy.py` (nebo s `--limit` pro dávkové zpracování)
- [ ] Ověřit, že všechny audio soubory mají správný název `{id_clanku}.mp3`
- [ ] Zpracovat přeskočené soubory (ty, které nenašly shodu) - buď manuálně nebo upravit matching
- [ ] Vyřešit audio data v DB (import/uložení zpracovaných audio souborů do databáze)

### 8. Sekce redakce a uživatelé
- [x] **Vypnout sekci redakce** - Skrýt uživatele, kteří nejsou potřeba vidět - ✅ HOTOVO
  - ✅ Už implementováno: `public_visible` sloupec v databázi
  - ✅ Už implementováno: checkbox v admin formuláři
  - ✅ Zkontrolováno: sekce redakce filtruje pouze viditelné uživatele (`public_visible = 1`) - implementováno v `app/Models/User.php::getAll()`
- [x] **Výchozí avatar** - Kdo nemá fotku, tak nějakého avatara tam dát - ✅ HOTOVO
  - ✅ Výchozí avatar `noimage.png` je používán v `app/Views/Web/user/index.php` (řádek 12)
  - ✅ Zobrazení uživatelů používá výchozí avatar když nemají fotku
- [ ] **Aktualizovat stránku O nás** - Aktualizovat obsah stránky "O nás" - ⏳ ČEKÁ NA DOMINIKA
  - Zkontrolovat aktuální obsah
  - Aktualizovat text, informace o redakci, atd.

### 9. Editor článků - formátování
- [x] **Zdroj obrázků** - Přidat možnost uvést zdroj/autora fotky (vypíše se pod obrázkem)
- [ ] **Nadpisy 2x, Text** - Upravit editor, aby podporoval správné formátování nadpisů a textu
  - Možná jde o podporu H2 nadpisů a textu v editoru
  - Zkontrolovat TinyMCE konfiguraci
- [ ] **Obrázky dva vedle nebo víc/ šablona** - Přidat šablonu pro zobrazení více obrázků vedle sebe
  - Vytvořit layout/šablonu pro 2+ obrázky vedle sebe
  - Přidat možnost v editoru vybrat layout pro obrázky
- [ ] **Odkaz v novém okně** - Přidat možnost otevřít odkaz v novém okně
  - V editoru přidat checkbox "Otevřít v novém okně"
  - Automaticky přidat `target="_blank"` a `rel="noopener noreferrer"` k odkazům
- [x] **Varování před zavřením stránky při neuložených změnách** - ✅ HOTOVO
  - Implementován JavaScript modul `web/js/article-unsaved-warning.js` pro detekci neuložených změn
  - Při pokusu o zavření stránky se zobrazí standardní prohlížečový dialog, pokud jsou neuložené změny
  - Při kliknutí na tlačítko "Zpět" se zobrazí JavaScript `confirm()` dialog s varováním
  - Sleduje změny v: obsahu TinyMCE editoru, názvu článku, datu publikace, viditelnosti a vybraných kategoriích
  - Alert se nezobrazí po úspěšném odeslání formuláře
  - Integrováno do `app/Views/Admin/articles/create.php` a `app/Views/Admin/articles/edit.php`
  - **Jak to funguje:**
    - Při načtení stránky se uloží původní hodnoty všech polí formuláře (ihned pro ostatní pole, později pro editor)
    - Při pokusu o zavření stránky (beforeunload event) se porovnají aktuální hodnoty s původními
    - Při kliknutí na tlačítko "Zpět" se zobrazí confirm dialog - pokud uživatel klikne "Zrušit", navigace se zruší
    - Pokud jsou rozdíly, zobrazí se standardní prohlížečový dialog s varováním
    - Po odeslání formuláře se nastaví flag `formSubmitted`, který zabrání zobrazení alertu
    - HTML obsah editoru se normalizuje před porovnáním pro lepší detekci změn

### 10. Sociální sítě
- [x] **Soc site - jaký??** - Rozhodnout, které sociální sítě integrovat - ✅ HOTOVO (Nutná kontrola)
  - Instagram, Strava, Twitter/X, LinkedIn, Threads, Facebook
- [x] **Sociální sítě pro uživatele** - Přidat podporu pro: - ✅ HOTOVO (Nutná kontrola)
  - Instagram (ig) - ✅
  - Strava - ✅
  - Twitter/X - ✅
  - LinkedIn - ✅
  - Threads - ✅
  - Facebook - ✅
- [x] **Vytvořit databázovou strukturu pro sociální sítě uživatelů** - ✅ HOTOVO (Nutná kontrola)
  - Použity existující `socials` a `user_social` tabulky
  - Přidána pole do formuláře pro editaci uživatele (`app/Views/Admin/users/settings.php`)
  - Zobrazení ikon sociálních sítí na profilu uživatele (veřejná část)
  - Správa sociálních sítí v admin panelu (`/admin/social-sites`)
  - CRUD operace: UPDATE/INSERT/DELETE podle odeslaných dat
  - Font Awesome 6.5.1 integrace pro ikony

### 11. Páteční pětka (kategorie)
- [ ] Připravit kategorie/sekci pro "Páteční pětku" (název, URL, pořadí)
- [ ] Domluvit strukturu obsahu a jak se bude vkládat (články vs. speciální typ)
- [ ] Ujistit se, že existuje místo ve frontendu pro zobrazení a případně filtraci

---

## 🟢 Volitelné úkoly / Do budoucna

**Poznámka:** Text to Speech a Automatizace jsou v ČÁSTI 2 - DO BUDOUCNA

### 11. Automatické generování URL
- [ ] Automatické generování URL při vytváření článku
- [ ] (Z otazky.md - "do budoucna se to bude generovat automaticky pri vytvareni clanku")

### 13. RSS Feed (SEO)
- [ ] Implementovat RSS feed (`web/rss.php`) - naplánováno na později
- [ ] Přidat odkaz na RSS feed do robots.txt
- [ ] Přidat RSS feed do Search Console
- [ ] **Poznámka:** Není priorita, implementace bude později

### 14. Vícejazyčnost
- [ ] Plánovat možnost vícejazyčnosti
- [ ] (Z otazky.md - "teoreticky do budoucna, ale to tam nepis")

### 15. Click Tracking - rozšíření
- [ ] Grafy - vizualizace časových trendů
- [ ] Export dat - CSV/Excel export statistik
- [ ] Filtry - filtrování podle zařízení, země, atd.
- [ ] A/B testování - testování různých pozic odkazů
- [ ] E-mailové reporty - automatické týdenní/měsíční reporty
- [ ] Heatmapy - vizualizace kliků na stránce

### 16. Veřejná viditelnost uživatelů - rozšíření
- [ ] Aktualizovat metody `create()` a `createUser()` v `app/Models/User.php`
- [ ] Přidat podporu `public_visible` při vytváření nových uživatelů
- [ ] **Poznámka:** Není nutné, protože výchozí hodnota v databázi je `1` (viditelný)

---

## ✅ Dokončené úkoly

### Veřejná viditelnost uživatelů
- ✅ Vytvořen SQL migrační soubor (`config/add_public_visible_column.sql`)
- ✅ Přidán checkbox do admin formuláře (`app/Views/Admin/users/edit.php`)
- ✅ Upraven controller pro zpracování hodnoty (`app/Controllers/Admin/UserAdminController.php`)
- ✅ Upraven model pro uložení hodnoty (`app/Models/User.php` - metoda `update()`)
- ✅ Upravena metoda `getAll()` pro filtrování viditelných uživatelů

### Sitemap
- ✅ Implementován hlavní sitemap.xml (`web/sitemap.php`)
- ✅ Implementován image sitemap (`web/sitemap-images.php`)
- ✅ Implementován Google News sitemap (`web/sitemap-news.php`)
- ✅ Cache mechanismus (1 hodina)

### SEO Optimalizace
- ✅ Oprava noindex, nofollow (kritický problém)
- ✅ Konzistentní použití SEOHelper ve všech kontrolerech
- ✅ Rozšíření base.php layout o kompletní SEO meta tagy
- ✅ Rozšíření SEOHelper o nové metody (NewsArticle, ImageSchema, VideoSchema, EventSchema, atd.)
- ✅ Optimalizace robots.txt (přidány odkazy na všechny sitemapy)
- ✅ Optimalizace .htaccess (gzip, caching, security headers)
- ✅ Structured Data pro všechny typy stránek (Article, NewsArticle, Organization, WebSite, BreadcrumbList, Person, ContactPage)
- ✅ Open Graph a Twitter Cards vylepšení (kompletní meta tagy)
- ✅ Canonical URL optimalizace
- ✅ hreflang tags pro mezinárodní verze
- ✅ Image SEO (použití "velke" obrázků v sitemap-images.php)

### Click Tracking
- ✅ Implementován detailní tracking systém
- ✅ Vytvořena tabulka `link_click_events`
- ✅ Implementovány všechny metriky (IP, User Agent, Geolokace, atd.)
- ✅ Admin rozhraní pro zobrazení statistik

### Meta Pixel Tracking
- ✅ Implementován Meta Pixel (Facebook Pixel) tracking
- ✅ Meta Pixel ID nastaveno v `web/config/seo_config.json` (1295970118998945)
- ✅ Meta Pixel kód vložen do `app/Views/Web/layouts/base.php`
- ✅ Podmíněné vkládání (pouze když je tracking enabled a ID je nastavené)
- ✅ TrackingHelper podporuje Meta Pixel generování
- ✅ Automatické trackování PageView na všech stránkách

### 🎨 Design & UX Vylepšení (Galerie & Lightbox)
- ✅ **Oprava galerie** - Nový "Cool" Grid layout pro 1-5+ fotek, responzivita, ořez (object-fit: cover)
- ✅ **Oprava Lightboxu** - Zobrazení na výšku bez černých pruhů, fix scrollování stránky, overlay "+X", moderní UI prvky
- ✅ **Samostatné obrázky** - Oprava klikatelnosti a zobrazení v lightboxu pro obrázky v textu mimo galerie
- ✅ **Moderní Scrollbar** - Nový "Slim" design (10px), brandové barvy (tmavá + růžová při hoveru), plovoucí efekt

---

## 📝 Poznámky

### Migrace databáze
- **admin_access** - **NIKDY NEPŘEPISOVAT!** Už jsou tam vyplněné hodnoty
- **user_id u článků** - pokud uživatel neexistuje v nové DB, použít `0`
- **Podkategorie** - nepřenášejí se vůbec
- **Fotky** - tabulka se nepřenáší, zpracuje se později (soubory + zmenšení)
- **Audio** - tabulka se nepřenáší, zpracuje se později (soubory, přejmenování podle id_clanku)
- **Propagace** - `user_id` = `0`, `konec` = původní `datum`, `zacatek` = `datum` - 7 dní
- **Password resets** - nepřenášet vypršelé tokeny (`expires_at` < NOW())
- **Sociální sítě** - `ig`, `twitter`, `strava` se ignorují, vyřeší se později

### Sitemap
- Všechny URL musí být absolutní (s https://www.cyklistickey.cz)
- URL musí být kódované (htmlspecialchars)
- lastmod musí být ve formátu YYYY-MM-DD nebo ISO 8601
- Pro Google News: publication_date musí být ISO 8601 s časovou zónou
- Hlavní sitemap: max 50,000 URL, max 50MB
- Google News: max 1000 článků, pouze poslední 2-3 dny

### Click Tracking
- Používá se ip-api.com (free tier): Max 45 requestů/minutu
- Timeout 2 sekundy pro geolokaci
- Automatické ignorování lokálních IP adres
- Tichá chyba při selhání (neblokuje redirect)

---

## 🔗 Užitečné odkazy

- [Google Sitemap Guidelines](https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview)
- [Google News Sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap)
- [Image Sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps)
- [Google Search Console](https://search.google.com/search-console)
- [Google News Publisher Center](https://publishers.google.com/)

---

## 📋 Související soubory

- `SITEMAP_INSTRUKCE.md` - Detailní instrukce pro sitemap
- `migrace_mapovani.md` - Mapování migrace databáze
- `docs/click_tracking_implementation_summary.md` - Click tracking implementace
- `docs/click_tracking_metrics.md` - Dostupné metriky
- `dokumentace/events_system.md` - Systém pro správu závodů
- `rename_audio_fuzzy.py` - Skript pro přejmenování audio souborů
- `config/add_public_visible_column.sql` - SQL migrace pro veřejnou viditelnost
- `app/Models/Ad.php` - Model pro správu reklam
- `app/Controllers/Admin/AdAdminController.php` - Controller pro správu reklam
- `app/Views/Admin/ads/` - Views pro správu reklam v adminu

---

## ✅ Správa přístupů (Admin Access Control)

### Dokončené úkoly
- ✅ Navbar zobrazuje tlačítka podle oprávnění z databáze `admin_access`
- ✅ AccessControl model správně filtruje sekce podle role
- ✅ AuthMiddleware kontroluje přístup podle databáze
- ✅ Role 3 (Administrátor) má neomezený přístup ke všemu
- ✅ Role 1 a 2 mají omezený přístup podle databáze
- ✅ Flash News je automaticky jen pro admina (není v DB)
- ✅ Správa přístupů je jen pro admina
- ✅ Náhled článku používá admin navbar a veřejné CSS styly
- ✅ Tlačítka v seznamu článků jsou správně stylizovaná a vycentrovaná

### Poznámky
- **link-clicks** a **logs** nejsou v databázi `admin_access` → automaticky jen pro admina (role 3)
- **flashnews** není v databázi → automaticky jen pro admina (role 3)
- Kontrolery spoléhají na AuthMiddleware pro kontrolu přístupu (není potřeba explicitní kontrola v každém kontroleru)
- Podle databáze:
  - Role 1 (Moderátor): nemá přístup k `users`, `access-control`, `flashnews`, `link-clicks`, `logs`, `categories/create/edit/update/delete`, `users/edit/update/delete`
  - Role 2 (Editor): nemá přístup k `access-control`, `flashnews`, `link-clicks`, `logs`, `categories/create/edit/update/delete`, `users/edit/update/delete`
  - Role 3 (Administrátor): má přístup ke všemu

---

## 📌 SEO Implementace - Status

### ✅ Kompletně implementováno (kód) - OVĚŘENO NA SERVERU
- ✅ Oprava noindex, nofollow (kritický problém vyřešen)
- ✅ Konzistentní použití SEOHelper ve všech kontrolerech
- ✅ Rozšíření base.php layout o kompletní SEO meta tagy
- ✅ Rozšíření SEOHelper o nové metody
- ✅ Dynamický sitemap s cache (hlavní, images, news)
- ✅ Optimalizace robots.txt
- ✅ Optimalizace .htaccess
- ✅ Structured Data pro všechny typy stránek
- ✅ Open Graph a Twitter Cards
- ✅ Canonical URL optimalizace
- ✅ hreflang tags
- ✅ Image SEO (použití "velke" obrázků)

### ✅ Ověřeno na serveru bicenc.cyklistickey.cz (26.11.2025)
- ✅ **Hlavní stránka** (`/`) - Title: "cyklistika - Cyklistický magazín – Novinky, závody a... | Cyklistický magazín" - ✅ FUNGUJE
- ✅ **Kontakt** (`/kontakt/`) - Title: "kontakt - Kontakt | Cyklistický magazín | Cyklistický magazín" - ✅ FUNGUJE
  - Breadcrumbs: ✅ Zobrazeny (Domů > Kontakt)
  - Structured Data: ✅ ContactPage + Organization + BreadcrumbList
- ✅ **Events** (`/events/`) - Title: "events - Events | Cyklistický magazín | Cyklistický magazín" - ✅ FUNGUJE
  - Breadcrumbs: ✅ Zobrazeny (Domů > Event)
  - Structured Data: ✅ CollectionPage + BreadcrumbList
- ✅ **Kategorie** (`/category/aktuality/`) - Title: "kategorie - Aktuality | Cyklistický magazín | Cyklistický magazín" - ✅ FUNGUJE
- ⚠️ **Články** (`/article/{url}/`) - vrací 404 (možná jiná URL struktura nebo článek neexistuje)

**Poznámka:** SEO meta tagy fungují na správném testovacím serveru `bicenc.cyklistickey.cz`. Všechny stránky mají správné title tagy, breadcrumbs a structured data.

### ✅ Oprava title tagů (26.11.2025)
- ✅ **Opraveno:** `AISEOHelper::generateOptimizedTitle()` - odstraněna logika, která přidávala prefixy jako "kontakt -", "events -", "kategorie -" na začátek title tagů
- ✅ **Problém:** Title tagy měly prefixy (např. "kontakt - Kontakt | Cyklistický magazín"), což není ideální pro SEO
- ✅ **Řešení:** Odstraněna logika v `AISEOHelper`, která přidávala první keyword jako prefix. Title tagy jsou nyní správně nastavené v kontrolerech a nebudou se upravovat.
- ⏳ **Status:** Oprava je v kódu, po nahrání na server budou title tagy bez prefixů

### ⏳ Zbývá udělat (ruční nastavení)
- [ ] **Google Search Console** - ověření webu a odeslání sitemapů (viz sekce 2 výše)
- [ ] **Google News Publisher Center** - přidání webu (viz sekce 2 výše)
- [ ] **Google Analytics ID** - doplnit do `web/config/seo_config.json` (viz sekce 2 výše)
- [ ] **RSS Feed** - implementace naplánována na později (není priorita, viz sekce 10 výše)

**Poznámka:** Všechny technické úkoly jsou hotové a ověřené na serveru. Zbývá pouze ruční nastavení v Google nástrojích a doplnění Google Analytics ID.

---

## 🚀 SEO - Jak dostat web mezi co nejvíc lidí (Marketing & Obsah)

### 1. Google Search Console & Indexace
- [ ] **Ověřit web v Google Search Console**
  - Přihlásit se na https://search.google.com/search-console
  - Přidat property: `https://www.cyklistickey.cz` (nebo aktuální doménu)
  - Ověřit vlastnictví (HTML tag metoda - přidat meta tag do base.php)
- [ ] **Odeslat všechny 3 sitemapy:**
  - `https://www.cyklistickey.cz/sitemap.xml` (hlavní)
  - `https://www.cyklistickey.cz/sitemap-images.xml` (obrázky)
  - `https://www.cyklistickey.cz/sitemap-news.xml` (Google News)
- [ ] **Google News Publisher Center**
  - Přidat web na https://publishers.google.com/
  - Vyplnit: Název, URL, Jazyk (Čeština), Kategorie (Sport, Lifestyle)
  - Počkat 1-2 týdny na schválení
- [ ] **Google Analytics**
  - Doplnit skutečné Google Analytics ID do `web/config/seo_config.json`
  - Nahradit "YOUR_GA_ID" skutečným ID
  - Nastavit conversion tracking pro důležité akce

### 2. Obsah & Publikování
- [ ] **Pravidelné publikování článků**
  - Publikovat minimálně 2-3 články týdně
  - Konzistentní kvalita obsahu
  - Aktuální témata (závody, novinky, trénink)
- [ ] **Optimalizace článků pro SEO**
  - Každý článek má unikátní title (max 60 znaků)
  - Každý článek má unikátní description (max 160 znaků)
  - Používat relevantní keywords v obsahu
  - Interní propojování článků (odkazy na související články)
- [ ] **Obrázky v článcích**
  - Všechny obrázky mají alt text (popisný)
  - Obrázky jsou optimalizované (velikost, formát)
  - Používat relevantní názvy souborů

### 3. Sociální sítě & Sdílení
- [ ] **Automatické sdílení na sociální sítě**
  - Při publikaci nového článku automaticky sdílet na Facebook, Instagram, Twitter
  - Používat správné Open Graph obrázky (1200x630px)
  - Přidat tlačítka pro sdílení do článků
- [ ] **Newsletter**
  - Pravidelně posílat newsletter s novými články
  - Přidat CTA (Call To Action) na webu pro přihlášení k newsletteru
- [ ] **Křížové propojení**
  - Odkazy z Instagramu na články
  - Odkazy z Facebooku na články
  - Odkazy z YouTube na články

### 4. Backlinks & Spolupráce
- [ ] **Získat backlinky z jiných webů**
  - Kontaktovat cyklistické weby/blogy pro spolupráci
  - Guest posting na jiných webech
  - Spolupráce s cyklistickými značkami
- [ ] **Partnerské weby**
  - Odkazy z e-shopu (cycli.cz)
  - Odkazy z partnerských webů
  - Spolupráce s cyklistickými kluby

### 5. Lokální SEO
- [ ] **Google My Business** (pokud máš fyzickou lokaci)
  - Vytvořit/aktualizovat Google My Business profil
  - Přidat fotky, otevírací dobu, kontakt
- [ ] **Lokální klíčová slova**
  - Používat "cyklistika Praha", "cyklistika ČR" v článcích
  - Lokální eventy a závody

### 6. Technické optimalizace
- [ ] **Rychlost načítání**
  - Optimalizovat obrázky (WebP formát, lazy loading)
  - Minifikace CSS/JS
  - Caching (už je implementováno v .htaccess)
- [ ] **Mobile-first**
  - Zkontrolovat, že web je plně responzivní
  - Testovat na různých zařízeních
  - Google Mobile-Friendly test
- [ ] **HTTPS & Security**
  - Ověřit, že celý web běží na HTTPS
  - SSL certifikát je platný
  - Security headers (už jsou v .htaccess)

### 7. Monitoring & Analýza
- [ ] **Pravidelná kontrola Search Console**
  - Kontrolovat indexované stránky (1x týdně)
  - Kontrolovat chyby (404, crawl errors)
  - Kontrolovat performance (Core Web Vitals)
- [ ] **Analýza klíčových slov**
  - Zjistit, na jaká klíčová slova chceš být viditelný
  - Optimalizovat články pro tato klíčová slova
  - Sledovat pozice v Google (Google Search Console)
- [ ] **Competitor analysis**
  - Sledovat konkurenční weby
  - Zjistit, co dělají dobře
  - Inspirovat se (ne kopírovat)

### 8. Obsahové strategie
- [ ] **Long-tail keywords**
  - Psát články na specifická témata (např. "jak opravit řetěz na kole")
  - Méně konkurence, lepší pozice
- [ ] **FAQ sekce**
  - Přidat FAQ do článků (structured data FAQPage už je připraveno)
  - Odpovídat na časté otázky
- [ ] **Video obsah**
  - Přidat videa do článků (YouTube embed)
  - Video structured data (VideoObject schema už je připraveno)
  - Videa zvyšují čas na stránce

### 9. Email marketing
- [ ] **Newsletter signup**
  - Přidat prominentní CTA pro přihlášení k newsletteru
  - Pravidelně posílat novinky
  - Segmentace podle zájmů (závody, trénink, technika)

### 10. Community building
- [ ] **Komentáře pod články**
  - Povolit komentáře (zvyšuje engagement)
  - Moderovat komentáře
- [ ] **Uživatelské příspěvky**
  - Umožnit uživatelům posílat vlastní články/fotky
  - Uživatelské příběhy ze závodů

**Priorita:** Začít s body 1-3 (Google Search Console, obsah, sociální sítě) - to jsou nejdůležitější pro rychlý růst.

---

## 🔧 SEO - Co ještě potřebuje dodělat (kód)

### ✅ Všechny technické SEO úkoly jsou HOTOVÉ
- ✅ ArticleController::index() - má kompletní SEO
- ✅ HomeController - všechny metody používají SEOHelper::generateCanonicalUrl()
- ✅ UserController::userArticles() - má canonicalUrl i structuredData
- ✅ Breadcrumbs - zobrazeny v base.php (skryté CSS, ale structured data fungují)
- ✅ HomeController - všechny metody mají keywords i structuredData
- ✅ ArticleController - cesta k obrázku je správná (`/uploads/thumbnails/velke/`)
- ✅ Open Graph - všechny stránky mají kompletní OG tagy
- ✅ Twitter Cards - všechny stránky mají kompletní Twitter Card tagy
- ✅ Article schema - články mají article:section a article:tag
- ✅ NOINDEX - login/register/reset/404 mají noindex, nofollow

**Status:** Všechny technické SEO úkoly jsou dokončené! ✅