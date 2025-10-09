<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (isset($_POST['add_slot'])) {
    $location_id = intval($_POST['location_id']);
    $slot_number = mysqli_real_escape_string($conn, $_POST['slot_number']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO Parking_Slots (location_id, slot_number, status) 
            VALUES ('$location_id', '$slot_number', '$status')";
    mysqli_query($conn, $sql);
    header("Location: view_slots.php");
    exit;
}

// Fetch locations for dropdown
$locations = mysqli_query($conn, "SELECT * FROM Parking_Locations ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Parking Slot</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h2>Add New Parking Slot</h2>
            <hr>
            <form method="POST">
                <div class="form-group">
                    <label>Location</label>
                    <select name="location_id" class="form-control" required>
                        <option value="">Select Location</option>
                        <?php while ($loc = mysqli_fetch_assoc($locations)) { ?>
                            <option value="<?= $loc['location_id'] ?>"><?= $loc['name'] ?> (<?= $loc['city'] ?>)</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Slot Number</label>
                    <input type="text" name="slot_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                    </select>
                </div>
                <button type="submit" name="add_slot" class="btn btn-success">Add Slot</button>
                <a href="view_slots.php" class="btn btn-primary">View Slots</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
