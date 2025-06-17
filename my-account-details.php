<?php
// my-account-details.php
// This page allows the logged-in user to view and update their account details.

include 'header.php'; // Include the main header

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_data = null;
$message = '';

// Fetch current user data
$sql_fetch_user = "SELECT full_name, email, phone_number FROM `users` WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch_user);
if ($stmt_fetch) {
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    if ($result_fetch->num_rows > 0) {
        $user_data = $result_fetch->fetch_assoc();
    } else {
        $message = "<div class='message error'>User data not found. Please re-login.</div>";
        session_destroy(); // Invalidate session if user not found
        header("Location: login.php");
        exit();
    }
    $stmt_fetch->close();
} else {
    $message = "<div class='message error'>Database error fetching user details: " . $conn->error . "</div>";
    error_log("SQL Error in my-account-details.php fetch prepare: " . $conn->error);
}


// Handle form submission for updating details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_details'])) {
    $new_full_name = $conn->real_escape_string(trim($_POST['full_name']));
    $new_phone_number = $conn->real_escape_string(trim($_POST['phone_number']));
    // Password update is handled separately for security

    if (empty($new_full_name)) {
        $message = "<div class='message error'>Full Name cannot be empty.</div>";
    } else {
        $sql_update_user = "UPDATE `users` SET full_name = ?, phone_number = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update_user);

        if ($stmt_update === FALSE) {
            $message = "<div class='message error'>Database error updating details: " . $conn->error . "</div>";
            error_log("SQL Error in my-account-details.php update prepare: " . $conn->error);
        } else {
            $stmt_update->bind_param("ssi", $new_full_name, $new_phone_number, $user_id);

            if ($stmt_update->execute()) {
                $message = "<div class='message success'>Account details updated successfully!</div>";
                // Update session variables if name changed
                $_SESSION['user_name'] = $new_full_name;
                // Re-fetch updated data to populate form correctly
                $user_data['full_name'] = $new_full_name;
                $user_data['phone_number'] = $new_phone_number;
            } else {
                $message = "<div class='message error'>Error updating details: " . $conn->error . "</div>";
            }
            $stmt_update->close();
        }
    }
}

// Handle password update submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // First, verify current password
    $sql_get_password_hash = "SELECT password_hash FROM `users` WHERE id = ?";
    $stmt_password_check = $conn->prepare($sql_get_password_hash);
    if ($stmt_password_check) {
        $stmt_password_check->bind_param("i", $user_id);
        $stmt_password_check->execute();
        $result_password_check = $stmt_password_check->get_result();
        $user_password_hash = $result_password_check->fetch_assoc()['password_hash'];
        $stmt_password_check->close();

        if (!password_verify($current_password, $user_password_hash)) {
            $message = "<div class='message error'>Current password is incorrect.</div>";
        } elseif (empty($new_password) || empty($confirm_new_password)) {
            $message = "<div class='message error'>Please fill in all password fields.</div>";
        } elseif ($new_password !== $confirm_new_password) {
            $message = "<div class='message error'>New passwords do not match.</div>";
        } elseif (strlen($new_password) < 6) {
            $message = "<div class='message error'>New password must be at least 6 characters long.</div>";
        } else {
            // Hash new password and update
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update_password = "UPDATE `users` SET password_hash = ? WHERE id = ?";
            $stmt_update_password = $conn->prepare($sql_update_password);
            if ($stmt_update_password) {
                $stmt_update_password->bind_param("si", $new_password_hash, $user_id);
                if ($stmt_update_password->execute()) {
                    $message = "<div class='message success'>Password changed successfully!</div>";
                } else {
                    $message = "<div class='message error'>Error changing password: " . $conn->error . "</div>";
                }
                $stmt_update_password->close();
            } else {
                $message = "<div class='message error'>Database error preparing password update: " . $conn->error . "</div>";
                error_log("SQL Error in my-account-details.php password update prepare: " . $conn->error);
            }
        }
    } else {
        $message = "<div class='message error'>Database error fetching password hash: " . $conn->error . "</div>";
        error_log("SQL Error in my-account-details.php password hash fetch prepare: " . $conn->error);
    }
}

?>
<style>
    /* Reusing styles from my-account.php and general styles */
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

    /* Form Styles (reusing general styles) */
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
    .form-group input[type="email"][readonly] {
        background-color: #f0f0f0;
        cursor: not-allowed;
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
        width: auto;
        margin-top: 15px;
        display: inline-block; /* Allow buttons to sit side-by-side */
    }
    .form-actions button:hover {
        background-color: var(--cta-green-hover);
        transform: translateY(-2px);
    }

    /* Password section specific styles */
    .password-section {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid var(--light-grey-border);
    }
    .password-section h2 {
        border-color: var(--danger-red); /* Different color for password section header */
    }
    .password-section .form-actions button {
        background-color: var(--primary-dark);
    }
    .password-section .form-actions button:hover {
        background-color: var(--accent-hover);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .account-dashboard-grid {
            grid-template-columns: 1fr; /* Stack vertically on small screens */
        }
        .my-account-header h1 {
            font-size: 2.2em;
        }
        .form-group input, .form-actions button {
            font-size: 0.95em;
        }
    }
</style>

<div class="container">
    <section class="my-account-header">
        <h1>Account Details</h1>
        <p>Update your personal information and manage your password.</p>
    </section>

    <div class="account-dashboard-grid">
        <aside class="account-sidebar">
            <h3>My Account</h3>
            <ul>
                <li><a href="my-account.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                <li><a href="my-account-orders.php"><i class="fa-solid fa-box-open"></i> My Orders</a></li>
                <li><a href="my-account-details.php" class="active"><i class="fa-solid fa-user-circle"></i> Account Details</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <section class="account-main-content">
            <h2>Personal Information</h2>
            <?php echo $message; ?>
            <form method="POST" action="my-account-details.php">
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <!-- Email is usually not editable as it's the primary identifier -->
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number (Optional):</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>" placeholder="+255 7XX XXX XXX">
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_details"><i class="fa-solid fa-save"></i> Save Changes</button>
                </div>
            </form>

            <div class="password-section">
                <h2>Change Password</h2>
                <form method="POST" action="my-account-details.php">
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_new_password">Confirm New Password:</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="change_password"><i class="fa-solid fa-key"></i> Change Password</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php
include 'footer.php'; // Include the main footer
?>
