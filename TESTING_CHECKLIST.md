# Checklist pro testování Click Tracking statistik

## 🔍 1. ZÁKLADNÍ FUNKCIONALITA

### 1.1 Hlavní stránka statistik (`/admin/statistics`)
- [ ] Zobrazuje se karta "Celkový počet kliků" (fialová barva)
- [ ] Číslo na kartě odpovídá skutečnému počtu kliků v databázi
- [ ] Kliknutí na "Zobrazit detail" přesměruje na `/admin/statistics/clicks`
- [ ] Pokud nejsou žádné kliky, zobrazuje se 0 (ne chyba)

### 1.2 Detailní stránka kliků (`/admin/statistics/clicks`)
- [ ] Stránka se načte bez chyb
- [ ] Zobrazují se 4 souhrnné karty:
  - [ ] Celkový počet kliků
  - [ ] Průměr kliků na článek
  - [ ] Top odkaz (pokud existuje, zobrazí URL; pokud ne, zobrazí 0)
  - [ ] Články s kliky

## 📊 2. GRAFY A VIZUALIZACE

### 2.1 Trend kliků v čase
- [ ] Graf se zobrazí správně
- [ ] Osa X zobrazuje datumy
- [ ] Osa Y zobrazuje počet kliků
- [ ] Data odpovídají skutečným klikům (zkontroluj v databázi)
- [ ] Pokud nejsou žádné kliky, graf zobrazuje nulovou čáru (ne chybu)
- [ ] **DŮLEŽITÉ:** Zkontroluj, že graf neobsahuje divné spike (např. 2700 kliků najednou) - pokud ano, jsou to pravděpodobně testovací data

### 2.2 Top 10 článků podle kliků
- [ ] Zobrazují se jen články, které mají alespoň 1 klik
- [ ] Články jsou seřazené podle počtu kliků (sestupně)
- [ ] Zobrazují se správné hodnoty kliků
- [ ] Pokud nejsou žádné kliky, zobrazí se prázdná tabulka s informační zprávou

### 2.3 Top 10 odkazů
- [ ] Zobrazují se odkazy s nejvíce kliky
- [ ] URL je zkrácené (max 50 znaků) s "..." na konci
- [ ] Zobrazuje se název článku (pokud existuje)
- [ ] Zobrazuje se počet kliků
- [ ] **DŮLEŽITÉ:** Zkontroluj, že URL neobsahuje divné cesty (např. `/data/web/virtuals/...`) - pokud ano, jsou to pravděpodobně chybné záznamy

### 2.4 Kliky podle hodin
- [ ] Graf zobrazuje 24 hodin (0-23)
- [ ] Data odpovídají skutečným klikům
- [ ] Pokud nejsou žádné kliky, všechny hodnoty jsou 0

### 2.5 Kliky podle dnů v týdnu
- [ ] Graf zobrazuje 7 dní (Neděle-Sobota)
- [ ] Data odpovídají skutečným klikům
- [ ] Pokud nejsou žádné kliky, všechny hodnoty jsou 0

### 2.6 Kliky podle kategorií
- [ ] Zobrazují se kategorie s kliky
- [ ] Počet kliků je správný
- [ ] Počet článků v kategorii je správný
- [ ] Průměr kliků/článek je správně vypočítaný

## 🔄 3. FILTRY A ČASOVÉ OBDOBÍ

### 3.1 Filtrování podle období
- [ ] Filtrování podle 7/30/90/365 dní funguje
- [ ] Po změně filtru se data aktualizují
- [ ] Grafy se přepočítají podle vybraného období
- [ ] Vlastní období (custom) funguje správně

## 📋 4. STATISTIKY ČLÁNKŮ

### 4.1 Stránka statistik článků (`/admin/statistics/articles`)
- [ ] Přidal se sloupec "Kliky" do tabulky
- [ ] Přidal se sloupec "CTR" (Click-Through Rate) do tabulky
- [ ] CTR se počítá správně: `(kliky / zobrazení) * 100`
- [ ] Možnost řazení podle kliků funguje (vzestupně i sestupně)
- [ ] Pokud článek nemá žádné kliky, zobrazuje se 0 (ne chyba)
- [ ] Pokud článek nemá žádná zobrazení, CTR je 0% (ne dělení nulou)

## 🗄️ 5. DATABÁZOVÉ KONTROLY

### 5.1 Kontrola dat v databázi
- [ ] Tabulka `link_clicks` obsahuje správná data
- [ ] Tabulka `link_click_events` obsahuje správná data
- [ ] Nejsou tam testovací/chybné záznamy:
  - [ ] Žádné divné URL (cesty k souborům místo HTTP/HTTPS)
  - [ ] Žádné budoucí datumy (`clicked_at` > NOW())
  - [ ] Žádné NULL hodnoty tam, kde by neměly být

### 5.2 Kontrola integrity dat
- [ ] `link_click_events.link_click_id` odkazuje na existující `link_clicks.id`
- [ ] `link_clicks.id_clanku` odkazuje na existující `clanky.id`
- [ ] Agregované počty v `link_clicks.click_count` odpovídají počtu záznamů v `link_click_events`

## 🧪 6. FUNKČNÍ TESTY

### 6.1 Test kliknutí na odkaz
1. [ ] Otevři článek na webu (např. `bicenc.cyklistickey.cz/clanek/...`)
2. [ ] Klikni na nějaký externí odkaz v článku
3. [ ] Zkontroluj, že se odkaz otevřel
4. [ ] Jdi do admin panelu → `/admin/statistics/clicks`
5. [ ] Zkontroluj, že se klik zaznamenal:
   - [ ] Celkový počet kliků se zvýšil
   - [ ] V tabulce "Top články" se zobrazuje tento článek
   - [ ] V tabulce "Top odkazy" se zobrazuje tento odkaz
   - [ ] V grafu "Trend kliků" se zobrazuje klik pro dnešní den

### 6.2 Test více kliků
1. [ ] Klikni na stejný odkaz několikrát (3-5x)
2. [ ] Zkontroluj, že se všechny kliky zaznamenaly
3. [ ] Zkontroluj, že agregovaný počet v `link_clicks` odpovídá počtu eventů

### 6.3 Test různých odkazů
1. [ ] Klikni na různé odkazy v různých článcích
2. [ ] Zkontroluj, že se všechny zaznamenaly správně
3. [ ] Zkontroluj, že se správně zobrazují v "Top odkazy"

## 🐛 7. KONTROLA CHYB

### 7.1 Edge cases
- [ ] Co se stane, když není žádný klik? (mělo by zobrazit 0, ne chybu)
- [ ] Co se stane, když článek nemá žádné zobrazení, ale má kliky? (CTR by měl být správně vypočítán)
- [ ] Co se stane, když odkaz nemá URL? (mělo by se filtrovat)
- [ ] Co se stane, když `clicked_at` je NULL? (mělo by se filtrovat)

### 7.2 Kontrola konzole prohlížeče
- [ ] Otevři Developer Tools (F12)
- [ ] Zkontroluj Console - neměly by být žádné JavaScript chyby
- [ ] Zkontroluj Network tab - všechny requesty by měly být úspěšné (200)

## 📱 8. RESPONZIVNÍ DESIGN

### 8.1 Různé velikosti obrazovek
- [ ] Na desktopu (1920x1080) vypadá vše dobře
- [ ] Na tabletu (768px) se grafy a tabulky správně zobrazují
- [ ] Na mobilu (375px) je vše čitelné a funkční

## ⚡ 9. VÝKONNOST

### 9.1 Rychlost načítání
- [ ] Stránka `/admin/statistics/clicks` se načte do 2 sekund
- [ ] Grafy se vykreslí bez zpoždění
- [ ] Tabulky se načtou rychle

### 9.2 Optimalizace dotazů
- [ ] Zkontroluj, že dotazy používají správné indexy
- [ ] Zkontroluj, že nejsou zbytečné JOINy

## 🔐 10. BEZPEČNOST

### 10.1 SQL Injection
- [ ] Všechny dotazy používají prepared statements (zkontroluj v kódu)
- [ ] Všechny user inputy jsou sanitizované

### 10.2 XSS
- [ ] Všechny výstupy používají `htmlspecialchars()` (zkontroluj v views)
- [ ] URL odkazy jsou správně escapované

## 📝 11. DOKUMENTACE A KÓD

### 11.1 Kontrola kódu
- [ ] Všechny metody v `Statistics.php` mají správné SQL dotazy
- [ ] Všechny metody v `StatisticsAdminController.php` správně předávají data
- [ ] Views správně zobrazují data (včetně edge cases)

### 11.2 Komentáře
- [ ] Důležité metody mají komentáře
- [ ] Složité logiky jsou vysvětlené

---

## 🚨 DŮLEŽITÉ POZNÁMKY K TESTOVÁNÍ

1. **Testovací data:** Pokud vidíš divné hodnoty (např. 2700 kliků najednou), pravděpodobně jsou to testovací data v databázi. Můžeš je smazat:
   ```sql
   DELETE FROM link_click_events WHERE clicked_at > NOW();
   DELETE FROM link_clicks WHERE click_count = 0;
   ```

2. **Prázdná data:** Pokud nemáš žádné kliky, všechny statistiky by měly zobrazovat 0, ne chyby.

3. **URL odkazy:** Pokud vidíš divné cesty místo URL (např. `/data/web/virtuals/...`), jsou to pravděpodobně chybné záznamy. Můžeš je smazat:
   ```sql
   DELETE FROM link_clicks WHERE url NOT LIKE 'http%' AND url NOT LIKE 'mailto:%' AND url NOT LIKE 'tel:%';
   ```

4. **Budoucí datumy:** Pokud máš kliky s budoucími datumy, jsou to chybné záznamy:
   ```sql
   DELETE FROM link_click_events WHERE clicked_at > NOW();
   ```

---

## ✅ FINÁLNÍ KONTROLA

Po dokončení všech testů:
- [ ] Všechny funkce fungují správně
- [ ] Žádné chyby v konzoli
- [ ] Data jsou konzistentní
- [ ] Grafy zobrazují správné hodnoty
- [ ] Tabulky zobrazují správné hodnoty
- [ ] Filtry fungují
- [ ] Edge cases jsou ošetřené

---

**Datum testování:** _______________
**Testoval:** _______________
**Výsledek:** ☐ Úspěšné  ☐ S chybami (popiš níže)

**Poznámky:**
_________________________________________________
_________________________________________________
_________________________________________________



