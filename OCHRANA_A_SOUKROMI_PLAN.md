# 🛡️ Ochrana & Soukromí (CSRF + GDPR + Bezpečná konfigurace)

Tento dokument slouží jako komplexní průvodce pro implementaci bezpečnostních prvků a ochrany soukromí uživatelů.

---

## 🔒 1. CSRF Ochrana (Cross-Site Request Forgery)

**Cíl:** Zabránit útočníkům v podvržení formulářů, které by admin nevědomky odeslal. Každý formulář musí obsahovat unikátní token, který server ověří.

### A. Backend - Generování a Validace
1.  **Vytvořit `app/Helpers/CsrfHelper.php`:**
    *   Metoda `generate()`: Pokud v `$_SESSION['csrf_token']` není token, vygeneruje nový (`bin2hex(random_bytes(32))`) a uloží ho.
    *   Metoda `verify($token)`: Porovná odeslaný token s tím v session.
    *   Metoda `field()`: Vrátí HTML input `<input type="hidden" name="csrf_token" value="...">`.

2.  **Middleware / BaseController:**
    *   V `app/Controllers/BaseAdminController.php` (nebo v každém admin controlleru zvlášť) přidat kontrolu v metodách, které zpracovávají POST requesty (`store`, `update`, `delete`, `save`).
    *   Pokud `CsrfHelper::verify($_POST['csrf_token'])` vrátí `false`, zastavit akci a vyhodit chybu "Neplatný bezpečnostní token".

### B. Frontend - Úprava formulářů
Projít všechny view soubory v `app/Views/Admin/` a přidat do každého `<form>` elementu volání helperu:

*   **Články:** `app/Views/Admin/articles/create.php`, `edit.php`
*   **Kategorie:** `app/Views/Admin/categories/index.php` (pokud tam je formulář), `create.php`, `edit.php`
*   **Uživatelé:** `app/Views/Admin/users/create.php`, `edit.php`, `settings.php`
*   **Reklamy:** `app/Views/Admin/ads/create.php`, `edit.php`
*   **Flash News:** `app/Views/Admin/flashnews/create.php`, `edit.php`
*   **Promotions:** `app/Views/Admin/promotions/create.php`
*   **Login:** `app/Views/Admin/login.php` (i přihlašovací formulář by měl být chráněn)

---

## 🍪 2. GDPR & Cookie Consent (Souhlas s cookies)

**Cíl:** Nespouštět sledovací skripty (Meta Pixel, Google Analytics) dříve, než uživatel udělí explicitní souhlas.

### A. Frontend - Cookie Banner
1.  **Vytvořit `web/js/cookie-consent.js`:**
    *   Zkontroluje `localStorage.getItem('cookie_consent')`.
    *   Pokud není nastaveno, zobrazí banner.
    *   Po kliknutí na "Souhlasím":
        *   Uloží `localStorage.setItem('cookie_consent', 'accepted')`.
        *   Spustí funkci `loadTrackingScripts()`.
        *   Skryje banner.
    *   Po kliknutí na "Odmítnout":
        *   Uloží `localStorage.setItem('cookie_consent', 'rejected')`.
        *   Skryje banner.
        *   Nespustí nic.

2.  **HTML Šablona (`app/Views/Web/layouts/base.php`):**
    *   Přidat HTML kód pro banner (fixní pozice dole, z-index vysoký).
    *   Text: "Používáme cookies k analýze návštěvnosti a personalizaci reklam. [Více informací](/privacy-policy)."
    *   Tlačítka: [Přijmout vše], [Odmítnout].

### B. Gating (Podmíněné spouštění)
1.  **Úprava `base.php`:**
    *   Najít existující kódy pro Meta Pixel a Google Analytics.
    *   Změnit je z `type="text/javascript"` na `type="text/plain"` a přidat `data-cookiecategory="analytics"`.
    *   Nebo je obalit do funkce `loadTrackingScripts()`, kterou zavolá až `cookie-consent.js`.

### C. Privacy Policy (Stránka Ochrana údajů)
1.  **Aktualizovat stránku:**
    *   Přidat sekci "Analytika a Cookies".
    *   Uvést, že používáme **Google Analytics** a **Meta Pixel**.
    *   Výslovně zmínit, že v rámci Click Trackingu (interní systém) provádíme **anonymizaci IP adres** (poslední oktet u IPv4, poslední část u IPv6) a neukládáme osobní údaje.

---

## 🔑 3. Bezpečná konfigurace (Citlivé údaje)

**Cíl:** Odstranit hesla k databázi z kódu, aby se nedostala do repozitáře.

### A. Konfigurační soubor
1.  **Vytvořit `config/db_credentials.php` (nebo `.env`):**
    *   Tento soubor bude obsahovat definice konstant: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
    *   **Důležité:** Tento soubor přidat do `.gitignore`.

### B. Úprava skriptů
1.  **Upravit `web/migrate_db.php`:**
    *   Místo natvrdo napsaných hesel (`$oldDb = ...`, `$newDb = ...`) načítat hodnoty z konfiguračního souboru.
    *   Příklad: `require_once __DIR__ . '/../config/db_credentials.php';`

2.  **Upravit `config/db.php`:**
    *   Zajistit, aby i hlavní připojení k DB používalo tyto bezpečné konstanty/proměnné.

---

## 📅 Plán implementace

1.  **Krok 1 (Bezpečnost):** Vytvořit `CsrfHelper`, přidat ho do `BaseAdminController` a upravit formuláře v `articles/create.php` (jako test). Poté rozšířit na zbytek.
2.  **Krok 2 (Config):** Vytvořit `db_credentials.php`, přesunout tam hesla a upravit `migrate_db.php`.
3.  **Krok 3 (GDPR):** Vytvořit JS pro banner, přidat HTML do `base.php` a otestovat, že se Pixel nenačte bez souhlasu.

---

### Poznámky pro vývojáře
*   **CSRF Token expirace:** Token by měl mít platnost po dobu session. Při odhlášení se musí zneplatnit (regenerovat).
*   **Cookie Lišta UX:** Musí být neinvazivní, ale viditelná. Barvy sladit s designem webu (tmavý režim).
*   **Testování:**
    *   Zkusit odeslat formulář bez tokenu -> Musí vyhodit chybu.
    *   Zkusit otevřít web v anonymním okně -> Musí vyskočit lišta -> Pixel se nenačte. Po souhlasu -> Pixel se načte.
