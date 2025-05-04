<?php
// admin/delete_track.php

// Include necessary files
require_once 'includes/auth.php'; // Check if admin is logged in
require_once 'includes/db_connection.php'; // Database connection
require_once 'includes/config.php'; // Configuration settings (for upload paths)

// Initialize variables for error messages
$general_err = "";
$delete_success = false; // We'll redirect on success, but keep for potential debugging

// Check if the database connection was successful
if ($link === false) {
    $general_err = "Database connection error.";
    // Optionally log the error
    // error_log("Database connection failed in delete_track.php: " . mysqli_connect_error());
} else {

    // Check if the id parameter is set in the URL and is not empty
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        // Get URL parameter
        $id = trim($_GET["id"]);

        // Validate id (ensure it's a positive integer)
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
            $general_err = "Invalid track ID.";
        } else {
            // Proceed only if the ID is valid
            // 1. Fetch current file names from the database before deleting the record
            $file_name_to_delete = null;
            $image_name_to_delete = null;

            $sql_fetch = "SELECT file_name, image_name FROM tracks WHERE id = ?";
            if ($stmt_fetch = mysqli_prepare($link, $sql_fetch)) {
                mysqli_stmt_bind_param($stmt_fetch, "i", $param_id_fetch);
                $param_id_fetch = $id;

                if (mysqli_stmt_execute($stmt_fetch)) {
                    $result_fetch = mysqli_stmt_get_result($stmt_fetch);

                    if (mysqli_num_rows($result_fetch) == 1) {
                        // Track found, fetch file names
                        $row_fetch = mysqli_fetch_assoc($result_fetch);
                        $file_name_to_delete = $row_fetch["file_name"];
                        $image_name_to_delete = $row_fetch["image_name"];

                        // 2. Prepare a delete statement for the database record
                        $sql_delete = "DELETE FROM tracks WHERE id = ?";

                        if ($stmt_delete = mysqli_prepare($link, $sql_delete)) {
                            // Bind parameters
                            mysqli_stmt_bind_param($stmt_delete, "i", $param_id_delete);
                            $param_id_delete = $id; // Use the validated ID

                            // Attempt to execute the delete statement
                            if (mysqli_stmt_execute($stmt_delete)) {
                                // Database record deleted successfully. Now delete the files.

                                // 3. Delete the associated music file from the server
                                if (!empty($file_name_to_delete)) {
                                    $server_music_file_path = UPLOAD_DIR_MUSIC . $file_name_to_delete;
                                    if (file_exists($server_music_file_path)) {
                                        if (!unlink($server_music_file_path)) {
                                            // Failed to delete music file - log the error
                                            error_log("Failed to delete music file during track deletion: " . $server_music_file_path);
                                            // We might still redirect, database record is deleted
                                            $general_err = "Track deleted, but failed to delete the music file.";
                                        }
                                    } else {
                                         // File not found on server - could happen if DB record was inconsistent
                                         error_log("Music file not found on disk during track deletion: " . $server_music_file_path);
                                    }
                                }

                                // 4. Delete the associated image file from the server
                                if (!empty($image_name_to_delete)) {
                                    $server_image_file_path = UPLOAD_DIR_IMAGES . $image_name_to_delete;
                                    if (file_exists($server_image_file_path)) {
                                        if (!unlink($server_image_file_path)) {
                                            // Failed to delete image file - log the error
                                            error_log("Failed to delete image file during track deletion: " . $server_image_file_path);
                                            // Add to general error, but still redirect
                                            if (empty($general_err)) {
                                                $general_err = "Track deleted, but failed to delete the image file.";
                                            } else {
                                                 $general_err .= " Also failed to delete the image file.";
                                            }
                                        }
                                    } else {
                                         // File not found on server
                                         error_log("Image file not found on disk during track deletion: " . $server_image_file_path);
                                    }
                                }

                                // Set success flag (though we redirect immediately)
                                $delete_success = true;

                                // Redirect back to the tracks list page
                                header("location: tracks.php");
                                exit(); // Stop execution after redirect

                            } else {
                                // Error executing the delete query
                                $general_err = "ERROR: Could not delete track from database. " . mysqli_stmt_error($stmt_delete);
                                 // Log error: error_log("MySQL Execute Error in delete_track.php DELETE: " . mysqli_stmt_error($stmt_delete));
                            }

                            // Close delete statement
                            mysqli_stmt_close($stmt_delete);

                        } else {
                            // Error preparing the delete query
                            $general_err = "ERROR: Could not prepare delete query. " . mysqli_error($link);
                             // Log error: error_log("MySQL Prepare Error in delete_track.php DELETE: " . mysqli_error($link));
                        }

                    } else {
                        // Track not found for the given ID
                        $general_err = "Track not found.";
                         // Log error: error_log("Track ID not found for deletion: " . $id);
                    }

                    // Free fetch result memory
                    mysqli_free_result($result_fetch);

                } else {
                    // Error executing the fetch query
                    $general_err = "Oops! Something went wrong fetching track data for deletion. Please try again later.";
                     // Log error: error_log("MySQL Execute Error in delete_track.php FETCH: " . mysqli_stmt_error($stmt_fetch));
                }

                 // Close fetch statement
                mysqli_stmt_close($stmt_fetch);

            } else {
                 // Error preparing the fetch query
                 $general_err = "Oops! Something went wrong preparing fetch query for deletion. Please try again later.";
                  // Log error: error_log("MySQL Prepare Error in delete_track.php FETCH: " . mysqli_error($link));
            }
        }
    } else {
        // ID parameter was not passed in the URL
        $general_err = "No track ID provided for deletion.";
        // Log error: error_log("Track ID missing in delete_track.php");
    }
} // End of database connection check

// --- HTML Output ---
// This page typically redirects immediately on success.
// If there are errors, we will display them here.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Track - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; text-align: center;}
        .wrapper{ width: 400px; padding: 20px; margin: auto; margin-top: 50px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Delete Track</h2>
        <?php
        // Display general error message
        if(!empty($general_err)){
            echo '<div class="alert alert-danger">' . $general_err . '</div>';
        } elseif ($delete_success) {
             // This success message is less likely to be seen because of the redirect
             echo '<div class="alert alert-success">Track deleted successfully! Redirecting...</div>';
        } else {
            // Should not happen if GET and POST logic is correct, but a fallback
             echo '<div class="alert alert-info">Processing deletion...</div>';
        }
        ?>
        <p><a href="tracks.php" class="btn btn-secondary mt-3">Go to Tracks List</a></p>
    </div>
</body>
</html>

<?php
// Close database connection at the very end, only if it was successfully opened
if (isset($link) && $link !== false) {
     mysqli_close($link);
}
?>