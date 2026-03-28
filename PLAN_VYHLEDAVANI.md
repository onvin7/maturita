# Plán implementace vyhledávání článků

## 1. Frontend - Header a Vyhledávací pole
**Soubor:** `app/Views/partials/header.php` (nebo odpovídající soubor s hlavičkou)

*   **Ikona lupy:** Přidat ikonu lupy (např. pomocí SVG nebo FontAwesome) do pravé části navigace.
*   **Interakce:**
    *   Po kliknutí na lupu se zobrazí vyhledávací formulář (může se vysunout, zobrazit jako modal nebo nahradit menu).
    *   Formulář bude obsahovat input text `name="q"` a tlačítko hledat.
*   **Formulář:**
    *   Method: `GET`
    *   Action: `/search`
    *   Příklad URL po odeslání: `domena.cz/search?q=hledany+vyraz`

## 2. Routing
**Soubor:** `index.php` (nebo router konfigurace)

*   Zaregistrovat novou trasu:
    *   URL: `/search`
    *   Controller: `ArticleController` (nebo nový `SearchController`)
    *   Metoda: `search`

## 3. Backend Logika
**Soubor:** `app/Controllers/ArticleController.php` (metoda `search`)

1.  **Získání dotazu:** Přečíst parametr `q` z GET požadavku. Ošetřit vstupy (trim, htmlspecialchars).
2.  **Databázový dotaz:**
    *   Hledat výraz v `title` a `content`.
    *   **SQL logika:**
        ```sql
        SELECT *,
        CASE
            WHEN title LIKE :query THEN 1  -- Priorita pro shodu v nadpisu
            ELSE 2
        END as relevance
        FROM articles
        WHERE title LIKE :query OR content LIKE :query
        ORDER BY relevance ASC, created_at DESC
        ```
    *   Parametr `:query` bude `%hledany_vyraz%`.

3.  **Logika pro výpis (Snippet & Zvýraznění):**
    *   Vytvořit pomocnou metodu nebo logiku přímo v controlleru pro zpracování textu.
    *   **Pokud je shoda v nadpisu:** Zvýraznit hledané slovo v nadpisu.
    *   **Pokud je shoda v obsahu:**
        *   Najít pozici prvního výskytu hledaného slova (`strpos` / `mb_strpos`).
        *   Vytvořit výřez textu:
            *   Pokud je slovo na začátku článku: Vypsat prvních cca 200 znaků.
            *   Pokud je slovo hlouběji: Vyříznout text např. 50 znaků PŘED a 150 znaků ZA slovem.
            *   Přidat "..." na začátek a konec výřezu, pokud nejde o začátek/konec textu.
        *   Zvýraznit hledané slovo: `str_replace` nebo `preg_replace` (pozor na case sensitivity) -> obalit do `<span class="highlight">slovo</span>`.

## 4. Frontend - Stránka s výsledky
**Soubor:** `app/Views/articles/search_results.php` (nový soubor)

*   **Hlavička:** Nadpis "Výsledky vyhledávání pro: 'zadaný výraz'".
*   **Search Input:** Znovu zobrazit input field s předvyplněnou hledanou hodnotou (aby uživatel mohl hledání upřesnit).
*   **Výpis výsledků:**
    *   Iterovat přes pole nalezených článků.
    *   Pro každý článek zobrazit:
        *   **Nadpis:** Odkaz na detail článku (`/clanek/slug`).
        *   **Meta info:** Datum publikace, autor.
        *   **Snippet:** Zobrazit připravený výřez textu se zvýrazněním (pozor na escapování HTML značek pro zvýraznění - povolit jen `<span>` nebo `<strong>`).
*   **Stav "Nic nenalezeno":**
    *   Pokud DB nevrátí žádné výsledky, zobrazit hlášku: *"Pro výraz 'xyz' nebyly nalezeny žádné články."*

## 5. CSS Styly
**Soubor:** `web/css/appka.css`

*   Stylování vyhledávacího inputu v hlavičce.
*   Styl pro zvýraznění nalezeného textu:
    ```css
    .search-highlight {
        background-color: yellow;
        font-weight: bold;
        color: black;
    }
    ```
