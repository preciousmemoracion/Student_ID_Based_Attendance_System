(function () {
    'use strict';

    var CHECK_URL  = 'check_session.php';
    var LOGOUT_URL = '../clear_history.php';
    var isLoggedIn = true;
    var isIntentionalNav = false;
    var nukeTimer = null;

    // ─────────────────────────────────────────────
    //  NUKE HISTORY
    //  Floods browser history so back button has
    //  nowhere to go outside this page
    // ─────────────────────────────────────────────
    function nukeHistory() {
        try {
            history.replaceState({ admin: true, ts: Date.now() }, '', window.location.href);
            for (var i = 0; i < 50; i++) {
                history.pushState({ admin: true, i: i, ts: Date.now() }, '', window.location.href);
            }
        } catch (e) {}
    }

    // Debounced nuke — prevents thrashing on rapid events
    function debouncedNuke() {
        clearTimeout(nukeTimer);
        nukeTimer = setTimeout(nukeHistory, 50);
    }

    // ─────────────────────────────────────────────
    //  MARK INTERNAL NAVIGATION
    //  Whitelist clicks on same-origin links so
    //  they are not blocked
    // ─────────────────────────────────────────────
    function attachLinkListeners() {
        document.querySelectorAll('a[href]').forEach(function (link) {
            var href = link.getAttribute('href') || '';
            var isExternal = href.startsWith('http') || href.startsWith('//') || href.startsWith('mailto');
            if (!isExternal) {
                link.addEventListener('click', function () {
                    isIntentionalNav = true;
                    // Auto-reset in case page doesn't navigate
                    setTimeout(function () { isIntentionalNav = false; }, 5000);
                });
            }
        });

        // Also mark form submits as intentional
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                isIntentionalNav = true;
                setTimeout(function () { isIntentionalNav = false; }, 5000);
            });
        });
    }

    // ─────────────────────────────────────────────
    //  LAYER 1 — popstate
    //  Fires on every back/forward button press
    // ─────────────────────────────────────────────
    window.addEventListener('popstate', function () {
        if (!isIntentionalNav && isLoggedIn) {
            debouncedNuke();
            verifySession();
        }
    });

    // ─────────────────────────────────────────────
    //  LAYER 2 — pageshow
    //  Fires when bfcache restores the page
    //  (Safari / Firefox)
    // ─────────────────────────────────────────────
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            debouncedNuke();
            verifySession();
        }
    });

    // ─────────────────────────────────────────────
    //  LAYER 3 — visibilitychange
    //  Fires when user tabs back to this page
    //  Catches session expiry while tab is hidden
    // ─────────────────────────────────────────────
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            verifySession();
        }
    });

    // ─────────────────────────────────────────────
    //  LAYER 4 — focus
    //  Fires when user switches back to this window
    // ─────────────────────────────────────────────
    window.addEventListener('focus', function () {
        verifySession();
    });

    // ─────────────────────────────────────────────
    //  LAYER 5 — DOMContentLoaded
    // ─────────────────────────────────────────────
    window.addEventListener('DOMContentLoaded', function () {
        attachLinkListeners();
        nukeHistory();
        try {
            var nav = performance.getEntriesByType('navigation');
            if (nav.length > 0 && nav[0].type === 'back_forward') {
                verifySession();
            }
        } catch (e) {}
    });

    // ─────────────────────────────────────────────
    //  LAYER 6 — load
    // ─────────────────────────────────────────────
    window.addEventListener('load', function () {
        attachLinkListeners();
        nukeHistory();
        verifySession();
    });

    // ─────────────────────────────────────────────
    //  LAYER 7 — keyboard shortcuts
    //  Block Alt+Left (back) and stray Backspace
    // ─────────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.altKey && e.key === 'ArrowLeft') {
            e.preventDefault();
            debouncedNuke();
        }
        if (e.altKey && e.key === 'ArrowRight') {
            e.preventDefault(); // block forward too
        }
        if (e.key === 'Backspace') {
            var tag  = document.activeElement ? document.activeElement.tagName : '';
            var edit = document.activeElement ? document.activeElement.isContentEditable : false;
            if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT' && !edit) {
                e.preventDefault();
            }
        }
    });

    // ─────────────────────────────────────────────
    //  LAYER 8 — periodic check every 60 seconds
    //  Catches expired sessions while page is open
    // ─────────────────────────────────────────────
    setInterval(verifySession, 60000);

    // ─────────────────────────────────────────────
    //  SESSION VERIFY
    //  Pings check_session.php — if not logged in,
    //  shows overlay and redirects
    // ─────────────────────────────────────────────
    var verifyInProgress = false;
    function verifySession() {
        if (!isLoggedIn) return;
        if (verifyInProgress) return;
        verifyInProgress = true;

        fetch(CHECK_URL + '?_=' + Date.now(), {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin'
        })
        .then(function (r) {
            verifyInProgress = false;
            if (!r.ok) { showOverlay(); return null; }
            return r.json();
        })
        .then(function (data) {
            if (!data) return;
            if (!data.loggedIn) {
                isLoggedIn = false;
                showOverlay();
            } else {
                // Still logged in — keep history locked
                debouncedNuke();
            }
        })
        .catch(function () {
            verifyInProgress = false;
            // Retry once after 3s before giving up
            setTimeout(function () {
                fetch(CHECK_URL + '?_=' + Date.now(), {
                    cache: 'no-store',
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (data && !data.loggedIn) {
                        isLoggedIn = false;
                        showOverlay();
                    }
                })
                .catch(function () {});
            }, 3000);
        });
    }

    // ─────────────────────────────────────────────
    //  SHOW OVERLAY + COUNTDOWN + HARD REDIRECT
    // ─────────────────────────────────────────────
    var overlayShown = false;
    function showOverlay() {
        if (overlayShown) return;
        overlayShown = true;
        isLoggedIn   = false;

        // Clear the history flood before redirecting
        try {
            history.replaceState(null, '', window.location.href);
        } catch (e) {}

        var el = document.getElementById('session-overlay');
        if (el) {
            el.classList.add('show');
            var countdownEl = document.getElementById('overlay-countdown');
            var secs = 3;
            if (countdownEl) {
                countdownEl.textContent = 'Redirecting in ' + secs + ' seconds\u2026';
                var iv = setInterval(function () {
                    secs--;
                    if (secs <= 0) {
                        clearInterval(iv);
                        countdownEl.textContent = 'Redirecting\u2026';
                    } else {
                        countdownEl.textContent = 'Redirecting in ' + secs + ' seconds\u2026';
                    }
                }, 1000);
            }
        }

        setTimeout(function () {
            window.location.replace(LOGOUT_URL);
        }, 3000);
    }

})();