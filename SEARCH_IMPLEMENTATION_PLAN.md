# Plán implementace vyhledávání

Tento dokument popisuje kroky k implementaci fulltextového vyhledávání do redakčního systému.

## 1. Cíl
Přidat do hlavičky webu ikonu lupy, která po kliknutí otevře stránku s vyhledáváním. Vyhledávání bude procházet názvy i obsah článků. Výsledky budou zobrazovat kontext (úryvek textu) s zvýrazněným hledaným výrazem.

## 2. Změny v souborech

### A. Databáze a Modely
**Soubor:** `app/Models/Article.php`
- Přidat metodu `searchArticles(string $query): array`
- **Logika:**
  - SQL dotaz: `SELECT * FROM articles WHERE title LIKE :query OR content LIKE :query`
  - Třídění: Prioritizovat shodu v názvu před shodou v obsahu.
  - Filtrovat pouze publikované články.

### B. Helpery
**Soubor:** `app/Helpers/TextHelper.php`
- Přidat metodu `getSearchSnippet(string $text, string $query, int $contextLength = 100): string`
- **Logika:**
  1. Odstranit HTML tagy z obsahu (`strip_tags`).
  2. Najít pozici hledaného výrazu (`mb_stripos`).
  3. Pokud výraz v textu není (shoda byla jen v titulku), vrátit začátek textu.
  4. Pokud výraz je v textu:
     - Určit začátek výřezu (pozice - polovina kontextu).
     - Určit konec výřezu.
     - Oříznout text a přidat "..." na začátek/konec, pokud nejsme na hranici textu.
  5. Zvýraznit hledaný výraz (např. obalit do `<mark>` nebo `<b>`).

### C. Controllery
**Soubor:** `app/Controllers/Web/SearchController.php` (Nový soubor)
- Metoda `index()`:
  - Získat parametr `q` z URL (`$_GET['q']`).
  - Pokud je `q` prázdné -> zobrazit prázdný formulář.
  - Pokud `q` existuje -> zavolat `Article::searchArticles($q)`.
  - Předat výsledky do view.

### D. Views (Frontend)
**1. Header**
**Soubor:** `app/Views/Web/layouts/header.php` (nebo `navbar.php`)
- Přidat ikonku lupy (SVG nebo FontAwesome) doprava.
- Odkazovat na `/search` nebo otevírat input (dle zadání "otevře nějaký hledání" -> začneme samostatnou stránkou `/search` pro lepší UX na mobilu i desktopu, případně modalem, který na ni přesměruje).

**2. Stránka výsledků**
**Soubor:** `app/Views/Web/search/index.php` (Nový soubor)
- **Komponenty:**
  - Velký input field s aktuálně hledaným textem.
  - **Sekce výsledků:**
    - Pokud `count($results) === 0`: Zobrazit hlášku "Nic takového tu není".
    - Pokud `count($results) > 0`: Foreach loop přes články.
      - Zobrazit: Náhledový obrázek, Titulek (zvýrazněná shoda), Datum.
      - Zobrazit: Snippet z obsahu (vygenerovaný přes `TextHelper`).

### E. Routování
**Soubor:** `web/index.php`
- Přidat routu pro `/search` -> `SearchController::index`.

### F. Stylování
**Soubor:** `web/css/search.css` (Nový soubor) nebo přidat do `main-page.css`
- Styly pro input field.
- Styly pro zvýraznění (`mark` tag).
- Layout výsledků.

## 3. Postup implementace (kroky pro AI)

1.  **Vytvořit Controller a Routu**: Abychom mohli testovat URL `/search`.
2.  **Vytvořit View**: Základní HTML struktura s inputem.
3.  **Upravit Model**: Implementovat vyhledávací SQL dotaz.
4.  **Implementovat Helper**: Logika pro ořezávání a zvýrazňování textu.
5.  **Propojit vše**: Zobrazit reálná data ve View.
6.  **Upravit Header**: Přidat ikonku lupy.
7.  **Stylování**: Upravit CSS.

## 4. Detaily k logice zvýrazňování (dle zadání)
- **Shoda v názvu:** Zvýraznit slovo v názvu.
- **Shoda v obsahu:**
  - Najít výskyt slova.
  - Pokud je slovo na začátku -> vypsat od začátku.
  - Pokud je slovo dále -> vypsat `... text před slovem [SLOVO] text za slovem ...`.
  - Zarovnat tak, aby hledané slovo bylo opticky uprostřed snippetu.
