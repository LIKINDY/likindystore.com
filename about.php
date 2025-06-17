<?php
// about.php
// This page provides information about Likindy Digital Solution.

// Include the header which contains database connection and starts HTML
include 'header.php'; // This also handles the database connection ($conn)

?>
<style>
    /* Specific styles for the About Us page */
    .about-header {
        background-color: var(--primary-dark);
        color: var(--white);
        padding: 50px 20px;
        text-align: center;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }
    .about-header h1 {
        font-size: 3em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    .about-header p {
        font-size: 1.2em;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .about-content {
        background-color: var(--white);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .about-content h2 {
        font-size: 2.2em;
        color: var(--primary-dark);
        margin-top: 0;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--accent-blue);
        padding-bottom: 10px;
        text-align: center;
    }

    .about-content p {
        font-size: 1.1em;
        line-height: 1.8;
        margin-bottom: 20px;
        color: var(--text-dark);
    }

    .about-feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }
    .about-feature-card {
        background-color: var(--background-light);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        text-align: center;
        transition: transform 0.3s ease;
    }
    .about-feature-card:hover {
        transform: translateY(-5px);
    }
    .about-feature-card h3 {
        font-size: 1.5em;
        color: var(--accent-blue);
        margin-top: 0;
        margin-bottom: 15px;
    }
    .about-feature-card p {
        font-size: 0.95em;
        color: var(--secondary-grey);
        line-height: 1.6;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .about-header h1 {
            font-size: 2.2em;
        }
        .about-header p {
            font-size: 1em;
        }
        .about-content h2 {
            font-size: 1.8em;
        }
    }
</style>

<div class="container">
    <section class="about-header">
        <h1>About Likindy Digital Solution</h1>
        <p>Your Reliable Partner for Mobile Accessories & Expert Repairs</p>
    </section>

    <section class="about-content">
        <h2>Our Story</h2>
        <p>
            Likindy Digital Solution was founded in 2023 with a vision to become the leading provider of quality mobile phone accessories and trusted repair services in Tanzania. Located in MTONI KWA KISASI, Dar es Salaam, we started as a small shop driven by a passion for technology and a commitment to serving our community's growing digital needs.
        </p>
        <p>
            We quickly realized the gap in the market for reliable, honest, and professional mobile repair, alongside genuine and durable accessories. Our aim is not just to sell products or fix phones, but to build lasting relationships with our customers by providing exceptional service and value. We believe in empowering our customers to get the most out of their mobile devices, ensuring longevity and optimal performance.
        </p>

        <h2>Our Mission</h2>
        <p>
            Our mission is to provide high-quality, affordable mobile accessories and expert repair services, ensuring customer satisfaction through professionalism, transparency, and technical excellence. We strive to be "more than a professional" by going the extra mile for every client.
        </p>

        <h2>Why Choose Us?</h2>
        <div class="about-feature-grid">
            <div class="about-feature-card">
                <h3>Quality Products</h3>
                <p>We source only the best mobile accessories, from durable phone covers and reliable screen protectors to fast chargers and high-fidelity earphones, ensuring authenticity and performance.</p>
            </div>
            <div class="about-feature-card">
                <h3>Expert Repair Technicians</h3>
                <p>Our skilled technicians are trained to handle a wide range of mobile phone issues, including screen replacements, battery problems, charging system repairs, and complex software solutions.</p>
            </div>
            <div class="about-feature-card">
                <h3>Customer-Centric Service</h3>
                <p>Your satisfaction is our priority. We offer transparent pricing, clear communication, and personalized solutions to meet your specific needs. We're dedicated to quick and efficient service.</p>
            </div>
            <div class="about-feature-card">
                <h3>Local & Accessible</h3>
                <p>Conveniently located at MTONI KWA KISASI, Dar es Salaam, we are easily accessible for all your mobile needs. We are proud to serve our local community and beyond.</p>
            </div>
        </div>

        <h2>Our Vision</h2>
        <p>
            To be the most trusted and sought-after mobile solution provider in Tanzania, continually adapting to technological advancements and exceeding customer expectations. We aim to grow our services while maintaining the personal touch that defines Likindy Digital Solution.
        </p>
    </section>

    <section class="cta-section container" style="margin-top: 50px;">
        <h2>Ready to Experience the Likindy Difference?</h2>
        <p>Explore our products or get a quote for your repair today. We are here to help!</p>
        <a href="shop.php" class="cta-button" style="margin-right: 15px;">Shop Accessories</a>
        <a href="contact.php" class="cta-button" style="background-color: var(--accent-blue);">Request Repair</a>
    </section>

</div>

<?php
// Include the footer which closes HTML tags and database connection
include 'footer.php';
?>
