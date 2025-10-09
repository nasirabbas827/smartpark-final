<?php
include('config.php');
session_start();

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT name, city, total_slots, latitude, longitude FROM parking_locations 
              WHERE name LIKE '%$search%' OR city LIKE '%$search%' 
              ORDER BY created_at DESC LIMIT 6";
} else {
    $query = "SELECT name, city, total_slots, latitude, longitude FROM parking_locations 
              ORDER BY created_at DESC LIMIT 6";
}
$result = mysqli_query($conn, $query);
$locations = [];
$locations_js = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = $row;
        $locations_js[] = [
            'name' => $row['name'],
            'city' => $row['city'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Smart Park</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .jumbotron {
            height: 500px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./images/hotel.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .jumbotron h1 {
            font-size: 3.5rem;
            margin-bottom: 15px;
        }
        .jumbotron p {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        #map {
            height: 400px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .search-bar {
            max-width: 500px;
            margin: 0 auto 20px;
        }
        .feature-card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            text-align: center;
            padding: 20px;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 15px;
        }
        .location-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        footer {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <!-- Hero Section -->
    <div class="jumbotron text-center">
        <h1>Welcome to Smart Park System</h1>
        <p>Find & Reserve Your Parking Slot Easily</p>
        <?php if (!isset($_SESSION["id"])) { ?>
            <a href="login.php" class="btn btn-primary btn-lg">Login to Explore</a>
        <?php } else { ?>
            <a href="home.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
        <?php } ?>
    </div>

    <!-- Search + Map Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Find Parking Locations</h2>
            <form method="GET" class="form-inline search-bar">
                <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" class="form-control w-75 mr-2" placeholder="Search by location or city">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Search</button>
            </form>
            <div id="map"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Smart Park?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-map-marked-alt feature-icon"></i>
                        <h4>Real-Time Availability</h4>
                        <p>Check parking slot availability instantly and reserve with confidence.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-car feature-icon"></i>
                        <h4>Easy Booking</h4>
                        <p>Reserve your parking slot in just a few clicks, anytime, anywhere.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <h4>Secure & Reliable</h4>
                        <p>Your data and bookings are protected with top-notch security.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Locations Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Popular Parking Locations</h2>
            <div class="row">
                <?php if (count($locations) > 0) { ?>
                    <?php foreach ($locations as $location) { ?>
                        <div class="col-md-4 mb-4">
                            <div class="card location-card">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($location['name']); ?></h5>
                                    <p class="card-text">
                                        <strong>City:</strong> <?= htmlspecialchars($location['city']); ?><br>
                                        <strong>Total Slots:</strong> <?= htmlspecialchars($location['total_slots']); ?>
                                    </p>
                                    <?php if (!isset($_SESSION["id"])) { ?>
                                        <a href="login.php" class="btn btn-outline-primary btn-block">View Details</a>
                                    <?php } else { ?>
                                        <a href="view_slots_user.php?location_id=<?= htmlspecialchars($location['location_id'] ?? ''); ?>" class="btn btn-outline-primary btn-block">View Slots</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-12 text-center">
                        <p>No parking locations found.</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 text-center">
        <div class="container">
            <h3>Ready to Park Smarter?</h3>
            <p>Join thousands of users who trust Smart Park for hassle-free parking.</p>
            <?php if (!isset($_SESSION["id"])) { ?>
                <a href="register.php" class="btn btn-primary btn-lg">Sign Up Now</a>
            <?php } else { ?>
                <a href="home.php" class="btn btn-primary btn-lg">Explore Now</a>
            <?php } ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4">
        <div class="container text-center">
            <p>&copy; 2025 Smart Park. All rights reserved.</p>
            <p>
                <a href="#" class="text-white mx-2">Privacy Policy</a> |
                <a href="#" class="text-white mx-2">Terms of Service</a> |
                <a href="#" class="text-white mx-2">Contact Us</a>
            </p>
        </div>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const locations = <?= json_encode($locations_js); ?>;
        const map = L.map('map').setView([30.3753, 69.3451], 6); // Centered in Pakistan

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        if (locations.length > 0) {
            locations.forEach(loc => {
                if (loc.latitude && loc.longitude) {
                    const marker = L.marker([loc.latitude, loc.longitude]).addTo(map);
                    marker.bindPopup(`<b>${loc.name}</b><br>${loc.city}`);
                }
            });

            // Fit map to bounds if multiple locations
            const group = new L.featureGroup(locations.map(loc => L.marker([loc.latitude, loc.longitude])));
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
        } else {
            console.log("No locations to display on map.");
        }
    </script>
</body>
</html>