<?php
// index.php

// Include the header which contains database connection and starts HTML
include 'header.php';

// --- Fetch Featured Products ---
$sql_featured_products = "SELECT * FROM `products` WHERE `is_featured` = TRUE LIMIT 4"; // Get up to 4 featured products
$result_featured_products = $conn->query($sql_featured_products);

// --- Fetch Recent Services (or featured services if you add a flag) ---
$sql_recent_services = "SELECT * FROM `services` ORDER BY `created_at` DESC LIMIT 3"; // Get up to 3 recent services
$result_recent_services = $conn->query($sql_recent_services);

?>
        <section class="hero-section">
            <div class="container">
                <h1>Likindy Digital Solution</h1>
                <p>Your Trusted Partner for Quality Mobile Accessories & Expert Phone Repair Services in Tanzania.</p>
                <a href="shop.php" class="hero-cta-button">Shop Now</a>
            </div>
        </section>

        <section class="products-section container">
            <h2 class="section-title">Featured Mobile Accessories</h2>
            <div class="product-grid">
                <?php if ($result_featured_products->num_rows > 0): ?>
                    <?php while($product = $result_featured_products->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-card-content">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="description">
                                    <?php
                                    // Display English description if available, otherwise Swahili
                                    echo htmlspecialchars($product['description_en'] ?: $product['description_sw']);
                                    ?>
                                </p>
                                <p class="price">Tsh <?php echo number_format($product['price'], 2); ?></p>
                                <!-- UPDATED LINK HERE: product_single.php (with underscore) -->
                                <a href="product_single.php?id=<?php echo $product['id']; ?>" class="view-details-button">View Details</a>
                                <!-- Add to Cart functionality will be added later -->
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; grid-column: 1 / -1;">No featured products found. Please add some in the admin panel.</p>
                <?php endif; ?>
            </div>
            <p style="text-align: center; margin-top: 30px;">
                <a href="shop.php" class="view-details-button" style="background-color: var(--accent-blue);">View All Products</a>
            </p>
        </section>

        <section class="services-section container">
            <h2 class="section-title">Our Expert Repair Services</h2>
            <div class="service-grid">
                <?php if ($result_recent_services->num_rows > 0): ?>
                    <?php while($service = $result_recent_services->fetch_assoc()): ?>
                        <div class="service-card">
                            <img src="<?php echo htmlspecialchars($service['image_url'] ?: 'assets/images/service_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($service['name_en']); ?>">
                            <div class="service-card-content">
                                <h3><?php echo htmlspecialchars($service['name_en']); ?></h3>
                                <p class="description">
                                    <?php
                                    echo htmlspecialchars($service['description_en'] ?: $service['description_sw']);
                                    ?>
                                </p>
                                <a href="services.php#service-<?php echo $service['id']; ?>" class="view-details-button">Learn More</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; grid-column: 1 / -1;">No services found yet. Please add some in the admin panel.</p>
                <?php endif; ?>
            </div>
            <p style="text-align: center; margin-top: 30px;">
                <a href="services.php" class="view-details-button" style="background-color: var(--primary-dark);">View All Services</a>
            </p>
        </section>


        <section class="cta-section container">
            <h2>Need a Phone Repair?</h2>
            <p>Our expert technicians are here to fix your mobile device with precision and speed. From screen replacements to battery issues, we've got you covered.</p>
            <a href="contact.php" class="cta-button">Get a Free Quote</a>
        </section>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
