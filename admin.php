<?php
// admin.php
// Admin page for viewing and managing orders.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn)

// --- Simple Admin Authentication (For Local Development) ---
// **IMPORTANT: Change this password for better security even in local dev.**
$admin_password_local_dev = "likindyadmin2025"; 

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (!isset($_POST['admin_pass']) || $_POST['admin_pass'] !== $admin_password_local_dev) {
        // Show login form if not logged in or password incorrect
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Admin Login - Likindy Digital Solution</title>
            <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
            <style>
                body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
                .login-container { background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); text-align: center; max-width: 400px; width: 90%; }
                .login-container h2 { color: #2C3E50; margin-bottom: 25px; }
                .login-container input[type='password'] { width: calc(100% - 20px); padding: 12px 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
                .login-container button { background-color: #0F2038; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background-color 0.3s ease; }
                .login-container button:hover { background-color: #1a3350; }
                .error-message { color: #e74c3c; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='login-container'>
                <h2>Likindy Admin Login</h2>
                <form method='POST' action='admin.php'>
                    <input type='password' name='admin_pass' placeholder='Admin Password' required>
                    <button type='submit'>Login</button>
                </form>
                " . (isset($_POST['admin_pass']) && $_POST['admin_pass'] !== $admin_password_local_dev ? "<p class='error-message'>Incorrect password. Please try again.</p>" : "") . "
            </div>
        </body>
        </html>";
        exit();
    } else {
        $_SESSION['admin_logged_in'] = true; // Set session variable on successful login
    }
}

$admin_message = ''; // To store success/error messages for admin actions

// --- Handle Order Status Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_order_status') {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $new_status = $conn->real_escape_string($_POST['new_status']);

    // Validate inputs
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if ($order_id === false || !in_array($new_status, $allowed_statuses)) {
        $admin_message = "<div class='message error'>Error: Invalid order ID or status.</div>";
    } else {
        $sql_update_status = "UPDATE `orders` SET `status` = ? WHERE `id` = ?";
        $stmt_update_status = $conn->prepare($sql_update_status);

        if ($stmt_update_status === FALSE) {
            $admin_message = "<div class='message error'>Database error preparing status update: " . $conn->error . "</div>";
            error_log("SQL Error in admin.php status update prepare: " . $conn->error);
        } else {
            $stmt_update_status->bind_param("si", $new_status, $order_id);
            if ($stmt_update_status->execute()) {
                $admin_message = "<div class='message success'>Order #{$order_id} status updated to " . htmlspecialchars(ucfirst($new_status)) . "!</div>";
            } else {
                $admin_message = "<div class='message error'>Error updating order #{$order_id} status: " . $conn->error . "</div>";
                error_log("SQL Error in admin.php status update execute: " . $conn->error);
            }
            $stmt_update_status->close();
        }
    }
}

// --- Fetch Orders from Database ---
$sql_orders = "SELECT * FROM `orders` ORDER BY `order_date` DESC";
$result_orders = $conn->query($sql_orders);

?>

<!-- CSS Specific to Admin Orders Page -->
<style>
    /* Admin specific styles */
    .admin-nav {
        background-color: var(--primary-dark);
        padding: 10px 0;
        text-align: center;
        margin-bottom: 20px;
        border-radius: 8px;
    }
    .admin-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap; /* Allow wrapping for more links */
    }
    .admin-nav ul li a {
        color: var(--white);
        text-decoration: none;
        font-weight: 600;
        padding: 8px 15px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        display: flex; /* For icon alignment */
        align-items: center;
        gap: 5px;
    }
    .admin-nav ul li a:hover,
    .admin-nav ul li a.active {
        background-color: var(--accent-blue);
    }

    /* Dashboard Section Styling */
    .dashboard-section {
        background-color: var(--white);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }
    .dashboard-section h2 {
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        font-size: 1.8em;
        border-bottom: 2px solid var(--light-grey-border); 
        padding-bottom: 15px;
    }

    /* Table Styling for Orders */
    .order-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .order-table th, .order-table td {
        padding: 12px 15px;
        border: 1px solid var(--light-grey-border);
        text-align: left;
    }
    .order-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .order-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .order-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.85em;
        font-weight: 600;
        text-transform: capitalize;
        color: var(--white);
    }
    .status-badge.pending { background-color: #f39c12; } /* Orange */
    .status-badge.processing { background-color: #3498db; } /* Blue */
    .status-badge.shipped { background-color: #8e44ad; }    /* Purple */
    .status-badge.delivered { background-color: #27ae60; } /* Green */
    .status-badge.cancelled { background-color: #e74c3c; } /* Red */

    /* Action column for status update form */
    .status-update-form {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    .status-update-form select {
        padding: 5px 8px;
        border-radius: 5px;
        border: 1px solid var(--light-grey-border);
        background-color: var(--white);
        font-size: 0.9em;
    }
    .status-update-form button {
        background-color: var(--accent-blue);
        color: white;
        padding: 5px 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.85em;
        transition: background-color 0.3s ease;
    }
    .status-update-form button:hover {
        background-color: var(--accent-hover);
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


    /* Responsive Table */
    @media (max-width: 768px) {
        .admin-nav ul {
            flex-direction: column;
            gap: 10px;
        }
        .order-table, .order-table tbody, .order-table tr, .order-table td, .order-table th {
            display: block;
            width: 100%;
        }
        .order-table thead {
            display: none;
        }
        .order-table tr {
            margin-bottom: 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .order-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
        }
        .order-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            width: calc(50% - 30px);
            padding-right: 10px;
            white-space: nowrap;
            text-align: left;
            font-weight: 600;
            color: var(--primary-dark);
        }
        .order-table td:last-child { /* Adjust for the action column */
             text-align: center;
             padding-left: 15px; /* No specific label for actions */
        }
        .status-update-form {
            justify-content: flex-end; /* Align to right on mobile */
        }
    }
</style>

<div class="container">
    <header class="admin-header">
        <h1>Admin Panel - Likindy Digital Solution</h1>
        <p>Manage Orders and Site Information</p>
    </header>

    <nav class="admin-nav">
        <ul>
            <li><a href="admin.php" class="active"><i class="fa-solid fa-list-check"></i> Manage Orders</a></li>
            <li><a href="admin_products.php"><i class="fa-solid fa-boxes-stacked"></i> Manage Products</a></li>
            <li><a href="admin_services.php"><i class="fa-solid fa-wrench"></i> Manage Services</a></li>
            <li><a href="admin_messages.php"><i class="fa-solid fa-envelope"></i> View Messages</a></li>
            <li><a href="admin_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </nav>

    <?php echo $admin_message; // Display admin action messages ?>

    <section class="dashboard-section">
        <h2>Recent Orders</h2>
        <?php if ($result_orders->num_rows > 0): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Shipping Address</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row_order = $result_orders->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Order ID"><?php echo $row_order['id']; ?></td>
                            <td data-label="Customer Name"><?php echo htmlspecialchars($row_order['customer_name']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($row_order['customer_email']); ?></td>
                            <td data-label="Phone"><?php echo htmlspecialchars($row_order['customer_phone']); ?></td>
                            <td data-label="Total Amount">Tsh <?php echo number_format($row_order['total_amount'], 2); ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?php echo strtolower($row_order['status']); ?>"><?php echo htmlspecialchars(ucfirst($row_order['status'])); ?></span>
                            </td>
                            <td data-label="Order Date"><?php echo date('Y-m-d H:i', strtotime($row_order['order_date'])); ?></td>
                            <td data-label="Shipping Address"><?php echo nl2br(htmlspecialchars($row_order['shipping_address'])); ?></td>
                            <td data-label="Payment Method"><?php echo htmlspecialchars($row_order['payment_method']); ?></td>
                            <td data-label="Actions">
                                <form method="POST" action="admin.php" class="status-update-form">
                                    <input type="hidden" name="order_id" value="<?php echo $row_order['id']; ?>">
                                    <input type="hidden" name="action" value="update_order_status">
                                    <select name="new_status" onchange="this.form.submit()">
                                        <?php
                                        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                                        foreach ($statuses as $status_option) {
                                            $selected = ($row_order['status'] == $status_option) ? 'selected' : '';
                                            echo "<option value='{$status_option}' {$selected}>" . ucfirst($status_option) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <!-- A submit button could be added here if not using onchange submit -->
                                    <!-- <button type="submit">Update</button> -->
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found yet.</p>
        <?php endif; ?>
    </section>
</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
