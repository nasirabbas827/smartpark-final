<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM Parking_Locations WHERE location_id = $id");
    header("Location: view_locations.php");
    exit;
}

// Search
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM Parking_Locations 
              WHERE name LIKE '%$search%' OR city LIKE '%$search%' 
              ORDER BY created_at DESC";
} else {
    $query = "SELECT * FROM Parking_Locations ORDER BY created_at DESC";
}
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Parking Locations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2>All Parking Locations</h2>
    <hr>

    <div class="d-flex justify-content-between mb-3">
        <a href="add_location.php" class="btn btn-success">+ Add Location</a>
        <form method="GET" class="form-inline">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control mr-2" placeholder="Search by name or city">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>City</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Total Slots</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['location_id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['city']) ?></td>
                    <td><?= $row['latitude'] ?></td>
                    <td><?= $row['longitude'] ?></td>
                    <td><?= $row['total_slots'] ?></td>
                    <td>
                        <a href="edit_location.php?id=<?= $row['location_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="view_locations.php?delete=<?= $row['location_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this location?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        <?php else: ?>
            <tr><td colspan="8" class="text-center">No Locations Found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
