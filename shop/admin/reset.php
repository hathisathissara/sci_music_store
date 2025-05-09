<?php
// admin/temp_reset_password_script.php
// *** TEMPORARY SCRIPT TO RESET ADMIN PASSWORD DIRECTLY ***
// *** DO NOT USE IN PRODUCTION - DELETE IMMEDIATELY AFTER USE ***

// Include database connection script
require_once 'includes/db_connection.php'; // Adjust path

// *** CONFIGURATION - CHANGE THESE VALUES ***
$admin_username_to_reset = 'hathisa'; // The username of the admin account to reset
$new_temp_password = 'bfa0701207991'; // *** Set your NEW password here ***
// *****************************************


$success_message = "";
$error_message = "";

// Check database connection
if ($link === false) {
    $error_message = "Database connection error.";
} else {

    // Hash the new password
    $hashed_password = password_hash($new_temp_password, PASSWORD_DEFAULT);

    // Prepare the update statement
    $sql = "UPDATE admin_users SET password = ? WHERE username = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_username);

        // Set parameters
        $param_password = $hashed_password;
        $param_username = $admin_username_to_reset;

        // Attempt to execute the update statement
        if (mysqli_stmt_execute($stmt)) {
            // Check how many rows were affected (should be 1 if user exists)
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                 $success_message = "Password for user '" . htmlspecialchars($admin_username_to_reset) . "' has been updated successfully!";
            } else {
                 $error_message = "User '" . htmlspecialchars($admin_username_to_reset) . "' not found. Password not updated.";
            }

        } else {
            $error_message = "ERROR: Could not execute password update query. " . mysqli_stmt_error($stmt);
             // Log error
        }

        // Close statement
        mysqli_stmt_close($stmt);

    } else {
        $error_message = "ERROR: Could not prepare password update query. " . mysqli_error($link);
        // Log error
    }

    // Close database connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Temporary Admin Password Reset</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 500px; padding: 20px; margin: auto; margin-top: 100px; border: 1px solid #ddd; border-radius: 5px; }
        .alert strong { color: inherit; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Temporary Admin Password Reset Script</h2>
        <p class="text-danger"><strong>WARNING:</strong> This script is for temporary use only. Delete it immediately after updating the password!</p>

        <?php
        if(!empty($error_message)){
            echo '<div class="alert alert-danger">' . $error_message . '</div>';
        }
        if(!empty($success_message)){
             echo '<div class="alert alert-success">' . $success_message . '</div>';
         }
        ?>

        <p>Configuration in script:</p>
        <ul>
            <li>Admin Username: <strong><?php echo htmlspecialchars($admin_username_to_reset); ?></strong></li>
            <li>New Password: <strong><?php echo htmlspecialchars($new_temp_password); ?></strong></li>
        </ul>

         <?php if(empty($success_message) && empty($error_message)): ?>
             <p class="text-info">Script is ready to run. Load this page in your browser to execute.</p>
         <?php endif; ?>


        <p class="mt-3"><a href="login.php">Back to Admin Login</a></p>
    </div>
</body>
</html>