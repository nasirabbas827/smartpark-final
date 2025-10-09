<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $slot_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM Parking_Slots WHERE slot_id = $slot_id");
    header("Location: view_slots.php");
    exit;
}

// Search
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT ps.*, pl.name AS location_name, pl.city 
              FROM Parking_Slots ps 
              JOIN Parking_Locations pl ON ps.location_id = pl.location_id
              WHERE ps.slot_number LIKE '%$search%' 
              OR pl.name LIKE '%$search%' 
              OR pl.city LIKE '%$search%'
              ORDER BY ps.slot_id DESC";
} else {
    $query = "SELECT ps.*, pl.name AS location_name, pl.city 
              FROM Parking_Slots ps 
              JOIN Parking_Locations pl ON ps.location_id = pl.location_id
              ORDER BY ps.slot_id DESC";
}
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Parking Slots</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2>All Parking Slots</h2>
    <hr>

    <div class="d-flex justify-content-between mb-3">
        <a href="add_slot.php" class="btn btn-success">+ Add Slot</a>
        <form method="GET" class="form-inline">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control mr-2" placeholder="Search by slot or location">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Slot Number</th>
                <th>Location</th>
                <th>City</th>
                <th>Status</th>
                <th>Updated At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['slot_id'] ?></td>
                    <td><?= htmlspecialchars($row['slot_number']) ?></td>
                    <td><?= htmlspecialchars($row['location_name']) ?></td>
                    <td><?= htmlspecialchars($row['city']) ?></td>
                    <td>
                        <?php if ($row['status'] == 'available'): ?>
                            <span class="badge badge-success">Available</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?= ucfirst($row['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['updated_at']) ?></td>
                    <td>
                        <a href="edit_slot.php?id=<?= $row['slot_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="view_slots.php?delete=<?= $row['slot_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this slot?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">No Slots Found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
