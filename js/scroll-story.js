/* =====================================================
   scroll-story.js - scroll-driven zoom + crossfade
   (vanilla JavaScript, no libraries)
 
   The idea in one sentence:
   scroll position -> progress number from 0 to 1 -> a formula
   turns that number into scale / opacity / translate for each slide.
   ===================================================== */
(function () {
    const story = document.getElementById('scroll-story');
    if (!story) return;
 
    // Users who asked for less motion: leave the slides stacked (see the CSS).
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
 
    const stage  = story.querySelector('.story-stage');
    const header = document.querySelector('.navbar');
 
    // One object per slide, so we don't search the DOM on every frame.
    const slides = Array.from(story.querySelectorAll('.story-slide')).map(function (el) {
        return {
            el: el,
            media: el.querySelector('.story-media'),
            content: el.querySelector('.story-content'),
            last: ''            // remembers what we wrote last time, to skip useless writes
        };
    });
    const count = slides.length;
 
    /* ----- Tuning knobs (change these to taste) ----- */
    const FADE        = 0.12;   // how much of the scroll the crossfade takes (0.12 = 12%)
    const ENTRY_SCALE = 0.12;   // next slide starts 12% zoomed in, then settles to 1
    const SMOOTHING   = 0.1;    // 0.05 = very floaty, 0.3 = almost instant
    const ZOOM_DESKTOP = 10;    // final size = 10x (dives into the coffee). Lower = less zoom
    const ZOOM_MOBILE  = 6;     // phones need less to fill the screen
 
    let maxZoom = ZOOM_DESKTOP;
    let navHeight = 0;
    let scrollable = 1;         // how far we can scroll while the stage is pinned
    let target = 0;             // progress the scroll position asks for
    let current = 0;            // progress we are actually showing (chases target)
    let running = false;
    let inView = true;
 
    /* ----- Small math helpers ----- */
    function clamp(n) { return Math.min(1, Math.max(0, n)); }
 
    // Smoothstep: starts slowly, ends slowly. Makes fades feel soft.
    function smooth(n) { n = clamp(n); return n * n * (3 - 2 * n); }
 
    /* ----- Measure things (only on load / resize, not on every scroll) ----- */
    function measure() {
        navHeight  = header ? header.offsetHeight : 0;
        maxZoom    = window.matchMedia('(max-width: 700px)').matches ? ZOOM_MOBILE : ZOOM_DESKTOP;
 
        story.style.setProperty('--nav-h', navHeight + 'px');
        story.style.setProperty('--slide-count', count);
 
        scrollable = Math.max(1, story.offsetHeight - stage.offsetHeight);
        readScroll();
        render(current);
    }
 
    /* ----- 1) Scroll position -> progress (0 to 1) ----- */
    function readScroll() {
        const top = story.getBoundingClientRect().top;
        // The stage becomes pinned when the section's top reaches the navbar's bottom.
        target = clamp((navHeight - top) / scrollable);
    }
 
    /* ----- 2) Progress -> look of each slide ----- */
    function render(p) {
        const span = 1 / count;   // each slide "owns" this share of the progress
 
        slides.forEach(function (s, i) {
            const appearAt    = i * span;          // middle of the fade-in
            const disappearAt = (i + 1) * span;    // middle of the fade-out
 
            // fade in (first slide is already visible) and fade out (last slide stays)
            const inP  = i === 0         ? 1 : smooth((p - (appearAt - FADE / 2)) / FADE);
            const outP = i === count - 1 ? 0 : smooth((p - (disappearAt - FADE / 2)) / FADE);
 
            const opacity = Math.min(inP, 1 - outP);
 
            // Next slides begin slightly zoomed in and settle down to normal size.
            let scale = 1 + ENTRY_SCALE * (i === 0 ? 0 : (1 - inP));
 
            // Slides that are NOT the last one keep slowly zooming until they fade out.
            if (i < count - 1) {
                const zoomStart = i === 0 ? 0 : appearAt + FADE / 2;
                const zoomEnd   = disappearAt - FADE / 2;   // zoom finishes right when the fade starts
                const zoom = clamp((p - zoomStart) / (zoomEnd - zoomStart));
                scale *= Math.pow(maxZoom, zoom);        // 1 -> maxZoom, same speed all the way
            }
 
            const lift = ((1 - inP) * 28 - outP * 28).toFixed(1);   // text slides up gently
 
            // Build one string; only touch the DOM if something really changed.
            const key = opacity.toFixed(3) + '|' + scale.toFixed(4) + '|' + lift;
            if (key === s.last) return;
            s.last = key;
 
            s.el.style.opacity = opacity.toFixed(3);
            s.el.style.visibility = opacity < 0.01 ? 'hidden' : 'visible';
            s.media.style.transform = 'scale(' + scale.toFixed(4) + ')';
 
            if (s.content) {
                s.content.style.transform = 'translateY(' + lift + 'px)';
            }
        });
    }
 
    /* ----- 3) Easing loop: current slowly chases target ----- */
    function tick() {
        current += (target - current) * SMOOTHING;
 
        if (Math.abs(target - current) < 0.0004) {
            current = target;
            running = false;       // arrived: stop the loop, use no CPU while idle
        }
 
        render(current);
        if (running) requestAnimationFrame(tick);
    }
 
    function onScroll() {
        if (!inView) return;
        readScroll();
        if (!running) {
            running = true;
            requestAnimationFrame(tick);
        }
    }
 
    /* ----- Only work while the section is on (or near) the screen ----- */
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            inView = entries[0].isIntersecting;
            if (inView) onScroll();
        }, { rootMargin: '100px 0px' }).observe(story);
    }
 
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', measure);
    window.addEventListener('load', measure);   // images above may change the layout
    measure();
})();
 