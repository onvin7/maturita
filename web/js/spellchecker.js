/**
 * SpellChecker třída pro kontrolu pravopisu s hunspell slovníky
 */
class SpellChecker {
    constructor() {
        this.dictionary = null;
        this.affix = null;
        this.isLoaded = false;
        this.loadDictionary();
    }

    /**
     * Načte hunspell slovník
     */
    async loadDictionary() {
        try {
            // Načtení affix souboru
            const affResponse = await fetch('/js/hunspell/cs_CZ.aff');
            this.affix = await affResponse.text();
            
            // Načtení dictionary souboru
            const dicResponse = await fetch('/js/hunspell/cs_CZ.dic');
            const dicText = await dicResponse.text();
            
            // Parsování dictionary
            this.dictionary = this.parseDictionary(dicText);
            this.isLoaded = true;
            
            console.log('Hunspell slovník načten úspěšně');
        } catch (error) {
            console.error('Chyba při načítání hunspell slovníku:', error);
            this.isLoaded = false;
        }
    }

    /**
     * Parsuje dictionary soubor
     */
    parseDictionary(dicText) {
        const words = new Set();
        const lines = dicText.split('\n');
        
        // První řádek obsahuje počet slov
        const wordCount = parseInt(lines[0]);
        
        // Načte slova (omezeně pro výkon)
        const maxWords = Math.min(wordCount, 100000); // Zvýšeno na 100k
        
        for (let i = 1; i < lines.length && i <= maxWords; i++) {
            const line = lines[i].trim();
            if (line) {
                // Rozdělí slovo a affixy
                const parts = line.split('/');
                const word = parts[0].toLowerCase();
                
                // Přidá pouze slova s českými znaky nebo běžná slova
                if (word.length > 1) {
                    words.add(word);
                }
            }
        }
        
        console.log(`Načteno ${words.size} slov ze slovníku`);
        return words;
    }

    /**
     * Zkontroluje text a vrátí seznam chybných slov
     */
    checkText(text) {
        if (!this.isLoaded) {
            console.warn('Slovník není načten');
            return [];
        }

        const misspelled = [];
        const words = this.extractWords(text);
        
        for (const word of words) {
            if (!this.isWordValid(word)) {
                misspelled.push(word);
            }
        }
        
        return misspelled;
    }

    /**
     * Extrahuje slova z textu
     */
    extractWords(text) {
        // Odstraní HTML tagy a extrahuje slova
        const cleanText = text.replace(/<[^>]*>/g, ' ')
                             .replace(/[^\w\s\u00C0-\u024F\u1E00-\u1EFF]/g, ' ') // Zachovat diakritiku
                             .toLowerCase();
        
        const words = cleanText.split(/\s+/)
                              .filter(word => word.length > 1)
                              .filter(word => !/^\d+$/.test(word)); // Ignorovat čísla
        
        return [...new Set(words)]; // Odstraní duplicity
    }

    /**
     * Zkontroluje, jestli je slovo platné
     */
    isWordValid(word) {
        if (!this.dictionary) return true;
        
        const lowerWord = word.toLowerCase();
        
        // Přímá kontrola ve slovníku
        if (this.dictionary.has(lowerWord)) {
            return true;
        }
        
        // Kontrola variant (základní implementace)
        const variants = this.generateVariants(lowerWord);
        for (const variant of variants) {
            if (this.dictionary.has(variant)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generuje varianty slova pro kontrolu
     */
    generateVariants(word) {
        const variants = [];
        
        // Odstranění koncovek (základní implementace)
        const endings = ['y', 'é', 'í', 'á', 'ů', 'ě', 'i', 'e', 'a', 'o', 'u', 'ou', 'ech', 'ách', 'em', 'ami', 'ovi'];
        
        for (const ending of endings) {
            if (word.endsWith(ending)) {
                variants.push(word.slice(0, -ending.length));
            }
        }
        
        // Odstranění dvojitých koncovek
        if (word.endsWith('ých')) {
            variants.push(word.slice(0, -3));
            variants.push(word.slice(0, -3) + 'ý');
        }
        if (word.endsWith('ého')) {
            variants.push(word.slice(0, -3));
            variants.push(word.slice(0, -3) + 'ý');
        }
        if (word.endsWith('ém')) {
            variants.push(word.slice(0, -2));
            variants.push(word.slice(0, -2) + 'ý');
        }
        
        return variants;
    }

    /**
     * Zvýrazní chyby v editoru (Bezpečně přes DOM)
     */
    highlightErrors(editor, misspelledWords) {
        // Odstraní předchozí zvýraznění
        this.removeHighlighting(editor);
        
        const body = editor.getBody();
        const walker = document.createTreeWalker(
            body,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        const nodesToReplace = [];
        let node;

        while (node = walker.nextNode()) {
            // Ignorovat script a style tagy
            if (node.parentElement && (node.parentElement.tagName === 'SCRIPT' || node.parentElement.tagName === 'STYLE')) {
                continue;
            }

            let text = node.nodeValue;
            let hasError = false;

            // Kontrola, zda text obsahuje chybná slova
            for (const word of misspelledWords) {
                // Regex pro celé slovo, case insensitive
                const regex = new RegExp(`\\b(${this.escapeRegex(word)})\\b`, 'gi');
                if (regex.test(text)) {
                    hasError = true;
                    break;
                }
            }

            if (hasError) {
                nodesToReplace.push(node);
            }
        }

        // Nahrazení textových uzlů HTML obsahem se zvýrazněním
        nodesToReplace.forEach(node => {
            let html = this.escapeHtml(node.nodeValue);
            
            for (const word of misspelledWords) {
                const regex = new RegExp(`\\b(${this.escapeRegex(word)})\\b`, 'gi');
                html = html.replace(regex, '<span class="spell-error" style="background-color: #ffcccc; border-bottom: 2px solid #ff0000; cursor: help;" title="Možná chyba pravopisu: $1">$1</span>');
            }
            
            const span = editor.dom.create('span', {'data-spell-check': 'true'}, html);
            // Protože nodeValue je čistý text, ale my vkládáme HTML, musíme to udělat takto:
            // Vytvoříme dočasný element, vložíme HTML a nahradíme původní node
            const tempDiv = editor.dom.create('div', null, html);
            
            // Nahrazení původního uzlu novými uzly (protože html může obsahovat více elementů)
            const parent = node.parentNode;
            while (tempDiv.firstChild) {
                parent.insertBefore(tempDiv.firstChild, node);
            }
            parent.removeChild(node);
        });
    }

    /**
     * Odstraní zvýraznění chyb
     */
    removeHighlighting(editor) {
        const body = editor.getBody();
        const errors = body.querySelectorAll('.spell-error');
        
        errors.forEach(span => {
            const parent = span.parentNode;
            // Nahradí span jeho textovým obsahem
            while (span.firstChild) {
                parent.insertBefore(span.firstChild, span);
            }
            parent.removeChild(span);
        });
        
        // Spojit sousední textové uzly (volitelné, pro čistotu DOM)
        body.normalize();
    }

    /**
     * Escapuje speciální znaky pro regex
     */
    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Escapuje HTML entity
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Zkontroluje, jestli je slovník načten
     */
    isReady() {
        return this.isLoaded;
    }
}

// Export pro použití v TinyMCE
window.SpellChecker = SpellChecker;
