<?php
// public_html/about.php
// About Us Page

// Include common header
require_once 'includes/header.php';

// No database connection needed for this simple page

?>

    <!-- --- Main Content Specific to About Page --- -->
    <!-- The container is opened in header.php -->

    <h1 class="text-center mb-4">About Sci-Fi Music Tracks</h1>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <p>Welcome to Sci-Fi Music Tracks! We are dedicated to providing unique and captivating music for creators looking for futuristic soundscapes for their projects, including films, games, videos, and more.</p>
                    <p>Our collection features a variety of sub-genres within the sci-fi realm, from atmospheric ambient sounds to driving electronic beats.</p>
                    <p>Our simple purchase process allows you to connect directly with the music producer via WhatsApp to discuss licensing and acquire the tracks you need quickly and easily.</p>
                    <!-- Add more about your project, mission, etc. -->
                </div>
            </div>
        </div>
    </div>

<?php
// No database connection to close

// Include common footer
require_once 'includes/footer.php';
?>