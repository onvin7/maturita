document.addEventListener('DOMContentLoaded', function() {
    // 1. Najdeme všechny galerie (divy s třídou images-gallery-X)
    const galleries = document.querySelectorAll('div[class*="images-gallery-"]');
    
    if (galleries.length === 0) return;

    // 2. Vytvoříme Lightbox HTML strukturu, pokud neexistuje
    if (!document.getElementById('lightbox')) {
        const lightboxHtml = `
            <div id="lightbox">
                <button id="lightbox-close">&times;</button>
                <div id="lightbox-content">
                    <button id="lightbox-prev" class="lightbox-btn">&#10094;</button>
                    <img id="lightbox-img" src="" alt="Zvětšený obrázek">
                    <button id="lightbox-next" class="lightbox-btn">&#10095;</button>
                </div>
                <div id="lightbox-counter"></div>
                <div id="lightbox-thumbnails"></div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', lightboxHtml);
    }

    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const prevBtn = document.getElementById('lightbox-prev');
    const nextBtn = document.getElementById('lightbox-next');
    const closeBtn = document.getElementById('lightbox-close');
    const counter = document.getElementById('lightbox-counter');
    const thumbnailsContainer = document.getElementById('lightbox-thumbnails');

    let currentGalleryImages = []; // Pole URL obrázků v aktuální galerii
    let currentIndex = 0;

    // 3. Inicializace galerií
    galleries.forEach(gallery => {
        // Přidáme třídu gallery-grid pro CSS stylování
        gallery.classList.add('gallery-grid');
        
        // Získáme všechny obrázky v galerii
        const images = Array.from(gallery.querySelectorAll('img'));
        const count = images.length;
        gallery.setAttribute('data-count', count);
        
        // Wrapper pro každý obrázek (pro lepší stylování a overlay)
        images.forEach((img, index) => {
            // Kontrola, zda už wrapper neexistuje (pro případ opakovaného spuštění)
            if (img.parentElement.classList.contains('gallery-item-wrapper')) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'gallery-item-wrapper';
            
            // Vložíme wrapper před img a přesuneme img do něj
            img.parentNode.insertBefore(wrapper, img);
            wrapper.appendChild(img);
            
            // Kliknutí na wrapper nebo img otevře lightbox
            // Použijeme wrapper pro click event, aby to fungovalo i na overlay
            wrapper.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Zastavíme propagaci
                openLightbox(images, index);
            });

            // Pokud je to 4. obrázek a je jich víc než 4, přidáme overlay "+X"
            // (Pro 4 a více fotek chceme, aby poslední viditelná (čtvrtá) měla overlay, pokud jich je víc)
            // Ale wait, CSS skrývá nth-child(n+6), takže zobrazujeme 5 fotek?
            // Podle CSS:
            // 4 fotky -> layout 1 nahoře, 3 dole. Zobrazí se všechny 4. Overlay netřeba.
            // 5 fotek -> layout 2 nahoře, 3 dole. Zobrazí se všechny 5. Overlay netřeba.
            // 6+ fotek -> layout 2 nahoře, 3 dole. Zobrazí se 5. Pátá (poslední dole vpravo) by měla mít overlay.
            
            // Původní logika:
            // if (count > 5 && index === 4) { ... }
            
            // Nový požadavek pro 4 fotky: "ta napravo uplne musi byt vzdy s tim prekrytim a +(cislo)"
            // To znamená, pokud je layout 1+3 (celkem 4 pozice), tak poslední (4.) pozice má overlay, pokud count > 4.
            // Ale pro 5+ fotek máme jiný layout (2+3 = 5 pozic).
            
            // Upravíme logiku overlaye podle počtu zobrazených pozic v CSS:
            
            let overlayIndex = -1;
            
            if (count > 4) {
                // Pokud máme více než 4 fotky, chceme overlay.
                // Kde se má zobrazit?
                // Pro 5 a více fotek používáme layout 2+3 (5 slotů). Takže na indexu 4 (pátá fotka).
                // Pro přesně 4 fotky (layout 1+3) se zobrazí všechny, overlay netřeba.
                // Pro > 4 fotky, ale < ?
                
                // Uživatel: "ted tam mam 4 fotky v galerii a vypada to hrozne" -> "dej jednu fotku na celou sirku, a potom do druhyho radku dej 1 max 1 fotky" (zmatečné)
                // "ta napravo uplne musi byt vzdy s tim prekrytim a +(cislo) aby vzdy bylo videt ze je to galerie, pokavad tam jsou alespon 3 fotky"
                
                // Interpretace:
                // 3 fotky -> 1 nahoře, 2 dole. Poslední (vpravo dole) má overlay "+X" pokud count > 3? 
                // "kdyz jsou dve tak je dej jen vedle sebe a nedavej tam ten prekryv"
                
                // OK, sjednotíme to:
                // 1 fotka: bez overlay
                // 2 fotky: bez overlay
                // 3 fotky: Layout 1+2. Zobrazí se 3. Pokud count > 3, třetí má overlay.
                // 4 fotky: Layout 1+3. Zobrazí se 4. Pokud count > 4, čtvrtá má overlay.
                // 5+ fotek: Layout 2+3. Zobrazí se 5. Pokud count > 5, pátá má overlay.
                
                if (count > 5) {
                     overlayIndex = 4; // 5. fotka (index 4) má overlay "+(count-5)"
                } else if (count > 4) {
                     // Specifický případ pro přesně 5 fotek v layoutu pro 4? 
                     // Ne, CSS pro 5+ je 2+3. Takže 5 fotek se vejde akorát.
                     // Pokud ale chceme "aby vzdy bylo videt ze je to galerie, pokavad tam jsou alespon 3 fotky"
                     // Tak možná chce overlay i když nejsou skryté fotky? Ne, "+(cislo)" implikuje skryté.
                     
                     // Vraťme se k požadavku: "ta napravo uplne musi byt vzdy s tim prekrytim a +(cislo) ... pokavad tam jsou alespon 3 fotky"
                     // To zní, jako by chtěl overlay vždy na poslední viditelné, pokud je fotek víc než slotů.
                }
            }
            
            // Reimplementace podle CSS limitů:
            // CSS Gallery Grid:
            // data-count="1": 1 slot
            // data-count="2": 2 sloty
            // data-count="3": 2 sloty (1+1, 3. skrytý)
            // data-count="4": 3 sloty (1+2, 4. skrytý)
            // data-count="5+": 5 slotů (2+3, 6+. skryté)
            
            let visibleSlots = count;
            if (count === 3) visibleSlots = 2;
            else if (count === 4) visibleSlots = 3;
            else if (count >= 5) visibleSlots = 5;
            
            // Pokud je fotek víc než viditelných slotů, dáme overlay na poslední viditelný slot
            if (count > visibleSlots) {
                overlayIndex = visibleSlots - 1;
            }
            
            if (index === overlayIndex) {
                const remaining = count - visibleSlots; // Kolik jich zbývá NEZOBRAZENÝCH
                // Pokud remaining > 0, zobrazíme +X.
                // Pokud remaining == 0 (všechny se vešly), nezobrazujeme nic?
                // Ale logika výše (count > visibleSlots) už to řeší.
                
                // ALE: U 5 fotek je visibleSlots=5. count=5. count > visibleSlots je false. Žádný overlay. Správně.
                // U 6 fotek je visibleSlots=5. count=6. count > visibleSlots je true. Overlay na indexu 4. "+1". Správně.
                
                // Co když chce overlay i pro 3 fotky, pokud jich je tam třeba 10?
                // CSS pro 10 fotek použije layout "5 a více", takže visibleSlots=5.
                // Takže u 10 fotek bude overlay na 5. fotce s textem "+5".
                
                // Co když mám 4 fotky a layout pro 4? visibleSlots=4. count=4. Žádný overlay.
                
                // Zkusme interpretovat "aby vzdy bylo videt ze je to galerie".
                // Možná chce overlay i u poslední fotky, i když žádné další nejsou? Ne, to by bylo "+0".
                
                // Jdeme na jistotu: Overlay jen pro skryté fotky.
                
                const overlay = document.createElement('div');
                overlay.className = 'gallery-overlay';
                overlay.textContent = '+' + (remaining + 1); // +1 protože tento slot je taky "překrytý" a slouží jako tlačítko
                
                // Overlay click handler
                overlay.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openLightbox(images, index);
                });
                
                wrapper.appendChild(overlay);
            }
        });
    });

    // 4. Funkce Lightboxu
    let scrollPosition = 0; // Proměnná pro uložení pozice

    function openLightbox(imagesElements, index) {
        // Uložení aktuální pozice scrollu
        scrollPosition = window.scrollY;
        
        // Získáme URL všech obrázků v galerii
        currentGalleryImages = imagesElements.map(img => {
            // Pokud je obrázek obalen odkazem, zkusíme vzít href odkazu (plná verze)
            // Jinak vezmeme src obrázku
            const parentLink = img.closest('a');
            return parentLink ? parentLink.href : img.src;
        });
        
        currentIndex = index;
        
        updateLightboxContent();
        lightbox.classList.add('active');
        lightbox.classList.remove('single'); // Reset pro případ, že předtím byl otevřen single
        
        // Zamezit scrollování stránky (ale zachovat pozici)
        // Kompenzace zmizení scrollbaru (aby obsah neuskočil doprava)
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        
        document.body.style.top = `-${scrollPosition}px`;
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.boxSizing = 'border-box';
        document.body.style.paddingRight = `${scrollbarWidth}px`;
        
        // Klávesnice
        document.addEventListener('keydown', handleKeydown);
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        
        // Obnovit scrollování a pozici
        document.body.style.paddingRight = '';
        document.body.style.boxSizing = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        window.scrollTo(0, scrollPosition);
        
        document.removeEventListener('keydown', handleKeydown);
    }

    function updateLightboxContent() {
        if (!currentGalleryImages[currentIndex]) return;
        
        lightboxImg.src = currentGalleryImages[currentIndex];
        counter.textContent = (currentIndex + 1) + ' / ' + currentGalleryImages.length;
        
        // Šipky
        if (currentGalleryImages.length > 1) {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        } else {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            lightbox.classList.add('single');
        }
        
        // Náhledy
        renderThumbnails();
    }

    function showNext(e) {
        if(e) e.stopPropagation();
        currentIndex = (currentIndex + 1) % currentGalleryImages.length;
        updateLightboxContent();
    }

    function showPrev(e) {
        if(e) e.stopPropagation();
        currentIndex = (currentIndex - 1 + currentGalleryImages.length) % currentGalleryImages.length;
        updateLightboxContent();
    }

    function renderThumbnails() {
        thumbnailsContainer.innerHTML = '';
        
        // Změna: Zobrazit thumbnail i pro jednu fotku, pokud je to "single" režim (samostatná fotka)
        // Původně: if (currentGalleryImages.length <= 1) return;
        
        // Nově: Pokud je to single image (lightbox má třídu 'single'), chceme vidět thumbnail té jedné fotky?
        // Uživatel: "kdyz otevres jen klasickou fotku, tak bud smaz dole ten radek na vsechny ty fotky co tam jsou v galerii a nebo tam dej nahled tehle jedne fotky, spis tam dej nahled te fotky"
        
        // Takže thumbnail strip chceme VŽDY, i když je tam jen jedna fotka.
        // Ale musíme zajistit, aby to vypadalo dobře (vycentrované).
        
        // Pokud je pole prázdné, končíme
        if (currentGalleryImages.length === 0) return;

        currentGalleryImages.forEach((src, idx) => {
            const thumb = document.createElement('img');
            thumb.src = src;
            thumb.className = 'lightbox-thumb';
            if (idx === currentIndex) thumb.classList.add('active');
            
            thumb.addEventListener('click', function(e) {
                e.stopPropagation();
                currentIndex = idx;
                updateLightboxContent();
            });
            
            thumbnailsContainer.appendChild(thumb);
        });
    }

    function handleKeydown(e) {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
    }

    // Event listenery ovládání
    closeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        closeLightbox();
    });
    
    nextBtn.addEventListener('click', showNext);
    prevBtn.addEventListener('click', showPrev);

    // Kliknutí mimo obrázek zavře lightbox
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox || e.target.id === 'lightbox-content') {
            closeLightbox();
        }
    });

    // 5. Oprava pro samostatné obrázky v textu (mimo galerie)
    // Najdeme všechny obrázky v editoru, které nejsou součástí galerie
    const singleImages = document.querySelectorAll('.text-editor img:not(.gallery-grid img)');
    
    singleImages.forEach(img => {
        // Přidáme cursor pointer pro indikaci klikatelnosti
        img.style.cursor = 'pointer';
        
        img.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Uložení aktuální pozice scrollu (stejně jako u galerie)
            scrollPosition = window.scrollY;
            
            // Pro samostatný obrázek vytvoříme "galerii o jednom prvku"
            const src = img.closest('a') ? img.closest('a').href : img.src;
            
            // Nastavíme globální proměnné
            currentGalleryImages = [src];
            currentIndex = 0;
            
            updateLightboxContent();
            lightbox.classList.add('active');
            lightbox.classList.add('single'); // Skryje šipky a náhledy
            
            // Zamezit scrollování stránky (ale zachovat pozici)
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            
            document.body.style.top = `-${scrollPosition}px`;
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.boxSizing = 'border-box';
            document.body.style.paddingRight = `${scrollbarWidth}px`;
            
            document.addEventListener('keydown', handleKeydown);
        });
    });
});