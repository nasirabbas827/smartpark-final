<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}
// Fetch all bookings with joined details
$sql = "
    SELECT 
        b.booking_id, 
        u.username AS user_name, 
        u.email AS user_email,
        l.name AS location_name, 
        l.city AS location_city,
        s.slot_number,
        b.start_time, 
        b.duration, 
        b.end_time, 
        b.estimated_arrival_time, 
        b.status,
        b.created_at
    FROM Bookings b
    JOIN Users u ON b.user_id = u.id
    JOIN Parking_Locations l ON b.location_id = l.location_id
    JOIN Parking_Slots s ON b.slot_id = s.slot_id
    ORDER BY b.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - All Bookings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        @media print {
            .no-print { display: none; }
            table { font-size: 12px; }
        }
    </style>

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>All Parking Bookings</h3>
        <button class="btn btn-primary no-print" onclick="window.print()">Print Report</button>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Location</th>
                    <th>City</th>
                    <th>Slot No</th>
                    <th>Start Time</th>
                    <th>Duration (hr)</th>
                    <th>End Time</th>
                    <th>Arrival</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['location_city']); ?></td>
                        <td><?php echo htmlspecialchars($row['slot_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['duration']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                        <td><?php echo $row['estimated_arrival_time'] ? htmlspecialchars($row['estimated_arrival_time']) : '—'; ?></td>
                        <td>
                            <?php if ($row['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php elseif ($row['status'] === 'completed'): ?>
                                <span class="badge badge-primary">Completed</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No bookings found.</div>
    <?php endif; ?>
</div>

</body>
</html>
