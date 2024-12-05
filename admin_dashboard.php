<?php
include("dbconfig.php");

// Retrieve user_id and role from URL parameters
// $user_id = $_GET['user_id'];
// $role = isset($_GET['role']) ? $_GET['role'] : null;

if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $user_id = $userSession['user_id'];
    $role = $userSession['role'];
    $userName = $userSession['name'];
    echo "Welcome, $userName ($userRole)";



// Ensure the user is an admin
if ($role !== 'admin') {
    echo "You must be an admin to access this page.";
    exit;
}

// Connect to the database
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("Cannot connect to the database.");

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Admin Dashboard</title>";
echo "</head>";
echo "<body>";
echo "<h1>Admin Dashboard</h1>";
echo "<p>Welcome, Admin!</p>";
echo "<ul>";
// echo "<p><a href='my_bookings.php?customer_id=$userId'>My Bookings</a></p>\n";
echo "<li><a href='view_listed_properties.php'>View Booked Properties</a></li>";
// echo "<li><a href='view_listed_properties.php?user_id=$user_id&role=admin'>View Booked Properties</a></li>";
echo "<li><a href='add_property.php'>Post New Property</a></li>";
echo "</ul>";
echo "<a href='logout.php'>Logout</a>";
echo "</body>";
echo "</html>";


} else {
    echo "Please log in.";
}

mysqli_close($con);
?>
