<?php
// admin/includes/config.php

// Database Configuration
define('DB_SERVER', 'localhost'); // Your database server hostname
define('DB_USERNAME', 'root'); // Your database username
define('DB_PASSWORD', ''); // Your database password
define('DB_NAME', 'sci_fi_music_db');     // Your database name

 // **CHANGE THIS TO A SECURE PASSWORD**

// --- WhatsApp Configuration (Handled primarily in JS, but keeping here for potential future use or just notes)
// Define the WhatsApp number and message template here IF you were fetching them from the backend.
// As per current plan (Section 6), these are defined in public_html/js/script.js
// --- File Upload Configuration ---

// Define the base directory of your project root (relative to where config.php is)
// If config.php is in your-project-root/admin/includes/
define('UPLOAD_DIR_BASE', __DIR__ . '/../../'); // Go up two levels from admin/includes to reach project root

// Define directories for file uploads
define('UPLOAD_DIR_MUSIC', UPLOAD_DIR_BASE . 'uploads/music/'); // Music files (outside public_html)
define('UPLOAD_DIR_IMAGES', UPLOAD_DIR_BASE . '/images/tracks/'); // Image files (inside public_html/images/)

// Define allowed file types (MIME types)
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_MUSIC_TYPES', ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/aac', 'audio/flac']); // Add/remove types as needed

// Define maximum allowed file size (in bytes)
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // Example: 20MB (Adjust as necessary)

?>