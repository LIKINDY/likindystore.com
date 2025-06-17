<?php
// checkout.php
// This page handles the checkout process and order placement.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn) and session_start()

// --- IMPORTANT: FORCE LOGIN/REGISTRATION BEFORE CHECKOUT ---
// If user is NOT logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php'; // Store current page to redirect back after login
    header("Location: login.php");
    exit();
}
// --- END FORCE LOGIN/REGISTRATION ---


// Check if cart is empty, if so, redirect to cart page
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$checkout_message = '';
$cart_total = 0;

// Initialize form fields with empty values or pre-fill if user is logged in
$customer_name = '';
$customer_email = '';
$customer_phone = '';
$shipping_address = '';
$notes = '';

// This block will always run now because we force login above
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql_user_details = "SELECT full_name, email, phone_number, address FROM `users` WHERE id = ?"; // Added 'address' column
    $stmt_user = $conn->prepare($sql_user_details);
    if ($stmt_user === FALSE) {
        error_log("SQL Error in checkout.php user details prepare: " . $conn->error);
        // Display a user-friendly message if there's a database error here
        $checkout_message .= "<div class='message error'>Could not load user details due to a database error. Please try again.</div>";
    } else {
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows > 0) {
            $user_data = $result_user->fetch_assoc();
            $customer_name = htmlspecialchars($user_data['full_name']);
            $customer_email = htmlspecialchars($user_data['email']);
            $customer_phone = htmlspecialchars($user_data['phone_number']);
            $shipping_address = htmlspecialchars($user_data['address']); // Pre-fill address if available
        } else {
            // User ID in session but not found in DB - indicates an issue or old session
            error_log("User ID " . $user_id . " in session but not found in database.");
            $checkout_message .= "<div class='message error'>Your user session is invalid. Please log out and log in again.</div>";
            session_destroy(); // Invalidate session
            header("Location: login.php"); // Redirect to login
            exit();
        }
        $stmt_user->close();
    }
}


// Calculate cart total and fetch product details to ensure current prices/info
$cart_items_display = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item_id => $item_details) {
        // Re-fetch product data from DB to ensure latest price and stock
        $sql_product_check = "SELECT `name`, `price`, `image_url`, `stock` FROM `products` WHERE `id` = " . (int)$item_id;
        $result_product_check = $conn->query($sql_product_check);

        if ($result_product_check->num_rows > 0) {
            $db_product = $result_product_check->fetch_assoc();
            $current_price = $db_product['price'];
            $current_stock = $db_product['stock'];

            // Validate quantity against current stock
            if ($item_details['quantity'] > $current_stock) {
                $item_details['quantity'] = $current_stock; // Adjust quantity to available stock
                $_SESSION['cart'][$item_id]['quantity'] = $current_stock; // Update session cart
                if ($current_stock > 0) {
                     $checkout_message = "<div class='message error'>Adjusted quantity for " . htmlspecialchars($db_product['name']) . " to available stock (" . $current_stock . ").</div>";
                } else {
                    unset($_SESSION['cart'][$item_id]); // Remove if out of stock
                    $checkout_message = "<div class='message error'>Product " . htmlspecialchars($db_product['name']) . " is out of stock and removed from your cart.</div>";
                    continue; // Skip to next item
                }
            }

            // Ensure we use the latest price from the database
            $item_details['price'] = $current_price;

            $subtotal = $item_details['price'] * $item_details['quantity'];
            $cart_total += $subtotal;

            $cart_items_display[] = [
                'id' => $item_id,
                'name' => $db_product['name'],
                'price' => $current_price,
                'image_url' => $db_product['image_url'],
                'quantity' => $item_details['quantity'],
                'subtotal' => $subtotal
            ];
        } else {
            // Product not found in DB, remove from cart
            unset($_SESSION['cart'][$item_id]);
            $checkout_message = "<div class='message error'>A product in your cart was not found and has been removed.</div>";
        }
    }
}


// If cart becomes empty after validation, redirect
if (empty($cart_items_display)) {
    header("Location: cart.php");
    exit();
}


// --- Handle Checkout Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    // Sanitize and validate customer details
    $customer_name = $conn->real_escape_string(trim($_POST['customer_name']));
    $customer_email = $conn->real_escape_string(trim($_POST['customer_email']));
    $customer_phone = $conn->real_escape_string(trim($_POST['customer_phone']));
    $shipping_address = $conn->real_escape_string(trim($_POST['shipping_address']));
    $payment_method = $conn->real_escape_string(trim($_POST['payment_method']));
    $notes = $conn->real_escape_string(trim($_POST['notes']));

    // Basic validation
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($shipping_address) || empty($payment_method)) {
        $checkout_message = "<div class='message error'>Please fill in all required customer and shipping details.</div>";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $checkout_message = "<div class='message error'>Please enter a valid email address.</div>";
    } else {
        // Start a transaction for atomicity (either all inserts succeed or all fail)
        $conn->begin_transaction();

        try {
            // 1. Insert into `orders` table
            // Ensure the columns match your current 'orders' table schema exactly
            // The `sql-create-orders-tables` immersive confirms the schema:
            // id, user_id, total_amount, status, customer_name, customer_email,
            // customer_phone, shipping_address, payment_method, notes, order_date
            $sql_insert_order = "INSERT INTO `orders` (`user_id`, `total_amount`, `status`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `payment_method`, `notes`) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)";
            $stmt_order = $conn->prepare($sql_insert_order);
            
            // user_id is $_SESSION['user_id'] if logged in, otherwise NULL (though we force login now)
            $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

            if ($stmt_order === FALSE) {
                // This indicates an error in the SQL query itself or database connection
                throw new Exception("SQL PREPARE Error for order insertion: " . $conn->error);
            }
            
            // Bind parameters:
            // 'i' for integer (user_id)
            // 'd' for double (total_amount)
            // 's' for string (customer_name, customer_email, customer_phone, shipping_address, payment_method, notes)
            // Total: 1 integer, 1 double, 6 strings = "idssssss" (status is fixed to 'pending')
            // Re-evaluating types for bind_param:
            // user_id (int), cart_total (decimal/double), customer_name (string), customer_email (string),
            // customer_phone (string), shipping_address (string), payment_method (string), notes (string)
            // Correct type string should be "idssssss" (for 8 parameters)
            $stmt_order->bind_param("idsssssss", $current_user_id, $cart_total, $customer_name, $customer_email, $customer_phone, $shipping_address, $payment_method, $notes);


            if (!$stmt_order->execute()) {
                // This indicates an error during execution (e.g., constraint violation, data too long)
                throw new Exception("SQL EXECUTE Error inserting order: " . $stmt_order->error);
            }
            $order_id = $conn->insert_id; // Get the ID of the newly inserted order

            // 2. Insert into `order_items` table for each cart item
            // Ensure column names match your 'order_items' table:
            // id, order_id, product_id, item_name, quantity, price_at_time_of_purchase
            $sql_insert_item = "INSERT INTO `order_items` (`order_id`, `product_id`, `item_name`, `quantity`, `price_at_time_of_purchase`) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_insert_item);
            if ($stmt_item === FALSE) {
                throw new Exception("SQL PREPARE Error for order item insertion: " . $conn->error);
            }

            foreach ($cart_items_display as $item) { // Iterate through the validated cart_items_display
                // Update stock in products table
                $sql_update_stock = "UPDATE `products` SET `stock` = `stock` - ? WHERE `id` = ?";
                $stmt_stock = $conn->prepare($sql_update_stock);
                if ($stmt_stock === FALSE) {
                    throw new Exception("SQL PREPARE Error for stock update: " . $conn->error);
                }
                $stmt_stock->bind_param("ii", $item['quantity'], $item['id']);
                if (!$stmt_stock->execute()) {
                    throw new Exception("SQL EXECUTE Error updating stock for product ID " . $item['id'] . ": " . $stmt_stock->error);
                }
                $stmt_stock->close();

                // Insert into order_items
                // Types: order_id (i), product_id (i), item_name (s), quantity (i), price_at_time_of_purchase (d)
                $stmt_item->bind_param("iisid", $order_id, $item['id'], $item['name'], $item['quantity'], $item['price']);
                if (!$stmt_item->execute()) {
                    throw new Exception("SQL EXECUTE Error inserting order item for product ID " . $item['id'] . ": " . $stmt_item->error);
                }
            }

            // If all successful, commit the transaction
            $conn->commit();

            // Clear the cart after successful order
            unset($_SESSION['cart']);

            // Redirect to a confirmation page
            header("Location: order-confirmation.php?order_id=" . $order_id);
            exit();

        } catch (Exception $e) {
            // If any error, rollback the transaction
            $conn->rollback();
            $checkout_message = "<div class='message error'>Order failed: " . htmlspecialchars($e->getMessage()) . "</div>";
            error_log("Checkout transaction error: " . $e->getMessage()); // Log detailed error for debugging
        } finally {
            // Close prepared statements if they were successfully prepared
            if (isset($stmt_order) && $stmt_order !== FALSE) $stmt_order->close();
            if (isset($stmt_item) && $stmt_item !== FALSE) $stmt_item->close();
            // stmt_stock is closed inside the loop
        }
    }
}
?>
<style>
    /* Specific styles for the Checkout page */
    .checkout-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .checkout-header h1 {
        font-size: 3em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .checkout-header p {
        font-size: 1.2em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .checkout-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 40px;
        margin-bottom: 60px;
    }

    .billing-shipping-section, .order-summary-section {
        background-color: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    }

    .billing-shipping-section {
        flex: 2 1 60%; /* Takes more space */
        min-width: 450px;
    }
    .order-summary-section {
        flex: 1 1 30%; /* Takes less space */
        min-width: 300px;
    }

    .checkout-grid h2 {
        font-size: 2em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
    }

    /* Form Styles (reusing from contact/admin) */
    .form-group {
        margin-bottom: 18px;
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
    .form-group input[type="tel"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--light-grey-border);
        border-radius: 8px;
        font-size: 1em;
        box-sizing: border-box;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        outline: none;
    }
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    /* Payment Methods */
    .payment-methods {
        margin-top: 25px;
    }
    .payment-methods label {
        display: block;
        margin-bottom: 15px;
        background-color: var(--background-light);
        padding: 15px;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease, border-color 0.3s ease;
        border: 2px solid transparent;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: var(--primary-dark);
    }
    .payment-methods label:hover {
        background-color: #e5e7eb;
    }
    .payment-methods input[type="radio"] {
        margin-right: 10px;
        transform: scale(1.2); /* Make radio button slightly larger */
    }
    .payment-methods input[type="radio"]:checked + span {
        color: var(--accent-blue);
        font-weight: 700;
    }
    .payment-methods input[type="radio"]:checked + span::before {
        content: '\f00c'; /* Font Awesome checkmark icon */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        margin-right: 5px;
        color: var(--cta-green);
    }


    /* Order Summary */
    .order-summary-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 15px;
        border-bottom: 1px dashed var(--light-grey-border);
        padding-bottom: 15px;
    }
    .order-summary-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .order-summary-item img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
    }
    .item-details {
        flex-grow: 1;
    }
    .item-details h4 {
        margin: 0 0 5px 0;
        font-size: 1.1em;
        color: var(--primary-dark);
    }
    .item-details p {
        margin: 0;
        font-size: 0.9em;
        color: var(--secondary-grey);
    }
    .item-total {
        font-weight: 600;
        color: var(--primary-dark);
    }
    .order-total {
        font-size: 2.2em;
        font-weight: 700;
        color: var(--accent-blue);
        margin-top: 25px;
        text-align: right;
    }

    .place-order-button {
        background-color: var(--cta-green);
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1.2em;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
        width: 100%;
        margin-top: 30px;
    }
    .place-order-button:hover {
        background-color: var(--cta-green-hover);
        transform: translateY(-3px);
    }

    /* Messages (Success/Error - reused from admin pages) */
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

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .checkout-grid {
            flex-direction: column;
            gap: 20px;
        }
        .billing-shipping-section, .order-summary-section {
            flex: 1 1 100%;
            min-width: unset;
        }
    }
    @media (max-width: 576px) {
        .checkout-header h1 {
            font-size: 2.2em;
        }
        .checkout-header p {
            font-size: 1em;
        }
    }

</style>

<div class="container">
    <section class="checkout-header">
        <h1>Checkout</h1>
        <p>Almost there! Please provide your details to complete your order.</p>
    </section>

    <?php echo $checkout_message; // Display checkout messages ?>

    <form method="POST" action="checkout.php">
        <div class="checkout-grid">
            <div class="billing-shipping-section">
                <h2>Billing & Shipping Details</h2>
                <?php if (isset($_SESSION['user_id'])): // This block will always run now if user is logged in ?>
                    <p>You are logged in as: <strong><?php echo $_SESSION['user_email']; ?></strong>. Your details will be pre-filled.</p>
                    <div class="form-group">
                        <label for="customer_name">Full Name:</label>
                        <input type="text" id="customer_name" name="customer_name" value="<?php echo $customer_name; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_email">Email Address:</label>
                        <input type="email" id="customer_email" name="customer_email" value="<?php echo $customer_email; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">Phone Number:</label>
                        <input type="tel" id="customer_phone" name="customer_phone" placeholder="+255 7XX XXX XXX" pattern="^\+?255[0-9]{9}$" value="<?php echo $customer_phone; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address (Include Street, City, Region):</label>
                        <textarea id="shipping_address" name="shipping_address" required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                    </div>
                <?php else: // This block will actually never be reached due to redirect ?>
                    <!-- This part is effectively redundant after forcing login, but kept for clarity -->
                    <p style="margin-bottom: 20px;">Already have an account? <a href="login.php" style="color: var(--accent-blue); text-decoration: underline;">Login</a> or <a href="register.php" style="color: var(--accent-blue); text-decoration: underline;">Register</a> for faster checkout and order tracking.</p>
                    <div class="form-group">
                        <label for="customer_name">Full Name:</label>
                        <input type="text" id="customer_name" name="customer_name" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_email">Email Address:</label>
                        <input type="email" id="customer_email" name="customer_email" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">Phone Number:</label>
                        <input type="tel" id="customer_phone" name="customer_phone" placeholder="+255 7XX XXX XXX" pattern="^\+?255[0-9]{9}$" value="" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address (Include Street, City, Region):</label>
                        <textarea id="shipping_address" name="shipping_address" required></textarea>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="notes">Order Notes (Optional):</label>
                    <textarea id="notes" name="notes" placeholder="e.g., Delivery instructions, preferred time"><?php echo htmlspecialchars($notes); ?></textarea>
                </div>

                <h2>Payment Method</h2>
                <div class="payment-methods">
                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="Cash on Delivery" required>
                            <span><i class="fa-solid fa-money-bill-wave"></i> Cash on Delivery (COD)</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="Mobile Money Transfer" required>
                            <span><i class="fa-solid fa-mobile-alt"></i> Mobile Money Transfer (M-Pesa, Tigo Pesa, Airtel Money)</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="Bank Transfer" required>
                            <span><i class="fa-solid fa-bank"></i> Bank Transfer</span>
                        </label>
                    </div>
                    <!-- Add more payment options here if needed later -->
                </div>
            </div>

            <div class="order-summary-section">
                <h2>Your Order Summary</h2>
                <?php if (!empty($cart_items_display)): ?>
                    <?php foreach ($cart_items_display as $item): ?>
                        <div class="order-summary-item">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://placehold.co/70x70/f0f0f0/AAAAAA?text=Item'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Quantity: <?php echo $item['quantity']; ?> x Tsh <?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <span class="item-total">Tsh <?php echo number_format($item['subtotal'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center;">Your cart is empty. Please add products to proceed.</p>
                <?php endif; ?>

                <div class="order-total">
                    Total: Tsh <?php echo number_format($cart_total, 2); ?>
                </div>

                <button type="submit" name="place_order" class="place-order-button" <?php echo empty($cart_items_display) ? 'disabled' : ''; ?>>
                    <i class="fa-solid fa-check-circle"></i> Place Order
                </button>
            </div>
        </div>
    </form>

</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
