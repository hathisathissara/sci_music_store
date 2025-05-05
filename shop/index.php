<?php
// public_html/index.php
// Home Page with featured and latest tracks sections

// Include common header
require_once 'includes/header.php';

// Include database connection script (relative path from public_html)
require_once 'admin/includes/db_connection.php';

// --- PHP Logic to Fetch Tracks for Sections ---

$featuredTrack = null;
$latestTracks = [];
// We'll fetch the latest few tracks and use them for Featured and Latest sections.
// Let's fetch the latest 6 tracks.
$sql_latest = "SELECT id, name, description, file_name, image_name FROM tracks ORDER BY created_at DESC LIMIT 6";
$result_latest = null;

// Check if the database connection was successful
if ($link === false) {
    // Handle connection error - Sections might not display data
    echo '<div class="container mt-4"><div class="alert alert-danger">Database connection error. Track sections cannot be displayed.</div></div>';
} else {
    // Fetch latest tracks
    $result_latest = mysqli_query($link, $sql_latest);

    if ($result_latest && mysqli_num_rows($result_latest) > 0) {
        // Fetch all latest tracks into an array
        while ($row = mysqli_fetch_assoc($result_latest)) {
            $latestTracks[] = $row;
        }
        mysqli_free_result($result_latest); // Free result memory

        // Assign the first track fetched as the "Featured" track
        $featuredTrack = $latestTracks[0];

        // We can use the rest of the $latestTracks array for the Latest Tracks section
        // If you wanted distinct sets, you'd use different queries.

    } else {
        // No tracks found message will be handled in the HTML sections
        // error_log("No tracks found in database for Home page sections.");
    }

     // Close database connection after fetching data
    mysqli_close($link);
     // Set $link to false to prevent closing again in the footer
    $link = false; // Or handle closing only if it was opened
}

?>

    <!-- --- Main Content Specific to Home Page --- -->
    <!-- The container div is opened in header.php -->

    <!-- Hero Section (Example using your sample code) -->
    <section class="hero bg-primary text-light py-5">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <!-- Animation classes might require Animate.css library -->
                    <h1 class="animate__animated animate__fadeIn">Unlock Unique Sonic Experiences</h1>
                    <p class="animate__animated animate__fadeIn lead">Discover extraordinary music tracks with our sci-fi inspired collection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Track Section (Example using your sample code) -->
    <?php if ($featuredTrack): ?>
    <section class="featured-track-section py-4">
        <div class="container">
            <h2 class="section-title text-center mb-4">Featured Track</h2>
            <div class="card shadow">
                <div class="row g-0 align-items-center">
                    <div class="col-md-6">
                         <?php
                         // Image path logic for the featured track
                         $featured_image_path = '';
                         $featured_image_exists = false;
                         $public_image_directory = 'images/tracks/'; // Ensure correct path

                         if (!empty($featuredTrack['image_name'])) {
                             $server_image_path = __DIR__ . '/' . $public_image_directory . $featuredTrack['image_name'];
                             if (file_exists($server_image_path)) {
                                 $featured_image_path = $public_image_directory . htmlspecialchars($featuredTrack['image_name']);
                                 $featured_image_exists = true;
                             }
                         }
                         ?>
                        <?php if ($featured_image_exists): ?>
                            <img src="<?php echo $featured_image_path; ?>" alt="<?php echo htmlspecialchars($featuredTrack['name']); ?>" class="img-fluid rounded-start" style="height: auto; max-height: 250px; object-fit: cover;">
                        <?php else: ?>
                             <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center rounded-start" style="height: 250px;">
                                 No Image
                             </div>
                         <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($featuredTrack['name']); ?></h3>
                            <div class="d-flex align-items-center">
                                <!-- Play Button for Featured Track (Now a LINK) -->
                                <a href="player.php?id=<?php echo $featuredTrack['id']; ?>" class="btn btn-secondary btn-play mr-3 <?php echo empty($featuredTrack['file_name']) ? 'disabled' : ''; ?>" <?php echo empty($featuredTrack['file_name']) ? 'aria-disabled="true"' : ''; ?>>
                                     Play Preview
                                </a>
                                <!-- Buy Button for Featured Track -->
                                <button class="btn btn-primary btn-buy" data-track-name="<?php echo htmlspecialchars($featuredTrack['name']); ?>">Buy Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Popular Tracks Section (Example using your sample code - showing a subset of latest as placeholder) -->
    <?php
    // Use a subset of fetched latest tracks as "Popular" for demonstration
    $popularTracksSubset = array_slice($latestTracks, 1, 4); // Get the next 4 tracks after the featured one
    if (!empty($popularTracksSubset)):
    ?>
    <section class="popular-tracks-section py-4 ">
        <div class="container">
            <h2 class="section-title text-center mb-4">Popular Tracks</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($popularTracksSubset as $track): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                         <?php
                         // Image path logic for popular tracks
                         $popular_image_path = '';
                         $popular_image_exists = false;
                         $public_image_directory = 'images/tracks/'; // Ensure correct path

                         if (!empty($track['image_name'])) {
                             $server_image_path = __DIR__ . '/' . $public_image_directory . $track['image_name'];
                             if (file_exists($server_image_path)) {
                                 $popular_image_path = $public_image_directory . htmlspecialchars($track['image_name']);
                                 $popular_image_exists = true;
                             }
                         }
                         ?>
                        <?php if ($popular_image_exists): ?>
                        <img src="<?php echo $popular_image_path; ?>" alt="<?php echo htmlspecialchars($track['name']); ?>" class="card-img-top">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                                No Image
                            </div>
                         <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($track['name']); ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <!-- Play Button for Popular Track (Now a LINK) -->
                                <a href="player.php?id=<?php echo $track['id']; ?>" class="btn btn-secondary btn-sm btn-play <?php echo empty($track['file_name']) ? 'disabled' : ''; ?>" <?php echo empty($track['file_name']) ? 'aria-disabled="true"' : ''; ?>>
                                     Play
                                </a>
                                <!-- Buy Button for Popular Track -->
                                <button class="btn btn-sm btn-primary btn-buy" data-track-name="<?php echo htmlspecialchars($track['name']); ?>">Buy Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>


    <!-- Latest Tracks Section (Example using your sample code - showing the first few latest) -->
     <?php
     // Use the fetched latest tracks (excluding the featured one if needed, or just use the first few)
     // Let's use the first 4 tracks from the fetched latest tracks (could overlap with PopularSubset)
     $latestTracksSubset = array_slice($latestTracks, 0, 4);
     if (!empty($latestTracksSubset)):
     ?>
    <section class="latest-tracks-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-4">Latest Tracks</h2>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($latestTracksSubset as $track): ?>
                <div class="col">
                    <div class="card shadow-sm">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-4">
                                <?php
                                // Image path logic for latest tracks
                                $latest_image_path = '';
                                $latest_image_exists = false;
                                $public_image_directory = 'images/tracks/'; // Ensure correct path

                                if (!empty($track['image_name'])) {
                                    $server_image_path = __DIR__ . '/' . $public_image_directory . $track['image_name'];
                                    if (file_exists($server_image_path)) {
                                        $latest_image_path = $public_image_directory . htmlspecialchars($track['image_name']);
                                        $latest_image_exists = true;
                                    }
                                }
                                ?>
                                <?php if ($latest_image_exists): ?>
                                <img src="<?php echo $latest_image_path; ?>" alt="<?php echo htmlspecialchars($track['name']); ?>" class="img-fluid rounded-start" style="height: auto; max-height: 180px; object-fit: cover;">
                                <?php else: ?>
                                     <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-start" style="height: 180px;">
                                         No Image
                                     </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($track['name']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Play Button for Latest Track (Now a LINK) -->
                                        <a href="player.php?id=<?php echo $track['id']; ?>" class="btn btn-secondary btn-sm btn-play <?php echo empty($track['file_name']) ? 'disabled' : ''; ?>" <?php echo empty($track['file_name']) ? 'aria-disabled="true"' : ''; ?>>
                                             Play
                                        </a>
                                        <!-- Buy Button for Latest Track -->
                                        <button class="btn btn-sm btn-primary btn-buy" data-track-name="<?php echo htmlspecialchars($track['name']); ?>">Buy Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all-btn mt-4 text-center">
                <!-- Link to the full tracks listing page -->
                <a href="tracks.php" class="btn btn-secondary">View All Tracks</a>
            </div>
        </div>
    </section>
     <?php endif; ?>

    <!-- Intro Section linking to About Page (Example using your sample code) -->
    <section class="intro-section py-5  text-light">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h2 class="section-title mb-4">About Our Music</h2>
                    <p class="lead mb-4">Our music collection features unique tracks inspired by cosmic sounds and futuristic vibes. Each piece is carefully crafted to transport you to other dimensions through sound.</p>
                    <a href="about.php" class="btn btn-outline-light">Learn More</a>
                </div>
            </div>
        </div>
    </section>


<?php
// Database connection is closed in the PHP logic block above
// Ensure $link is false or null afterwards if it's checked in footer

// Include common footer
require_once 'includes/footer.php';
?>