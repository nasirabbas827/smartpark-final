<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch data for dashboard
$users_query = "SELECT * FROM users LIMIT 5";
$users_result = mysqli_query($conn, $users_query);

$bookings_query = "SELECT b.*, u.username, pl.name as location_name 
                 FROM bookings b 
                 JOIN users u ON b.user_id = u.id 
                 JOIN parking_locations pl ON b.location_id = pl.location_id 
                 LIMIT 5";
$bookings_result = mysqli_query($conn, $bookings_query);

$locations_query = "SELECT * FROM parking_locations LIMIT 5";
$locations_result = mysqli_query($conn, $locations_query);

$slots_query = "SELECT ps.*, pl.name as location_name 
               FROM parking_slots ps 
               JOIN parking_locations pl ON ps.location_id = pl.location_id 
               LIMIT 5";
$slots_result = mysqli_query($conn, $slots_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .dashboard-card {
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .main-content {
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container-fluid main-content">
        <h1 class="mt-4 mb-4">Admin Dashboard</h1>

        <!-- Overview Cards -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">Total Users</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users")); ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">Active Bookings</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bookings WHERE status='active'")); ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">Parking Locations</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM parking_locations")); ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">Available Slots</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM parking_slots WHERE status='available'")); ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Section -->
        <div id="users" class="card mb-4">
            <div class="card-header">Recent Users</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Full Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($users_result)) { ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bookings Section -->
        <div id="bookings" class="card mb-4">
            <div class="card-header">Recent Bookings</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Username</th>
                                <th>Location</th>
                                <th>Start Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($bookings_result)) { ?>
                                <tr>
                                    <td><?php echo $row['booking_id']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['location_name']; ?></td>
                                    <td><?php echo $row['start_time']; ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Parking Locations Section -->
        <div id="locations" class="card mb-4">
            <div class="card-header">Parking Locations</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Total Slots</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($locations_result)) { ?>
                                <tr>
                                    <td><?php echo $row['location_id']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['address']; ?></td>
                                    <td><?php echo $row['city']; ?></td>
                                    <td><?php echo $row['total_slots']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Parking Slots Section -->
        <div id="slots" class="card mb-4">
            <div class="card-header">Parking Slots</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Slot ID</th>
                                <th>Location</th>
                                <th>Slot Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($slots_result)) { ?>
                                <tr>
                                    <td><?php echo $row['slot_id']; ?></td>
                                    <td><?php echo $row['location_name']; ?></td>
                                    <td><?php echo $row['slot_number']; ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>