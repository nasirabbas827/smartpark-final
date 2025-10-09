<?php
session_start();
include('config.php');

// Redirect if not logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Get location ID
if (!isset($_GET['location_id'])) {
    die("Location not specified.");
}
$location_id = intval($_GET['location_id']);

// Fetch location info
$location_sql = "SELECT * FROM Parking_Locations WHERE location_id = $location_id";
$location_res = mysqli_query($conn, $location_sql);
$location = mysqli_fetch_assoc($location_res);
if (!$location) {
    die("Invalid location.");
}

// Handle booking submission
if (isset($_POST['book_slot'])) {
    $slot_id = intval($_POST['slot_id']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $duration = intval($_POST['duration']);
    $end_time = mysqli_real_escape_string($conn, $_POST['end_time']);
    $estimated_arrival_time = mysqli_real_escape_string($conn, $_POST['estimated_arrival_time']);

    // Insert booking
    $insert_sql = "INSERT INTO Bookings (user_id, slot_id, location_id, start_time, duration, end_time, estimated_arrival_time)
                   VALUES ('$user_id', '$slot_id', '$location_id', '$start_time', '$duration', '$end_time', '$estimated_arrival_time')";
    if (mysqli_query($conn, $insert_sql)) {
        // Update slot status to occupied
        mysqli_query($conn, "UPDATE Parking_Slots SET status = 'occupied' WHERE slot_id = $slot_id");

        $_SESSION['success'] = "Your booking has been confirmed successfully!";
        header("Location: view_slots_user.php?location_id=$location_id");
        exit;
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: view_slots_user.php?location_id=$location_id");
        exit;
    }
}

// Fetch all slots for location
$sql = "SELECT * FROM Parking_Slots WHERE location_id = $location_id ORDER BY slot_number ASC";
$slots = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Slots - <?= htmlspecialchars($location['name']); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .slot-card {
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
        }
        .slot-status {
            font-weight: bold;
            text-transform: capitalize;
        }
        .available { color: green; }
        .occupied { color: red; }
        .reserved { color: orange; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3>Parking Slots at <span class="text-primary"><?= htmlspecialchars($location['name']); ?></span></h3>
    <p style="color:red;">City: <?= htmlspecialchars($location['city']); ?> | Total Slots: <?= htmlspecialchars($location['total_slots']); ?></p>
    <hr>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($slots)) { ?>
            <div class="col-md-3 mb-4">
                <div class="slot-card border card">
                    <h5>Slot <?= htmlspecialchars($row['slot_number']); ?></h5>
                    <p class="slot-status <?= $row['status']; ?>">Status: <?= htmlspecialchars($row['status']); ?></p>
                    <?php if ($row['status'] === 'available'): ?>
                        <button class="btn btn-success btn-sm" data-toggle="modal" 
                                data-target="#bookModal" 
                                data-slotid="<?= $row['slot_id']; ?>"
                                data-slotnum="<?= htmlspecialchars($row['slot_number']); ?>">
                            Book Now
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-sm" disabled>Unavailable</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookModal" tabindex="-1" role="dialog" aria-labelledby="bookModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Book Parking Slot</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="slot_id" id="slot_id">
                <p><strong>Slot Number:</strong> <span id="slot_number_display"></span></p>
                <div class="form-group">
                    <label>Start Time</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Duration (hours)</label>
                    <input type="number" name="duration" class="form-control" required min="1">
                </div>
                <div class="form-group">
                    <label>End Time</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Estimated Arrival Time</label>
                    <input type="datetime-local" name="estimated_arrival_time" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="book_slot" class="btn btn-primary">Confirm Booking</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </form>
  </div>
</div>

<!-- JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$('#bookModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var slotId = button.data('slotid');
    var slotNum = button.data('slotnum');
    $('#slot_id').val(slotId);
    $('#slot_number_display').text(slotNum);
});
</script>

</body>
</html>
