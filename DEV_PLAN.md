# 📅 Plán vývoje: Dokončení webu před spuštěním

Tento dokument slouží jako centrální rozvrh prací. Úkoly jsou seřazeny logicky podle závislostí.

## 🛠️ 1. Editor Článků (Backend) - ✅ HOTOVO
*   [x] **Sjednocení konfigurace:** Používáme `tinymce-simple.js` jako hlavní.
*   [x] **Vylepšení toolbaru:** Ikony místo textu, logické skupiny (Galerie, Embed, Gramatika).
*   [x] **Social Embeds:** Tlačítko pro vkládání příspěvků (Instagram, Twitter, atd.) jako čisté odkazy.
*   [x] **Galerie obrázků:**
    *   Dynamické přidávání inputů (až 10).
    *   Drag & Drop pro více souborů.
    *   Generování HTML struktury (grid).
*   [x] **Kontrola pravopisu:**
    *   Zapnutá automatická kontrola prohlížeče.
    *   Tlačítko pro manuální kontrolu (Hunspell).

## 🔗 2. Správa URL a SEO (Backend) - ✅ HOTOVO
*   [x] **Automatické generování URL (Slug):**
    *   Při psaní nadpisu článku se automaticky vyplní pole URL.
    *   Validace: pouze malá písmena, čísla, pomlčky.
    *   Kontrola duplicity (aby neexistovaly dva články se stejnou URL).
*   [x] **Odkazy v editoru:**
    *   Přidat checkbox "Otevřít v novém okně" (`target="_blank"`) do dialogu pro vložení odkazu. (Vyřešeno nastavením default_link_target: '_blank' a dropdownem).

## 🎨 3. Frontend - Zobrazení článku (Public) - ✅ HOTOVO
*   [x] **Galerie (Lightbox):**
    *   Nastylovat mřížku obrázků (CSS Grid).
    *   Implementovat "maskovací efekt" (+X dalších) pro 5. a další obrázek.
    *   Nasadit Lightbox knihovnu (vlastní robustní řešení) pro prohlížení galerie (overlay, šipky, pás náhledů).
*   [x] **Social Embeds:**
    *   Ověřit, že se všechny embedy (Instagram, TikTok, YouTube) zobrazují správně a responsivně. (Styly v `clanek.css` jsou připraveny a responsivní).

## 💰 4. Správa Reklam - ⏳ NA ŘADĚ
*   [ ] **Backend:** Zprovoznit sekci Reklamy v adminu (CRUD).
*   [ ] **Databáze:** Vytvořit tabulku `reklamy`.
*   [ ] **Frontend:** Zobrazování reklamních bannerů v článcích a sidebarech.

## 📅 5. Events Systém (Závody) - ✅ HOTOVO
*   [x] **Frontend:** Zobrazit informaci o přesunu závodů na projekt "Bav se sportem".
*   [x] **Admin:** Správa závodů se ruší.

## ⚖️ 6. GDPR & Legislativa
*   [ ] **Cookie Lišta:** Nasadit řešení pro souhlas s cookies.

---

### 🚀 Co děláme teď?
Přesouváme se na bod **4. Správa Reklam**.
