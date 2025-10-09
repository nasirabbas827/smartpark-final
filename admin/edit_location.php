<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: view_locations.php");
    exit;
}

$id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM Parking_Locations WHERE location_id = $id");
$location = mysqli_fetch_assoc($query);

if (!$location) {
    header("Location: view_locations.php");
    exit;
}

if (isset($_POST['update_location'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $total_slots = intval($_POST['total_slots']);

    $sql = "UPDATE Parking_Locations 
            SET name='$name', address='$address', city='$city', latitude='$latitude', longitude='$longitude', total_slots='$total_slots'
            WHERE location_id=$id";
    mysqli_query($conn, $sql);
    header("Location: view_locations.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Parking Location</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Edit Parking Location</h2>
    <hr>

    <form method="POST">
        <div class="form-group">
            <label>Location Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($location['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control" required><?= htmlspecialchars($location['address']) ?></textarea>
        </div>
        <div class="form-group">
            <label>City</label>
            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($location['city']) ?>" required>
        </div>
        <div class="form-group">
            <label>Latitude</label>
            <input type="text" name="latitude" class="form-control" value="<?= $location['latitude'] ?>" required>
        </div>
        <div class="form-group">
            <label>Longitude</label>
            <input type="text" name="longitude" class="form-control" value="<?= $location['longitude'] ?>" required>
        </div>
        <div class="form-group">
            <label>Total Slots</label>
            <input type="number" name="total_slots" class="form-control" value="<?= $location['total_slots'] ?>" required>
        </div>
        <button type="submit" name="update_location" class="btn btn-primary">Update Location</button>
        <a href="view_locations.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</div>
</div>

</body>
</html>
