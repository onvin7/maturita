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

            // Pokud je to 5. obrázek a je jich víc než 5, přidáme overlay "+X"
            if (count > 5 && index === 4) {
                const remaining = count - 5;
                const overlay = document.createElement('div');
                overlay.className = 'gallery-overlay';
                overlay.textContent = '+' + (remaining + 1); // +1 protože počítáme i tento pátý
                
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
    function openLightbox(imagesElements, index) {
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
        document.body.style.overflow = 'hidden'; // Zamezit scrollování stránky
        
        // Klávesnice
        document.addEventListener('keydown', handleKeydown);
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
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
        if (currentGalleryImages.length <= 1) return;

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
});