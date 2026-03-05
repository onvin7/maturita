const fs = require('fs');
const path = require('path');

// --- DOM MOCKING ---
class Node {
    constructor(nodeType, nodeValue) {
        this.nodeType = nodeType;
        this.nodeValue = nodeValue;
        this.childNodes = [];
        this.parentNode = null;
        this.tagName = null;
    }
    
    appendChild(child) {
        child.parentNode = this;
        this.childNodes.push(child);
    }

    insertBefore(newNode, referenceNode) {
        const index = this.childNodes.indexOf(referenceNode);
        if (index !== -1) {
            this.childNodes.splice(index, 0, newNode);
            newNode.parentNode = this;
        } else {
            this.appendChild(newNode);
        }
    }

    removeChild(child) {
        const index = this.childNodes.indexOf(child);
        if (index !== -1) {
            this.childNodes.splice(index, 1);
            child.parentNode = null;
        }
    }
}

const NodeFilter = {
    SHOW_TEXT: 4
};

class TreeWalker {
    constructor(root) {
        this.root = root;
        this.currentNode = root;
        this.nodes = [];
        this._collectTextNodes(root);
        this.index = -1;
    }

    _collectTextNodes(node) {
        if (node.nodeType === 3) { // Text node
            this.nodes.push(node);
        }
        for (const child of node.childNodes) {
            this._collectTextNodes(child);
        }
    }

    nextNode() {
        this.index++;
        if (this.index < this.nodes.length) {
            this.currentNode = this.nodes[this.index];
            return this.currentNode;
        }
        return null;
    }
}

const document = {
    createTreeWalker: (root) => new TreeWalker(root),
    createElement: (tag) => {
        const node = new Node(1, null);
        node.tagName = tag.toUpperCase();
        return node;
    },
    createTextNode: (text) => new Node(3, text)
};

global.NodeFilter = NodeFilter;
global.document = document;
global.window = {};

// --- MOCK FETCH ---
global.fetch = async (url) => {
    // Convert URL to local path
    // url is like '/js/hunspell/cs_CZ.aff'
    const relativePath = url.replace(/^\//, 'web/'); 
    const filePath = path.join(__dirname, relativePath);
    
    console.log(`[MOCK FETCH] Loading: ${filePath}`);
    
    try {
        const content = fs.readFileSync(filePath, 'utf8');
        return {
            text: async () => content
        };
    } catch (e) {
        console.error(`[MOCK FETCH] Error loading ${filePath}: ${e.message}`);
        throw e;
    }
};

// --- LOAD SPELLCHECKER ---
// We need to read the file and eval it, or require it if it was a module.
// Since it's a browser script, we'll read and eval.
const spellCheckerCode = fs.readFileSync('web/js/spellchecker.js', 'utf8');
// Remove "window.SpellChecker = SpellChecker;" to avoid error if window is not perfect, 
// or just let it run since we mocked window.
eval(spellCheckerCode);

// --- RUN TESTS ---
async function runTests() {
    console.log("🚀 Spouštím Node.js testy pro SpellChecker...\n");

    const checker = new SpellChecker();
    
    // Wait for dictionary to load
    await checker.loadDictionary();
    
    if (!checker.isReady()) {
        console.error("❌ Nepodařilo se načíst slovník.");
        return;
    }
    
    console.log("✅ Slovník načten.");

    let passed = 0;
    let failed = 0;

    function assert(condition, message) {
        if (condition) {
            console.log(`✅ ${message}`);
            passed++;
        } else {
            console.error(`❌ ${message}`);
            failed++;
        }
    }

    // TEST 1: Basic words
    const knownWords = ['ahoj', 'svět', 'člověk', 'práce', 'kolo'];
    for (const word of knownWords) {
        const errors = checker.checkText(word);
        assert(errors.length === 0, `Slovo '${word}' by mělo být správně.`);
    }

    // TEST 2: Bad words
    const badWords = ['ahjo', 'svtě', 'clovek', 'praceee']; // 'clovek' without diacritics might be wrong in strict mode
    for (const word of badWords) {
        const errors = checker.checkText(word);
        assert(errors.length > 0, `Slovo '${word}' by mělo být chyba.`);
    }

    // TEST 3: Sentence
    const sentence = "Ahoj světe, toto je tst.";
    const errors = checker.checkText(sentence);
    assert(errors.includes('tst'), "Mělo by najít chybu 'tst'");
    assert(!errors.includes('ahoj'), "Nemělo by označit 'ahoj'");

    // TEST 4: Diacritics extraction
    const diaText = "Příliš žluťoučký kůň";
    const words = checker.extractWords(diaText);
    assert(words.includes('příliš'), "Extrakce 'Příliš' -> 'příliš'");
    assert(words.includes('žluťoučký'), "Extrakce 'žluťoučký'");
    assert(words.includes('kůň'), "Extrakce 'kůň'");

    // TEST 5: HTML stripping logic in extractWords
    const htmlText = "<p>Slovo <strong>tučně</strong>.</p>";
    const extracted = checker.extractWords(htmlText);
    assert(extracted.includes('slovo'), "Extrakce z HTML: slovo");
    assert(extracted.includes('tučně'), "Extrakce z HTML: tučně");
    assert(!extracted.includes('strong'), "Ignorování tagů");

    // TEST 6: Variants generation logic
    const variants = checker.generateVariants('cyklistou');
    // 'ou' is an ending. 'cyklist' should be a variant?
    // Let's check what variants are generated
    // console.log('Variants for cyklistou:', variants);
    assert(variants.includes('cyklist'), "Generování variant pro 'cyklistou'");

    console.log(`\nVýsledky: ${passed} prošlo, ${failed} selhalo.`);
}

runTests();
