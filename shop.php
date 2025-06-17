<?php
// shop.php
// This page displays all products.

// Include the header which contains database connection and starts HTML
include 'header.php';

// --- Fetch All Products ---
$sql_all_products = "SELECT * FROM `products` ORDER BY `name` ASC"; // Order by name alphabetically
$result_all_products = $conn->query($sql_all_products);

?>
        <section class="shop-page-header">
            <div class="container">
                <h1 class="section-title" style="margin-bottom: 20px; border-bottom: none;">All Mobile Accessories</h1>
                <p style="text-align: center; font-size: 1.1em; color: var(--secondary-grey);">Browse our wide range of quality mobile phone covers, screen protectors, chargers, earphones, and more!</p>
            </div>
        </section>

        <section class="products-list container">
            <div class="product-grid">
                <?php if ($result_all_products->num_rows > 0): ?>
                    <?php while($product = $result_all_products->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-card-content">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="description">
                                    <?php
                                    echo htmlspecialchars($product['description_en'] ?: $product['description_sw']);
                                    ?>
                                </p>
                                <p class="price">Tsh <?php echo number_format($product['price'], 2); ?></p>
                                <a href="product-single.php?id=<?php echo $product['id']; ?>" class="view-details-button">View Details</a>
                                <!-- Add to Cart functionality will be added here later -->
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; grid-column: 1 / -1; padding: 50px 0;">No products found in the store. Please add products in the admin panel.</p>
                <?php endif; ?>
            </div>
        </section>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
