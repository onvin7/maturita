# Manuál k editoru článků (TinyMCE)

Tento editor byl upraven na míru pro maximální jednoduchost a "blbuvzdornost". Níže najdete popis speciálních funkcí a tlačítek.

## 1. Hlavní panel nástrojů (Toolbar)

Tlačítka jsou seřazena logicky zleva doprava:

### Základní úpravy
*   **Zpět / Vpřed (Undo/Redo):** Klasické šipky pro vrácení změn.
*   **Obnovit koncept (Restore Draft):** Pokud vám spadne prohlížeč nebo omylem zavřete okno, editor si pamatuje rozepsaný text (ukládá se každých 30 sekund). Tímto tlačítkem ho obnovíte.

### Formátování
*   **Styly:** Výběr nadpisů (Nadpis 2, Nadpis 3) a odstavce. (Nadpis 1 se používá automaticky pro titulek článku, v textu ho nepoužívejte).
*   **B, I, U:** Tučné, kurzíva, podtržené.
*   **Zarovnání:** Vlevo, na střed, vpravo.
*   **Seznamy:** Odrážky a číslovaný seznam.

### Vkládání obsahu (Vlastní funkce)

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
*   **Funkce:** Vloží interaktivní náhled příspěvku (Instagram, Facebook, Twitter, TikTok, YouTube...).
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

## 2. Další vychytávky

*   **Autosave:** Editor automaticky ukládá vaši práci do prohlížeče každých 30 sekund.
*   **Čištění textu:** Pokud zkopírujete text z Wordu, editor ho automaticky "vyčistí" od bordelu a formátování, které by rozbilo web.
*   **Pravopis:** Editor využívá kontrolu pravopisu vašeho prohlížeče. Chyby se podtrhávají červeně (ale neukládají se do textu, vidíte je jen vy).
*   **Zákaz rozbíjení:** Nelze měnit velikost obrázků tažením myši (to často rozbíjelo vzhled na mobilech). Velikost se řeší automaticky.

## 3. Klávesové zkratky (Shrnutí)

| Funkce | Zkratka |
| :--- | :--- |
| **Vložit odkaz** | `Ctrl + K` |
| **Vložit obrázek** | `Ctrl + Shift + I` |
| **Vložit galerii** | `Ctrl + Shift + G` |
| **Vložit soc. příspěvek** | `Ctrl + Shift + E` |
| **Uložit (běžné)** | `Ctrl + S` (v prohlížeči může vyvolat uložení stránky, spoléhejte na tlačítko Uložit pod editorem) |
