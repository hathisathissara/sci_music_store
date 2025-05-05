<?php
// public_html/tracks.php
// Page to display ALL music tracks with search functionality

// Include common header
require_once 'includes/header.php';

// Include database connection script (relative path from public_html)
require_once 'admin/includes/db_connection.php'; // Correct path relative to public_html

// Initialize variables
$search_query = ""; // Variable to hold the search query
$sql = ""; // Variable to hold the SQL query string
$param_types = ""; // Variable to hold parameter types string for prepared statement
$params = []; // Array to hold parameters for prepared statement
$general_err = ""; // Variable for general errors
$search_message = ""; // Message to display if search results are filtered

// Check if the database connection was successful
if ($link === false) {
    $general_err = "Database connection error. Tracks cannot be displayed.";
    // Optionally log the error: error_log("Tracks page DB connection failed: " . mysqli_connect_error());
} else {

    // --- Handle Search Query ---
    // Check if a search query was submitted via GET
    if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
        // Sanitize and store the search query
        $search_query = trim($_GET['search_query']);

        // Build the SQL query for searching
        // Search case-insensitive in 'name' and 'description' fields
        $sql = "SELECT id, name, description, image_name, file_name FROM tracks WHERE LOWER(name) LIKE ? OR LOWER(description) LIKE ? ORDER BY created_at DESC";

        // Set parameter types and parameters for the prepared statement
        $param_types = "ss";
        // Add '%' wildcards for partial matching and convert query to lowercase
        $like_search_query = '%' . strtolower($search_query) . '%';
        $params = [$like_search_query, $like_search_query]; // Add the query twice for name and description

        $search_message = "Showing tracks matching: <strong>" . htmlspecialchars($search_query) . "</strong>";

    } else {
        // If no search query, select all tracks
        $sql = "SELECT id, name, description, image_name, file_name FROM tracks ORDER BY created_at DESC";
        // No parameters needed in this case
        $param_types = "";
        $params = [];
         $search_query = ""; // Ensure search_query is empty if form was submitted empty
    }

    // --- Execute the SQL Query using Prepared Statement ---
    $result = null; // Initialize result variable

    if (empty($general_err)) { // Only proceed if no database connection error

        if ($stmt = mysqli_prepare($link, $sql)) {

            // Bind parameters if they exist
            if (!empty($params)) {
                // mysqli_stmt_bind_param requires parameters passed by reference.
                // This requires a bit of a workaround if parameters are in an array.
                // However, for a fixed number of parameters like 2 ('ss'), we can bind manually.
                if ($param_types === "ss") {
                    mysqli_stmt_bind_param($stmt, $param_types, $params[0], $params[1]);
                }
                // If you had more complex dynamic binding, you'd use call_user_func_array
                // call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt, $param_types], $refs));
            }

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

            } else {
                $general_err = "ERROR: Could not execute query. " . mysqli_stmt_error($stmt);
                 // Optionally log the error: error_log("Tracks page DB query execute failed: " . mysqli_stmt_error($stmt));
            }

             // Close statement
            mysqli_stmt_close($stmt);

        } else {
            $general_err = "ERROR: Could not prepare query. " . mysqli_error($link);
             // Optionally log the error: error_log("Tracks page DB query prepare failed: " . mysqli_error($link));
        }
    }
} // End of database connection check


// Close database connection (only if it was successfully opened)
if (isset($link) && $link !== false) {
    mysqli_close($link);
}

?>

    <!-- --- Main Content Specific to Tracks Page --- -->
    <!-- The container div is opened in header.php -->

    <h1 class="text-center mb-4">All Sci-Fi Music Tracks</h1>

    <!-- --- Search Form --- -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <form method="get" action="tracks.php">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search tracks..." name="search_query" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>
    <!-- --- End Search Form --- -->

    <?php
    // Display general error message if any
    if (!empty($general_err)) {
        echo '<div class="alert alert-danger text-center">' . $general_err . '</div>';
    }

    // Display search message if a search was performed
     if (!empty($search_query) && empty($general_err)) {
         echo '<div class="alert alert-info text-center">' . $search_message . '</div>';
     }
    ?>

    <div class="row">
        <?php
        // Check if query result is valid and has rows (only display tracks if no general errors)
        if (empty($general_err) && $result && mysqli_num_rows($result) > 0):
        ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4">
                    <div class="card">
                        <?php
                        // ... Image Display Logic (as in previous update) ...
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
                                <!-- Play Button (Anchor linking to dedicated player page) -->
                                <a href="player.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm btn-play <?php echo empty($row['file_name']) ? 'disabled' : ''; ?>" <?php echo empty($row['file_name']) ? 'aria-disabled="true"' : ''; ?>>
                                    Play Preview
                                </a>
                                <!-- Buy Button -->
                                <button class="btn btn-sm btn-primary btn-buy" data-track-name="<?php echo htmlspecialchars($row['name']); ?>">Buy Now</button>
                            </div>
                        </div>
                    </div>
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

// Close database connection is done above


// Include common footer
require_once 'includes/footer.php';
?>