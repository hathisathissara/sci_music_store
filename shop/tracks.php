<?php
// public_html/tracks.php
// Page to display ALL music tracks

// Include common header
require_once 'includes/header.php';

// Include database connection script (relative path from public_html)
// Adjust path if admin dir is located differently
require_once 'admin/includes/db_connection.php'; // Correct path relative to public_html

// Prepare a select statement to get ALL track data
$sql = "SELECT id, name, description, image_name, file_name FROM tracks ORDER BY created_at DESC"; // Select all necessary fields
$result = null; // Initialize result variable
$general_err = ""; // Variable for general errors

// Check if the database connection was successful
if ($link === false) {
    $general_err = "Database connection error. Tracks cannot be displayed.";
    // Optionally log the error: error_log("Tracks page DB connection failed: " . mysqli_connect_error());
} else {
    // Execute the query
    $result = mysqli_query($link, $sql);

    // Check if query execution was successful
    if ($result === false) {
        $general_err = "ERROR: Could not execute query: " . mysqli_error($link);
        // Optionally log the error: error_log("Tracks page DB query failed: " . mysqli_error($link));
    }
}

?>

    <!-- --- Main Content Specific to Tracks Page --- -->
    <!-- The container div is opened in header.php -->

    <h1 class="text-center mb-4">All Sci-Fi Music Tracks</h1>

    <?php if (!empty($general_err)): ?>
         <div class="alert alert-danger text-center"><?php echo $general_err; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php
        // Check if query result is valid and has rows (only display tracks if no general errors)
        if (empty($general_err) && $result && mysqli_num_rows($result) > 0):
        ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4">
                    <div class="card">
                        <?php
                        // ... Image Display Logic ...
                        $image_path = '';
                        $image_exists = false;
                        // Define the public directory where images are stored, relative to this file (tracks.php is in public_html/)
                        $public_image_directory = 'images/tracks/'; // Ensure this is correct relative path

                        if (!empty($row['image_name'])) {
                            // Construct the full server path to check if the file actually exists on disk
                            // __DIR__ gives the directory of the current script (public_html/)
                            $server_image_path = __DIR__ . '/' . $public_image_directory . $row['image_name'];

                            // Check if the file exists on the server
                            if (file_exists($server_image_path)) {
                                // If it exists, construct the public URL path for the <img> src
                                $image_path = $public_image_directory . htmlspecialchars($row['image_name']); // Path for the browser src
                                $image_exists = true;
                            }
                        }
                        ?>

                        <?php if ($image_exists): ?>
                            <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                                No Image
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <?php if (!empty($row['description'])): ?>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <?php endif; ?>
                            <!-- Play and Buy Buttons -->
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Play Button (Now an ANCHOR TAG linking to dedicated player page) -->
                                <a href="player.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm btn-play <?php echo empty($row['file_name']) ? 'disabled' : ''; ?>" <?php echo empty($row['file_name']) ? 'aria-disabled="true"' : ''; ?>>
                                    <!-- Removed spinner HTML here as play happens on a new page -->
                                    Play Preview
                                </a>
                                <!-- Buy Button -->
                                <button class="btn btn-sm btn-primary btn-buy" data-track-name="<?php echo htmlspecialchars($row['name']); ?>">Buy Now</button>
                            </div>
                        </div>
                    </div>
                    <!-- JSON-LD Markup for this Track (Optional on this page) -->
                    <!-- If you want rich results for each track page, keep this -->
                    <!-- Remember to replace https://YOUR_WEBSITE_URL -->
                </div> <!-- Close col-md-4 -->
            <?php endwhile; ?>
        <?php elseif (empty($general_err)): // Display "No tracks found" only if the query was successful but returned no rows ?>
             <div class="col-12">
                 <p class="text-center text-muted">No music tracks available yet.</p>
             </div>
        <?php endif; ?>
    </div> <!-- Close row -->

    <!-- *** Music Player Modal is NOT included on this page *** -->


<?php
// Free result set memory
if ($result) {
    mysqli_free_result($result);
}

// Close database connection (only if it was successfully opened)
if (isset($link) && $link !== false) {
    mysqli_close($link);
}

// Include common footer
require_once 'includes/footer.php';
?>