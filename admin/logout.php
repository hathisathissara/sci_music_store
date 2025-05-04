<?php
// admin/logout.php

// Start the session.
// This is necessary to access the current session data before destroying it.
session_start();

// Unset all session variables.
// This removes all data stored in the $_SESSION superglobal array (e.g., $_SESSION["loggedin"], $_SESSION["username"]).
session_unset();

// Destroy the session.
// This deletes the actual session file stored on the server.
session_destroy();

// Redirect the user to the login page.
// header() must be called before any output (like HTML, echoes, etc.).
header("location: login.php");

// Ensure that no further code is executed after the redirect header is sent.
exit;

?>