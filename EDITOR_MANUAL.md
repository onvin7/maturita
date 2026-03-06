# Manuál k editoru článků (TinyMCE)

Tento editor byl upraven na míru pro maximální jednoduchost a "blbuvzdornost". Níže najdete popis všech funkcí a tlačítek.

## 1. Hlavní panel nástrojů (Toolbar)

Tlačítka jsou seřazena logicky zleva doprava:

### Základní úpravy
*   **Zpět / Vpřed (Undo/Redo):** Klasické šipky pro vrácení změn.
*   **Obnovit koncept (Restore Draft):** Pokud vám spadne prohlížeč nebo omylem zavřete okno, editor si pamatuje rozepsaný text (ukládá se každých 30 sekund). Tímto tlačítkem ho obnovíte.

### Formátování textu
*   **Styly:** Dropdown menu pro výběr formátu.
    *   **Normální text:** Běžný odstavec.
    *   **Nadpis 2 (H2):** Hlavní podnadpisy v článku.
    *   **Nadpis 3 (H3):** Menší podnadpisy.
    *   *Poznámka: Nadpis 1 se používá automaticky pro titulek článku, v textu ho nepoužívejte.*
*   **B (Bold):** Tučné písmo.
*   **I (Italic):** Kurzíva.
*   **U (Underline):** Podtržené písmo.
*   **Zarovnání:** Tlačítka pro zarovnání textu vlevo, na střed a vpravo.
*   **Seznamy:**
    *   **Odrážky:** Nečíslovaný seznam.
    *   **Číslování:** Číslovaný seznam.

### Vkládání obsahu (Speciální funkce)

#### 🖼️ Vložit jeden obrázek (`simpleimage`)
*   **Ikona:** Obrázek (hory).
*   **Funkce:** Otevře jednoduché okno pro nahrání **jednoho** obrázku.
*   **Co zadáváte:**
    *   Vyberete soubor z počítače.
    *   **Popis (ALT):** Krátký text, co na obrázku je (důležité pro Google).
    *   **Zobrazit popis pod obrázkem:** Pokud zaškrtnete, text se zobrazí i čtenářům jako titulek pod fotkou.
*   **Klávesová zkratka:** `Ctrl + Shift + I`

#### 📸 Vložit galerii (`imagesgallery`)
*   **Ikona:** Galerie (více obrázků).
*   **Funkce:** Umožňuje nahrát 2 až 10 fotek najednou, které se zobrazí v mřížce vedle sebe.
*   **Co zadáváte:**
    *   Vyberete fotky (lze jich vybrat více najednou nebo přidávat postupně).
    *   **Společný popis:** Text, který se použije pro všechny fotky (automaticky se očíslují).
*   **Vzhled:** V editoru i na webu se zobrazí jako mřížka. Po kliknutí se otevřou ve velkém prohlížeči (Lightbox).
*   **Klávesová zkratka:** `Ctrl + Shift + G`

#### 💬 Vložit příspěvek ze sociálních sítí (`socialembed`)
*   **Ikona:** Bublina s plusem.
*   **Funkce:** Vloží interaktivní náhled příspěvku (Instagram, Facebook, Twitter/X, TikTok, YouTube...).
*   **Jak to funguje:**
    1.  Zkopírujte URL adresu příspěvku (např. `https://www.instagram.com/p/C...`).
    2.  Vložte ji do políčka.
    3.  V editoru se zobrazí jako odkaz.
    4.  **Na webu se automaticky promění** na hezký náhled (video, fotka s komentáři atd.).
*   **Klávesová zkratka:** `Ctrl + Shift + E`

#### 🔗 Vložit odkaz (`simplelink`)
*   **Ikona:** Řetěz.
*   **Funkce:** Zjednodušené vkládání odkazů.
*   **Co zadáváte:**
    *   **URL:** Adresa, kam odkaz vede.
    *   **Text:** Co se má zobrazit (pokud nevyplníte, zobrazí se URL).
*   **Vlastnost:** Odkaz se **vždy** otevře v novém okně (čtenář neodejde z vašeho webu).
*   **Klávesová zkratka:** `Ctrl + K`

#### 💻 Zdrojový kód (`code`)
*   **Ikona:** Závorky `< >`.
*   **Funkce:** Zobrazí HTML kód článku.
*   **Použití:** Pouze pro pokročilé uživatele, pokud potřebujete opravit něco, co nejde v editoru.

## 2. Automatické funkce a chování

*   **Autosave (Automatické ukládání):** Editor automaticky ukládá vaši práci do prohlížeče každých 30 sekund. Koncept zůstává v paměti 20 minut. Pokud omylem zavřete okno, po návratu použijte tlačítko "Obnovit koncept".
*   **Čištění textu (Paste Cleaning):** Pokud zkopírujete text z Wordu nebo jiného webu, editor ho automaticky "vyčistí" od zbytečného formátování, které by mohlo rozbít vzhled webu.
*   **Kontrola pravopisu:** Editor využívá vestavěnou kontrolu pravopisu vašeho prohlížeče. Chyby se podtrhávají červeně.
*   **Omezení nadpisů:** Pro zachování správné struktury webu jsou povoleny pouze nadpisy úrovně 2 a 3.
*   **Zákaz změny velikosti (No Resize):** Obrázky a objekty nelze v editoru zvětšovat/zmenšovat myší. Velikost se na webu přizpůsobuje automaticky (responzivita), aby vše vypadalo dobře na mobilech i počítačích.

## 3. Klávesové zkratky (Shrnutí)

| Funkce | Zkratka |
| :--- | :--- |
| **Vložit odkaz** | `Ctrl + K` |
| **Vložit obrázek** | `Ctrl + Shift + I` |
| **Vložit galerii** | `Ctrl + Shift + G` |
| **Vložit soc. příspěvek** | `Ctrl + Shift + E` |
| **Uložit (běžné)** | `Ctrl + S` (Uloží článek) |
| **Tučné** | `Ctrl + B` |
| **Kurzíva** | `Ctrl + I` |
| **Podtržené** | `Ctrl + U` |
