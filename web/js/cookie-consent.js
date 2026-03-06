document.addEventListener('DOMContentLoaded', function() {
    const cookieBanner = document.getElementById('cookie-consent-banner');
    const acceptBtn = document.getElementById('cookie-accept');
    const declineBtn = document.getElementById('cookie-decline');
    const STORAGE_KEY = 'cookie_consent_status';

    // Check if user has already made a choice
    const consentStatus = localStorage.getItem(STORAGE_KEY);

    if (consentStatus) {
        setCookie(STORAGE_KEY, consentStatus, 365);
    }

    if (consentStatus === 'granted') {
        activateTrackingScripts();
    } else if (consentStatus === 'denied') {
        // Do nothing, scripts remain inactive
    } else {
        // Show banner if no choice made
        if (cookieBanner) {
            cookieBanner.style.display = 'block';
        }
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            localStorage.setItem(STORAGE_KEY, 'granted');
            setCookie(STORAGE_KEY, 'granted', 365);
            if (cookieBanner) cookieBanner.style.display = 'none';
            activateTrackingScripts();
        });
    }

    if (declineBtn) {
        declineBtn.addEventListener('click', function() {
            // Uživatel nesouhlasil - banner skryjeme pouze pro tuto relaci (refresh stránky)
            // NEukládáme 'denied' do localStorage/cookie na dlouhou dobu
            if (cookieBanner) cookieBanner.style.display = 'none';
            
            // Pro jistotu smažeme případnou starou volbu, aby se příště ptal znovu
            localStorage.removeItem(STORAGE_KEY);
            document.cookie = STORAGE_KEY + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        });
    }

    // Helper to set cookie
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
    }

    function activateTrackingScripts() {
        const scripts = document.querySelectorAll('script[type="text/plain"][data-cookie-consent="tracking"]');
        
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.type = 'text/javascript';
            
            // Copy attributes
            Array.from(script.attributes).forEach(attr => {
                if (attr.name !== 'type' && attr.name !== 'data-cookie-consent') {
                    newScript.setAttribute(attr.name, attr.value);
                }
            });

            // Copy content
            if (script.innerHTML) {
                newScript.innerHTML = script.innerHTML;
            } else if (script.src) {
                newScript.src = script.src;
                newScript.async = script.async; // Ensure async is preserved/set
            }

            // Replace old script with new one to execute it
            script.parentNode.replaceChild(newScript, script);
        });

        // Also handle noscript tags for Meta Pixel if needed, but usually JS is enough for the main tracking.
        // For noscript, we can't really conditionally load it easily without backend logic, 
        // but modern tracking relies on JS.
    }
});
