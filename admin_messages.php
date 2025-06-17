<?php
// admin_messages.php
// Admin page for viewing and managing contact messages.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn) and session_start()

// --- Simple Admin Authentication (For Local Development) ---
$admin_password_local_dev = "likindyadmin2025"; // Must match password in other admin files

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php"); // Redirect to admin login if not authenticated
    exit();
}

// --- Handle Message Actions (Mark as Read/Unread, Delete) ---
$message_status = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['message_id'])) {
    $message_id = filter_var($_POST['message_id'], FILTER_VALIDATE_INT);
    if ($message_id === false) {
        $message_status = "<div class='message error'>Error: Invalid message ID.</div>";
    } else {
        if ($_POST['action'] == 'mark_read') {
            $sql = "UPDATE `contact_messages` SET `is_read` = TRUE WHERE `id` = $message_id";
            if ($conn->query($sql) === TRUE) {
                $message_status = "<div class='message success'>Message marked as read.</div>";
            } else {
                $message_status = "<div class='message error'>Error marking message as read: " . $conn->error . "</div>";
            }
        } elseif ($_POST['action'] == 'mark_unread') {
            $sql = "UPDATE `contact_messages` SET `is_read` = FALSE WHERE `id` = $message_id";
            if ($conn->query($sql) === TRUE) {
                $message_status = "<div class='message success'>Message marked as unread.</div>";
            } else {
                $message_status = "<div class='message error'>Error marking message as unread: " . $conn->error . "</div>";
            }
        } elseif ($_POST['action'] == 'delete_message') {
            $sql = "DELETE FROM `contact_messages` WHERE `id` = $message_id";
            if ($conn->query($sql) === TRUE) {
                $message_status = "<div class='message success'>Message deleted successfully.</div>";
            } else {
                $message_status = "<div class='message error'>Error deleting message: " . $conn->error . "</div>";
            }
        }
    }
}


// --- Fetch Contact Messages from Database ---
$sql_messages = "SELECT * FROM `contact_messages` ORDER BY `sent_at` DESC";
$result_messages = $conn->query($sql_messages);

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

    /* Messages (Success/Error - reused) */
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


    /* Message List Table Styles */
    .message-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .message-list-table th, .message-list-table td {
        padding: 12px 15px;
        border: 1px solid var(--light-grey-border);
        text-align: left;
    }
    .message-list-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .message-list-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .message-list-table tbody tr.unread {
        font-weight: 700; /* Bold for unread messages */
        background-color: #fffacd; /* Light yellow background */
    }
    .message-list-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .message-list-table .actions-column {
        white-space: nowrap;
        text-align: center;
    }
    .message-list-table .action-button {
        background-color: var(--accent-blue);
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        transition: background-color 0.3s ease;
        margin: 2px;
        display: inline-block;
    }
    .message-list-table .action-button:hover {
        background-color: var(--accent-hover);
    }
    .message-list-table .action-button.delete {
        background-color: var(--danger-red);
    }
    .message-list-table .action-button.delete:hover {
        background-color: #c0392b;
    }
    .message-list-table .action-button.mark-read,
    .message-list-table .action-button.mark-unread {
        background-color: #28a745; /* Green for read/unread toggle */
    }
    .message-list-table .action-button.mark-read:hover,
    .message-list-table .action-button.mark-unread:hover {
        background-color: #218838;
    }

    /* Responsive Table for Messages */
    @media (max-width: 768px) {
        .admin-nav ul {
            flex-direction: column;
            gap: 10px;
        }
        .message-list-table, .message-list-table tbody, .message-list-table tr, .message-list-table td, .message-list-table th {
            display: block;
            width: 100%;
        }
        .message-list-table thead {
            display: none;
        }
        .message-list-table tr {
            margin-bottom: 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .message-list-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
        }
        .message-list-table td::before {
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
        .message-list-table .actions-column {
            text-align: left;
            padding-left: 15px;
        }
    }
</style>

<div class="container">
    <header class="admin-header">
        <h1>Admin Panel - Likindy Digital Solution</h1>
        <p>Manage Customer Messages</p>
    </header>

    <nav class="admin-nav">
        <ul>
            <li><a href="admin.php"><i class="fa-solid fa-list-check"></i> Manage Orders</a></li>
            <li><a href="admin_products.php"><i class="fa-solid fa-boxes-stacked"></i> Manage Products</a></li>
            <li><a href="admin_services.php"><i class="fa-solid fa-wrench"></i> Manage Services</a></li>
            <li><a href="admin_messages.php" class="active"><i class="fa-solid fa-envelope"></i> View Messages</a></li>
            <li><a href="admin_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </nav>

    <?php echo $message_status; // Display success/error messages ?>

    <section class="dashboard-section">
        <h2>Customer Messages</h2>
        <?php if ($result_messages->num_rows > 0): ?>
            <table class="message-list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sender Name</th>
                        <th>Sender Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Service Inquiry</th>
                        <th>Sent At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_messages->fetch_assoc()): ?>
                        <tr class="<?php echo $row['is_read'] ? 'read' : 'unread'; ?>">
                            <td data-label="ID"><?php echo $row['id']; ?></td>
                            <td data-label="Sender Name"><?php echo htmlspecialchars($row['sender_name']); ?></td>
                            <td data-label="Sender Email"><?php echo htmlspecialchars($row['sender_email']); ?></td>
                            <td data-label="Subject"><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td data-label="Message" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($row['message']); ?>">
                                <?php echo htmlspecialchars($row['message']); ?>
                            </td>
                            <td data-label="Service Inquiry"><?php echo htmlspecialchars($row['service_inquiry'] ?: 'N/A'); ?></td>
                            <td data-label="Sent At"><?php echo date('Y-m-d H:i', strtotime($row['sent_at'])); ?></td>
                            <td data-label="Status"><?php echo $row['is_read'] ? 'Read' : 'Unread'; ?></td>
                            <td data-label="Actions" class="actions-column">
                                <form method="POST" action="admin_messages.php" style="display:inline-block;">
                                    <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                    <?php if ($row['is_read']): ?>
                                        <input type="hidden" name="action" value="mark_unread">
                                        <button type="submit" class="action-button mark-unread"><i class="fa-solid fa-eye-slash"></i> Mark Unread</button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="mark_read">
                                        <button type="submit" class="action-button mark-read"><i class="fa-solid fa-eye"></i> Mark Read</button>
                                    <?php endif; ?>
                                </form>
                                <form method="POST" action="admin_messages.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="action-button delete"><i class="fa-solid fa-trash-can"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No contact messages received yet.</p>
        <?php endif; ?>
    </section>
</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
