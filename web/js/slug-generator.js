document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('nazev');
    const urlInput = document.getElementById('url');
    const regenerateBtn = document.getElementById('btn-regenerate-url');
    
    if (!titleInput || !urlInput) return;

    // Funkce pro generování URL slugu
    function stringToSlug(str) {
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();
      
        // odstranění diakritiky - rozšířená sada znaků
        const from = "áäčďéěíľňóôřšťúůýžÁÄČĎÉĚÍĽŇÓÔŘŠŤÚŮÝŽ";
        const to   = "aacdeeilnoorstuuuyzAACDEEILNOORSTUUUYZ";
        for (let i = 0, l = from.length; i < l; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }
      
        str = str.replace(/[^a-z0-9 -]/g, '') // odstranění nepovolených znaků
                 .replace(/\s+/g, '-') // nahrazení mezer pomlčkou
                 .replace(/-+/g, '-'); // odstranění duplicitních pomlček
      
        return str;
    }

    // Pokud je URL pole prázdné (nový článek), povolíme automatickou aktualizaci
    // Pokud je vyplněné (editace), zamkneme ho, aby se neměnilo při psaní nadpisu
    let isUrlLocked = urlInput.value.trim() !== '';

    titleInput.addEventListener('input', function() {
        if (!isUrlLocked) {
            urlInput.value = stringToSlug(this.value);
        }
    });

    urlInput.addEventListener('input', function() {
        // Jakmile uživatel sáhne na URL, považujeme ho za manuálně spravované
        isUrlLocked = true;
    });
    
    // Při opuštění pole ho pro jistotu "dočistíme" (validace formátu)
    urlInput.addEventListener('change', function() {
         this.value = stringToSlug(this.value);
    });

    if (regenerateBtn) {
        regenerateBtn.addEventListener('click', function() {
            if (confirm('Opravdu chcete přegenerovat URL podle nadpisu? Původní hodnota bude přepsána.')) {
                urlInput.value = stringToSlug(titleInput.value);
                // Po ručním vyžádání regenerace můžeme nechat isUrlLocked = true,
                // protože uživatel provedl explicitní akci.
            }
        });
    }
});
