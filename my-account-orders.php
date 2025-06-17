<?php
// my-account-orders.php
// This page displays a list of orders for the logged-in user.

include 'header.php'; // Include the main header

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];

// Fetch orders for the logged-in user
$sql_orders = "SELECT id, total_amount, status, order_date FROM `orders` WHERE user_id = ? ORDER BY order_date DESC";
$stmt_orders = $conn->prepare($sql_orders);

if ($stmt_orders === FALSE) {
    echo "<div class='message error'>Database error fetching orders: " . $conn->error . "</div>";
    error_log("SQL Error in my-account-orders.php prepare: " . $conn->error);
} else {
    $stmt_orders->bind_param("i", $user_id);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();

    while ($row = $result_orders->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt_orders->close();
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

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .orders-table th, .orders-table td {
        border: 1px solid var(--light-grey-border);
        padding: 12px;
        text-align: left;
        font-size: 0.95em;
    }
    .orders-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
    }
    .orders-table tr:nth-child(even) {
        background-color: var(--background-light);
    }
    .orders-table tr:hover {
        background-color: #e9ecef;
    }
    .orders-table td.actions {
        text-align: center;
    }
    .orders-table .action-btn {
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
        color: var(--white);
        font-size: 0.9em;
        margin: 0 5px;
        transition: background-color 0.3s ease;
    }
    .orders-table .view-btn {
        background-color: var(--accent-blue);
    }
    .orders-table .view-btn:hover {
        background-color: var(--accent-hover);
    }
    /* Status styling */
    .status-pending { color: #f39c12; font-weight: 600; }
    .status-completed { color: #27ae60; font-weight: 600; }
    .status-cancelled { color: #e74c3c; font-weight: 600; }


    /* Responsive table */
    @media (max-width: 768px) {
        .account-dashboard-grid {
            grid-template-columns: 1fr;
        }
        .orders-table, .orders-table thead, .orders-table tbody, .orders-table th, .orders-table td, .orders-table tr {
            display: block;
        }
        .orders-table thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }
        .orders-table tr {
            border: 1px solid var(--light-grey-border);
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
        }
        .orders-table td {
            border: none;
            border-bottom: 1px solid var(--light-grey-border);
            position: relative;
            padding-left: 50%;
            text-align: right;
        }
        .orders-table td:last-child {
            border-bottom: none;
        }
        .orders-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            text-align: left;
            font-weight: 600;
            color: var(--primary-dark);
        }
        .orders-table td.actions {
            text-align: center;
            padding-left: 10px;
        }
    }
</style>

<div class="container">
    <section class="my-account-header">
        <h1>My Orders</h1>
        <p>Here you can view the history and status of your past orders.</p>
    </section>

    <div class="account-dashboard-grid">
        <aside class="account-sidebar">
            <h3>My Account</h3>
            <ul>
                <li><a href="my-account.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                <li><a href="my-account-orders.php" class="active"><i class="fa-solid fa-box-open"></i> My Orders</a></li>
                <li><a href="my-account-details.php"><i class="fa-solid fa-user-circle"></i> Account Details</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <section class="account-main-content">
            <h2>Your Order History</h2>
            <?php if (empty($orders)): ?>
                <p>You have not placed any orders yet.</p>
                <p><a href="shop.php" style="color: var(--accent-blue); text-decoration: underline;">Start shopping now!</a></p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="Order ID">#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td data-label="Date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></td>
                            <td data-label="Total Amount">Tsh <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td data-label="Status">
                                <span class="status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                    <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                </span>
                            </td>
                            <td data-label="Actions" class="actions">
                                <a href="order-confirmation.php?order_id=<?php echo $order['id']; ?>" class="action-btn view-btn"><i class="fa-solid fa-eye"></i> View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php
include 'footer.php'; // Include the main footer
?>
