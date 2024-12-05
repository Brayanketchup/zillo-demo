<?php
include("dbconfig.php");

// Check if the 'user_session' cookie exists
if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $user_id = $userSession['user_id'];
    $role = $userSession['role'];
    $userName = $userSession['name'];
    echo "Welcome, $userName ($role)";
} else {
    echo "Please log in.";
    exit;
}

// Ensure the user is an admin
if ($role !== 'admin') {
    echo "<br>You must be an admin to access this page.";
    exit;
}

// Connect to the database
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("Cannot connect to the database.");

// SQL query to fetch properties posted by this admin
$sql = "SELECT id, loc, price, hometype, note, booked FROM properties WHERE created_by = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listed Properties</title>
</head>
<body>
    <h1>Listed Properties</h1>
    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Price</th>
                <th>Home Type</th>
                <th>Note</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['loc']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['hometype']); ?></td>
                    <td><?php echo htmlspecialchars($row['note']); ?></td>
                    <td><?php echo $row['booked'] == 1 ? 'Booked' : 'Available'; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No properties have been listed by you.</p>
    <?php endif; ?>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>

<?php
$stmt->close();
mysqli_close($con);
?>
