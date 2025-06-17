<?php
// cart.php
// This page displays the contents of the user's shopping cart.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn) and session_start()

$cart_message = '';

// --- Handle Cart Actions (Update Quantity, Remove Item) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cart_action'])) {
    // Check if the action is for updating quantity or removing an item
    if (strpos($_POST['cart_action'], 'update_quantity_') === 0) {
        $product_id_to_act = (int)str_replace('update_quantity_', '', $_POST['cart_action']);
        $new_quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT); // Get the quantity from the general 'quantity' field

        if ($product_id_to_act && isset($_SESSION['cart'][$product_id_to_act]) && $new_quantity !== false) {
            if ($new_quantity > 0) {
                // Check stock before updating
                $sql_stock = "SELECT `stock`, `name` FROM `products` WHERE `id` = " . $product_id_to_act . " LIMIT 1";
                $result_stock = $conn->query($sql_stock);
                if ($result_stock->num_rows > 0) {
                    $product_data = $result_stock->fetch_assoc();
                    $product_stock = $product_data['stock'];
                    $product_name = $product_data['name'];

                    if ($new_quantity <= $product_stock) {
                        $_SESSION['cart'][$product_id_to_act]['quantity'] = $new_quantity;
                        $cart_message = "<div class='message success'>Quantity in cart updated for " . htmlspecialchars($product_name) . ".</div>";
                    } else {
                        $cart_message = "<div class='message error'>Cannot update quantity. Product " . htmlspecialchars($product_name) . " has only " . $product_stock . " in stock.</div>";
                        $_SESSION['cart'][$product_id_to_act]['quantity'] = $product_stock; // Set to max available stock
                    }
                } else {
                    // Product not found in DB, remove from cart
                    unset($_SESSION['cart'][$product_id_to_act]);
                    $cart_message = "<div class='message error'>Product no longer exists and was removed from your cart.</div>";
                }
            } else {
                // If new quantity is 0 or less, remove the item
                unset($_SESSION['cart'][$product_id_to_act]);
                $cart_message = "<div class='message success'>Product removed from cart.</div>";
            }
        } else {
            $cart_message = "<div class='message error'>Invalid quantity update request or product ID.</div>";
        }
    } elseif (strpos($_POST['cart_action'], 'remove_item_') === 0) {
        $product_id_to_act = (int)str_replace('remove_item_', '', $_POST['cart_action']);
        
        if ($product_id_to_act && isset($_SESSION['cart'][$product_id_to_act])) {
            unset($_SESSION['cart'][$product_id_to_act]);
            $cart_message = "<div class='message success'>Product removed from cart.</div>";
        } else {
            $cart_message = "<div class='message error'>Invalid remove item request or product ID.</div>";
        }
    }
}


// Recalculate cart total (important to do this after any updates)
$cart_total = 0;
// Re-validate cart items against current database information
$updated_cart = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item_id => $item_details) {
        // Re-fetch product data from DB to ensure latest price and stock
        $sql_product_check = "SELECT `name`, `price`, `image_url`, `stock` FROM `products` WHERE `id` = " . (int)$item_id;
        $result_product_check = $conn->query($sql_product_check);

        if ($result_product_check->num_rows > 0) {
            $db_product = $result_product_check->fetch_assoc();
            $current_price = $db_product['price'];
            $current_stock = $db_product['stock'];

            // Ensure quantity doesn't exceed stock, and use current price
            $item_details['quantity'] = min($item_details['quantity'], $current_stock);
            $item_details['price'] = $current_price; // Update price in session if it changed

            // If product is out of stock after adjustment, remove it
            if ($item_details['quantity'] <= 0) {
                unset($_SESSION['cart'][$item_id]); // Remove directly from session cart
                $cart_message .= "<div class='message error'>Product " . htmlspecialchars($db_product['name']) . " is out of stock and was removed from your cart.</div>";
                continue; // Skip to the next item
            }

            $subtotal = $item_details['price'] * $item_details['quantity'];
            $cart_total += $subtotal;
            $updated_cart[$item_id] = $item_details; // Add to a new, validated cart
        } else {
            // Product not found in DB, remove from cart
            unset($_SESSION['cart'][$item_id]); // Remove directly from session cart
            $cart_message .= "<div class='message error'>A product in your cart was not found and has been removed.</div>";
        }
    }
    $_SESSION['cart'] = $updated_cart; // Overwrite session cart with validated items
}

?>
<style>
    /* Specific styles for the Shopping Cart page */
    .cart-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .cart-header h1 {
        font-size: 3em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .cart-header p {
        font-size: 1.2em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .cart-content-section {
        background-color: var(--white);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .cart-table th, .cart-table td {
        padding: 12px 15px;
        border: 1px solid var(--light-grey-border);
        text-align: left;
    }
    .cart-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .cart-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .cart-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .cart-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }
    .cart-item-quantity input[type="number"] {
        width: 60px;
        padding: 8px;
        border: 1px solid var(--light-grey-border);
        border-radius: 6px;
        text-align: center;
        -moz-appearance: textfield; /* Remove Firefox number input arrows */
    }
    /* Remove Chrome/Safari number input arrows */
    .cart-item-quantity input[type="number"]::-webkit-outer-spin-button,
    .cart-item-quantity input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .cart-item-actions button {
        background-color: var(--danger-red);
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        transition: background-color 0.3s ease;
    }
    .cart-item-actions button:hover {
        background-color: #c0392b;
    }
    .cart-item-actions button.update-btn {
        background-color: var(--accent-blue);
        margin-left: 5px;
    }
    .cart-item-actions button.update-btn:hover {
        background-color: var(--accent-hover);
    }

    .cart-summary {
        margin-top: 30px;
        border-top: 2px solid var(--light-grey-border);
        padding-top: 20px;
        text-align: right;
    }
    .cart-summary h3 {
        font-size: 1.8em;
        color: var(--primary-dark);
        margin-bottom: 15px;
    }
    .cart-summary .total-amount {
        font-size: 2.2em;
        font-weight: 700;
        color: var(--accent-blue);
        margin-bottom: 25px;
    }
    .cart-actions {
        display: flex;
        justify-content: flex-end; /* Align buttons to the right */
        gap: 15px;
        margin-top: 20px;
    }
    .cart-actions a, .cart-actions button {
        background-color: var(--primary-dark);
        color: white;
        padding: 15px 30px;
        text-decoration: none;
        border-radius: 10px;
        font-size: 1.1em;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
        border: none; /* For button */
        cursor: pointer; /* For button */
    }
    .cart-actions a:hover, .cart-actions button:hover {
        background-color: var(--accent-hover);
        transform: translateY(-3px);
    }
    .cart-actions a.checkout-button {
        background-color: var(--cta-green);
    }
    .cart-actions a.checkout-button:hover {
        background-color: var(--cta-green-hover);
    }

    /* Message styles (reused from admin pages) */
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
        .cart-table, .cart-table tbody, .cart-table tr, .cart-table td, .cart-table th {
            display: block;
            width: 100%;
        }
        .cart-table thead {
            display: none;
        }
        .cart-table tr {
            margin-bottom: 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .cart-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
        }
        .cart-table td::before {
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
        .cart-actions {
            flex-direction: column;
            align-items: center; /* Align buttons to center on mobile */
        }
        .cart-actions a, .cart-actions button {
            width: 100%; /* Full width buttons */
            text-align: center;
        }
    }
</style>

<div class="container">
    <section class="cart-header">
        <h1>Your Shopping Cart</h1>
        <p>Review your selected mobile accessories before proceeding to checkout.</p>
    </section>

    <?php echo $cart_message; // Display cart messages ?>

    <section class="cart-content-section">
        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <form method="POST" action="cart.php">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($_SESSION['cart'] as $item_id => $item_details) {
                            $subtotal = $item_details['price'] * $item_details['quantity'];
                        ?>
                            <tr>
                                <td data-label="Product">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <img src="<?php echo htmlspecialchars($item_details['image_url'] ?: 'https://placehold.co/80x80/f0f0f0/AAAAAA?text=Product'); ?>" alt="<?php echo htmlspecialchars($item_details['name']); ?>" class="cart-item-image">
                                        <span><?php echo htmlspecialchars($item_details['name']); ?></span>
                                    </div>
                                </td>
                                <td data-label="Price">Tsh <?php echo number_format($item_details['price'], 2); ?></td>
                                <td data-label="Quantity" class="cart-item-quantity">
                                    <input type="number" name="quantity_<?php echo $item_id; ?>" value="<?php echo $item_details['quantity']; ?>" min="1" data-product-id="<?php echo $item_id; ?>">
                                    <!-- Name changed to be unique per product -->
                                    <button type="submit" name="cart_action" value="update_quantity_<?php echo $item_id; ?>" class="update-btn" style="display:none;">Update</button>
                                </td>
                                <td data-label="Subtotal">Tsh <?php echo number_format($subtotal, 2); ?></td>
                                <td data-label="Actions" class="cart-item-actions">
                                    <button type="submit" name="cart_action" value="remove_item_<?php echo $item_id; ?>" formmethod="POST">
                                        <i class="fa-solid fa-trash-can"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <!-- Hidden fields for form submission to know which product and action was triggered -->
                <input type="hidden" name="product_id" id="hiddenProductIdTriggered">
                <input type="hidden" name="cart_action" id="hiddenCartActionTypeTriggered">
            </form>

            <div class="cart-summary">
                <h3>Cart Total:</h3>
                <p class="total-amount">Tsh <?php echo number_format($cart_total, 2); ?></p>
                <div class="cart-actions">
                    <a href="shop.php"><i class="fa-solid fa-arrow-left"></i> Continue Shopping</a>
                    <a href="checkout.php" class="checkout-button"><i class="fa-solid fa-credit-card"></i> Proceed to Checkout</a>
                </div>
            </div>
        <?php else: ?>
            <p style="text-align: center; padding: 50px 0;">Your cart is empty. <a href="shop.php" style="color: var(--accent-blue); text-decoration: underline;">Start shopping now!</a></p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInputs = document.querySelectorAll('.cart-item-quantity input[type="number"]');
        const cartForm = document.querySelector('.cart-content-section form');

        // Function to submit the form
        function submitCartAction(productId, actionType, quantity = null) {
            const hiddenProductId = document.getElementById('hiddenProductIdTriggered');
            const hiddenCartActionType = document.getElementById('hiddenCartActionTypeTriggered');
            
            hiddenProductId.value = productId;
            hiddenCartActionType.value = actionType;

            // If updating quantity, ensure the quantity is also sent correctly
            if (actionType === 'update_quantity' && quantity !== null) {
                // Find the specific quantity input for this product and set its value
                const specificQuantityInput = document.querySelector(`input[name="quantity_${productId}"]`);
                if (specificQuantityInput) {
                    specificQuantityInput.value = quantity;
                }
                // Also, create a temporary hidden input if needed for the 'quantity' POST variable
                const tempQuantityInput = document.createElement('input');
                tempQuantityInput.type = 'hidden';
                tempQuantityInput.name = 'quantity'; // The name PHP expects
                tempQuantityInput.value = quantity;
                cartForm.appendChild(tempQuantityInput);
            }
            
            cartForm.submit(); // Submit the form
        }

        quantityInputs.forEach(input => {
            let initialQuantity = parseInt(input.value); // Convert to integer
            const productId = input.dataset.productId;

            // Get product stock from backend to set max attribute
            // This would ideally be fetched from the server when the page loads
            // For now, let's assume `input.max` attribute is correctly set
            // If not, you might need an AJAX call here to fetch it or pass it from PHP.
            // For this example, PHP will handle stock validation on form submission.
            
            // Listen for 'change' event to detect when the user finishes typing/changing quantity
            input.addEventListener('change', function() {
                const newQuantity = parseInt(this.value); // Convert to integer
                
                if (isNaN(newQuantity) || newQuantity < 0) { // Handle invalid or negative input
                    this.value = initialQuantity; // Revert to initial
                    alert('Please enter a valid quantity.'); // Using alert for simplicity, replace with custom modal
                    return;
                }

                if (newQuantity === 0) {
                    // Custom confirmation dialog instead of alert
                    const confirmRemove = confirm('Are you sure you want to remove this product from your cart?');
                    if (confirmRemove) {
                        submitCartAction(productId, 'remove_item');
                    } else {
                        this.value = initialQuantity; // Revert to original quantity if cancelled
                    }
                } else if (newQuantity !== initialQuantity) {
                    // Submit only if quantity actually changed
                    submitCartAction(productId, 'update_quantity', newQuantity);
                }
            });

            // Also handle blur event to catch changes without pressing Enter/Tab
            input.addEventListener('blur', function() {
                const newQuantity = parseInt(this.value);
                if (isNaN(newQuantity) || newQuantity < 0) {
                    this.value = initialQuantity;
                    // Optional: show a message if needed
                    return;
                }
                if (newQuantity !== initialQuantity) {
                    submitCartAction(productId, 'update_quantity', newQuantity);
                }
            });
        });

        // Add event listeners for direct remove buttons
        document.querySelectorAll('.cart-item-actions button[name^="cart_action"]').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default form submission by button
                
                const actionValue = this.value; // e.g., "remove_item_123"
                const parts = actionValue.split('_');
                const productId = parts[parts.length - 1]; // Get ID
                const actionType = parts.slice(0, -1).join('_'); // e.g., "remove_item"

                if (actionType === 'remove_item') {
                    // Custom confirmation dialog instead of alert
                    const confirmRemove = confirm('Are you sure you want to remove this product from your cart?');
                    if (confirmRemove) {
                        submitCartAction(productId, 'remove_item');
                    }
                }
                // No need for update_quantity button logic here as it's handled by input change/blur
            });
        });
    });
</script>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
