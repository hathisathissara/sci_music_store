<?php
// admin/add_track.php

// Include necessary files
require_once 'includes/auth.php'; // Check if admin is logged in
require_once 'includes/db_connection.php'; // Database connection
require_once 'includes/config.php'; // Configuration settings (including upload paths)

// Define variables and initialize with empty values
$name = $description = ""; // Text fields
$music_file_name = $image_file_name = ""; // Variables to store NEW generated filenames (for database)

$name_err = $description_err = ""; // Text field errors
$music_file_err = $image_file_err = ""; // File upload errors
$general_err = $success_msg = "";

// Check if the database connection was successful
if ($link === false) {
    $general_err = "Database connection error.";
    // Optionally log the error
    // error_log("Database connection failed in add_track.php: " . mysqli_connect_error());
} else {
    // Ensure upload directories exist (create them if they don't)
    if (!is_dir(UPLOAD_DIR_MUSIC)) {
        mkdir(UPLOAD_DIR_MUSIC, 0755, true); // Create recursively with permissions
    }
    if (!is_dir(UPLOAD_DIR_IMAGES)) {
        mkdir(UPLOAD_DIR_IMAGES, 0755, true); // Create recursively with permissions
    }
    // Check if directories are writable
    if (!is_writable(UPLOAD_DIR_MUSIC)) {
        $general_err .= " Music upload directory is not writable.";
    }
    if (!is_writable(UPLOAD_DIR_IMAGES)) {
        $general_err .= " Image upload directory is not writable.";
    }


    // Processing form data when form is submitted ONLY if connection and directories are okay
    if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($general_err)) {

        // 1. Validate Text Fields
        // Validate name
        if (empty(trim($_POST["name"]))) {
            $name_err = "Please enter a track name.";
        } else {
            $name = trim($_POST["name"]);
        }

        // Description is optional
        $description = trim($_POST["description"]);


        // 2. Handle File Uploads

        // --- Handle Music File Upload ---
        if (isset($_FILES["music_file"]) && $_FILES["music_file"]["error"] === UPLOAD_ERR_OK) {
            $music_file = $_FILES["music_file"];

            // Validate file type (MIME type)
            $file_type = mime_content_type($music_file["tmp_name"]); // Get MIME type
            if (!in_array($file_type, ALLOWED_MUSIC_TYPES)) {
                $music_file_err = "Invalid music file type. Allowed types: " . implode(', ', ALLOWED_MUSIC_TYPES);
            }

            // Validate file size
            if ($music_file["size"] > MAX_FILE_SIZE) {
                $music_file_err = "Music file is too large. Max size: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
            }

            // Check if the file is an actual uploaded file
            if (!is_uploaded_file($music_file["tmp_name"])) {
                $music_file_err = "Invalid file upload operation.";
            }

            // If no file errors, process the upload
            if (empty($music_file_err)) {
                // Generate a unique filename to prevent conflicts
                $file_extension = pathinfo($music_file["name"], PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_extension;
                $target_file_path = UPLOAD_DIR_MUSIC . $new_file_name;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($music_file["tmp_name"], $target_file_path)) {
                    $music_file_name = $new_file_name; // Store the new name for the database
                } else {
                    $music_file_err = "Error uploading music file.";
                    // Optionally log the specific error: error_log("File move failed for music: " . error_get_last()['message']);
                }
            }
        } elseif (isset($_FILES["music_file"]) && $_FILES["music_file"]["error"] !== UPLOAD_ERR_NO_FILE) {
            // Handle other potential upload errors (beyond NO_FILE)
            $music_file_err = "Music file upload error: " . $_FILES["music_file"]["error"]; // Use a more user-friendly message in production
        }
        // If UPLOAD_ERR_NO_FILE or no file field exists, $music_file_name remains empty, which is okay for optional fields.


        // --- Handle Image File Upload ---
        if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] === UPLOAD_ERR_OK) {
            $image_file = $_FILES["image_file"];

            // Validate file type (MIME type)
            $file_type = mime_content_type($image_file["tmp_name"]); // Get MIME type
            if (!in_array($file_type, ALLOWED_IMAGE_TYPES)) {
                $image_file_err = "Invalid image file type. Allowed types: " . implode(', ', ALLOWED_IMAGE_TYPES);
            }

            // Validate file size
            if ($image_file["size"] > MAX_FILE_SIZE) { // Using the same max size for images for simplicity
                $image_file_err = "Image file is too large. Max size: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
            }

            // Check if the file is an actual uploaded file
            if (!is_uploaded_file($image_file["tmp_name"])) {
                $image_file_err = "Invalid file upload operation.";
            }

            // If no image errors, process the upload
            if (empty($image_file_err)) {
                // Generate a unique filename
                $file_extension = pathinfo($image_file["name"], PATHINFO_EXTENSION);
                $new_image_name = uniqid() . '.' . $file_extension;
                $target_image_path = UPLOAD_DIR_IMAGES . $new_image_name;

                // Move the uploaded file
                if (move_uploaded_file($image_file["tmp_name"], $target_image_path)) {
                    $image_file_name = $new_image_name; // Store the new name for the database
                } else {
                    $image_file_err = "Error uploading image file.";
                    // Optionally log the specific error: error_log("File move failed for image: " . error_get_last()['message']);
                }
            }
        } elseif (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] !== UPLOAD_ERR_NO_FILE) {
            // Handle other potential upload errors
            $image_file_err = "Image file upload error: " . $_FILES["image_file"]["error"]; // Use a more user-friendly message
        }
        // If UPLOAD_ERR_NO_FILE or no file field exists, $image_file_name remains empty.


        // 3. Insert into Database (only if NO errors occurred)
        if (empty($name_err) && empty($music_file_err) && empty($image_file_err) && empty($general_err)) {
            // Prepare an insert statement
            // We are now inserting the generated file names (or empty strings if no upload)
            $sql = "INSERT INTO tracks (name, description, file_name, image_name) VALUES (?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                // Bind variables to the prepared statement as parameters
                // 'ssss' indicates all four parameters are strings
                mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_description, $param_file_name, $param_image_name);

                // Set parameters using the potentially new filenames
                $param_name = $name;
                $param_description = $description;
                $param_file_name = $music_file_name; // Use the processed filename
                $param_image_name = $image_file_name; // Use the processed filename

                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    $success_msg = "Track '" . htmlspecialchars($name) . "' added successfully!";
                    // Clear form fields after successful submission to prepare for next entry
                    $name = $description = ""; // File inputs cannot have default values for security
                    $music_file_name = $image_file_name = ""; // Clear these internal variables too

                } else {
                    $general_err = "ERROR: Could not execute database insert query. " . mysqli_stmt_error($stmt);
                    // Optionally log the specific database error
                    // error_log("MySQL Execute Error in add_track.php INSERT: " . mysqli_stmt_error($stmt));

                    // IMPORTANT: If database insert fails, clean up uploaded files
                    if (!empty($param_file_name) && file_exists(UPLOAD_DIR_MUSIC . $param_file_name)) {
                        unlink(UPLOAD_DIR_MUSIC . $param_file_name); // Delete music file
                    }
                    if (!empty($param_image_name) && file_exists(UPLOAD_DIR_IMAGES . $param_image_name)) {
                        unlink(UPLOAD_DIR_IMAGES . $param_image_name); // Delete image file
                    }
                }

                // Close statement
                mysqli_stmt_close($stmt);
            } else {
                $general_err = "ERROR: Could not prepare database insert query. " . mysqli_error($link);
                // Optionally log the specific database error
                // error_log("MySQL Prepare Error in add_track.php INSERT: " . mysqli_error($link));
            }
        }
        // If there are any errors (text field or file upload), the script will fall through
        // to display the form with the relevant error messages.
    }
    // Note: mysqli_close($link) is called at the very end of the script
}

// Close database connection at the very end, only if it was successfully opened
if (isset($link) && $link !== false) {
    mysqli_close($link);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Track - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 600px;
            margin: auto;
            margin-top: 50px;
        }

        .help-block {
            font-size: 0.8em;
            color: #dc3545;
        }

        /* Style for error messages */
        .form-text {
            font-size: 0.8em;
        }

        /* Style for muted text */
    </style>
</head>

<body>
    <div class="wrapper">
        <h2>Add New Music Track</h2>
        <p>Please fill this form to add a new track to the website.</p>

        <?php
        // Display general error message
        if (!empty($general_err)) {
            echo '<div class="alert alert-danger">' . $general_err . '</div>';
        }
        // Display success message
        if (!empty($success_msg)) {
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>

        <!-- IMPORTANT: enctype="multipart/form-data" is required for file uploads -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
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
                <!-- Change from text input to file input -->
                <input type="file" name="music_file" class="form-control-file <?php echo (!empty($music_file_err)) ? 'is-invalid' : ''; ?>">
                <!-- Use help-block for specific file errors -->
                <?php if (!empty($music_file_err)): ?>
                    <span class="help-block"><?php echo $music_file_err; ?></span>
                <?php endif; ?>
                <small class="form-text text-muted">Upload the actual music file. Max size: <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB. Allowed types: <?php echo implode(', ', array_map(function ($mime) {
                                                                                                                                                                return str_replace('audio/', '', $mime);
                                                                                                                                                            }, ALLOWED_MUSIC_TYPES)); ?></small>
            </div>
            <div class="form-group">
                <label>Thumbnail Image (JPG, PNG, GIF, etc.)</label>
                <!-- Change from text input to file input -->
                <input type="file" name="image_file" class="form-control-file <?php echo (!empty($image_file_err)) ? 'is-invalid' : ''; ?>">
                <!-- Use help-block for specific file errors -->
                <?php if (!empty($image_file_err)): ?>
                    <span class="help-block"><?php echo $image_file_err; ?></span>
                <?php endif; ?>
                <small class="form-text text-muted">Upload a thumbnail image. Max size: <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB. Allowed types: <?php echo implode(', ', array_map(function ($mime) {
                                                                                                                                                            return str_replace('image/', '', $mime);
                                                                                                                                                        }, ALLOWED_IMAGE_TYPES)); ?></small>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add Track">
                <a href="tracks.php" class="btn btn-secondary ml-2">Cancel</a>
                <p><a href="index.php" class="btn btn-secondary mt-3">Back to Dashboard</a></p>
            </div>
        </form>
    </div>
</body>

</html>