<?php
// logout.php
// This page handles user logout.

session_start(); // Start the session to access session variables

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page or home page
header("Location: login.php"); // Or index.php
exit();
?>
