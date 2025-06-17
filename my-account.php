<?php
// my-account.php
// This page is the user's dashboard after logging in.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn) and session_start()

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch user details from DB (optional, but good for robust user data)
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['user_name'] ?? 'User'; // Fallback if name not in session
$user_email = $_SESSION['user_email'] ?? 'No Email';
$user_phone = 'N/A'; // Default value
$registration_date = 'N/A'; // Default value

// Fetch more details if needed
$sql_user_details = "SELECT full_name, email, phone_number, registration_date FROM `users` WHERE id = ?";
$stmt_user = $conn->prepare($sql_user_details);
if ($stmt_user === FALSE) {
    // Handle error, though unlikely if header.php connection is fine
    error_log("SQL Error in my-account.php prepare: " . $conn->error);
} else {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows > 0) {
        $user_data = $result_user->fetch_assoc();
        $user_full_name = htmlspecialchars($user_data['full_name']);
        $user_email = htmlspecialchars($user_data['email']);
        $user_phone = htmlspecialchars($user_data['phone_number']);
        $registration_date = date('F j, Y', strtotime($user_data['registration_date']));
    }
    $stmt_user->close();
}

?>
<style>
    /* Specific styles for My Account page */
    .my-account-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .my-account-header h1 {
        font-size: 3em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .my-account-header p {
        font-size: 1.2em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .account-dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 2fr; /* Sidebar and main content */
        gap: 30px;
        margin-top: 40px;
        margin-bottom: 60px;
    }

    .account-sidebar {
        background-color: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        align-self: start; /* Stick to top */
    }
    .account-sidebar h3 {
        font-size: 1.8em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--light-grey-border);
        padding-bottom: 10px;
    }
    .account-sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .account-sidebar ul li {
        margin-bottom: 10px;
    }
    .account-sidebar ul li a {
        display: block;
        padding: 10px 15px;
        text-decoration: none;
        color: var(--primary-dark);
        font-weight: 600;
        border-radius: 8px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .account-sidebar ul li a:hover,
    .account-sidebar ul li a.active {
        background-color: var(--accent-blue);
        color: var(--white);
    }
    .account-sidebar ul li a i {
        margin-right: 10px;
    }


    .account-main-content {
        background-color: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    }
    .account-main-content h2 {
        font-size: 2em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
    }
    .account-info p {
        font-size: 1.1em;
        margin-bottom: 10px;
        color: var(--text-dark);
    }
    .account-info p strong {
        color: var(--primary-dark);
    }

    /* Logout Button */
    .logout-btn {
        background-color: var(--danger-red);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        font-weight: 600;
        transition: background-color 0.3s ease;
        margin-top: 20px;
    }
    .logout-btn:hover {
        background-color: #c0392b;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .account-dashboard-grid {
            grid-template-columns: 1fr; /* Stack vertically on small screens */
        }
        .my-account-header h1 {
            font-size: 2.2em;
        }
    }
</style>

<div class="container">
    <section class="my-account-header">
        <h1>Welcome, <?php echo $user_full_name; ?>!</h1>
        <p>This is your account dashboard. Here you can manage your details and orders.</p>
    </section>

    <div class="account-dashboard-grid">
        <aside class="account-sidebar">
            <h3>My Account</h3>
            <ul>
                <li><a href="my-account.php" class="active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                <li><a href="my-account-orders.php"><i class="fa-solid fa-box-open"></i> My Orders</a></li>
                <li><a href="my-account-details.php"><i class="fa-solid fa-user-circle"></i> Account Details</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <section class="account-main-content">
            <h2>Your Account Details</h2>
            <div class="account-info">
                <p><strong>Full Name:</strong> <?php echo $user_full_name; ?></p>
                <p><strong>Email:</strong> <?php echo $user_email; ?></p>
                <p><strong>Phone Number:</strong> <?php echo $user_phone; ?></p>
                <p><strong>Registration Date:</strong> <?php echo $registration_date; ?></p>
            </div>
            <p style="margin-top: 30px;">This is your dashboard. Here you can get an overview of your account and navigate to other sections such as your orders or updating your details.</p>
        </section>
    </div>
</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
