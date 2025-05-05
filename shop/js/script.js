// public_html/js/script.js

document.addEventListener('DOMContentLoaded', function () {

    // ** --- Configuration --- **
    const whatsappPhoneNumber = '94701207991'; // Replace with actual number
    const messageTemplate = 'I Want To Buy This Track: '; // Or your preferred message

    // Removed Music Player Configuration variables (previewDurationSeconds, previewTimer)
    // Removed Element References for Modal, Audio Player, etc.

    // ** --- GSAP Animations (Optional) --- **
    // Make sure you have GSAP included in your HTML if you use these
    if (typeof gsap !== 'undefined') {
        gsap.from(".card", { duration: 1, y: 50, opacity: 0, stagger: 0.2, ease: "back.out(1.7)" });
        gsap.from("h1", { duration: 1, opacity: 0, y: -30, delay: 0.5 });
        // Add other page-specific animations if needed, maybe wrap in page checks
    } else {
        console.warn("GSAP library not loaded. Animations skipped.");
    }


    // ** --- Particles.js Initialization --- **
    // Check if particlesJS function exists before calling it (from CDN)
    if (typeof particlesJS === 'function') {
        particlesJS('particles-js', { // Use the ID of the container div
            "particles": {
                "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": ["#00ffcc", "#ffffff", "#cccccc", "#cc00ff"] }, // Sci-Fi colors (Cyan, White, Light Grey, Magenta)
                "shape": { "type": "circle", "stroke": { "width": 0, "color": "#000000" }, "polygon": { "nb_sides": 5 } },
                "opacity": { "value": 0.6, "random": false, "anim": { "enable": false, "speed": 1, "opacity_min": 0.1, "sync": false } },
                "size": { "value": 3, "random": true, "anim": { "enable": false, "speed": 40, "size_min": 0.1, "sync": false } },
                "line_linked": { "enable": true, "distance": 150, "color": "#00ffff", "opacity": 0.4, "width": 1 },
                "move": { "enable": true, "speed": 2, "direction": "none", "random": false, "straight": false, "out_mode": "out", "bounce": false, "attract": { "enable": false, "rotateX": 600, "rotateY": 1200 } }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": { "onhover": { "enable": true, "mode": "grab" }, "onclick": { "enable": true, "mode": "push" }, "resize": true },
                "modes": {
                    "grab": { "distance": 140, "line_linked": { "opacity": 1 } },
                    "bubble": { "distance": 400, "size": 40, "duration": 2, "opacity": 8, "speed": 3 },
                    "repulse": { "distance": 200, "duration": 0.4 },
                    "push": { "particles_nb": 4 },
                    "remove": { "particles_nb": 2 }
                }
            },
            "retina_detect": true
        });
        console.log("Particles.js initialized.");
    } else {
        console.error("particlesJS function not found. Particles.js CDN might not be loaded or script order is wrong.");
    }


    // ** --- Page Specific Logic --- **
    // This script now handles only the Buy buttons on listing pages and any general JS needed on all pages.
    // Player page logic is handled directly on player.php using <audio controls>.

    const buyButtons = document.querySelectorAll('.btn-buy');
    // Play buttons are now links, so no click listener needed for them in this script

    // Check if buy buttons exist on this page (index.php or tracks.php)
    if (buyButtons.length > 0) {

        console.log("Initializing Buy Button JS logic.");

        // Add listeners to Buy Buttons
        buyButtons.forEach(button => {
            // Use the previously defined handler function
            button.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default button action

                const trackName = this.getAttribute('data-track-name');
                const fullMessage = `${messageTemplate}${trackName}`;
                const encodedMessage = encodeURIComponent(fullMessage);
                const whatsappUrl = `https://wa.me/${whatsappPhoneNumber}?text=${encodedMessage}`;

                window.open(whatsappUrl, '_blank');
                console.log(`Attempting to open WhatsApp for track: ${trackName}`);
            });
        });

    } else {
        // This log will show on pages like About, Contact, Player.php unless they have .btn-buy
        console.log("Buy buttons (.btn-buy) not found on this page. Skipping Buy button JS logic.");
    }

    // Removed Music Player Modal related logic entirely from here.
    // Removed handlePlayButtonClick function.
    // Removed handleModalHide function.
    // Removed Modal hide listener initialization.
    // Removed formatTime helper function (as it was only used for the Modal text).


    // ** --- Other Page Specific Initializations (if any) --- **
    // You can add other page-specific JS initializations here if needed,
    // wrapped in checks for elements that only exist on those pages.
    // E.g., specific animations for About page, a contact form handler on Contact page.

}); // End of DOMContentLoaded