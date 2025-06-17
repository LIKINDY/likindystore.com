<?php
// order-confirmation.php
// This page displays order confirmation details after a successful checkout.

include 'header.php'; // Include the main header for consistent styling

$order_id = $_GET['order_id'] ?? 0;
$order_details = null;
$order_items = [];

if ($order_id > 0) {
    // Fetch order details
    $sql_order = "SELECT * FROM `orders` WHERE `id` = ?";
    $stmt_order = $conn->prepare($sql_order);
    if ($stmt_order) {
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();
        if ($result_order->num_rows > 0) {
            $order_details = $result_order->fetch_assoc();
        }
        $stmt_order->close();
    }

    // Fetch order items if order details are found
    if ($order_details) {
        $sql_items = "SELECT * FROM `order_items` WHERE `order_id` = ?";
        $stmt_items = $conn->prepare($sql_items);
        if ($stmt_items) {
            $stmt_items->bind_param("i", $order_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            while ($row = $result_items->fetch_assoc()) {
                $order_items[] = $row;
            }
            $stmt_items->close();
        }
    }
}
?>
<style>
    /* Specific styles for the Order Confirmation page */
    .confirmation-header {
        background-color: var(--cta-green); /* Use success green */
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .confirmation-header h1 {
        font-size: 3.5em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .confirmation-header p {
        font-size: 1.3em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }
    .confirmation-header i {
        font-size: 1.5em;
        margin-right: 10px;
    }

    .order-details-section {
        background-color: var(--white);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        margin-top: 40px;
        margin-bottom: 60px;
    }

    .order-details-section h2 {
        font-size: 2.2em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
        text-align: center;
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    .order-info-box {
        background-color: var(--background-light);
        padding: 20px;
        border-radius: 10px;
        border: 1px solid var(--light-grey-border);
    }
    .order-info-box h3 {
        font-size: 1.4em;
        color: var(--accent-blue);
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 1px dashed var(--light-grey-border);
    }
    .order-info-box p {
        margin-bottom: 8px;
        font-size: 1.05em;
        color: var(--text-dark);
    }
    .order-info-box p strong {
        color: var(--primary-dark);
    }

    .order-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }
    .order-items-table th, .order-items-table td {
        border: 1px solid var(--light-grey-border);
        padding: 12px 15px;
        text-align: left;
    }
    .order-items-table th {
        background-color: var(--primary-dark);
        color: var(--white);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    .order-items-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .order-items-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .order-summary-total {
        font-size: 2.5em;
        font-weight: 700;
        color: var(--accent-blue);
        margin-top: 30px;
        text-align: right;
        padding-top: 15px;
        border-top: 2px solid var(--primary-dark);
    }

    .call-to-action-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 40px;
        flex-wrap: wrap;
    }
    .call-to-action-buttons a {
        background-color: var(--primary-dark);
        color: white;
        padding: 15px 30px;
        text-decoration: none;
        border-radius: 10px;
        font-size: 1.1em;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
        border: none;
        cursor: pointer;
    }
    .call-to-action-buttons a:hover {
        background-color: var(--accent-hover);
        transform: translateY(-3px);
    }
    .call-to-action-buttons a.track-order-btn {
        background-color: var(--cta-green);
    }
    .call-to-action-buttons a.track-order-btn:hover {
        background-color: var(--cta-green-hover);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .confirmation-header h1 {
            font-size: 2.5em;
        }
        .order-info-grid {
            grid-template-columns: 1fr;
        }
        .order-items-table, .order-items-table tbody, .order-items-table tr, .order-items-table td, .order-items-table th {
            display: block;
            width: 100%;
        }
        .order-items-table thead {
            display: none;
        }
        .order-items-table tr {
            margin-bottom: 15px;
            border: 1px solid var(--light-grey-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .order-items-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
        }
        .order-items-table td::before {
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
        .order-summary-total {
            font-size: 1.8em;
        }
        .call-to-action-buttons {
            flex-direction: column;
            align-items: center;
        }
        .call-to-action-buttons a {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="container">
    <?php if ($order_details): ?>
        <section class="confirmation-header">
            <h1><i class="fa-solid fa-check-circle"></i> Order Confirmed!</h1>
            <p>Thank you for your purchase from Likindy Digital Solution. Your order #<?php echo htmlspecialchars($order_details['id']); ?> has been successfully placed.</p>
            <p>We've sent an email confirmation to <strong><?php echo htmlspecialchars($order_details['customer_email']); ?></strong> with your order details.</p>
        </section>

        <section class="order-details-section">
            <h2>Order Details</h2>
            <div class="order-info-grid">
                <div class="order-info-box">
                    <h3>Order Information</h3>
                    <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_details['id']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('F j, Y, H:i', strtotime($order_details['order_date'])); ?></p>
                    <p><strong>Total Amount:</strong> Tsh <?php echo number_format($order_details['total_amount'], 2); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_details['payment_method']); ?></p>
                    <p><strong>Order Status:</strong> <span style="text-transform: capitalize; color: <?php echo ($order_details['status'] == 'pending' ? '#f39c12' : ($order_details['status'] == 'completed' ? '#27ae60' : '#e74c3c')); ?>; font-weight: 700;"><?php echo htmlspecialchars($order_details['status']); ?></span></p>
                </div>
                <div class="order-info-box">
                    <h3>Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['customer_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['customer_phone']); ?></p>
                </div>
                <div class="order-info-box">
                    <h3>Shipping Information</h3>
                    <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order_details['shipping_address'])); ?></p>
                    <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($order_details['notes'] ?: 'N/A')); ?></p>
                </div>
            </div>

            <?php if (!empty($order_items)): ?>
                <h3>Items in Your Order</h3>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td data-label="Item Name"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td data-label="Quantity"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td data-label="Unit Price">Tsh <?php echo number_format($item['price_at_time_of_purchase'], 2); ?></td>
                                <td data-label="Total">Tsh <?php echo number_format($item['quantity'] * $item['price_at_time_of_purchase'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="order-summary-total">Grand Total: Tsh <?php echo number_format($order_details['total_amount'], 2); ?></p>
            <?php else: ?>
                <p style="text-align: center; margin-top: 30px;">No items found for this order.</p>
            <?php endif; ?>

            <div class="call-to-action-buttons">
                <a href="shop.php"><i class="fa-solid fa-bag-shopping"></i> Continue Shopping</a>
                <a href="my-account-orders.php" class="track-order-btn"><i class="fa-solid fa-truck-fast"></i> Track Your Order</a>
            </div>
        </section>
    <?php else: ?>
        <section class="confirmation-header" style="background-color: var(--danger-red);">
            <h1><i class="fa-solid fa-triangle-exclamation"></i> Order Not Found</h1>
            <p>We could not find details for the requested order. Please ensure the link is correct or check your <a href="my-account-orders.php" style="color: white; text-decoration: underline;">order history</a>.</p>
        </section>
    <?php endif; ?>
</div>

<?php
include 'footer.php'; // Include the main footer
?>
