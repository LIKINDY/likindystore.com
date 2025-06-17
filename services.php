<?php
// services.php
// This page displays all mobile phone repair services offered by Likindy Digital Solution.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn)

// --- Fetch All Services ---
$sql_all_services = "SELECT * FROM `services` ORDER BY `name_en` ASC"; // Order by English name alphabetically
$result_all_services = $conn->query($sql_all_services);

?>
<style>
    /* Specific styles for the services page */
    .services-page-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .services-page-header h1 {
        font-size: 3em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .services-page-header p {
        font-size: 1.2em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    /* Reusing .service-grid and .service-card styles from header.php */
    .services-list-container {
        margin-top: 40px;
        margin-bottom: 60px;
    }

    /* Individual Service Detail Section (if linking to specific services on this page) */
    .service-detail-anchor {
        scroll-margin-top: 100px; /* Offset for sticky header */
    }


    /* Responsive adjustments */
    @media (max-width: 768px) {
        .services-page-header h1 {
            font-size: 2.2em;
        }
        .services-page-header p {
            font-size: 1em;
        }
    }
</style>

<div class="container">
    <section class="services-page-header">
        <h1>Our Expert Repair Services</h1>
        <p>At Likindy Digital Solution, we specialize in comprehensive mobile phone repair services. From broken screens to complex system issues, our skilled technicians ensure your device is restored to optimal condition.</p>
    </section>

    <section class="services-list-container">
        <h2 class="section-title">What We Offer</h2>
        <div class="service-grid">
            <?php if ($result_all_services->num_rows > 0): ?>
                <?php while($service = $result_all_services->fetch_assoc()): ?>
                    <div class="service-card service-detail-anchor" id="service-<?php echo $service['id']; ?>">
                        <img src="<?php echo htmlspecialchars($service['image_url'] ?: 'assets/images/service_placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($service['name_en']); ?>">
                        <div class="service-card-content">
                            <h3><?php echo htmlspecialchars($service['name_en']); ?></h3>
                            <?php if (!empty($service['name_sw'])): ?>
                                <p style="font-style: italic; color: var(--secondary-grey); font-size: 0.9em; margin-top: -8px; margin-bottom: 10px;"><?php echo htmlspecialchars($service['name_sw']); ?></p>
                            <?php endif; ?>

                            <p class="description">
                                <?php
                                // Display English description if available, otherwise Swahili
                                echo nl2br(htmlspecialchars($service['description_en'] ?: $service['description_sw']));
                                ?>
                            </p>
                            <?php if ($service['base_price'] !== NULL): ?>
                                <p class="price" style="font-size: 1.4em; font-weight: 600; color: var(--accent-blue);">
                                    Base Price: Tsh <?php echo number_format($service['base_price'], 2); ?>
                                </p>
                            <?php else: ?>
                                <p class="price" style="font-size: 1.4em; font-weight: 600; color: var(--primary-dark);">
                                    Price: Custom Quote
                                </p>
                            <?php endif; ?>
                            <a href="contact.php?service=<?php echo urlencode(htmlspecialchars($service['name_en'])); ?>" class="view-details-button">Get a Quote</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1 / -1; padding: 50px 0;">No services found yet. Please add services in the admin panel.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="cta-section container" style="margin-top: 50px;">
        <h2>Ready for a Repair?</h2>
        <p>Don't let a broken phone disrupt your life. Contact us today for a free consultation and personalized quote for your mobile repair needs.</p>
        <a href="contact.php" class="cta-button">Contact Our Experts</a>
    </section>

</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
