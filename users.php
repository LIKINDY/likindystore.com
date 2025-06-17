<?php
// admin/users.php
// This page displays a list of all registered users for the admin.

// Include necessary files (assuming an admin header and database connection)
// You might have a specific admin header, or reuse the main header if suitable.
// For simplicity, let's include the main header for now.
include '../header.php'; // Adjust path if your admin folder structure is different

// IMPORTANT: Implement proper admin authentication here!
// For example:
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); // Redirect to admin login
    exit();
}
*/

// Fetch all users from the database
$users = [];
$sql_get_users = "SELECT id, full_name, email, phone_number, registration_date FROM `users` ORDER BY registration_date DESC";
$result_users = $conn->query($sql_get_users);

if ($result_users) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    echo "<div class='message error'>Error fetching users: " . $conn->error . "</div>";
}

?>
<style>
    /* Admin specific styles (can be moved to a separate admin.css) */
    .admin-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 40px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .admin-header h1 {
        font-size: 3em;
        margin-bottom: 10px;
    }
    .admin-header p {
        font-size: 1.1em;
        opacity: 0.9;
    }

    .admin-content-section {
        background-color: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        margin-bottom: 60px;
    }

    .admin-content-section h2 {
        font-size: 2.2em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .users-table th, .users-table td {
        border: 1px solid var(--light-grey-border);
        padding: 12px;
        text-align: left;
        font-size: 0.95em;
    }
    .users-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
    }
    .users-table tr:nth-child(even) {
        background-color: var(--background-light);
    }
    .users-table tr:hover {
        background-color: #e9ecef;
    }
    .users-table td.actions {
        text-align: center;
    }
    .users-table .action-btn {
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
        color: var(--white);
        font-size: 0.9em;
        margin: 0 5px;
        transition: background-color 0.3s ease;
    }
    .users-table .view-btn {
        background-color: var(--accent-blue);
    }
    .users-table .view-btn:hover {
        background-color: var(--accent-hover);
    }
    .users-table .edit-btn {
        background-color: var(--cta-green);
    }
    .users-table .edit-btn:hover {
        background-color: var(--cta-green-hover);
    }
    .users-table .delete-btn {
        background-color: var(--danger-red);
    }
    .users-table .delete-btn:hover {
        background-color: #c0392b;
    }

    /* Message styles (reused from global) */
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

    /* Responsive table */
    @media (max-width: 768px) {
        .users-table, .users-table thead, .users-table tbody, .users-table th, .users-table td, .users-table tr {
            display: block;
        }
        .users-table thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }
        .users-table tr {
            border: 1px solid var(--light-grey-border);
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
        }
        .users-table td {
            border: none;
            border-bottom: 1px solid var(--light-grey-border);
            position: relative;
            padding-left: 50%; /* Space for pseudo-element label */
            text-align: right;
        }
        .users-table td:last-child {
            border-bottom: none;
        }
        .users-table td::before {
            content: attr(data-label); /* Use data-label for responsive headers */
            position: absolute;
            left: 10px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            text-align: left;
            font-weight: 600;
            color: var(--primary-dark);
        }
        .users-table td.actions {
            text-align: center;
            padding-left: 10px; /* No label for actions */
        }
    }
</style>

<div class="container">
    <section class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>Manage users, orders, products, and services.</p>
    </section>

    <div class="admin-content-section">
        <h2>Registered Users (<?php echo count($users); ?>)</h2>
        <?php if (empty($users)): ?>
            <p>No registered users found.</p>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td data-label="ID"><?php echo htmlspecialchars($user['id']); ?></td>
                        <td data-label="Full Name"><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td data-label="Phone Number"><?php echo htmlspecialchars($user['phone_number'] ?: 'N/A'); ?></td>
                        <td data-label="Registration Date"><?php echo date('Y-m-d H:i', strtotime($user['registration_date'])); ?></td>
                        <td data-label="Actions" class="actions">
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn"><i class="fa-solid fa-edit"></i> Edit</a>
                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fa-solid fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the footer
include '../footer.php'; // Adjust path if your admin folder structure is different
?>
