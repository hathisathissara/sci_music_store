<?php
// public_html/serve_music.php
// Script to securely serve music files from the non-public upload directory

// Include necessary files (Database connection and Config for upload paths)
// Adjust paths relative to public_html folder
require_once 'admin/includes/db_connection.php';
require_once 'admin/includes/config.php';

// Check if database connection was successful
if ($link === false) {
    http_response_code(500); // Internal Server Error
    die("Database connection error.");
}

// Check if track ID is provided in the URL
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    http_response_code(400); // Bad Request
    die("Track ID not provided.");
}

$track_id = trim($_GET['id']);

// Validate track ID (ensure it's a positive integer)
if (!filter_var($track_id, FILTER_VALIDATE_INT) || $track_id <= 0) {
    http_response_code(400); // Bad Request
    die("Invalid track ID.");
}

$file_name = null;

// Fetch the music file name from the database using the track ID
$sql = "SELECT file_name FROM tracks WHERE id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $param_id);
    $param_id = $track_id;

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $fetched_file_name);
            mysqli_stmt_fetch($stmt);
            $file_name = $fetched_file_name;
        } else {
            // Track not found
            http_response_code(404); // Not Found
            die("Track not found.");
        }

    } else {
        http_response_code(500); // Internal Server Error
        // Log error: error_log("MySQL Execute Error in serve_music.php: " . mysqli_stmt_error($stmt));
        die("Error fetching track data.");
    }

    mysqli_stmt_close($stmt);
} else {
    http_response_code(500); // Internal Server Error
    // Log error: error_log("MySQL Prepare Error in serve_music.php: " . mysqli_error($link));
    die("Error preparing query.");
}

// Close database connection
mysqli_close($link);

// Check if a file name was found for the track
if (empty($file_name)) {
    http_response_code(404); // Not Found
    die("Music file not associated with this track.");
}

// Construct the full server path to the music file using the upload directory from config
$file_path = UPLOAD_DIR_MUSIC . $file_name;

// Check if the file exists on the server
if (!file_exists($file_path)) {
    http_response_code(404); // Not Found
    // Log error: error_log("Music file not found on disk: " . $file_path);
    die("Music file not found on the server.");
}

// --- Serve the file ---

// Determine the MIME type (Content-Type header)
$finfo = finfo_open(FILEINFO_MIME_TYPE); // Use fileinfo extension for better type detection
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

// Fallback if fileinfo fails or type is generic (can refine this)
if ($mime_type === false || $mime_type === 'application/octet-stream') {
    // Guess type based on extension (less reliable)
     $extension = pathinfo($file_path, PATHINFO_EXTENSION);
     switch (strtolower($extension)) {
         case 'mp3': $mime_type = 'audio/mpeg'; break;
         case 'wav': $mime_type = 'audio/wav'; break;
         case 'ogg': $mime_type = 'audio/ogg'; break;
         case 'aac': $mime_type = 'audio/aac'; break;
         case 'flac': $mime_type = 'audio/flac'; break;
         default:
             // If type still unknown or not in our allowed list, block access
             http_response_code(415); // Unsupported Media Type
             die("Unsupported file type.");
     }
      // Optionally add a check against ALLOWED_MUSIC_TYPES from config if needed
}


// Set headers for streaming
header("Content-Type: " . $mime_type);
header("Content-Length: " . filesize($file_path)); // Tell the browser the file size
header("Content-Disposition: inline; filename=\"" . basename($file_name) . "\""); // Display inline
header("Accept-Ranges: bytes"); // Essential for audio/video seeking (Byte Serving)


// Output the file content
// readfile() is generally more memory efficient than file_get_contents() for large files
if (!readfile($file_path)) {
    // Handle errors during file reading
    http_response_code(500); // Internal Server Error
    // Log error: error_log("Failed to read file: " . $file_path . " Error: " . error_get_last()['message']);
    // Die only if no output has been sent yet
    if (!headers_sent()) {
         die("Error serving file.");
    }
}

// Exit the script
exit;
?>