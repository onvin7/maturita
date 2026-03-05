# 🚀 LAUNCH CHECKLIST - Doporučený postup

Seřazeno logicky od vývoje funkcí, přes nastavení, až po finální migraci dat.

## 🛠️ FÁZE 1: DOKONČENÍ FUNKCIONALIT (Code Freeze)
*Nejdříve doděláme funkce, aby byl systém připravený na data.*

### 1. Editor Článků (Vylepšení pro redaktory)
- [ ] **Formátování textu** (Podpora H2, H3 nadpisů v editoru)
- [x] **Obrázky** (Šablona pro vložení 2 a více obrázků vedle sebe) - ✅ HOTOVO
- [ ] **Odkazy** (Přidat checkbox "Otevřít v novém okně" `target="_blank"`)
- [ ] **Automatické generování URL** (Slug z nadpisu při psaní)
- [x] **Kontrola gramatiky** (Tlačítko pro spuštění kontroly, přesné a funkční pro češtinu) - ✅ HOTOVO
- [x] **Vylepšení Toolbaru** (Ikony místo textu, smazání Iframe, přejmenování Embed) - ✅ HOTOVO

### 2. Správa Reklam (Monetizace)
- [ ] **Aktivovat v Adminu** (Odkomentovat v `navbar.php`)
- [ ] **Databáze** (Spustit SQL migraci tabulky `reklamy`)
- [ ] **Testování** (Upload, zobrazení v článku, výchozí reklama)

### 3. Events Systém (Závody)
- [ ] **Admin rozhraní** (CRUD pro správu závodů)
- [ ] **Frontend** (Upravit šablony, aby četly z DB místo PHP souborů)
- [ ] *Pozn: Migrace dat závodů proběhne až ve fázi 4.*

### 4. GDPR & Soukromí (Legislativa)
- [ ] **Cookie Consent Banner** (Lišta se souhlasem, blokování Pixel/GA skriptů bez souhlasu)

---

## ⚙️ FÁZE 2: PŘÍPRAVA OBSAHU A SOUBORŮ
*Příprava statických věcí a souborů, které nejsou v databázi.*

### 5. Audio soubory (Podcasty)
- [ ] **Přejmenování souborů** (Spustit `rename_audio_fuzzy.py`)
- [ ] **Kontrola** (Ověřit, že sedí názvy `{id_clanku}.mp3`)

### 6. Statický obsah
- [ ] **Stránka "O nás"** (Aktualizovat texty)
- [ ] **Páteční pětka** (Připravit kategorii/sekci)

### 7. SEO Konfigurace (Příprava klíčů)
- [ ] **Google Analytics** (Doplnit ID do `seo_config.json`)
- [ ] **Google Search Console** (Připravit ověřovací meta tag/soubor)

---

## 📦 FÁZE 3: MIGRACE DAT (The Big Move)
*Teď, když systém funguje, nalijeme do něj ostrá data.*

### 8. Velká Migrace Databáze
- [ ] **Záloha** (Dump staré i nové DB)
- [ ] **Spustit migraci** (Kategorie -> Uživatelé -> Články -> Vazby -> Statistiky)
- [ ] **Migrace Závodů** (Převod ze starých PHP souborů do nové tabulky Events)
- [ ] **Audio DB** (Spárování souborů s články v DB)

### 9. Kontrola a Přesměrování
- [ ] **Test integrity** (Namátková kontrola článků, fotek, autorů)
- [ ] **301 Redirects** (Ověřit, že staré URL z Facebooku vedou na nové články)

---

## 🚀 FÁZE 4: SPUŠTĚNÍ A MONITORING (Post-Launch)
*Co udělat ihned po nahrání na produkci.*

### 10. Ostrý start
- [ ] **Vypnout Debug mód** (V `config` souborech)
- [ ] **Ověření v GSC** (Kliknout na "Verify" v Google Search Console)
- [ ] **Odeslat Sitemaps** (V GSC odeslat `sitemap.xml`, `images`, `news`)
- [ ] **Google News** (Registrace v Publisher Center)
- [ ] **Monitoring** (Sledovat error logy a GA traffic)
