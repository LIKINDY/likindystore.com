<?php
// admin_services.php
// Admin page for managing mobile phone repair services.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn) and session_start()

// --- Simple Admin Authentication (For Local Development) ---
// **IMPORTANT: Change this password for better security even in local dev.**
$admin_password_local_dev = "likindyadmin2025"; // Must match the password in admin.php and admin_products.php

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, redirect to admin.php (login page)
    header("Location: admin.php");
    exit();
}

// --- Handle Service Form Submissions ---
$message = ""; // To store success/error messages

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_service' || $_POST['action'] == 'edit_service') {
        // Sanitize and validate inputs
        $name_en = $conn->real_escape_string($_POST['name_en']);
        $name_sw = $conn->real_escape_string($_POST['name_sw']);
        $description_en = $conn->real_escape_string($_POST['description_en']);
        $description_sw = $conn->real_escape_string($_POST['description_sw']);
        $base_price = $_POST['base_price'] === '' ? 'NULL' : filter_var($_POST['base_price'], FILTER_VALIDATE_FLOAT);
        $image_url = $conn->real_escape_string($_POST['image_url']);

        // Basic validation
        if (!$name_en) {
            $message = "<div class='message error'>Error: Service Name (English) is required.</div>";
        } else {
            if ($_POST['action'] == 'add_service') {
                $sql = "INSERT INTO `services` (`name_en`, `name_sw`, `description_en`, `description_sw`, `base_price`, `image_url`) VALUES ('$name_en', '$name_sw', '$description_en', '$description_sw', " . ($base_price === false ? 'NULL' : "'$base_price'") . ", '$image_url')";
                if ($conn->query($sql) === TRUE) {
                    $message = "<div class='message success'>New service added successfully!</div>";
                } else {
                    $message = "<div class='message error'>Error adding service: " . $conn->error . "</div>";
                }
            } elseif ($_POST['action'] == 'edit_service' && isset($_POST['service_id'])) {
                $service_id = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);
                if ($service_id === false) {
                    $message = "<div class='message error'>Error: Invalid service ID for editing.</div>";
                } else {
                    $sql = "UPDATE `services` SET `name_en`='$name_en', `name_sw`='$name_sw', `description_en`='$description_en', `description_sw`='$description_sw', `base_price`=" . ($base_price === false ? 'NULL' : "'$base_price'") . ", `image_url`='$image_url' WHERE `id`=$service_id";
                    if ($conn->query($sql) === TRUE) {
                        $message = "<div class='message success'>Service updated successfully!</div>";
                    } else {
                        $message = "<div class='message error'>Error updating service: " . $conn->error . "</div>";
                    }
                }
            }
        }
    } elseif ($_POST['action'] == 'delete_service' && isset($_POST['service_id'])) {
        $service_id = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);
        if ($service_id === false) {
            $message = "<div class='message error'>Error: Invalid service ID for deletion.</div>";
        } else {
            $sql = "DELETE FROM `services` WHERE `id`=$service_id";
            if ($conn->query($sql) === TRUE) {
                $message = "<div class='message success'>Service deleted successfully!</div>";
            } else {
                $message = "<div class='message error'>Error deleting service: " . $conn->error . "</div>";
            }
        }
    }
}

// --- Fetch Services from Database ---
$sql_services = "SELECT * FROM `services` ORDER BY `name_en` ASC";
$result_services = $conn->query($sql_services);

?>

<!-- CSS Specific to Admin Services Page (reusing general admin styles from header) -->
<style>
    /* Reusing .admin-nav, .dashboard-section, .message from header.php / admin_products.php */

    /* Form Styles (reused from admin_products.php for consistency) */
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: var(--primary-dark);
    }
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--light-grey-border);
        border-radius: 8px;
        font-size: 1em;
        box-sizing: border-box;
    }
    .form-group textarea {
        min-height: 80px;
        resize: vertical;
    }
    .form-actions {
        text-align: right;
    }
    .form-actions button {
        background-color: var(--cta-green);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.05em;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    .form-actions button:hover {
        background-color: var(--cta-green-hover);
    }
    .form-actions button.cancel-edit {
        background-color: var(--secondary-grey);
        margin-right: 10px;
    }
    .form-actions button.cancel-edit:hover {
        background-color: #5a6773;
    }

    /* Service List Table Styles */
    .service-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .service-list-table th, .service-list-table td {
        padding: 12px 15px;
        border: 1px solid var(--light-grey-border);
        text-align: left;
    }
    .service-list-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .service-list-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .service-list-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .service-list-table td img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
    }
    .service-list-table .actions-column {
        white-space: nowrap;
        text-align: center;
    }
    .service-list-table .action-button {
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
    .service-list-table .action-button:hover {
        background-color: var(--accent-hover);
    }
    .service-list-table .action-button.delete {
        background-color: var(--danger-red);
    }
    .service-list-table .action-button.delete:hover {
        background-color: #c0392b;
    }

    /* Responsive Table for Services */
    @media (max-width: 768px) {
        .admin-nav ul {
            flex-direction: column;
            gap: 10px;
        }
        .service-list-table, .service-list-table tbody, .service-list-table tr, .service-list-table td, .service-list-table th {
            display: block;
            width: 100%;
        }
        .service-list-table thead {
            display: none;
        }
        .service-list-table tr {
            margin-bottom: 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .service-list-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
        }
        .service-list-table td::before {
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
        .service-list-table .actions-column {
            text-align: left;
            padding-left: 15px;
        }
    }
</style>

<div class="container">
    <header class="admin-header">
        <h1>Admin Panel - Likindy Digital Solution</h1>
        <p>Manage Your Store's Products & Services</p>
    </header>

    <nav class="admin-nav">
        <ul>
            <li><a href="admin.php"><i class="fa-solid fa-list-check"></i> Manage Orders</a></li>
            <li><a href="admin_products.php"><i class="fa-solid fa-boxes-stacked"></i> Manage Products</a></li>
            <li><a href="admin_services.php" class="active"><i class="fa-solid fa-wrench"></i> Manage Services</a></li>
            <li><a href="admin_messages.php"><i class="fa-solid fa-envelope"></i> View Messages</a></li>
            <li><a href="admin_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </nav>

    <?php echo $message; // Display success/error messages ?>

    <section class="dashboard-section">
        <h2 id="add-service-section">Add/Edit Service</h2>
        <form method="POST" action="admin_services.php" id="serviceForm">
            <input type="hidden" name="action" id="formAction" value="add_service">
            <input type="hidden" name="service_id" id="serviceId">

            <div class="form-group">
                <label for="name_en">Service Name (English):</label>
                <input type="text" id="name_en" name="name_en" required>
            </div>
            <div class="form-group">
                <label for="name_sw">Service Name (Swahili):</label>
                <input type="text" id="name_sw" name="name_sw">
            </div>
            <div class="form-group">
                <label for="description_en">Description (English):</label>
                <textarea id="description_en" name="description_en"></textarea>
            </div>
            <div class="form-group">
                <label for="description_sw">Description (Swahili):</label>
                <textarea id="description_sw" name="description_sw"></textarea>
            </div>
            <div class="form-group">
                <label for="base_price">Base Price (Tsh - optional, leave empty for custom quote):</label>
                <input type="number" id="base_price" name="base_price" step="0.01">
            </div>
            <div class="form-group">
                <label for="image_url">Image URL (e.g., assets/images/service_repair.jpg):</label>
                <input type="text" id="image_url" name="image_url">
            </div>
            <div class="form-actions">
                <button type="submit" id="submitButton"><i class="fa-solid fa-plus"></i> Add Service</button>
                <button type="button" id="cancelEditButton" class="cancel-edit" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel Edit</button>
            </div>
        </form>
    </section>

    <section class="dashboard-section">
        <h2>Existing Services</h2>
        <?php if ($result_services->num_rows > 0): ?>
            <table class="service-list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name (EN)</th>
                        <th>Name (SW)</th>
                        <th>Base Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_services->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo $row['id']; ?></td>
                            <td data-label="Image">
                                <img src="<?php echo htmlspecialchars($row['image_url'] ?: 'https://placehold.co/60x60/f0f0f0/AAAAAA?text=NoImg'); ?>" alt="<?php echo htmlspecialchars($row['name_en']); ?>">
                            </td>
                            <td data-label="Name (EN)"><?php echo htmlspecialchars($row['name_en']); ?></td>
                            <td data-label="Name (SW)"><?php echo htmlspecialchars($row['name_sw']); ?></td>
                            <td data-label="Base Price">
                                <?php echo $row['base_price'] !== NULL ? 'Tsh ' . number_format($row['base_price'], 2) : 'Custom Quote'; ?>
                            </td>
                            <td data-label="Actions" class="actions-column">
                                <button class="action-button edit-button" data-id="<?php echo $row['id']; ?>"
                                        data-name-en="<?php echo htmlspecialchars($row['name_en']); ?>"
                                        data-name-sw="<?php echo htmlspecialchars($row['name_sw']); ?>"
                                        data-desc-en="<?php echo htmlspecialchars($row['description_en']); ?>"
                                        data-desc-sw="<?php echo htmlspecialchars($row['description_sw']); ?>"
                                        data-price="<?php echo htmlspecialchars($row['base_price']); ?>"
                                        data-image="<?php echo htmlspecialchars($row['image_url']); ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <form method="POST" action="admin_services.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                    <input type="hidden" name="action" value="delete_service">
                                    <input type="hidden" name="service_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="action-button delete"><i class="fa-solid fa-trash-can"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No services added yet. Use the form above to add your first service.</p>
        <?php endif; ?>
    </section>
</div>

<!-- JavaScript for Edit Functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceForm = document.getElementById('serviceForm');
        const formAction = document.getElementById('formAction');
        const serviceIdInput = document.getElementById('serviceId');
        const nameEnInput = document.getElementById('name_en');
        const nameSwInput = document.getElementById('name_sw');
        const descEnInput = document.getElementById('description_en');
        const descSwInput = document.getElementById('description_sw');
        const basePriceInput = document.getElementById('base_price');
        const imageUrlInput = document.getElementById('image_url');
        const submitButton = document.getElementById('submitButton');
        const cancelEditButton = document.getElementById('cancelEditButton');
        const addServiceSection = document.getElementById('add-service-section');

        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nameEn = this.dataset.nameEn;
                const nameSw = this.dataset.nameSw;
                const descEn = this.dataset.descEn;
                const descSw = this.dataset.descSw;
                const price = this.dataset.price; // This will be 'null' for NULL values in DB, or a number
                const image = this.dataset.image;

                // Populate the form with existing service data
                serviceIdInput.value = id;
                nameEnInput.value = nameEn;
                nameSwInput.value = nameSw;
                descEnInput.value = descEn;
                descSwInput.value = descSw;
                // Handle base_price being 'null' or a number
                basePriceInput.value = (price === 'null' || price === null) ? '' : price;
                imageUrlInput.value = image;

                // Change form action to 'edit_service'
                formAction.value = 'edit_service';
                submitButton.innerHTML = '<i class="fa-solid fa-check"></i> Update Service';
                cancelEditButton.style.display = 'inline-block';
                addServiceSection.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit Service'; // Change section title

                // Scroll to the form
                window.scrollTo({
                    top: serviceForm.offsetTop - 100,
                    behavior: 'smooth'
                });
            });
        });

        cancelEditButton.addEventListener('click', function() {
            // Reset form to 'Add Service' mode
            serviceForm.reset(); // Clears all form fields
            formAction.value = 'add_service';
            serviceIdInput.value = '';
            submitButton.innerHTML = '<i class="fa-solid fa-plus"></i> Add Service';
            cancelEditButton.style.display = 'none';
            addServiceSection.innerHTML = 'Add/Edit Service'; // Reset section title
        });
    });
</script>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
