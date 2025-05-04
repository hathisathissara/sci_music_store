<?php
// admin/tracks.php

// Include necessary files
require_once 'includes/auth.php'; // Admin Authentication Check
require_once 'includes/db_connection.php'; // Database connection
require_once 'includes/config.php'; // Configuration settings (for upload paths)

// Prepare a select statement
$sql = "SELECT id, name, description, image_name, created_at FROM tracks ORDER BY created_at DESC";
$result = null; // Initialize result variable

// Check if the database connection was successful before querying
if ($link === false) {
    // Handle connection error - Display an error message or log
    $general_err = "Database connection error.";
    // Optionally log: error_log("Admin tracks DB connection failed: " . mysqli_connect_error());
} else {
    // Execute the query
    $result = mysqli_query($link, $sql);

    // Check if query execution was successful
    if ($result === false) {
        // Handle query error
        $general_err = "ERROR: Could not execute query: " . mysqli_error($link);
        // Optionally log: error_log("Admin tracks DB query failed: " . mysqli_error($link));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tracks - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 90%; margin: auto; margin-top: 50px; } /* Adjusted width for better display */
        table th, table td { vertical-align: middle; }
        .track-image {
            width: 50px;
            height: auto;
            display: block; /* To center or control margin if needed */
            margin: auto;
        }
         .no-image-placeholder {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef; /* Light grey placeholder */
            color: #6c757d; /* Dark grey text */
            font-size: 0.7em;
            text-align: center;
            line-height: 1em;
            padding: 5px;
         }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Manage Music Tracks</h2>
        <p><a href="add_track.php" class="btn btn-success mb-3">Add New Track</a></p>

         <?php
         // Display general error message
         if(!empty($general_err)){
             echo '<div class="alert alert-danger">' . $general_err . '</div>';
         }
         ?>


        <?php
        // Check if query result is valid and has rows
        if ($result && mysqli_num_rows($result) > 0):
        ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                        <td>
                            <?php
                            $image_src = ''; // Variable for the <img> src attribute
                            $image_found = false;

                            if (!empty($row['image_name'])) {
                                // Construct the full server path to the image file using UPLOAD_DIR_IMAGES from config.php
                                // This is needed for file_exists check on the server's file system
                                $server_image_path = UPLOAD_DIR_IMAGES . $row['image_name'];

                                // Check if the image file exists on the server
                                if (file_exists($server_image_path)) {
                                    // If the file exists, construct the public URL path for the browser's <img> tag
                                    // Relative path from admin/ directory to public_html/images/tracks/
                                    // ../ goes up one dir (to project root)
                                    // public_html/images/tracks/ is the path from project root
                                    $public_image_url_path = '../images/tracks/'; // <-- Corrected public path

                                    $image_src = $public_image_url_path . htmlspecialchars($row['image_name']);
                                    $image_found = true;
                                }
                            }
                            ?>
                            <?php if ($image_found): ?>
                                <!-- Use the constructed image_src for the img tag -->
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?> Thumbnail" class="track-image">
                            <?php else: ?>
                                <!-- Placeholder if no image name in DB or file not found -->
                                <div class="no-image-placeholder">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="edit_track.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_track.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this track?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($result !== false): // Display "No tracks found" only if the query was successful but returned no rows ?>
            <p>No tracks found.</p>
        <?php endif; ?>

        <?php
        // Free result set memory if the query was successful
        if ($result) {
            mysqli_free_result($result);
        }
        ?>

        <p><a href="index.php" class="btn btn-secondary mt-3">Back to Dashboard</a></p>
    </div>
</body>
</html>

<?php
// Close database connection at the very end, only if it was successfully opened
if (isset($link) && $link !== false) {
     mysqli_close($link);
}
?>