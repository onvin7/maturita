$(document).ready(function() {
    var parallax = -0.3;
    var $parallaxImages = $(".parallax-image");
    var originalOffsets = [];
    var scheduled = false;
    var lastScrollTop = 0;

    $parallaxImages.each(function(i, el) {
        originalOffsets.push($(el).offset().top);
    });

    var updateParallax = function() {
        scheduled = false;
        var dy = lastScrollTop;
        $parallaxImages.each(function(i, el) {
            var originalOffset = originalOffsets[i];
            $(el).css("top", (originalOffset + dy * parallax) + "px");
        });
    };

    $(window).on('scroll', function() {
        lastScrollTop = $(this).scrollTop();
        if (scheduled) {
            return;
        }
        scheduled = true;
        window.requestAnimationFrame(updateParallax);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const gallery = document.querySelector('.galerie.team');

    if (!gallery) {
        return;
    }

    let isDown = false;
    let startX;
    let scrollLeft;

    gallery.addEventListener('mousedown', (e) => {
        isDown = true;
        startX = e.pageX - gallery.offsetLeft;
        scrollLeft = gallery.scrollLeft;
        gallery.classList.add('active');
    });

    gallery.addEventListener('mouseleave', () => {
        isDown = false;
        gallery.classList.remove('active');
    });

    gallery.addEventListener('mouseup', () => {
        isDown = false;
        gallery.classList.remove('active');
    });

    gallery.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - gallery.offsetLeft;
        const walk = (x - startX) * 2;
        gallery.scrollLeft = scrollLeft - walk;
    });
});
