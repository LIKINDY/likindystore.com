<?php
// auth_header.php
// This file contains a minimal HTML header for authentication pages (login, register).

ob_start(); // Start output buffering

// Database connection setup (same as header.php)
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password (often empty)
$dbname = "likindydgdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Likindy Digital Solution - Authentication</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Global CSS Variables (from Logo colors) */
        :root {
            --primary-dark: #0F2038; /* Dark Blue from Logo text */
            --secondary-grey: #6C7A89; /* Grey from Logo sub-text */
            --background-light: #f0f2f5;
            --white: #ffffff;
            --text-dark: #333333;
            --light-grey-border: #e0e0e0;
            --accent-blue: #3498db; /* A bright blue for links/buttons */
            --accent-hover: #2980b9;
            --cta-green: #2ecc71; /* For "Add to Cart" or success */
            --cta-green-hover: #27ae60;
            --danger-red: #e74c3c; /* For delete/logout buttons */
        }

        /* General Body and Font Settings */
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure body takes full viewport height */
        }

        /* Container for content centering and responsiveness */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
            flex-grow: 1; /* Allow container to grow and push footer down */
            display: flex;
            align-items: center; /* Vertically center content if it's small */
            justify-content: center; /* Horizontally center content */
        }

        /* Site Header for Auth Pages (only logo, no nav) */
        .site-header-auth {
            background-color: var(--white);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            text-align: center; /* Center the logo */
            margin-bottom: 30px; /* Space before content */
        }
        .site-header-auth .site-branding img {
            max-height: 50px; /* Smaller logo for auth pages */
            width: auto;
            border-radius: 8px;
        }

        /* Specific styles for Login/Registration forms */
        .auth-container {
            background-color: var(--white);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%; /* Make it responsive */
            margin: 60px auto; /* Center the form with good vertical spacing */
            text-align: center;
        }
        .auth-container h2 {
            font-size: 2.5em;
            color: var(--primary-dark);
            margin-bottom: 25px;
            border-bottom: 2px solid var(--accent-blue);
            padding-bottom: 10px;
            display: inline-block;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 1.05em;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-grey-border);
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .form-actions button {
            background-color: var(--cta-green);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
            margin-top: 15px;
        }
        .form-actions button:hover {
            background-color: var(--cta-green-hover);
            transform: translateY(-2px);
        }
        .form-link {
            margin-top: 20px;
            font-size: 0.95em;
        }
        .form-link a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
        }
        .form-link a:hover {
            text-decoration: underline;
        }

        /* Messages (Success/Error) */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            animation: fadeIn 0.5s forwards;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-container {
                padding: 25px;
                margin: 30px auto;
            }
            .auth-container h2 {
                font-size: 2em;
            }
            .form-group input, .form-actions button {
                font-size: 0.95em;
            }
        }

        /* Footer Styling (minimal for auth pages) */
        .site-footer-auth {
            background-color: var(--primary-dark);
            color: var(--white);
            padding: 20px 0; /* Reduced padding */
            text-align: center;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            margin-top: auto; /* Push footer to the bottom */
            width: 100%;
        }
        .site-footer-auth p {
            margin: 0;
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.7);
        }
    </style>
</head>
<body>
    <header class="site-header-auth">
        <div class="site-branding">
            <a href="index.php" class="logo-link">
                <img src="logo.png" alt="Likindy Digital Solution Logo">
            </a>
        </div>
    </header>

    <main>
        