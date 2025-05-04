<?php
// admin/includes/auth.php

// Start or resume the session
session_start();

// Check if the admin session variable is set
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    // Admin is not logged in, redirect to login page
    header("location: login.php");
    exit; // Stop further script execution
}
?>