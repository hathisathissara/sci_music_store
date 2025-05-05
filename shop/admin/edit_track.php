<?php
// admin/edit_track.php

// Include necessary files
require_once 'includes/auth.php'; // Check if admin is logged in
require_once 'includes/db_connection.php'; // Database connection
require_once 'includes/config.php'; // Configuration settings (upload paths, etc.)

// Define variables and initialize with empty values
// Variables to hold current track data (fetched from DB)
$id = $name = $description = $file_name_current = $image_name_current = "";

// Variables to hold submitted data (if form is posted with errors)
$name_post = $description_post = ""; // Hold values from $_POST for sticky form

// Variables to hold NEW generated filenames after successful upload (for database)
$music_file_name_new = $image_file_name_new = "";

// Error variables
$name_err = $description_err = ""; // Text field errors
$music_file_err = $image_file_err = ""; // File upload errors
$general_err = $success_msg = "";

// Check if the database connection was successful
if ($link === false) {
    $general_err = "Database connection error.";
    // Optionally log the error
    // error_log("Database connection failed in edit_track.php: " . mysqli_connect_error());
} else {
    // Ensure upload directories exist (create them if they don't)
    if (!is_dir(UPLOAD_DIR_MUSIC)) {
        if (!mkdir(UPLOAD_DIR_MUSIC, 0755, true)) {
             $general_err .= " Could not create music upload directory.";
        }
    }
    if (!is_dir(UPLOAD_DIR_IMAGES)) {
         if (!mkdir(UPLOAD_DIR_IMAGES, 0755, true)) {
             $general_err .= " Could not create image upload directory.";
         }
    }
    // Check if directories are writable
     if (!is_writable(UPLOAD_DIR_MUSIC)) {
         $general_err .= " Music upload directory is not writable.";
     }
     if (!is_writable(UPLOAD_DIR_IMAGES)) {
         $general_err .= " Image upload directory is not writable.";
     }


    // --- Process GET request (Display form with current data) ---
    // Check if id parameter is set in the URL
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
            // Get URL parameter
            $id = trim($_GET["id"]);

            // Prepare a select statement
            $sql = "SELECT id, name, description, file_name, image_name FROM tracks WHERE id = ?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "i", $param_id);

                // Set parameters
                $param_id = $id;

                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($result) == 1) {
                        // Fetch result row
                        $row = mysqli_fetch_assoc($result);

                        // Retrieve individual field value
                        $name = $row["name"];
                        $description = $row["description"];
                        $file_name_current = $row["file_name"]; // Store current file names
                        $image_name_current = $row["image_name"]; // Store current file names

                        // Populate post variables for sticky form (initially same as current)
                        $name_post = $name;
                        $description_post = $description;

                    } else {
                        // URL doesn't contain valid id. Redirect to error page or tracks page
                        header("location: tracks.php"); // Or an error page
                        exit();
                    }

                } else {
                    $general_err = "Oops! Something went wrong retrieving track data. Please try again later.";
                    // Log error: error_log("MySQL Execute Error in edit_track.php GET: " . mysqli_stmt_error($stmt));
                }

                // Close statement
                mysqli_stmt_close($stmt);
            } else {
                 $general_err = "Oops! Something went wrong preparing select query. Please try again later.";
                 // Log error: error_log("MySQL Prepare Error in edit_track.php GET: " . mysqli_error($link));
            }
        } else {
            // URL doesn't contain id parameter. Redirect to tracks page
            header("location: tracks.php");
            exit();
        }
    }

    // --- Process POST request (Submit form data) ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($general_err)) {

        // Get hidden ID and current file names from form (or re-fetch)
        // Re-fetching current file names from DB is safer against tampering
         if (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
             $id = trim($_POST["id"]); // Get ID from hidden input

             // Re-fetch current file names from DB for potential deletion later
             $sql_fetch_current = "SELECT file_name, image_name FROM tracks WHERE id = ?";
              if ($stmt_fetch = mysqli_prepare($link, $sql_fetch_current)) {
                 mysqli_stmt_bind_param($stmt_fetch, "i", $param_id);
                 $param_id = $id;
                 if (mysqli_stmt_execute($stmt_fetch)) {
                     $result_fetch = mysqli_stmt_get_result($stmt_fetch);
                     if (mysqli_num_rows($result_fetch) == 1) {
                         $row_current = mysqli_fetch_assoc($result_fetch);
                         $file_name_current = $row_current["file_name"];
                         $image_name_current = $row_current["image_name"];
                     } else {
                         // Track not found based on submitted ID - critical error
                         $general_err = "Error: Track not found for update.";
                         // Log error: error_log("Track ID not found for update in edit_track.php POST: " . $id);
                     }
                 } else {
                     $general_err = "Error fetching current file names.";
                      // Log error: error_log("MySQL Execute Error fetching current files in edit_track.php POST: " . mysqli_stmt_error($stmt_fetch));
                 }
                  mysqli_stmt_close($stmt_fetch);
              } else {
                  $general_err = "Error preparing fetch current file names query.";
                  // Log error: error_log("MySQL Prepare Error fetching current files in edit_track.php POST: " . mysqli_error($link));
              }

         } else {
             // ID not submitted in POST form - critical error
             $general_err = "Error: Track ID not provided for update.";
             // Log error: error_log("Track ID missing in edit_track.php POST");
         }


        // Proceed with update only if ID is valid and current file names fetched (or confirmed not found)
         if (!empty($id) && empty($general_err)) {

             // 1. Validate Text Fields
            // Validate name
            if (empty(trim($_POST["name"]))) {
                $name_err = "Please enter a track name.";
            } else {
                $name_post = trim($_POST["name"]); // Store in post var for sticky form
            }

            // Description is optional
            $description_post = trim($_POST["description"]); // Store in post var for sticky form


            // 2. Handle File Uploads (Similar to add_track.php, but need to track new names)

            // --- Handle Music File Upload ---
            if (isset($_FILES["music_file"]) && $_FILES["music_file"]["error"] === UPLOAD_ERR_OK) {
                $music_file = $_FILES["music_file"];

                // Validate file type, size, etc.
                 $file_type = mime_content_type($music_file["tmp_name"]);
                 if (!in_array($file_type, ALLOWED_MUSIC_TYPES)) {
                      $music_file_err = "Invalid music file type. Allowed types: " . implode(', ', ALLOWED_MUSIC_TYPES);
                 }
                 if ($music_file["size"] > MAX_FILE_SIZE) {
                     $music_file_err = "Music file is too large. Max size: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
                 }
                 if (!is_uploaded_file($music_file["tmp_name"])) {
                      $music_file_err = "Invalid music file upload operation.";
                 }

                // If no file errors, process the upload
                if (empty($music_file_err)) {
                    // Generate a unique filename
                    $file_extension = pathinfo($music_file["name"], PATHINFO_EXTENSION);
                    $music_file_name_new = uniqid() . '.' . $file_extension; // Store the NEW name
                    $target_file_path = UPLOAD_DIR_MUSIC . $music_file_name_new;

                    // Move the uploaded file
                    if (move_uploaded_file($music_file["tmp_name"], $target_file_path)) {
                        // File moved successfully. Now DELETE the old file if it exists.
                        if (!empty($file_name_current) && file_exists(UPLOAD_DIR_MUSIC . $file_name_current)) {
                            if (!unlink(UPLOAD_DIR_MUSIC . $file_name_current)) {
                                // Log or report cleanup error, but don't stop the update
                                error_log("Failed to delete old music file: " . UPLOAD_DIR_MUSIC . $file_name_current);
                            }
                        }
                    } else {
                        $music_file_err = "Error uploading new music file.";
                         // Log error: error_log("File move failed for music in edit_track.php: " . error_get_last()['message']);
                    }
                }

            } elseif (isset($_FILES["music_file"]) && $_FILES["music_file"]["error"] !== UPLOAD_ERR_NO_FILE) {
                 // Handle other potential upload errors for music file
                 $music_file_err = "Music file upload error: " . $_FILES["music_file"]["error"]; // Use a more user-friendly message
            }
            // Note: If no new file uploaded (UPLOAD_ERR_NO_FILE), $music_file_name_new remains empty.


            // --- Handle Image File Upload ---
            if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] === UPLOAD_ERR_OK) {
                $image_file = $_FILES["image_file"];

                // Validate file type, size, etc.
                 $file_type = mime_content_type($image_file["tmp_name"]);
                  if (!in_array($file_type, ALLOWED_IMAGE_TYPES)) {
                      $image_file_err = "Invalid image file type. Allowed types: " . implode(', ', ALLOWED_IMAGE_TYPES);
                 }
                 if ($image_file["size"] > MAX_FILE_SIZE) {
                     $image_file_err = "Image file is too large. Max size: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
                 }
                if (!is_uploaded_file($image_file["tmp_name"])) {
                     $image_file_err = "Invalid image file upload operation.";
                }

                // If no image errors, process the upload
                if (empty($image_file_err)) {
                    // Generate a unique filename
                    $file_extension = pathinfo($image_file["name"], PATHINFO_EXTENSION);
                    $image_file_name_new = uniqid() . '.' . $file_extension; // Store the NEW name
                    $target_image_path = UPLOAD_DIR_IMAGES . $image_file_name_new;

                    // Move the uploaded file
                    if (move_uploaded_file($image_file["tmp_name"], $target_image_path)) {
                         // File moved successfully. Now DELETE the old file if it exists.
                         if (!empty($image_name_current) && file_exists(UPLOAD_DIR_IMAGES . $image_name_current)) {
                             if (!unlink(UPLOAD_DIR_IMAGES . $image_name_current)) {
                                 // Log or report cleanup error
                                 error_log("Failed to delete old image file: " . UPLOAD_DIR_IMAGES . $image_name_current);
                             }
                         }
                    } else {
                        $image_file_err = "Error uploading new image file.";
                         // Log error: error_log("File move failed for image in edit_track.php: " . error_get_last()['message']);
                    }
                }

            } elseif (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] !== UPLOAD_ERR_NO_FILE) {
                 // Handle other potential upload errors for image file
                 $image_file_err = "Image file upload error: " . $_FILES["image_file"]["error"]; // Use a more user-friendly message
            }
             // Note: If no new file uploaded (UPLOAD_ERR_NO_FILE), $image_file_name_new remains empty.


            // 3. Update Database (only if NO errors occurred)
            if (empty($name_err) && empty($music_file_err) && empty($image_file_err) && empty($general_err)) {

                // Determine which file name to save in the database
                // If a new file was uploaded, use the new name. Otherwise, keep the current name.
                $file_name_to_save = !empty($music_file_name_new) ? $music_file_name_new : $file_name_current;
                $image_name_to_save = !empty($image_file_name_new) ? $image_file_name_new : $image_name_current;


                // Prepare an update statement
                $sql = "UPDATE tracks SET name=?, description=?, file_name=?, image_name=? WHERE id=?";

                if ($stmt = mysqli_prepare($link, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    // 'ssssi' indicates 4 strings and 1 integer
                    mysqli_stmt_bind_param($stmt, "ssssi", $param_name, $param_description, $param_file_name, $param_image_name, $param_id);

                    // Set parameters using the new/current filenames
                    $param_name = $name_post;
                    $param_description = $description_post;
                    $param_file_name = $file_name_to_save; // Use the determined filename
                    $param_image_name = $image_name_to_save; // Use the determined filename
                    $param_id = $id; // Use the ID from the hidden field

                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Update successful. Redirect back to tracks list.
                         header("location: tracks.php");
                         exit();
                    } else {
                        $general_err = "ERROR: Could not execute database update query. " . mysqli_stmt_error($stmt);
                         // Log error: error_log("MySQL Execute Error in edit_track.php UPDATE: " . mysqli_stmt_error($stmt));

                        // IMPORTANT: If database UPDATE fails *after* new files were moved, clean up the NEW files
                        if (!empty($music_file_name_new) && file_exists(UPLOAD_DIR_MUSIC . $music_file_name_new)) {
                             unlink(UPLOAD_DIR_MUSIC . $music_file_name_new); // Delete new music file
                        }
                         if (!empty($image_file_name_new) && file_exists(UPLOAD_DIR_IMAGES . $image_file_name_new)) {
                             unlink(UPLOAD_DIR_IMAGES . $image_file_name_new); // Delete new image file
                        }
                    }

                    // Close statement
                    mysqli_stmt_close($stmt);
                } else {
                    $general_err = "ERROR: Could not prepare database update query. " . mysqli_error($link);
                     // Log error: error_log("MySQL Prepare Error in edit_track.php UPDATE: " . mysqli_error($link));
                }
            }
            // If there are any errors (text field or file upload), the script will fall through
            // to display the form with the relevant error messages and sticky data.

            // If update was successful, we would have already redirected.
            // If update failed or had errors, we display the form with sticky data.
            // So, refresh the current variables with post values to make the form sticky.
            $name = $name_post;
            $description = $description_post;
            // file_name_current and image_name_current retain values fetched initially or re-fetched on POST

         } // End of if(!empty($id) && empty($general_err)) for POST processing

    } // End of POST method processing

     // If GET request or POST with errors, display the form.
     // If POST was successful, the script would have redirected.

} // End of database connection check

// Close database connection at the very end, only if it was successfully opened
if (isset($link) && $link !== false) {
     mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Track - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 600px; margin: auto; margin-top: 50px; }
        .help-block { font-size: 0.8em; color: #dc3545; } /* Style for error messages */
        .form-text { font-size: 0.8em; } /* Style for muted text */
        .current-file-info {
            font-size: 0.9em;
            color: #6c757d; /* Muted grey */
            margin-top: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Edit Music Track</h2>
        <p>Please edit the track details below.</p>

        <?php
        // Display general error message
        if(!empty($general_err)){
            echo '<div class="alert alert-danger">' . $general_err . '</div>';
        }
        // Display success message (Less likely here as we redirect on success)
         if(!empty($success_msg)){
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>

        <?php if (empty($general_err) && !empty($id) || $_SERVER["REQUEST_METHOD"] == "POST" && !empty($id) && !empty($general_err)): ?>
         <!-- Show form only if track data was fetched successfully (GET) or if POST had errors -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <!-- Hidden input field to send the track ID -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

            <div class="form-group">
                <label>Track Name</label>
                <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                <span class="invalid-feedback"><?php echo $name_err; ?></span>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($description); ?></textarea>
                <span class="invalid-feedback"><?php echo $description_err; ?></span>
            </div>

             <div class="form-group">
                <label>Music File (MP3, WAV, etc.)</label>
                 <!-- Display current file info -->
                <?php if (!empty($file_name_current)): ?>
                     <div class="current-file-info">Current File: <code><?php echo htmlspecialchars($file_name_current); ?></code></div>
                <?php else: ?>
                    <div class="current-file-info text-muted">No music file currently uploaded.</div>
                <?php endif; ?>
                 <!-- File input for new file -->
                <input type="file" name="music_file" class="form-control-file <?php echo (!empty($music_file_err)) ? 'is-invalid' : ''; ?>">
                 <?php if (!empty($music_file_err)): ?>
                     <span class="help-block"><?php echo $music_file_err; ?></span>
                 <?php endif; ?>
                 <small class="form-text text-muted">Upload a new music file to replace the current one. Max size: <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB. Allowed types: <?php echo implode(', ', array_map(function($mime) { return str_replace('audio/', '', $mime); }, ALLOWED_MUSIC_TYPES)); ?></small>
            </div>

             <div class="form-group">
                <label>Thumbnail Image (JPG, PNG, GIF, etc.)</label>
                 <!-- Display current file info -->
                 <?php if (!empty($image_name_current)): ?>
                     <div class="current-file-info">Current File: <code><?php echo htmlspecialchars($image_name_current); ?></code></div>
                 <?php else: ?>
                     <div class="current-file-info text-muted">No image currently uploaded.</div>
                 <?php endif; ?>
                  <!-- File input for new file -->
                <input type="file" name="image_file" class="form-control-file <?php echo (!empty($image_file_err)) ? 'is-invalid' : ''; ?>">
                 <?php if (!empty($image_file_err)): ?>
                     <span class="help-block"><?php echo $image_file_err; ?></span>
                 <?php endif; ?>
                  <small class="form-text text-muted">Upload a new image file to replace the current one. Max size: <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB. Allowed types: <?php echo implode(', ', array_map(function($mime) { return str_replace('image/', '', $mime); }, ALLOWED_IMAGE_TYPES)); ?></small>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Save Changes">
                <a href="tracks.php" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
        <?php elseif (empty($general_err) && empty($id)): ?>
            <!-- This case should ideally not happen if redirected correctly -->
            <div class="alert alert-warning">No track ID provided for editing.</div>
             <p><a href="tracks.php" class="btn btn-secondary mt-3">Back to Tracks</a></p>
        <?php endif; ?>

    </div>
</body>
</html>

<?php
// Free result set memory if result was fetched successfully
if (isset($result) && $result) {
    mysqli_free_result($result);
}
// Database connection is closed at the very end outside the if/else blocks
?>