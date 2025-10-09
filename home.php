<?php
include('config.php');
session_start();

// Redirect if not logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Get logged-in user details
$stmt = $conn->prepare("SELECT username, email, phone, full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $phone, $full_name);
$stmt->fetch();
$stmt->close();

// Fetch user's bookings
$bookings_query = "SELECT b.*, pl.name as location_name, ps.slot_number 
                  FROM bookings b 
                  JOIN parking_locations pl ON b.location_id = pl.location_id 
                  JOIN parking_slots ps ON b.slot_id = ps.slot_id 
                  WHERE b.user_id = ? AND b.status = 'active' 
                  ORDER BY b.created_at DESC LIMIT 5";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$stmt->close();

// Handle search for parking locations
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM parking_locations 
              WHERE name LIKE '%$search%' OR city LIKE '%$search%' 
              ORDER BY created_at DESC";
} else {
    $query = "SELECT * FROM parking_locations ORDER BY created_at DESC";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap + Leaflet CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        #map {
            height: 400px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-title {
            font-weight: bold;
            color: #007bff;
        }
        .search-bar {
            max-width: 500px;
        }
        .welcome-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .timer {
            color: #dc3545;
            font-weight: bold;
        }
        .timer.expired {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="welcome-section text-center mb-5">
            <h2>Welcome, <?= htmlspecialchars($username); ?>!</h2>
            <p>Explore parking locations, check availability, and manage your bookings.</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?= htmlspecialchars($email); ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($phone); ?></p>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($full_name); ?></p>
                </div>
            </div>
        </div>

        <!-- User's Bookings -->
        <div class="card mb-5">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Your Active Bookings</h4>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($bookings_result) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Location</th>
                                    <th>Slot Number</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Time Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($bookings_result)) { 
                                    $end_time = strtotime($row['end_time']) * 1000; // Convert to milliseconds for JS
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['booking_id']); ?></td>
                                        <td><?= htmlspecialchars($row['location_name']); ?></td>
                                        <td><?= htmlspecialchars($row['slot_number']); ?></td>
                                        <td><?= htmlspecialchars($row['start_time']); ?></td>
                                        <td><?= htmlspecialchars($row['end_time']); ?></td>
                                        <td><span class="timer" data-endtime="<?= $end_time; ?>"></span></td>
                                        <td><?= htmlspecialchars($row['status']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p class="text-center">No active bookings found.</p>
                <?php } ?>
            </div>
        </div>

        <!-- Search + Map -->
        <div class="text-center mb-4">
            <form method="GET" class="form-inline justify-content-center">
                <div class="form-group mx-sm-2 mb-2">
                    <input type="text" 
                           name="search" 
                           value="<?= htmlspecialchars($search); ?>" 
                           class="form-control" 
                           style="width: 350px;" 
                           placeholder="Search by location or city">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
            </form>
        </div>

        <div id="map"></div>

        <!-- Parking Locations -->
        <div class="row">
            <?php
            $locations_js = [];
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $locations_js[] = [
                        'name' => $row['name'],
                        'city' => $row['city'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude'],
                    ];
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text">
                                    <strong>City:</strong> <?= htmlspecialchars($row['city']); ?><br>
                                    <strong>Address:</strong> <?= htmlspecialchars($row['address']); ?><br>
                                    <strong>Total Slots:</strong> <?= htmlspecialchars($row['total_slots']); ?>
                                </p>
                                <a href="view_slots_user.php?location_id=<?= $row['location_id']; ?>" class="btn btn-outline-primary btn-block">View Slots</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12 text-center'><p>No parking locations found.</p></div>";
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap JS + Leaflet JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Leaflet Map
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

        // Live countdown timer for active bookings
        function updateTimers() {
            const timers = document.querySelectorAll(".timer");
            timers.forEach(timer => {
                const endTime = parseInt(timer.dataset.endtime);
                const now = new Date().getTime();
                const distance = endTime - now;

                if (distance <= 0) {
                    timer.textContent = "Expired";
                    timer.classList.add("expired");
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timer.textContent = `${hours}h ${minutes}m ${seconds}s`;
            });
        }

        // Update timers every second
        setInterval(updateTimers, 1000);
        updateTimers();
    </script>
</body>
</html>