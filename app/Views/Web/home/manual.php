<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Návod pro editory | Cyklistický magazín</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        h1 { color: #e30613; margin-top: 0; border-bottom: 3px solid #e30613; padding-bottom: 20px; display: inline-block; }
        h2 { margin-top: 50px; color: #222; font-size: 1.8em; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        h3 { color: #444; margin-top: 30px; font-size: 1.3em; }
        p { margin-bottom: 15px; font-size: 1.1em; }
        
        .box {
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
            border-left: 5px solid #ccc;
        }
        .box-info { background: #e8f4fd; border-color: #2196f3; }
        .box-warning { background: #fff4e5; border-color: #ff9800; }
        .box-tip { background: #e8f5e9; border-color: #4caf50; }
        .box-action { background: #fce4ec; border-color: #e91e63; }

        .btn {
            display: inline-block;
            background: #e30613;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn:hover { background: #c20510; }

        table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 15px; text-align: left; }
        th { background-color: #f9f9f9; }
        
        .icon-large { font-size: 1.5em; margin-right: 10px; }
        code { background: #eee; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-weight: bold; }
        
        .step { font-weight: bold; color: #e30613; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚴 Jak psát na nový web</h1>
        
        <p>Ahoj! Pokud čteš tento návod, jsi pravděpodobně součástí týmu, který testuje náš nový web. Díky za to!</p>
        <p>Tento web jsme se snažili udělat co nejjednodušší, aby se vám na něm dobře psalo. Zapomeňte na složité systémy, tady je všechno "blbuvzdorné".</p>

        <div class="box box-info">
            <h3>📍 Kde co najdu?</h3>
            <p><strong>1. Veřejný web (to co vidí lidi):</strong> <br><a href="https://bicenc.cyklistickey.cz" target="_blank">bicenc.cyklistickey.cz</a></p>
            <p><strong>2. Administrace (tam kde píšeš):</strong> <br><a href="/admin" target="_blank">/admin</a> (nebo klikni na tlačítko níže)</p>
            <p><strong>3. Tento návod:</strong> <br><a href="/navod">/navod</a> (kdyby ses ztratil)</p>
            <a href="/admin" class="btn">Vstoupit do administrace</a>
        </div>

        <div class="box box-action">
            <h3>🧪 Co je tvůj úkol?</h3>
            <p>Web je v testovací fázi. Potřebujeme, abys:</p>
            <ol>
                <li>Se přihlásil/a (údaje jsou stejné jako na starém webu).</li>
                <li>Zkusil/a napsat článek (klidně nějaký nesmysl nebo zkopírovat starý).</li>
                <li>Zkusil/a tam dát fotky, video z YouTube, galerii.</li>
                <li><strong>Zkusil/a to "rozbít"</strong> – klikej na všechno, co vidíš.</li>
                <li>Když něco nebude fungovat, <strong>napiš to hned do skupiny</strong> (ideálně s fotkou chyby).</li>
            </ol>
        </div>

        <h2>Jak napsat článek (krok za krokem)</h2>

        <p><span class="step">1.</span> Přihlas se do administrace.</p>
        <p><span class="step">2.</span> V levém menu klikni na <strong>Články</strong> a pak <strong>Vytvořit nový</strong>.</p>
        <p><span class="step">3.</span> Vyplň tyto věci:</p>
        
        <ul>
            <li><strong>Název článku:</strong> Titulek, který lidi uvidí.</li>
            <li><strong>Kategorie:</strong> Vyber, kam článek patří (třeba "Závody" nebo "Aktuality").</li>
            <li><strong>Náhledové foto:</strong> To je ta velká fotka, která je vidět v seznamu článků. Vyber nějakou pěknou, ideálně na šířku.</li>
            <li><strong>Zvuková stopa:</strong> (Nemusíš řešit) Pokud bys měl nahrávku článku v MP3, tady ji můžeš nahrát.</li>
        </ul>

        <h2>Jak používat "psací stroj" (Editor)</h2>
        <p>Tady píšeš samotný text. Funguje to podobně jako Word, ale má to pár vychytávek pro náš web.</p>

        <div class="box box-tip">
            <strong>💡 Tip pro kopírování:</strong><br>
            Klidně si článek napiš ve Wordu a pak ho sem zkopíruj (Ctrl+C, Ctrl+V). Editor se postará o to, aby to vypadalo dobře a vymaže "bordel" ve formátování.
        </div>

        <h3>Tlačítka, která se ti budou hodit:</h3>

        <table>
            <tr>
                <td width="50" align="center"><span class="icon-large">🖼️</span></td>
                <td><strong>Vložit jednu fotku</strong><br>Otevře se okno, vybereš fotku z počítače. <br><em>Důležité: Napiš k ní krátký "Popis" (třeba "Peloton v kopci") – má to rád Google.</em></td>
            </tr>
            <tr>
                <td align="center"><span class="icon-large">📸</span></td>
                <td><strong>Galerie (více fotek)</strong><br>Když chceš dát třeba 4 fotky vedle sebe. Vybereš je všechny najednou a ony se hezky poskládají.</td>
            </tr>
            <tr>
                <td align="center"><span class="icon-large">💬</span></td>
                <td><strong>Instagram / YouTube / TikTok</strong><br>Chceš vložit video nebo fotku z Instagramu? Klikni na tohle, vlož odkaz (např. <code>https://instagram.com/p/...</code>) a je to. Na webu se to ukáže samo.</td>
            </tr>
            <tr>
                <td align="center"><span class="icon-large">🔗</span></td>
                <td><strong>Odkaz</strong><br>Označ slovo v textu, klikni na řetěz a vlož adresu, kam má vést.</td>
            </tr>
        </table>

        <h3>Na co si dát pozor?</h3>
        <ul>
            <li><strong>Nadpisy:</strong> V textu používej "Nadpis 2" pro hlavní části a "Nadpis 3" pro menší části. "Nadpis 1" nepoužívej, to je titulek celého článku.</li>
            <li><strong>Ukládání:</strong> Web si pamatuje, co píšeš. Kdyby ti spadl prohlížeč nebo sis to omylem zavřel, vrať se zpátky a klikni na šipku <strong>"Obnovit koncept"</strong>.</li>
        </ul>

        <h2>Co když chci změnit fotku nebo heslo?</h2>
        <p>Vpravo nahoře klikni na své jméno a vyber <strong>Můj profil</strong> (nebo <strong>Nastavení</strong>).</p>
        <ul>
            <li>Můžeš si tam nahrát fotku, která se bude zobrazovat u článků.</li>
            <li>Můžeš si změnit heslo.</li>
            <li><strong>Důležité:</strong> Můžeš si tam nastavit, jestli chceš být vidět na stránce "Redakce".</li>
        </ul>

        <h2>Další věci v menu</h2>
        <p><strong>Flash News (Bleskovky):</strong> To je ten běžící text úplně nahoře na webu. Používá se pro rychlé zprávy (např. "Závod byl zrušen").</p>
        <p><strong>Propagace:</strong> Když chceš, aby byl článek "vypíchnutý" nahoře na hlavní stránce jako velký banner.</p>

        <div class="box box-warning">
            <strong>Něco nefunguje?</strong><br>
            Neboj se, nic nerozbiješ tak, aby to nešlo opravit. Když narazíš na chybu, napiš nám. Od toho to testujeme!
        </div>

        <p style="text-align: center; margin-top: 50px; color: #888;">
            Vytvořeno pro tým Cyklistickey.cz &copy; 2024
        </p>
    </div>
</body>
</html>