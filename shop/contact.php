<?php
// public_html/contact.php
// Contact Us Page

// Include common header
require_once 'includes/header.php';

// No database connection needed for this simple page

?>

    <!-- --- Main Content Specific to Contact Page --- -->
    <!-- The container is opened in header.php -->

    <h1 class="text-center mb-4">Contact Us</h1>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <p>If you have any questions about the music, licensing, or anything else, feel free to reach out!</p>
                    <p>The best way to contact us regarding track purchases is via WhatsApp, using the "Buy Now" button on the <a href="index.php">Home page</a>.</p>
                    <p>For other inquiries, you can reach the producer/seller directly via:</p>
                    <ul>
                        <li>Email: <a href="mailto:producer@example.com" style="color: #00ffcc;">hatheesha6504@gmail.com</a></li> <!-- Replace with actual email -->
                        <li>General Inquiries WhatsApp: <a href="https://wa.me/+94701207991" style="color: #00ffcc;">+94 70 123 4567</a></li> <!-- Replace with actual number -->
                        <!-- Add other contact methods if available (e.g., social media links) -->
                    </ul>
                     <p class="text-muted small">Please note: Direct track purchase inquiries should use the button on the home page for faster service.</p>
                </div>
            </div>
        </div>
    </div>

<?php
// No database connection to close

// Include common footer
require_once 'includes/footer.php';
?>