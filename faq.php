<?php
// faq.php
// This page provides answers to frequently asked questions about Likindy Digital Solution.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn)

?>
<style>
    /* Specific styles for the FAQ page */
    .faq-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .faq-header h1 {
        font-size: 3em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .faq-header p {
        font-size: 1.2em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .faq-content {
        background-color: var(--white);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .faq-content h2 {
        font-size: 2.2em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
        text-align: center;
    }

    .faq-item {
        margin-bottom: 30px;
        border: 1px solid var(--light-grey-border);
        border-radius: 10px;
        overflow: hidden;
    }

    .faq-question {
        background-color: var(--background-light);
        padding: 20px 25px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.2em;
        font-weight: 600;
        color: var(--primary-dark);
        transition: background-color 0.3s ease;
    }

    .faq-question:hover {
        background-color: #e5e7eb;
    }

    .faq-question i {
        transition: transform 0.3s ease;
    }

    .faq-answer {
        padding: 0 25px;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out, padding 0.4s ease-out;
        color: var(--text-dark);
        line-height: 1.7;
    }

    /* State when active (open) */
    .faq-item.active .faq-answer {
        max-height: 500px; /* Adjust based on expected content length */
        padding: 15px 25px 20px; /* Restore padding when open */
    }

    .faq-item.active .faq-question i {
        transform: rotate(180deg);
    }

    .faq-answer p {
        margin-top: 0;
        margin-bottom: 10px;
    }

    /* Call to action section - reusing existing cta-section styles */
    .cta-section {
        margin-top: 50px;
        background-color: var(--accent-blue);
        color: var(--white);
        padding: 50px 30px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .cta-section h2 {
        font-size: 2.5em;
        margin-bottom: 15px;
        color: var(--white); /* Ensure heading is white */
        border-bottom: none; /* Override default border */
        padding-bottom: 0;
    }

    .cta-section p {
        font-size: 1.1em;
        max-width: 700px;
        margin: 0 auto 30px auto;
        opacity: 0.9;
    }

    .cta-button {
        background-color: var(--cta-green);
        color: white;
        padding: 15px 35px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 1.1em;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
        display: inline-block;
    }

    .cta-button:hover {
        background-color: var(--cta-green-hover);
        transform: translateY(-3px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .faq-header h1 {
            font-size: 2.2em;
        }
        .faq-header p {
            font-size: 1em;
        }
        .faq-content h2 {
            font-size: 1.8em;
        }
        .faq-question {
            font-size: 1.1em;
            padding: 15px 20px;
        }
        .faq-answer {
            padding: 0 20px;
        }
        .faq-item.active .faq-answer {
            padding: 10px 20px 15px;
        }
    }
</style>

<div class="container">
    <section class="faq-header">
        <h1>Frequently Asked Questions</h1>
        <p>Find quick answers to your most common questions about our products, services, ordering, and repairs.</p>
    </section>

    <section class="faq-content">
        <h2>General Questions</h2>

        <div class="faq-item">
            <div class="faq-question">
                <span>What services does Likindy Digital Solution offer?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>We offer a wide range of mobile accessories including phone covers, screen protectors, chargers, earphones, and power banks. Additionally, we specialize in professional mobile phone repair services for various issues like screen damage, battery replacement, charging port repair, and software troubleshooting.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Where is Likindy Digital Solution located?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Our shop is conveniently located at MTONI KWA KISASI, Dar es Salaam, Tanzania. You can find our exact location details and contact information on our <a href="contact.php">Contact Us page</a>.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>What are your business hours?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>We are open Monday to Friday from 9:00 AM to 5:00 PM, and on Saturdays from 9:00 AM to 1:00 PM. We are closed on Sundays.</p>
            </div>
        </div>

        <h2>Product Questions</h2>

        <div class="faq-item">
            <div class="faq-question">
                <span>How can I place an order for mobile accessories?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>You can browse our wide selection of products on our <a href="shop.php">Shop page</a>. Simply add the items you wish to purchase to your cart, and then proceed to checkout to finalize your order by providing your shipping details and choosing a payment method.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>What payment methods do you accept for online orders?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Currently, for local orders within Tanzania, we accept Cash on Delivery (COD), Mobile Money Transfer (M-Pesa, Tigo Pesa, Airtel Money), and Bank Transfer. You can select your preferred option during the checkout process.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Do you offer warranties on your products?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Warranty policies vary depending on the specific product. Please check the product description for warranty information. For any issues, feel free to <a href="contact.php">contact us</a> directly.</p>
            </div>
        </div>

        <h2>Repair Service Questions</h2>

        <div class="faq-item">
            <div class="faq-question">
                <span>How do I get a quote for a phone repair?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>You can view our range of repair services on the <a href="services.php">Services page</a>. For a personalized quote, we recommend contacting us directly via phone, email, or by filling out the form on our <a href="contact.php">Contact Us page</a>. You can even click the "Get a Quote" button on a specific service to pre-fill the inquiry form.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>How long does a typical phone repair take?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Repair times vary depending on the complexity of the issue and the availability of parts. Simple repairs like screen replacements might be completed within a few hours, while more complex issues could take longer. We will provide an estimated time frame when you bring in your device for diagnosis.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>What types of phones do you repair?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                <p>Our technicians are experienced in repairing a wide range of smartphone brands and models, including but not limited to Samsung, iPhone, Huawei, Xiaomi, Infinix, Tecno, and more. Please contact us with your specific phone model and issue for confirmation.</p>
            </div>
        </div>

    </section>

    <section class="cta-section container" style="margin-top: 50px;">
        <h2>Can't Find Your Answer?</h2>
        <p>If you still have questions or need further assistance, please don't hesitate to reach out to our friendly customer support team.</p>
        <a href="contact.php" class="cta-button"><i class="fa-solid fa-headset"></i> Contact Support</a>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            question.addEventListener('click', () => {
                // Close other open FAQ items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });

                // Toggle the current FAQ item
                item.classList.toggle('active');
            });
        });
    });
</script>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
