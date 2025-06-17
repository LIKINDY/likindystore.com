<?php
// admin_products.php
// Admin page for managing mobile accessories (products).

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn)

// --- Simple Admin Authentication (For Local Development) ---
// **IMPORTANT: Change this password for better security even in local dev.**
$admin_password_local_dev = "likindyadmin2025"; // Must match the password in admin.php

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
                <form method='POST' action='admin_products.php'>
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


// --- Handle Product Form Submissions ---
$message = ""; // To store success/error messages

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_product' || $_POST['action'] == 'edit_product') {
        // Sanitize and validate inputs
        $name = $conn->real_escape_string($_POST['name']);
        $description_en = $conn->real_escape_string($_POST['description_en']);
        $description_sw = $conn->real_escape_string($_POST['description_sw']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $image_url = $conn->real_escape_string($_POST['image_url']);
        $category = $conn->real_escape_string($_POST['category']);
        $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0; // Checkbox value

        // Basic validation
        if (!$name || $price === false || $stock === false) {
            $message = "<div class='message error'>Error: Name, Price, and Stock are required and must be valid.</div>";
        } else {
            if ($_POST['action'] == 'add_product') {
                $sql = "INSERT INTO `products` (`name`, `description_en`, `description_sw`, `price`, `image_url`, `category`, `stock`, `is_featured`) VALUES ('$name', '$description_en', '$description_sw', '$price', '$image_url', '$category', '$stock', '$is_featured')";
                if ($conn->query($sql) === TRUE) {
                    $message = "<div class='message success'>New product added successfully!</div>";
                } else {
                    $message = "<div class='message error'>Error adding product: " . $conn->error . "</div>";
                }
            } elseif ($_POST['action'] == 'edit_product' && isset($_POST['product_id'])) {
                $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
                if ($product_id === false) {
                    $message = "<div class='message error'>Error: Invalid product ID for editing.</div>";
                } else {
                    $sql = "UPDATE `products` SET `name`='$name', `description_en`='$description_en', `description_sw`='$description_sw', `price`='$price', `image_url`='$image_url', `category`='$category', `stock`='$stock', `is_featured`='$is_featured' WHERE `id`=$product_id";
                    if ($conn->query($sql) === TRUE) {
                        $message = "<div class='message success'>Product updated successfully!</div>";
                    } else {
                        $message = "<div class='message error'>Error updating product: " . $conn->error . "</div>";
                    }
                }
            }
        }
    } elseif ($_POST['action'] == 'delete_product' && isset($_POST['product_id'])) {
        $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
        if ($product_id === false) {
            $message = "<div class='message error'>Error: Invalid product ID for deletion.</div>";
        } else {
            $sql = "DELETE FROM `products` WHERE `id`=$product_id";
            if ($conn->query($sql) === TRUE) {
                $message = "<div class='message success'>Product deleted successfully!</div>";
            } else {
                $message = "<div class='message error'>Error deleting product: " . $conn->error . "</div>";
            }
        }
    }
}

// --- Fetch Products from Database ---
$sql_products = "SELECT * FROM `products` ORDER BY `name` ASC";
$result_products = $conn->query($sql_products);

?>

<!-- CSS Specific to Admin Products Page -->
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

    /* Form Styles */
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
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--light-grey-border);
        border-radius: 8px;
        font-size: 1em;
        box-sizing: border-box; /* Ensures padding doesn't increase total width */
    }
    .form-group textarea {
        min-height: 80px;
        resize: vertical;
    }
    .form-group input[type="checkbox"] {
        margin-right: 8px;
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

    /* Messages (Success/Error) */
    .message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
        opacity: 0; /* Hidden by default */
        transition: opacity 0.5s ease-in-out;
        animation: fadeIn 0.5s forwards; /* Animation to show it */
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


    /* Product List Table Styles */
    .product-list-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .product-list-table th, .product-list-table td {
        padding: 12px 15px;
        border: 1px solid var(--light-grey-border);
        text-align: left;
    }
    .product-list-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .product-list-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .product-list-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .product-list-table td img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
    }
    .product-list-table .actions-column {
        white-space: nowrap; /* Keep buttons on one line */
        text-align: center;
    }
    .product-list-table .action-button {
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
    .product-list-table .action-button:hover {
        background-color: var(--accent-hover);
    }
    .product-list-table .action-button.delete {
        background-color: var(--danger-red);
    }
    .product-list-table .action-button.delete:hover {
        background-color: #c0392b;
    }

    /* Responsive Table for Products */
    @media (max-width: 768px) {
        .admin-nav ul {
            flex-direction: column;
            gap: 10px;
        }
        .product-list-table, .product-list-table tbody, .product-list-table tr, .product-list-table td, .product-list-table th {
            display: block;
            width: 100%;
        }
        .product-list-table thead {
            display: none;
        }
        .product-list-table tr {
            margin-bottom: 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .product-list-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
        }
        .product-list-table td::before {
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
        .product-list-table .actions-column {
            text-align: left; /* Align actions to left on mobile */
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
            <li><a href="admin_products.php" class="active"><i class="fa-solid fa-boxes-stacked"></i> Manage Products</a></li>
            <li><a href="admin_services.php"><i class="fa-solid fa-wrench"></i> Manage Services</a></li>
            <li><a href="admin_messages.php"><i class="fa-solid fa-envelope"></i> View Messages</a></li>
            <li><a href="admin_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </nav>

    <?php echo $message; // Display success/error messages ?>

    <section class="dashboard-section">
        <h2 id="add-product-section">Add/Edit Product</h2>
        <form method="POST" action="admin_products.php" id="productForm">
            <input type="hidden" name="action" id="formAction" value="add_product">
            <input type="hidden" name="product_id" id="productId">

            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
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
                <label for="price">Price (Tsh):</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="image_url">Image URL (e.g., assets/images/product_name.jpg):</label>
                <input type="text" id="image_url" name="image_url">
            </div>
            <div class="form-group">
                <label for="category">Category (e.g., Covers, Chargers, Earphones):</label>
                <input type="text" id="category" name="category">
            </div>
            <div class="form-group">
                <label for="stock">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            <div class="form-group">
                <input type="checkbox" id="is_featured" name="is_featured">
                <label for="is_featured" style="display: inline;">Mark as Featured Product (for Homepage)</label>
            </div>
            <div class="form-actions">
                <button type="submit" id="submitButton"><i class="fa-solid fa-plus"></i> Add Product</button>
                <button type="button" id="cancelEditButton" class="cancel-edit" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel Edit</button>
            </div>
        </form>
    </section>

    <section class="dashboard-section">
        <h2>Existing Products</h2>
        <?php if ($result_products->num_rows > 0): ?>
            <table class="product-list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_products->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo $row['id']; ?></td>
                            <td data-label="Image">
                                <img src="<?php echo htmlspecialchars($row['image_url'] ?: 'https://placehold.co/60x60/f0f0f0/AAAAAA?text=NoImg'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            </td>
                            <td data-label="Name"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td data-label="Category"><?php echo htmlspecialchars($row['category']); ?></td>
                            <td data-label="Price">Tsh <?php echo number_format($row['price'], 2); ?></td>
                            <td data-label="Stock"><?php echo $row['stock']; ?></td>
                            <td data-label="Featured"><?php echo $row['is_featured'] ? 'Yes' : 'No'; ?></td>
                            <td data-label="Actions" class="actions-column">
                                <button class="action-button edit-button" data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                        data-desc-en="<?php echo htmlspecialchars($row['description_en']); ?>"
                                        data-desc-sw="<?php echo htmlspecialchars($row['description_sw']); ?>"
                                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                        data-image="<?php echo htmlspecialchars($row['image_url']); ?>"
                                        data-category="<?php echo htmlspecialchars($row['category']); ?>"
                                        data-stock="<?php echo htmlspecialchars($row['stock']); ?>"
                                        data-featured="<?php echo $row['is_featured']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <form method="POST" action="admin_products.php" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="action-button delete"><i class="fa-solid fa-trash-can"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No products added yet. Use the form above to add your first product.</p>
        <?php endif; ?>
    </section>
</div>

<!-- JavaScript for Edit Functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productForm = document.getElementById('productForm');
        const formAction = document.getElementById('formAction');
        const productId = document.getElementById('productId');
        const nameInput = document.getElementById('name');
        const descEnInput = document.getElementById('description_en');
        const descSwInput = document.getElementById('description_sw');
        const priceInput = document.getElementById('price');
        const imageUrlInput = document.getElementById('image_url');
        const categoryInput = document.getElementById('category');
        const stockInput = document.getElementById('stock');
        const isFeaturedCheckbox = document.getElementById('is_featured');
        const submitButton = document.getElementById('submitButton');
        const cancelEditButton = document.getElementById('cancelEditButton');
        const addProductSection = document.getElementById('add-product-section');

        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const descEn = this.dataset.descEn;
                const descSw = this.dataset.descSw;
                const price = this.dataset.price;
                const image = this.dataset.image;
                const category = this.dataset.category;
                const stock = this.dataset.stock;
                const featured = this.dataset.featured === '1' ? true : false; // Convert '1' to true, '0' to false

                // Populate the form with existing product data
                productId.value = id;
                nameInput.value = name;
                descEnInput.value = descEn;
                descSwInput.value = descSw;
                priceInput.value = price;
                imageUrlInput.value = image;
                categoryInput.value = category;
                stockInput.value = stock;
                isFeaturedCheckbox.checked = featured;

                // Change form action to 'edit_product'
                formAction.value = 'edit_product';
                submitButton.innerHTML = '<i class="fa-solid fa-check"></i> Update Product';
                cancelEditButton.style.display = 'inline-block';
                addProductSection.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit Product'; // Change section title

                // Scroll to the form
                window.scrollTo({
                    top: productForm.offsetTop - 100, // Scroll slightly above the form
                    behavior: 'smooth'
                });
            });
        });

        cancelEditButton.addEventListener('click', function() {
            // Reset form to 'Add Product' mode
            productForm.reset(); // Clears all form fields
            formAction.value = 'add_product';
            productId.value = '';
            submitButton.innerHTML = '<i class="fa-solid fa-plus"></i> Add Product';
            cancelEditButton.style.display = 'none';
            addProductSection.innerHTML = 'Add/Edit Product'; // Reset section title
            isFeaturedCheckbox.checked = false; // Reset checkbox manually as reset() might not handle it universally
        });
    });
</script>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
