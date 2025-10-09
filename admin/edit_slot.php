<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: view_slots.php");
    exit;
}

$slot_id = intval($_GET['id']);

// Fetch slot data
$slot = mysqli_query($conn, "SELECT * FROM Parking_Slots WHERE slot_id = $slot_id");
if (mysqli_num_rows($slot) == 0) {
    header("Location: view_slots.php");
    exit;
}
$slot = mysqli_fetch_assoc($slot);

// Update
if (isset($_POST['update_slot'])) {
    $location_id = intval($_POST['location_id']);
    $slot_number = mysqli_real_escape_string($conn, $_POST['slot_number']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE Parking_Slots 
            SET location_id='$location_id', slot_number='$slot_number', status='$status'
            WHERE slot_id=$slot_id";
    mysqli_query($conn, $sql);
    header("Location: view_slots.php");
    exit;
}

// Fetch locations
$locations = mysqli_query($conn, "SELECT * FROM Parking_Locations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Parking Slot</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h2>Edit Parking Slot</h2>
            <hr>
            <form method="POST">
                <div class="form-group">
                    <label>Location</label>
                    <select name="location_id" class="form-control" required>
                        <?php while ($loc = mysqli_fetch_assoc($locations)) { ?>
                            <option value="<?= $loc['location_id'] ?>" <?= $slot['location_id'] == $loc['location_id'] ? 'selected' : '' ?>>
                                <?= $loc['name'] ?> (<?= $loc['city'] ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Slot Number</label>
                    <input type="text" name="slot_number" class="form-control" value="<?= $slot['slot_number'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="available" <?= $slot['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="occupied" <?= $slot['status'] == 'occupied' ? 'selected' : '' ?>>Occupied</option>
                        <option value="reserved" <?= $slot['status'] == 'reserved' ? 'selected' : '' ?>>Reserved</option>
                    </select>
                </div>
                <button type="submit" name="update_slot" class="btn btn-primary">Update Slot</button>
                <a href="view_slots.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
