<?php
// admin_users.php
// Admin page for viewing and managing registered users.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn) and session_start()

// --- Simple Admin Authentication (For Local Development) ---
$admin_password_local_dev = "likindyadmin2025"; // Must match password in other admin files

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php"); // Redirect to admin login if not authenticated
    exit();
}

// --- Fetch Users from Database ---
// NOTE: Avoid selecting password_hash for security reasons unless strictly necessary for an action.
// If you need to hash a new password for a user, do it when updating their password, not fetching.
// FIX: Changed 'phone' to 'phone_number' and 'created_at' to 'registration_date' based on your provided table structure
$sql_users = "SELECT id, username, email, full_name, phone_number, role, registration_date FROM `users` ORDER BY `registration_date` DESC";
$result_users = $conn->query($sql_users);

// Add error checking for the query for robust debugging
if ($result_users === FALSE) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 8px; margin-bottom: 20px;'>";
    echo "<h2>Database Query Error!</h2>";
    echo "<p>There was an issue fetching users from the database. This usually means a column name is wrong or the 'users' table does not exist.</p>";
    echo "<p><strong>MySQL Error:</strong> " . $conn->error . "</p>";
    echo "<p><strong>SQL Query Attempted:</strong> <code>" . htmlspecialchars($sql_users) . "</code></p>";
    echo "<p>Please verify your 'users' table structure in phpMyAdmin matches the columns in the query.</p>";
    echo "</div>";
    $result_users = null; // Ensure $result_users is null so num_rows check doesn't error
}

?>
<style>
    /* Admin specific styles (reused from other admin pages) */
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

    /* Dashboard Section Styling (reused) */
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

    /* User List Table Styles */
    .user-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .user-list-table th, .user-list-table td {
        padding: 12px 15px;
        border: 1px solid var(--light-grey-border);
        text-align: left;
    }
    .user-list-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .user-list-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .user-list-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .user-list-table .role-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 5px;
        font-size: 0.8em;
        font-weight: 600;
        text-transform: capitalize;
        color: white;
    }
    .user-list-table .role-badge.customer { background-color: #3498db; } /* Blue */
    .user-list-table .role-badge.admin { background-color: #e74c3c; }    /* Red */


    /* Responsive Table for Users */
    @media (max-width: 768px) {
        .user-list-table, .user-list-table tbody, .user-list-table tr, .user-list-table td, .user-list-table th {
            display: block;
            width: 100%;
        }
        .user-list-table thead {
            display: none;
        }
        .user-list-table tr {
            margin-bottom: 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .user-list-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
        }
        .user-list-table td::before {
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
    }
</style>

<div class="container">
    <header class="admin-header">
        <h1>Admin Panel - Likindy Digital Solution</h1>
        <p>Manage Registered Users</p>
    </header>

    <nav class="admin-nav">
        <ul>
            <li><a href="admin.php"><i class="fa-solid fa-list-check"></i> Manage Orders</a></li>
            <li><a href="admin_products.php"><i class="fa-solid fa-boxes-stacked"></i> Manage Products</a></li>
            <li><a href="admin_services.php"><i class="fa-solid fa-wrench"></i> Manage Services</a></li>
            <li><a href="admin_messages.php"><i class="fa-solid fa-envelope"></i> View Messages</a></li>
            <li><a href="admin_users.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </nav>

    <section class="dashboard-section">
        <h2>Registered Users</h2>
        <?php if ($result_users && $result_users->num_rows > 0): // Check if $result_users is an object before accessing num_rows ?>
            <table class="user-list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <!-- Add actions like 'Edit User', 'Delete User' later if needed -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row_user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo $row_user['id']; ?></td>
                            <td data-label="Username"><?php echo htmlspecialchars($row_user['username']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($row_user['email']); ?></td>
                            <td data-label="Full Name"><?php echo htmlspecialchars($row_user['full_name'] ?: 'N/A'); ?></td>
                            <td data-label="Phone"><?php echo htmlspecialchars($row_user['phone_number'] ?: 'N/A'); ?></td>
                            <td data-label="Role"><span class="role-badge <?php echo strtolower($row_user['role']); ?>"><?php echo htmlspecialchars(ucfirst($row_user['role'])); ?></span></td>
                            <td data-label="Joined Date"><?php echo date('Y-m-d', strtotime($row_user['registration_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users registered yet.</p>
        <?php endif; ?>
    </section>
</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
