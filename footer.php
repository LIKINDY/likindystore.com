<?php
// footer.php
// This file contains the standard site footer and closing HTML tags for all pages.
?>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <div class="footer-column">
                <h4>About Likindy Digital</h4>
                <p>Your one-stop solution for quality mobile accessories and professional phone repair services in Mtoni Kwa Kisasi, Dar es Salaam. We ensure your devices are always in top condition.</p>
            </div>
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                    <li><a href="shop.php"><i class="fa-solid fa-bag-shopping"></i> Shop</a></li>
                    <li><a href="services.php"><i class="fa-solid fa-screwdriver-wrench"></i> Services</a></li>
                    <li><a href="about.php"><i class="fa-solid fa-circle-info"></i> About Us</a></li>
                    <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contact Us</h4>
                <p><strong><i class="fa-solid fa-location-dot"></i> Location:</strong> MTONI KWA KISASI, Zanzibar, Tanzania</p>
                <p><strong><i class="fa-solid fa-envelope"></i> Email:</strong> <a href="mailto:likindyismail@gmail.com">likindyismail@gmail.com</a></p>
                <p><strong><i class="fa-solid fa-phone"></i> Phone:</strong> <a href="tel:+255658415488">+255 658 415488</a></p>
                <p><strong><i class="fa-solid fa-mobile-alt"></i> Alt. Phone:</strong> <a href="tel:+255625415484">+255 625 415484</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; Likindy Digital All Rights Reserved 2025</p>
        </div>
    </footer>

    <?php
    // Close database connection
    $conn->close();

    // End output buffering and send content to browser
    ob_end_flush();
    ?>
