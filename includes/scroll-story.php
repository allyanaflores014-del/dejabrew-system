<?php
/*
 * scroll-story.php
 * ----------------
 * Cinematic scroll section. The page height is made of the section's own
 * height (see css/scroll-story.css); the .story-stage inside it stays
 * "stuck" on screen while you scroll, and js/scroll-story.js changes the
 * slides based on how far you have scrolled.
 *
 * To add another slide: copy one <div class="story-slide"> block and
 * paste it before the closing </div> of .story-stage. Nothing else to change.
 */
?>
<section class="scroll-story" id="scroll-story" aria-label="Deja Brew story" style="--slide-count: 2">
    <div class="story-stage">

        <!-- ========== SLIDE 1: top-view coffee cup ========== -->
        <div class="story-slide story-slide--cup">
            <div class="story-media">
                <img class="story-bg" src="assets/slide-1.png"
                    alt="Top view of a cup of black coffee on a caramel background">
            </div>
        </div>

        <!-- ========== SLIDE 2: call to action ========== -->
        <div class="story-slide story-slide--cta">
            <div class="story-media">
                <img class="story-bg" src="assets/slide-2-bg.png" alt="">
                <img class="story-cup" src="assets/ChocolateFrappe.png"
                    alt="Chocolate frappe topped with whipped cream">
                <img class="story-cup" src="assets/ChocolateFrappe.png"
                    alt="Chocolate frappe topped with whipped cream">
            </div>

            <div class="story-content">
                <h2 class="story-heading">
                    <span class="story-heading-line">Ready to get</span>
                    <span class="story-heading-line story-heading-accent">Deja Brew?</span>
                </h2>
                <a href="#menu" class="story-btn">Order Now!</a>
            </div>
        </div>

    </div>
</section>