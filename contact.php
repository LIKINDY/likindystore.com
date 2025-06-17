<?php
// contact.php
// This page provides a contact form and contact information for Likindy Digital Solution.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn)

$message_status = ''; // To store success/error messages for form submission
$prefill_subject = ''; // For pre-filling subject from services page link

// Check if a service name was passed from the services page
if (isset($_GET['service']) && !empty($_GET['service'])) {
    $prefill_subject = 'Inquiry about ' . htmlspecialchars($_GET['service']);
}


// --- Handle Contact Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $sender_name = $conn->real_escape_string($_POST['sender_name']);
    $sender_email = $conn->real_escape_string($_POST['sender_email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    // Basic validation
    if (empty($sender_name) || empty(trim($sender_email)) || empty(trim($message))) { // Trim whitespace
        $message_status = "<div class='message error'>Please fill in all required fields (Name, Email, Message).</div>";
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $message_status = "<div class='message error'>Please enter a valid email address.</div>";
    } else {
        // Save message to database
        $sql = "INSERT INTO `contact_messages` (`sender_name`, `sender_email`, `subject`, `message`, `service_inquiry`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $sender_name, $sender_email, $subject, $message, $prefill_subject); // $prefill_subject might be empty if not from services page

        if ($stmt->execute()) {
            $message_status = "<div class='message success'>Your message has been sent successfully! We will get back to you soon.</div>";

            // --- Email Sending Logic (Will only work on a properly configured live server) ---
            $to = "likindyismail@gmail.com"; // Your business email address
            $email_subject = "New Contact Message from Likindy Digital Website: " . $subject;
            $email_body = "You have received a new message from your website contact form.\n\n" .
                          "Name: " . $sender_name . "\n" .
                          "Email: " . $sender_email . "\n" .
                          "Subject: " . $subject . "\n" .
                          "Inquiry about Service: " . ($prefill_subject ? $prefill_subject : 'N/A') . "\n\n" .
                          "Message:\n" . $message;
            $headers = "From: webmaster@likindydigitalsolution.com\r\n" . // Replace with your actual domain email
                       "Reply-To: " . $sender_email . "\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            // Attempt to send email (This will NOT work reliably on XAMPP)
            // Uncomment this line when you deploy to a live server with mail() configured
            // mail($to, $email_subject, $email_body, $headers);

        } else {
            $message_status = "<div class='message error'>There was an error sending your message. Please try again later.</div>";
            error_log("Error saving contact message to DB: " . $stmt->error); // Log error for debugging
        }
        $stmt->close();
    }
}
?>
<style>
    /* Specific styles for the Contact Us page */
    .contact-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .contact-header h1 {
        font-size: 3em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .contact-header p {
        font-size: 1.2em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .contact-content-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .contact-info-section, .contact-form-section {
        flex: 1 1 45%; /* Distribute space */
        min-width: 300px; /* Minimum width before stacking */
        background-color: var(--white);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    }
    .contact-info-section h2, .contact-form-section h2 {
        font-size: 2.2em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
    }
    .contact-info-section p {
        font-size: 1.1em;
        line-height: 1.8;
        margin-bottom: 15px;
        color: var(--text-dark);
    }
    .contact-info-section p strong {
        color: var(--primary-dark);
    }
    .contact-info-section a {
        color: var(--accent-blue);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .contact-info-section a:hover {
        color: var(--accent-hover);
        text-decoration: underline;
    }

    /* Form Styles (reused from admin pages for consistency) */
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
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--light-grey-border);
        border-radius: 8px;
        font-size: 1em;
        box-sizing: border-box;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        outline: none;
    }
    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }
    .form-actions {
        text-align: right;
    }
    .form-actions button {
        background-color: var(--cta-green);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.1em;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .form-actions button:hover {
        background-color: var(--cta-green-hover);
        transform: translateY(-2px);
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

    /* Google Map Placeholder (Replace with actual embed later) */
    .google-map-placeholder {
        width: 100%;
        height: 300px;
        background-color: #e0e0e0;
        border-radius: 15px;
        margin-top: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--secondary-grey);
        font-size: 1.2em;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .google-map-placeholder iframe {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 15px;
    }


    /* Responsive adjustments */
    @media (max-width: 768px) {
        .contact-header h1 {
            font-size: 2.2em;
        }
        .contact-header p {
            font-size: 1em;
        }
        .contact-content-grid {
            flex-direction: column;
        }
        .contact-info-section, .contact-form-section {
            flex: 1 1 100%;
            min-width: unset;
            padding: 30px;
        }
    }
</style>

<div class="container">
    <section class="contact-header">
        <h1>Contact Likindy Digital Solution</h1>
        <p>We're here to help! Reach out to us for any inquiries about our products or mobile repair services.</p>
    </section>

    <?php echo $message_status; // Display form submission messages ?>

    <section class="contact-content-grid">
        <div class="contact-info-section">
            <h2>Our Details</h2>
            <p><strong><i class="fa-solid fa-location-dot"></i> Location:</strong> MTONI KWA KISASI, Dar es Salaam, Tanzania</p>
            <p><strong><i class="fa-solid fa-envelope"></i> Email:</strong> <a href="mailto:likindyismail@gmail.com">likindyismail@gmail.com</a></p>
            <p><strong><i class="fa-solid fa-phone"></i> Phone 1:</strong> <a href="tel:+255658415488">+255 658 415488</a></p>
            <p><strong><i class="fa-solid fa-mobile-alt"></i> Phone 2:</strong> <a href="tel:+255625415484">+255 625 415484</a></p>
            <p style="margin-top: 25px;"><strong><i class="fa-solid fa-clock"></i> Business Hours:</strong><br>
            Monday - Friday: 9:00 AM - 5:00 PM<br>
            Saturday: 9:00 AM - 1:00 PM<br>
            Sunday: Closed</p>

            <div class="google-map-placeholder">
                <!-- Replace this with an actual Google Map embed code later -->
                <p>Google Map Coming Soon</p>
                <!-- Example embed code (get yours from Google Maps)
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.54734898124!2d39.23192271477028!3d-6.81702419507963!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwNDknMDEuMyJTIDM5wrAxMyU2OS43IkU!5e0!3m2!1sen!2stz!4v1678901234567!5m2!1sen!2stz" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                -->
            </div>
        </div>

        <div class="contact-form-section">
            <h2>Send Us a Message</h2>
            <form method="POST" action="contact.php">
                <div class="form-group">
                    <label for="sender_name">Your Name:</label>
                    <input type="text" id="sender_name" name="sender_name" required>
                </div>
                <div class="form-group">
                    <label for="sender_email">Your Email:</label>
                    <input type="email" id="sender_email" name="sender_email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($prefill_subject); ?>">
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <div class="form-actions" style="text-align: left;">
                    <button type="submit"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
                </div>
            </form>
        </div>
    </section>

</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
