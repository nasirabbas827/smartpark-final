<?php
session_start();
include('config.php');

// Check user login
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Handle booking cancellation
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);

    // Get slot_id for that booking
    $slot_sql = "SELECT slot_id FROM Bookings WHERE booking_id = ? AND user_id = ? AND status = 'active'";
    $stmt = $conn->prepare($slot_sql);
    $stmt->bind_param("ii", $cancel_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $slot = $result->fetch_assoc();
        $slot_id = $slot['slot_id'];

        // Update booking status to cancelled
        $conn->query("UPDATE Bookings SET status = 'cancelled' WHERE booking_id = $cancel_id");

        // Update slot status back to available
        $conn->query("UPDATE Parking_Slots SET status = 'available' WHERE slot_id = $slot_id");
    }
    header("Location: my_bookings.php");
    exit;
}

// Auto-update expired bookings to "completed"
$conn->query("UPDATE Bookings SET status = 'completed' WHERE end_time < NOW() AND status = 'active'");

// Fetch all bookings of this user
$sql = "
    SELECT 
        b.booking_id, 
        b.start_time, 
        b.duration, 
        b.end_time, 
        b.status, 
        b.estimated_arrival_time,
        l.name AS location_name,
        l.city AS location_city,
        s.slot_number
    FROM Bookings b
    JOIN Parking_Locations l ON b.location_id = l.location_id
    JOIN Parking_Slots s ON b.slot_id = s.slot_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Bookings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .timer {
            font-weight: bold;
            color: #007bff;
        }
        .expired {
            color: red;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">My Bookings</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped text-center">
            <thead class="thead-dark">
                <tr>
                    <th>Booking ID</th>
                    <th>Location</th>
                    <th>City</th>
                    <th>Slot</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Timer</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $is_active = ($row['status'] === 'active');
                    $end_timestamp = strtotime($row['end_time']) * 1000; // JS timestamp
                ?>
                <tr>
                    <td><?php echo $row['booking_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['location_city']); ?></td>
                    <td><?php echo htmlspecialchars($row['slot_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['end_time']); ?></td>
                    <td>
                        <?php if ($is_active): ?>
                            <span class="timer" data-endtime="<?php echo $end_timestamp; ?>" id="timer-<?php echo $row['booking_id']; ?>">Calculating...</span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'active'): ?>
                            <span class="badge badge-success">Active</span>
                        <?php elseif ($row['status'] === 'completed'): ?>
                            <span class="badge badge-primary">Completed</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Cancelled</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($is_active): ?>
                            <a href="my_bookings.php?cancel_id=<?php echo $row['booking_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this booking?');">Cancel</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled>Cancel</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">You have no bookings yet.</div>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
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
