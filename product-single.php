<?php
// product_single.php
// Displays detailed information for a single product and handles direct checkout/order placement.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn) and session_start()

// --- IMPORTANT: FORCE LOGIN/REGISTRATION BEFORE CHECKOUT ---
// If user is NOT logged in, redirect to login page with redirect URL
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'product_single.php?id=' . ($_GET['id'] ?? ''); // Store current product page
    header("Location: login.php");
    exit();
}
// --- END FORCE LOGIN/REGISTRATION ---

$product_id = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $product_id = $_GET['id'];
}

$product = null;
if ($product_id) {
    $sql = "SELECT * FROM `products` WHERE `id` = ? LIMIT 1";
    $stmt_product = $conn->prepare($sql);
    if ($stmt_product) {
        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();
        if ($result_product->num_rows > 0) {
            $product = $result_product->fetch_assoc();
        }
        $stmt_product->close();
    }
}

// Redirect if product not found or no ID provided
if (!$product) {
    header("Location: shop.php");
    exit();
}

$checkout_message = '';
$customer_name = '';
$customer_email = '';
$customer_phone = '';
$shipping_address = '';
$notes = '';
$quantity_to_purchase = 1; // Default quantity for direct purchase

// Pre-fill user details if logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql_user_details = "SELECT full_name, email, phone_number, address FROM `users` WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user_details);
    if ($stmt_user === FALSE) {
        error_log("SQL Error in product_single.php user details prepare: " . $conn->error);
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
            $shipping_address = htmlspecialchars($user_data['address']);
        } else {
            error_log("User ID " . $user_id . " in session but not found in database.");
            $checkout_message .= "<div class='message error'>Your user session is invalid. Please log out and log in again.</div>";
            session_destroy();
            header("Location: login.php");
            exit();
        }
        $stmt_user->close();
    }
}

// --- Handle Direct Purchase Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $quantity_to_purchase = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
    if ($quantity_to_purchase <= 0 || $quantity_to_purchase > $product['stock']) {
        $checkout_message = "<div class='message error'>Invalid quantity or insufficient stock. Available: " . $product['stock'] . "</div>";
    } else {
        // Sanitize and validate customer details from POST
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
            $total_amount_for_product = $product['price'] * $quantity_to_purchase;

            // Start a transaction
            $conn->begin_transaction();

            try {
                // 1. Insert into `orders` table
                $sql_insert_order = "INSERT INTO `orders` (`user_id`, `total_amount`, `status`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `payment_method`, `notes`) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)";
                $stmt_order = $conn->prepare($sql_insert_order);
                $current_user_id = $_SESSION['user_id'];

                if ($stmt_order === FALSE) {
                    throw new Exception("SQL PREPARE Error for order insertion: " . $conn->error);
                }
                $stmt_order->bind_param("idsssssss", $current_user_id, $total_amount_for_product, $customer_name, $customer_email, $customer_phone, $shipping_address, $payment_method, $notes);

                if (!$stmt_order->execute()) {
                    throw new Exception("SQL EXECUTE Error inserting order: " . $stmt_order->error);
                }
                $order_id = $conn->insert_id;

                // 2. Insert into `order_items` table for this single product
                $sql_insert_item = "INSERT INTO `order_items` (`order_id`, `product_id`, `item_name`, `quantity`, `price_at_time_of_purchase`) VALUES (?, ?, ?, ?, ?)";
                $stmt_item = $conn->prepare($sql_insert_item);
                if ($stmt_item === FALSE) {
                    throw new Exception("SQL PREPARE Error for order item insertion: " . $conn->error);
                }
                $stmt_item->bind_param("iisid", $order_id, $product['id'], $product['name'], $quantity_to_purchase, $product['price']);
                if (!$stmt_item->execute()) {
                    throw new Exception("SQL EXECUTE Error inserting order item for product ID " . $product['id'] . ": " . $stmt_item->error);
                }
                $stmt_item->close();

                // 3. Update stock in `products` table
                $sql_update_stock = "UPDATE `products` SET `stock` = `stock` - ? WHERE `id` = ?";
                $stmt_stock = $conn->prepare($sql_update_stock);
                if ($stmt_stock === FALSE) {
                    throw new Exception("SQL PREPARE Error for stock update: " . $conn->error);
                }
                $stmt_stock->bind_param("ii", $quantity_to_purchase, $product['id']);
                if (!$stmt_stock->execute()) {
                    throw new Exception("SQL EXECUTE Error updating stock for product ID " . $product['id'] . ": " . $stmt_stock->error);
                }
                $stmt_stock->close();

                // If all successful, commit the transaction
                $conn->commit();

                // Clear the cart if anything was in it (though this flow bypasses cart)
                unset($_SESSION['cart']);

                // Redirect to a confirmation page
                header("Location: order-confirmation.php?order_id=" . $order_id);
                exit();

            } catch (Exception $e) {
                // If any error, rollback the transaction
                $conn->rollback();
                $checkout_message = "<div class='message error'>Order failed: " . htmlspecialchars($e->getMessage()) . "</div>";
                error_log("Direct checkout transaction error: " . $e->getMessage()); // Log detailed error for debugging
            } finally {
                if (isset($stmt_order) && $stmt_order !== FALSE) $stmt_order->close();
            }
        }
    }
}

?>
<style>
    /* Global styles and variables are in header.php */

    /* Specific styles for single product page (reusing checkout styles) */
    .product-detail-container {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        background-color: var(--white);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .product-image-section {
        flex: 1 1 40%;
        min-width: 280px;
        text-align: center;
    }
    .product-image-section img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .product-info-and-checkout-section {
        flex: 2 1 55%; /* Takes more space for info and form */
        min-width: 350px;
    }
    .product-info-and-checkout-section h1 {
        font-size: 2.8em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 10px;
    }
    .product-info-and-checkout-section .price {
        font-size: 2.5em;
        font-weight: 700;
        color: var(--accent-blue);
        margin-bottom: 25px;
    }
    .product-info-and-checkout-section .availability {
        font-size: 1.1em;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .product-info-and-checkout-section .availability.in-stock {
        color: var(--cta-green);
    }
    .product-info-and-checkout-section .availability.out-of-stock {
        color: var(--danger-red);
    }

    .product-info-and-checkout-section .product-description-section h2 {
        font-size: 1.5em;
        color: var(--primary-dark);
        margin-top: 25px;
        margin-bottom: 10px;
        border-bottom: 1px solid var(--light-grey-border);
        padding-bottom: 5px;
    }
    .product-info-and-checkout-section .product-description-section p {
        margin-bottom: 10px;
        line-height: 1.8;
    }

    /* Quantity input for direct purchase */
    .quantity-input-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .quantity-input-group label {
        font-weight: 600;
        color: var(--primary-dark);
        font-size: 1.05em;
    }
    .quantity-input-group input[type="number"] {
        width: 80px;
        padding: 10px;
        border: 1px solid var(--light-grey-border);
        border-radius: 8px;
        font-size: 1.1em;
        text-align: center;
        -moz-appearance: textfield;
    }
    .quantity-input-group input[type="number"]::-webkit-outer-spin-button,
    .quantity-input-group input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Form Styles (reusing from checkout.php) */
    .checkout-form-section h2 {
        font-size: 2em;
        color: var(--primary-dark);
        margin-top: 30px;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
    }
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
        transform: scale(1.2);
    }
    .payment-methods input[type="radio"]:checked + span {
        color: var(--accent-blue);
        font-weight: 700;
    }
    .payment-methods input[type="radio"]:checked + span::before {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        margin-right: 5px;
        color: var(--cta-green);
    }

    /* Order Summary */
    .order-summary-box {
        background-color: var(--background-light);
        padding: 25px;
        border-radius: 10px;
        border: 1px solid var(--light-grey-border);
        margin-top: 30px;
    }
    .order-summary-box h3 {
        font-size: 1.5em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 20px;
        border-bottom: 1px dashed var(--light-grey-border);
        padding-bottom: 10px;
    }
    .order-summary-item-single {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 15px;
    }
    .order-summary-item-single img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
    }
    .item-details-single {
        flex-grow: 1;
    }
    .item-details-single h4 {
        margin: 0 0 5px 0;
        font-size: 1.1em;
        color: var(--primary-dark);
    }
    .item-details-single p {
        margin: 0;
        font-size: 0.9em;
        color: var(--secondary-grey);
    }
    .item-total-single {
        font-weight: 600;
        color: var(--primary-dark);
    }
    .order-total-single {
        font-size: 2em;
        font-weight: 700;
        color: var(--accent-blue);
        margin-top: 20px;
        text-align: right;
        padding-top: 15px;
        border-top: 2px solid var(--primary-dark);
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


    /* Related Products Section */
    .related-products-section {
        margin-top: 60px;
        background-color: var(--white);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
    }
    .related-products-section h2 {
        font-size: 2em;
        color: var(--primary-dark);
        text-align: center;
        margin-bottom: 30px;
    }
    /* Reusing .product-grid and .product-card from global header styles */


    /* Responsive adjustments */
    @media (max-width: 992px) {
        .product-detail-container {
            flex-direction: column;
            gap: 30px;
        }
        .product-image-section, .product-info-and-checkout-section {
            flex: 1 1 100%; /* Stack vertically */
            min-width: unset;
        }
        .product-image-section img {
            max-width: 80%; /* Smaller on smaller screens */
        }
        .quantity-input-group {
            justify-content: center;
        }
        .place-order-button {
            width: 100%;
        }
    }
    @media (max-width: 576px) {
        .product-info-and-checkout-section h1 {
            font-size: 2em;
        }
        .product-info-and-checkout-section .price {
            font-size: 2em;
        }
        .checkout-form-section h2 {
            font-size: 1.8em;
        }
    }

</style>

<div class="container">
    <?php echo $checkout_message; // Display messages (e.g., stock issues, order success/failure) ?>
    <section class="product-detail-container">
        <div class="product-image-section">
            <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://placehold.co/600x400/f0f0f0/AAAAAA?text=Product+Image'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info-and-checkout-section">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="price">Tsh <?php echo number_format($product['price'], 2); ?></p>
            <p class="availability <?php echo ($product['stock'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                <?php echo ($product['stock'] > 0) ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock'; ?>
            </p>

            <div class="product-description-section">
                <h2>Description (English)</h2>
                <p><?php echo nl2br(htmlspecialchars($product['description_en'] ?: 'No English description available.')); ?></p>

                <?php if (!empty($product['description_sw'])): ?>
                    <h2>Maelezo (Kiswahili)</h2>
                    <p><?php echo nl2br(htmlspecialchars($product['description_sw'])); ?></p>
                <?php endif; ?>
            </div>

            <form method="POST" action="product_single.php?id=<?php echo $product['id']; ?>" class="checkout-form-section">
                <h2>Billing & Shipping Details</h2>
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
                <div class="form-group">
                    <label for="notes">Order Notes (Optional):</label>
                    <textarea id="notes" name="notes" placeholder="e.g., Delivery instructions, preferred time"><?php echo htmlspecialchars($notes); ?></textarea>
                </div>

                <div class="quantity-input-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity_to_purchase); ?>" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                </div>

                <h2>Choose Payment Method</h2>
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
                </div>

                <div class="order-summary-box">
                    <h3>Order Summary for This Product</h3>
                    <div class="order-summary-item-single">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'https://placehold.co/70x70/f0f0f0/AAAAAA?text=Item'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="item-details-single">
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p>Unit Price: Tsh <?php echo number_format($product['price'], 2); ?></p>
                            <p>Quantity: <span id="summary-quantity-display"><?php echo htmlspecialchars($quantity_to_purchase); ?></span></p>
                        </div>
                        <span class="item-total-single">Tsh <span id="summary-total-price"><?php echo number_format($product['price'] * $quantity_to_purchase, 2); ?></span></span>
                    </div>
                    <div class="order-total-single">
                        Total: Tsh <span id="grand-total-display"><?php echo number_format($product['price'] * $quantity_to_purchase, 2); ?></span>
                    </div>
                </div>

                <button type="submit" name="place_order" class="place-order-button" <?php echo ($product['stock'] == 0) ? 'disabled' : ''; ?>>
                    <i class="fa-solid fa-check-circle"></i> Place Order Now
                </button>
            </form>
        </div>
    </section>

    <section class="related-products-section">
        <h2 class="section-title">You Might Also Like</h2>
        <div class="product-grid">
            <?php
            // Fetch some related products (e.g., from the same category or random others)
            // For simplicity, let's fetch products excluding the current one, limiting to 3
            $sql_related_products = "SELECT * FROM `products` WHERE `id` != {$product['id']} ORDER BY RAND() LIMIT 3";
            $result_related_products = $conn->query($sql_related_products);

            if ($result_related_products && $result_related_products->num_rows > 0) {
                while($related_product = $result_related_products->fetch_assoc()) {
            ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($related_product['image_url'] ?: 'https://placehold.co/300x200/f0f0f0/AAAAAA?text=Product+Image'); ?>" alt="<?php echo htmlspecialchars($related_product['name']); ?>">
                        <div class="product-card-content">
                            <h3><?php echo htmlspecialchars($related_product['name']); ?></h3>
                            <p class="description">
                                <?php echo htmlspecialchars($related_product['description_en'] ?: $related_product['description_sw']); ?>
                            </p>
                            <p class="price">Tsh <?php echo number_format($related_product['price'], 2); ?></p>
                            <a href="product_single.php?id=<?php echo $related_product['id']; ?>" class="view-details-button"><i class="fa-solid fa-circle-info"></i> View Details</a>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p style='text-align: center; grid-column: 1 / -1;'>No related products found.</p>";
            }
            ?>
        </div>
    </section>

</div>

<script>
    // JavaScript to dynamically update total based on quantity
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const unitPrice = <?php echo $product['price']; ?>;
        const summaryQuantityDisplay = document.getElementById('summary-quantity-display');
        const summaryTotalPrice = document.getElementById('summary-total-price');
        const grandTotalDisplay = document.getElementById('grand-total-display');

        function updateSummary() {
            const currentQuantity = parseInt(quantityInput.value);
            const newTotal = (currentQuantity * unitPrice).toFixed(2); // Keep 2 decimal places

            summaryQuantityDisplay.textContent = currentQuantity;
            summaryTotalPrice.textContent = newTotal;
            grandTotalDisplay.textContent = newTotal;
        }

        if (quantityInput) {
            quantityInput.addEventListener('input', updateSummary);
            // Also update on initial load in case quantity_to_purchase was changed by validation
            updateSummary();
        }
    });
</script>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
