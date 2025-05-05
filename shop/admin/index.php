<?php
// admin/index.php

// Include the authentication check script
require_once 'includes/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; text-align: center; }
        .wrapper{ width: 600px; margin: auto; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Admin Dashboard</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>.</p>

        <div class="list-group">
            <a href="tracks.php" class="list-group-item list-group-item-action">Manage Music Tracks</a>
            <!-- Add more admin links here later -->
        </div>

        <p><a href="logout.php" class="btn btn-danger mt-3">Logout</a></p>
    </div>
</body>
</html>

<?php
// Optional: Create a logout.php file for logging out
// admin/logout.php
/*
<?php
session_start();
session_unset();
session_destroy();
header("location: login.php");
exit;
?>
*/
?>