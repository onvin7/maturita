document.addEventListener('DOMContentLoaded', function() {
    initGalleries();
    initStandaloneImages();
    initLightbox();
});

let currentGalleryImages = [];
let currentImageIndex = 0;

function initGalleries() {
    // Hledáme všechny divy, které obsahují třídu začínající na "images-gallery-"
    // To jsou ty, které generuje TinyMCE (např. images-gallery-4)
    const galleries = document.querySelectorAll('div[class*="images-gallery-"]');
    
    galleries.forEach(gallery => {
        // Přidáme naši univerzální třídu pro grid
        gallery.classList.add('gallery-grid');
        
        const images = Array.from(gallery.querySelectorAll('img'));
        const imageCount = images.length;
        
        // Set data attribute for CSS specific layouts
        gallery.setAttribute('data-count', imageCount);
        
        // Configuration
        const maxVisible = 4;
        
        // If we have more images than we want to show
        if (imageCount > maxVisible) {
            // Hide images beyond the limit
            for (let i = maxVisible; i < imageCount; i++) {
                images[i].style.display = 'none';
            }
            
            // Add overlay to the last visible image
            const lastVisibleIndex = maxVisible - 1;
            const lastVisibleImage = images[lastVisibleIndex];
            const remainingCount = imageCount - maxVisible;
            
            // Wrap the last visible image to position overlay
            const wrapper = document.createElement('div');
            wrapper.className = 'gallery-item-wrapper';
            
            // Insert wrapper before image
            lastVisibleImage.parentNode.insertBefore(wrapper, lastVisibleImage);
            
            // Move image into wrapper
            wrapper.appendChild(lastVisibleImage);
            
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'gallery-overlay';
            // Logic: if we have 10 images, max 4 visible.
            // 4th image (index 3) is visible.
            // Hidden: index 4,5,6,7,8,9 (6 images).
            // Overlay says "+6" (meaning 6 MORE images).
            // Or "+7" (meaning 7 images including this one)?
            // Usually "+6" is preferred ("and 6 others").
            overlay.innerHTML = `<span>+${remainingCount}</span>`;
            
            wrapper.appendChild(overlay);
            
            // Add click listener to overlay
            overlay.addEventListener('click', (e) => {
                e.stopPropagation();
                openLightbox(images, lastVisibleIndex);
            });
        }
        
        // Add click listeners to all visible images (including the wrapped one via bubbling or direct)
        images.forEach((img, index) => {
            img.addEventListener('click', () => {
                openLightbox(images, index);
            });
        });
    });
}

function initStandaloneImages() {
    // Select images inside .text-editor that are NOT inside .gallery-container
    // Note: We use .text-editor class which wraps the content in article.php
    const contentImages = document.querySelectorAll('.text-editor img');
    
    contentImages.forEach(img => {
        // Check if parent is not gallery-container (or wrapper inside gallery-container)
        // Používáme closest pro kontrolu, zda je v galerii, a používáme stejný selector jako v initGalleries
        if (!img.closest('div[class*="images-gallery-"]')) {
            // Check if it's not a functional image or inside a link
            if (img.closest('a')) return;

            img.style.cursor = 'pointer';
            img.addEventListener('click', () => {
                // Open lightbox with just this single image
                openLightbox([img], 0);
            });
        }
    });
}

function initLightbox() {
    const closeBtn = document.getElementById('lightbox-close');
    const prevBtn = document.getElementById('lightbox-prev');
    const nextBtn = document.getElementById('lightbox-next');
    const lightbox = document.getElementById('lightbox');
    
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (prevBtn) prevBtn.addEventListener('click', showPrevImage);
    if (nextBtn) nextBtn.addEventListener('click', showNextImage);
    
    // Close on background click
    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox || e.target.id === 'lightbox-content') {
                closeLightbox();
            }
        });
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!document.getElementById('lightbox') || document.getElementById('lightbox').style.display === 'none') return;
        
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') showPrevImage();
        if (e.key === 'ArrowRight') showNextImage();
    });
}

function openLightbox(images, startIndex) {
    currentGalleryImages = images;
    currentImageIndex = startIndex;
    
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const thumbnailsContainer = document.getElementById('lightbox-thumbnails');
    
    if (!lightbox || !lightboxImg) return;
    
    // Show lightbox
    lightbox.style.display = 'flex';
    lightbox.classList.add('active');
    
    // Setup thumbnails
    if (thumbnailsContainer) {
        thumbnailsContainer.innerHTML = '';
        
        // Only show thumbnails if multiple images
        if (images.length > 1) {
            images.forEach((img, index) => {
                const thumb = document.createElement('img');
                thumb.src = img.src;
                thumb.className = 'lightbox-thumb';
                if (index === startIndex) thumb.classList.add('active');
                
                thumb.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showImage(index);
                });
                
                thumbnailsContainer.appendChild(thumb);
            });
            thumbnailsContainer.style.display = 'flex';
        } else {
            thumbnailsContainer.style.display = 'none';
        }
    }
    
    // Single image mode check
    if (images.length === 1) {
        lightbox.classList.add('single');
    } else {
        lightbox.classList.remove('single');
    }
    
    showImage(startIndex);
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.style.display = 'none';
        lightbox.classList.remove('active');
    }
}

function showImage(index) {
    if (index < 0) index = currentGalleryImages.length - 1;
    if (index >= currentGalleryImages.length) index = 0;
    
    currentImageIndex = index;
    
    const img = currentGalleryImages[index];
    const lightboxImg = document.getElementById('lightbox-img');
    const counter = document.getElementById('lightbox-counter');
    
    // Update main image
    lightboxImg.src = img.src;
    lightboxImg.alt = img.alt || '';
    
    // Update counter
    if (counter) {
        if (currentGalleryImages.length > 1) {
            counter.textContent = `${index + 1} / ${currentGalleryImages.length}`;
            counter.style.display = 'block';
        } else {
            counter.style.display = 'none';
        }
    }
    
    // Update thumbnails
    const thumbnails = document.querySelectorAll('.lightbox-thumb');
    thumbnails.forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('active');
            thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
            thumb.classList.remove('active');
        }
    });
}

function showPrevImage(e) {
    if (e) e.stopPropagation();
    showImage(currentImageIndex - 1);
}

function showNextImage(e) {
    if (e) e.stopPropagation();
    showImage(currentImageIndex + 1);
}
