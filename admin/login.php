<?php
// admin/login.php

// Start or resume the session
session_start();

// Check if the user is already logged in, if yes then redirect them to dashboard
// Note: Also checks if $_SESSION["loggedin"] is explicitly true
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit; // Stop script execution after redirect
}

// Include database connection script
// Make sure the path is correct based on your file structure
require_once 'includes/db_connection.php';

$username = $password = "";
$username_err = $password_err = $login_err = "";

// Check if the database connection was successful
if ($link === false) {
    // Handle connection error - maybe redirect to an error page or display a message
    $login_err = "Database connection error.";
    // Optionally log the error: error_log("Database connection failed: " . mysqli_connect_error());
} else {
    // Processing form data when form is submitted ONLY if connection is successful
    if($_SERVER["REQUEST_METHOD"] == "POST"){

        // Check if username is empty
        if(empty(trim($_POST["username"]))){
            $username_err = "Please enter username.";
        } else{
            $username = trim($_POST["username"]);
        }

        // Check if password is empty
        if(empty(trim($_POST["password"]))){
            $password_err = "Please enter your password.";
        } else{
            $password = trim($_POST["password"]);
        }

        // Validate credentials if no initial input errors
        if(empty($username_err) && empty($password_err)){
            // Prepare a select statement to retrieve the user from the database
            $sql = "SELECT id, username, password FROM admin_users WHERE username = ?";

            if($stmt = mysqli_prepare($link, $sql)){
                // Bind parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);

                // Set parameters
                $param_username = $username;

                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Store result to check the number of rows
                    mysqli_stmt_store_result($stmt);

                    // Check if username exists (should be exactly 1 row)
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        // Bind result variables to retrieve data from the row
                        mysqli_stmt_bind_result($stmt, $id, $username_db, $hashed_password);

                        // Fetch the result row
                        if(mysqli_stmt_fetch($stmt)){
                            // ** --- Password Verification --- **
                            // Use password_verify() to check the submitted password against the stored hash
                            if(password_verify($password, $hashed_password)){
                                // Password is correct, start a new session (already started at top)

                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id; // Store admin user ID
                                $_SESSION["username"] = $username_db; // Store username fetched from DB

                                // Redirect user to dashboard page
                                header("location: index.php");
                                exit; // Stop script execution after redirect
                            } else{
                                // Password is not valid
                                $login_err = "Invalid username or password.";
                            }
                        }
                    } else{
                        // Username doesn't exist
                        $login_err = "Invalid username or password.";
                    }
                } else{
                    // Error executing the database query
                    $login_err = "Oops! Something went wrong. Please try again later.";
                    // Optionally log the specific database error: error_log("MySQL Execute Error: " . mysqli_stmt_error($stmt));
                }

                // Close statement
                mysqli_stmt_close($stmt);
            } else{
                // Error preparing the database query
                 $login_err = "Oops! Something went wrong. Please try again later.";
                 // Optionally log the specific database error: error_log("MySQL Prepare Error: " . mysqli_error($link));
            }
        }
        // If input errors exist ($username_err or $password_err are not empty),
        // the script will fall through to display the form with error messages.
    }
    // Note: mysqli_close($link) is called at the very end of the script execution
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; margin: auto; margin-top: 100px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Admin Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php
        // Display general login error
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <!-- Use ternary operator to safely echo value -->
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err) || (!empty($login_err) && empty($username))) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username ?? ''); ?>">
                 <!-- Display specific username error -->
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                 <!-- Use ternary operator to safely echo value -->
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err) || (!empty($login_err) && empty($password))) ? 'is-invalid' : ''; ?>">
                 <!-- Display specific password error -->
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p><a href="../index.php">Back to Home</a></p>
        </form>
    </div>
</body>
</html>