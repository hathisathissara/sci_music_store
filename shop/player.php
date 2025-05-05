<?php
// public_html/player.php
// Dedicated page to play a single track preview

// Include common header
require_once 'includes/header.php';

// Include database connection script
require_once 'admin/includes/db_connection.php'; // Adjust path relative to public_html

// Initialize variables
$track = null;
$general_err = "";
$track_id = null;

// Store the referrer URL for the back button (fallback to tracks.php)
$referrer = $_SERVER['HTTP_REFERER'] ?? 'tracks.php';
// Prevent redirecting back to the player page itself if somehow refreshed
if (strpos($referrer, 'player.php') !== false) {
    $referrer = 'tracks.php';
}


// Check if track ID is provided in the URL
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $track_id = trim($_GET['id']);

    // Validate track ID (ensure it's a positive integer)
    if (!filter_var($track_id, FILTER_VALIDATE_INT) || $track_id <= 0) {
        $general_err = "Invalid track ID.";
    } else {
        // Fetch the specific track details from the database
        $sql = "SELECT id, name, description, file_name, image_name FROM tracks WHERE id = ?";
        if ($link === false) {
             $general_err = "Database connection error.";
        } elseif ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $track_id;

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result) == 1) {
                    $track = mysqli_fetch_assoc($result);
                } else {
                    $general_err = "Track not found.";
                }
                mysqli_free_result($result);
            } else {
                $general_err = "Error fetching track data.";
            }
            mysqli_stmt_close($stmt);
        } else {
             $general_err = "Error preparing database query.";
        }
    }
} else {
    $general_err = "Track ID not provided.";
}

// Close database connection after fetching data
if (isset($link) && $link !== false) {
     mysqli_close($link);
}

// Construct the music serving URL
$music_serve_url = ($track && !empty($track['file_name'])) ? "serve_music.php?id=" . $track['id'] : null;

// Image path logic for the player page
$image_path = '';
$image_exists = false;
$public_image_directory = 'images/tracks/'; // Ensure correct path relative to player.php (in public_html/)

if ($track && !empty($track['image_name'])) {
     $server_image_path = __DIR__ . '/' . $public_image_directory . $track['image_name'];
     if (file_exists($server_image_path)) {
         $image_path = $public_image_directory . htmlspecialchars($track['image_name']);
         $image_exists = true;
     }
}

// Define preview duration here as it's specific to this page's logic
$preview_duration_seconds = 30;


?>

    <!-- --- Main Content Specific to Player Page --- -->
    <!-- The container div is opened in header.php -->

    <h1 class="text-center mb-4">Track Details</h1>

    <?php if (!empty($general_err)): ?>
        <div class="alert alert-danger text-center"><?php echo $general_err; ?></div>
    <?php elseif ($track): ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                     <div class="row g-0 align-items-center">
                         <div class="col-md-4">
                             <?php if ($image_exists): ?>
                                 <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($track['name']); ?>" class="img-fluid rounded-start" style="height: auto; max-height: 250px; object-fit: cover; width: 100%; border-right: 1px solid #00ffff;">
                             <?php else: ?>
                                 <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-start" style="height: 250px; object-fit: cover; width: 100%; border-right: 1px solid #00ffff;">
                                     No Image
                                 </div>
                              <?php endif; ?>
                         </div>
                         <div class="col-md-8">
                            <div class="card-body">
                                <h2 class="card-title"><?php echo htmlspecialchars($track['name']); ?></h2>
                                <?php if (!empty($track['description'])): ?>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($track['description'])); ?></p>
                                <?php endif; ?>

                                <!-- Audio Player for the Preview -->
                                <?php if ($music_serve_url): ?>
                                     <div class="mt-4 text-center">
                                         <h3 class="h5">Track Preview</h3>
                                          <!-- Use controls attribute to show default player controls -->
                                          <!-- Add ID for JS reference -->
                                          <!-- *** Add AUTOPLAY attribute here *** -->
                                         <audio id="playerPageAudio" controls preload="metadata" style="width: 100%;" autoplay>
                                             <source src="<?php echo $music_serve_url; ?>" type="audio/mpeg"> <!-- Assuming MP3 is common -->
                                             Your browser does not support the audio element.
                                         </audio>
                                          <!-- Add a span to show remaining time -->
                                         <p class="text-muted small mt-2" id="playerPageTimerText">Preview ends in <?php echo $preview_duration_seconds; ?> seconds.</p>
                                     </div>
                                <?php else: ?>
                                     <div class="mt-4 text-center">
                                         <p class="alert alert-warning">Music file not available for preview.</p>
                                     </div>
                                <?php endif; ?>

                                <!-- Buy Now Button on the player page -->
                                <div class="mt-4 text-center">
                                    <button class="btn btn-primary btn-buy" data-track-name="<?php echo htmlspecialchars($track['name']); ?>">Buy Now via WhatsApp</button>
                                </div>

                            </div>
                         </div>
                     </div>
                </div>
                 <!-- Link back to tracks list or referrer page -->
                <div class="text-center mt-3">
                     <a href="<?php echo htmlspecialchars($referrer); ?>" class="btn btn-secondary" id="closePlayerButton">Close Preview / Back</a>
                </div>
            </div>
        </div>
    <?php else: ?>
         <!-- This block handles cases where general_err is not set but track is null -->
         <div class="alert alert-danger text-center">Could not load track details.</div>
         <div class="text-center mt-3">
              <a href="<?php echo htmlspecialchars($referrer); ?>" class="btn btn-secondary">Back</a>
         </div>
    <?php endif; ?>


    <!-- *** JavaScript specific to this player page *** -->
    <?php if ($music_serve_url): // Only include JS if there is a track to play ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const audioPlayer = document.getElementById('playerPageAudio');
            const timerTextElement = document.getElementById('playerPageTimerText');
            const closeButton = document.getElementById('closePlayerButton'); // Get the close button
            const previewDuration = <?php echo $preview_duration_seconds; ?>;
            let timerInterval; // To update the countdown text
            let autoCloseTimeout; // To handle the 30-second auto close

            // Function to format time (copy from script.js)
            function formatTime(seconds) {
                 if (isNaN(seconds) || seconds < 0) return '0:00';
                 const minutes = Math.floor(seconds / 60);
                 const remainingSeconds = Math.floor(seconds % 60);
                 const paddedSeconds = remainingSeconds < 10 ? '0' + remainingSeconds : remainingSeconds;
                 return `${minutes}:${paddedSeconds}`;
             }

            // Function to handle auto-closing
            function autoClosePlayer() {
                console.log("Auto-closing player.");
                // Pause playback and clear timers before redirecting
                 if (audioPlayer) {
                     audioPlayer.pause();
                     audioPlayer.currentTime = 0; // Reset time
                     // Remove listeners to prevent issues after redirect
                     audioPlayer.onplay = null;
                     audioPlayer.onpause = null;
                     audioPlayer.onended = null;
                     audioPlayer.ontimeupdate = null;
                     audioPlayer.onerror = null;
                 }
                  if (timerInterval) clearInterval(timerInterval);
                  if (autoCloseTimeout) clearTimeout(autoCloseTimeout);

                // Redirect to the referrer page or tracks.php
                 window.location.href = "<?php echo htmlspecialchars($referrer); ?>";
            }

             // Function to update the timer countdown text
             function updateTimerText() {
                 // Check if audioPlayer is still valid and currentTime is available
                 if (!audioPlayer || isNaN(audioPlayer.currentTime)) {
                      clearInterval(timerInterval); // Stop the timer if audio state is invalid
                      timerTextElement.textContent = "Preview timer stopped.";
                      return;
                 }

                 const currentTime = audioPlayer.currentTime;
                 const remaining = previewDuration - currentTime;

                 if (remaining > 0) {
                     // Round up remaining time for display (e.g., 29.1 becomes 30)
                     timerTextElement.textContent = `Preview ends in ${Math.ceil(remaining)} seconds.`;
                 } else {
                      timerTextElement.textContent = `Preview ended.`;
                      clearInterval(timerInterval); // Stop the timer
                       // Auto-close will be triggered by the timeout, but this handles display if timeout is slightly delayed
                 }
             }


            // --- Event Listeners ---

             // Add listener for when playback starts
             // Note: Autoplay might trigger this, or user might press play button
             audioPlayer.onplay = function() {
                 console.log("Playback started.");
                 // Set timeout for auto-close after preview duration
                 // Clear any previous timeout or interval before setting new ones
                 if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
                 if (timerInterval) clearInterval(timerInterval);

                 // Calculate remaining time based on current position
                 const remainingTime = previewDuration - audioPlayer.currentTime;

                 if (remainingTime > 0) {
                     autoCloseTimeout = setTimeout(autoClosePlayer, remainingTime * 1000);

                     // Start updating the countdown timer text
                     timerInterval = setInterval(updateTimerText, 1000); // Update every second
                     updateTimerText(); // Update immediately

                 } else {
                      // If already past preview duration (e.g., user seeked)
                      autoClosePlayer(); // Auto-close immediately
                 }
             };

             // Listen for when playback is paused
             audioPlayer.onpause = function() {
                 console.log("Playback paused.");
                 // Clear timeout and interval when paused
                 if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
                 if (timerInterval) clearInterval(timerInterval);
             };

             // Listen for when playback ends naturally (e.g., short track finishes)
             audioPlayer.onended = function() {
                 console.log("Playback ended naturally.");
                 // Clear timeout and interval and trigger close
                 if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
                 if (timerInterval) clearInterval(timerInterval);
                 timerTextElement.textContent = `Preview ended.`;
                  // Optional: Auto-redirect after natural end? Redirect will happen if it's within preview duration
                  // If track is shorter than preview, onended fires before timeout.
                  // Auto-closePlayer will be called by timeout unless track is very short.
             };


            // Optional: Handle manual seek (user dragging the seek bar)
             audioPlayer.onseeking = function() {
                 console.log("Seeking...");
                 // Pause timers while seeking
                 if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
                 if (timerInterval) clearInterval(timerInterval);
             };
             audioPlayer.onseeked = function() {
                  console.log("Seeked.");
                  // Resume timers after seeking finishes IF playback is resumed
                  // The onplay listener will handle restarting timers if the user presses play after seeking
                  // If seeking while playing, onpause -> onseeking -> onseeked -> onplay might fire depending on browser
                  // Simple approach: just update text after seeked if not paused
                  if (!audioPlayer.paused) {
                       updateTimerText(); // Update the displayed time
                  }
             };


            // Listen for errors during loading or playback
            audioPlayer.onerror = function() {
                console.error("Audio playback error:", audioPlayer.error);
                timerTextElement.textContent = "Error playing preview.";
                if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
                if (timerInterval) clearInterval(timerInterval);
                 // Keep the page open to show the error message
            };


             // Add a click listener to the Close button for manual close
             // The button is already an anchor tag linking back, but this adds consistency
             if(closeButton) {
                 closeButton.addEventListener('click', function(event) {
                      // We don't prevent default here because it's a link and browser handles redirect
                      console.log("Close button clicked. Redirecting...");
                      // Clean up timers just in case before browser redirects
                      if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
                      if (timerInterval) clearInterval(timerInterval);
                      if (audioPlayer) audioPlayer.pause(); // Pause audio on manual close
                 });
                 // Ensure the close button is visible if track is found (default display is usually fine)
                 // closeButton.style.display = 'inline-block';
             }

             // *** Auto-play Attempt via JavaScript ***
             // Try to play the audio as soon as metadata is loaded.
             // This works in many browsers if the user click to get to this page is considered a gesture.
             // Use a separate function for clarity and potential retry logic
             function attemptAutoplay() {
                 // Check if audio player is ready and not paused
                 if (audioPlayer && audioPlayer.readyState >= 2 && audioPlayer.paused) { // readyState 2 means enough data for metadata
                     console.log("Attempting to autoplay via JS...");
                     audioPlayer.play().then(() => {
                         console.log("Autoplay started successfully.");
                         // onplay listener will handle setting timers and updating text
                     }).catch(error => {
                         console.warn("Autoplay failed (JS play promise rejected):", error);
                         timerTextElement.textContent = "Click play button to start preview.";
                         // Show controls if autoplay fails so user can manually play
                          audioPlayer.controls = true; // Make sure controls are visible if autoplay fails
                     });
                 } else if (audioPlayer && !audioPlayer.paused) {
                     console.log("Audio is already playing (perhaps via HTML autoplay).");
                     // Timers and text update will be handled by onplay listener
                 } else {
                      console.log("Audio player not ready to autoplay yet. Waiting for metadata.");
                       // The onloadedmetadata listener should trigger the JS play attempt there
                 }
             }


            // --- Initial Setup ---

            // Display total preview time immediately
             if (timerTextElement) {
                 timerTextElement.textContent = `Preview duration: ${formatTime(previewDuration)} seconds.`;
             }

            // Set the onloadedmetadata listener to attempt play AND set up subsequent listeners/timers
            // Ensure this is set BEFORE load() is called (which happens via the src attribute when the page loads)
            audioPlayer.onloadedmetadata = function() {
                 console.log("Metadata loaded for JS autoplay attempt.");
                 // Hide default controls initially if you only want custom ones OR if autoplay fails
                 // audioPlayer.controls = false; // Controls are visible by default due to HTML attribute

                 // Attempt to play using JS after metadata is loaded
                 attemptAutoplay();

                  // Remove this listener after it fires once to prevent multiple calls
                  audioPlayer.onloadedmetadata = null; // Remove the listener itself

                  // The onplay listener (defined above) will handle starting timers after successful play.
            };

            // Set the onerror listener
             audioPlayer.onerror = function() {
                  console.error("Error loading audio source (during metadata load or play).");
                  timerTextElement.textContent = "Error loading audio source.";
                  // Clear timers if error occurs
                  if (autoCloseTimeout) clearTimeout(autoCloseTimeout);
                  if (timerInterval) clearInterval(timerInterval);
                   if (audioPlayer) audioPlayer.controls = true; // Show controls if error
                   // Remove listener
                   audioPlayer.onerror = null;
             };

             // If autoplay is expected to work via HTML attribute or browser policy,
             // the onplay event should fire shortly after onloadedmetadata (or even before onloadedmetadata in some cases).

             // Fallback: If onloadedmetadata doesn't fire (e.g. file not found/invalid) and no onerror,
             // the attemptAutoplay might still be needed later. However, relying on onloadedmetadata/onerror is best.

             // If you want the JS play attempt to run even BEFORE metadata, move attemptAutoplay call here:
             // attemptAutoplay(); // UNCOMMENT THIS LINE to try playing immediately on DOMContentLoaded


        }); // End of DOMContentLoaded
    </script>
    <?php endif; ?>


<?php
// Include common footer
require_once 'includes/footer.php';
?>