/* =====================================================
   notifications.js - shows the popup messages one at a time
   (vanilla JavaScript)
 
   Timeline:
     1.5s after the page loads  -> message 1 slides in
     stays 7s (or until closed) -> slides out
     0.7s later                 -> message 2 slides in ... and so on
   Hovering a message pauses it so people have time to read.
   Messages are shown once per browser session (sessionStorage),
   so refreshing the page does not bring them back.
   ===================================================== */
(function () {
    const messages = Array.from(document.querySelectorAll('.notif'));
    if (messages.length === 0) return;
 
    const SEEN_KEY      = 'dejabrewNotifsSeen';
    const FIRST_DELAY   = 1500;   // wait before the first message
    const VISIBLE_TIME  = 9000;   // how long each message stays
    const RESUME_TIME   = 3000;   // time left after the mouse leaves a message
    const GAP_TIME      = 700;    // pause between two messages
    const SLIDE_OUT     = 500;    // must match the CSS transition
 
    // Already shown in this session? Do nothing.
    try {
        if (sessionStorage.getItem(SEEN_KEY)) return;
        sessionStorage.setItem(SEEN_KEY, '1');
    } catch (error) {
        // storage blocked: just show them every time
    }
 
    let index = 0;
    let current = null;
    let hideTimer;
 
    function showNext() {
        if (index >= messages.length) return;
 
        current = messages[index];
        index++;
 
        current.hidden = false;
        void current.offsetWidth;               // lets the browser register the start position
        current.classList.add('is-visible');    // CSS slides it in
 
        startTimer(VISIBLE_TIME);
    }
 
    function startTimer(time) {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(hideCurrent, time);
    }
 
    function hideCurrent() {
        if (!current) return;
 
        const leaving = current;
        current = null;
        clearTimeout(hideTimer);
 
        leaving.classList.remove('is-visible');   // CSS slides it out
        setTimeout(function () {
            leaving.hidden = true;
            setTimeout(showNext, GAP_TIME);       // next message
        }, SLIDE_OUT);
    }
 
    messages.forEach(function (message) {
        // Pause while the mouse or keyboard focus is on the message.
        message.addEventListener('pointerenter', function () { clearTimeout(hideTimer); });
        message.addEventListener('pointerleave', function () {
            if (message === current) startTimer(RESUME_TIME);
        });
        message.addEventListener('focusin', function () { clearTimeout(hideTimer); });
        message.addEventListener('focusout', function () {
            if (message === current) startTimer(RESUME_TIME);
        });
 
        // The x button
        message.querySelector('.notif-close').addEventListener('click', function () {
            if (message === current) hideCurrent();
        });
    });
 
    // Esc closes the message that is on screen.
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') hideCurrent();
    });
 
    setTimeout(showNext, FIRST_DELAY);
})();