<?php
// auth_footer.php
// This file contains a minimal HTML footer for authentication pages.
?>
    </main>

    <footer class="site-footer-auth">
        <p>&copy; Likindy Digital All Rights Reserved 2025</p>
    </footer>

    <?php
    // Close database connection
    $conn->close();

    // End output buffering and send content to browser
    ob_end_flush();
    ?>
</body>
</html>
