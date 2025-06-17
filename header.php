<?php
// header.php
// This file contains the standard HTML head, global CSS, and header navigation for all pages.

// Start output buffering at the very beginning to prevent "headers already sent" errors.
ob_start();

// Database connection setup
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

// Start session for potential cart/user management later
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
/* IMPORTANT: The PHP block for header.php ends here. There is NO closing '?>' tag after this line.
   This prevents accidental whitespace output that causes "headers already sent" errors. */

// The rest of the HTML and CSS directly follows below this PHP block.
// Any comments in this section must be HTML comments (<!-- ... -->) or CSS comments (/* ... */)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Likindy Digital Solution - Mobile Accessories & Repair</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
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
        }

        /* General Body and Font Settings */
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Container for content centering and responsiveness */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        /* Header Styling */
        .site-header {
            background-color: var(--white);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .site-branding img {
            max-height: 60px; /* Adjust logo size */
            width: auto;
            border-radius: 8px; /* Rounded corners for logo */
        }

        /* Navigation Menu */
        .main-nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center; /* Align items vertically */
            gap: 25px; /* Space between menu items */
        }
        .main-nav ul li a {
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 1.05em;
            padding: 5px 0;
            transition: color 0.3s ease;
        }
        .main-nav ul li a:hover {
            color: var(--accent-blue);
        }

        /* Search Form in Header */
        .header-search-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-search-form input[type="search"] {
            padding: 8px 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 20px; /* More rounded */
            font-size: 0.95em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            width: 180px; /* Default width */
        }
        .header-search-form input[type="search"]:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        .header-search-form button {
            background-color: var(--accent-blue);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.95em;
            transition: background-color 0.3s ease;
        }
        .header-search-form button:hover {
            background-color: var(--accent-hover);
        }
        .header-search-form button i {
            margin-right: 5px; /* Space between icon and text if any */
        }


        /* Section Titles */
        .section-title {
            text-align: center;
            font-size: 2.5em;
            color: var(--primary-dark);
            margin-bottom: 40px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--accent-blue);
            display: inline-block; /* To make border only under text */
            margin-left: auto;
            margin-right: auto;
        }

        /* Product and Service Card Base Styles (for consistency) */
        .product-grid, .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid */
            gap: 30px;
            margin-bottom: 40px;
        }
        .product-card, .service-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover, .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .product-card img, .service-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid var(--light-grey-border);
        }
        .product-card-content, .service-card-content {
            padding: 20px;
        }
        .product-card h3, .service-card h3 {
            font-size: 1.4em;
            color: var(--primary-dark);
            margin-top: 0;
            margin-bottom: 10px;
        }
        .product-card p.description, .service-card p.description {
            font-size: 0.95em;
            color: var(--secondary-grey);
            margin-bottom: 15px;
            height: 4.5em; /* Limit height to 3 lines for products */
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .service-card p.description {
             height: 6em; /* More height for service description */
        }
        .product-card .price {
            font-size: 1.6em;
            font-weight: 700;
            color: var(--accent-blue);
            margin-bottom: 15px;
        }
        .add-to-cart-button, .view-details-button {
            background-color: var(--cta-green); /* Changed to green for positive action */
            color: var(--white);
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.05em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
            margin-top: 10px;
            border: none; /* Ensure button has no default border */
            cursor: pointer;
        }
        .add-to-cart-button:hover, .view-details-button:hover {
            background-color: var(--cta-green-hover);
            transform: translateY(-3px);
        }
        /* Style for View Details specifically (e.g. if different color needed) */
        .view-details-button {
            background-color: var(--primary-dark);
        }
        .view-details-button:hover {
            background-color: var(--accent-hover);
        }

        /* Footer Styling (same as before) */
        .site-footer {
            background-color: var(--primary-dark);
            color: var(--white);
            padding: 40px 0;
            text-align: center;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 50px; /* Add some space above footer */
        }
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            align-items: flex-start;
            text-align: left;
            margin-bottom: 30px;
        }
        .footer-column {
            flex: 1;
            min-width: 250px;
            margin: 15px;
        }
        .footer-column h4 {
            font-size: 1.3em;
            margin-bottom: 15px;
            color: var(--accent-blue);
        }
        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-column ul li {
            margin-bottom: 10px;
        }
        .footer-column ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-column ul li a:hover {
            color: var(--white);
        }
        .footer-column p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 10px;
        }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            .main-nav ul {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            .hero-section h1 {
                font-size: 2.5em;
            }
            .hero-section p {
                font-size: 1.1em;
            }
            .section-title {
                font-size: 2em;
            }
            .product-grid, .service-grid {
                grid-template-columns: 1fr; /* Single column on small screens */
            }
            .footer-content {
                flex-direction: column;
                align-items: center;
            }
            .footer-column {
                text-align: center;
                margin: 10px 0;
            }
            .header-search-form {
                width: 100%; /* Full width on mobile */
                justify-content: center;
            }
            .header-search-form input[type="search"] {
                width: calc(100% - 80px); /* Adjust width to make space for button */
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <div class="site-branding">
                <a href="index.php" class="logo-link">
                    <img src="logo.png" alt="Likindy Digital Solution Logo">
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="cart.php"><i class="fa-solid fa-shopping-cart"></i> Cart</a></li>
                    <li class="header-search-item">
                        <form action="shop.php" method="GET" class="header-search-form">
                            <input type="search" name="search_query" placeholder="Search products..." value="<?php echo htmlspecialchars($_GET['search_query'] ?? ''); ?>">
                            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </form>
                    </li>
                    <!-- Conditional login/logout/my-account link -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="my-account.php"><i class="fa-solid fa-user-circle"></i> My Account</a></li>
                        <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- Page specific content will go here -->
