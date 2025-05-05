<?php
// public_html/includes/header.php
// Common header for public pages

// You might include config.php here if you need settings in the header (e.g., site title)
// require_once '../admin/includes/config.php';

// Define the current page based on the script name
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- *** SEO Meta Tags *** -->
    <meta name="description" content="Discover and buy unique sci-fi music tracks for your projects. Explore futuristic ambient, electronic sounds, and more. Easy WhatsApp purchase!">
    <title>Buy Sci-Fi Music Tracks | Futuristic Soundscapes</title> <!-- Update title specific to each page if needed, or keep general -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css"> <!-- Custom Sci-Fi CSS -->
    <!-- GSAP will be included in footer.php, but some libraries might go here if needed early -->

    <!-- Add favicon links here if you have one -->
    <link rel="icon" type="image/png" href="images/favicon.png">
    <!-- Animate.css for Hero section animations (if used) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <!-- *** You can add page-specific styles here if needed, using PHP *** -->
    <?php if ($currentPage == 'index.php'): ?>
        <!-- <link rel="stylesheet" href="css/home_styles.css"> -->
        <!-- Can add specific SEO meta tags for Home if needed -->
        <meta name="description" content="Explore featured and latest sci-fi music tracks for sale. Unique futuristic soundscapes await!">
        <title>Home | Sci-Fi Music Tracks</title>
    <?php elseif ($currentPage == 'tracks.php'): ?>
        <!-- <link rel="stylesheet" href="css/tracks_styles.css"> -->
        <meta name="description" content="Browse and buy our full collection of sci-fi music tracks. Find the perfect sound for your project.">
        <title>All Tracks | Sci-Fi Music</title>
    <?php elseif ($currentPage == 'about.php'): ?>
        <!-- <link rel="stylesheet" href="css/about_styles.css"> -->
        <meta name="description" content="Learn more about our project and the unique sci-fi music collection we offer.">
        <title>About Us | Sci-Fi Music</title>
    <?php elseif ($currentPage == 'contact.php'): ?>
        <!-- <link rel="stylesheet" href="css/contact_styles.css"> -->
        <meta name="description" content="Get in touch regarding sci-fi music track purchases or other inquiries.">
        <title>Contact Us | Sci-Fi Music</title>
    <?php endif; ?>
    <!-- Internal Styles for quick theming - Move to style.css -->

</head>

<body>

    <!-- --- particles.js container --- -->
    <div id="particles-js"></div>

    <!-- --- Navigation Bar --- -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <a class="navbar-brand" href="../index.html">Sci-Fi Tracks</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item <?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'active' : ''; ?>">
                    <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
                </li>
                <!-- New Tracks link -->
                <li class="nav-item <?php echo ($currentPage == 'tracks.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="tracks.php">Tracks</a>
                </li>
                <li class="nav-item <?php echo ($currentPage == 'about.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item <?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin/login.php">Admin Login</a>
                </li>
            </ul>

        </div>
    </nav>

    <!-- Main content starts here (in the files that include this header) -->
    <!-- The container div is opened here -->
    <div class="container">