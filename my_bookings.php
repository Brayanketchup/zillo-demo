



<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Optional: Link to a CSS file -->
</head>
<body>

<?php
include("dbconfig.php");

// Connect to the database
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("<br>Cannot connect to DB");

// Get the user ID from URL parameters
// $user_id = $_GET['customer_id'];

if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $user_id = $userSession['user_id'];
    $userRole = $userSession['role'];
    $userName = $userSession['name'];
    echo "Welcome, $userName ($userRole)";



// Check if a booking cancellation was requested
if (isset($_POST['cancel_booking_id'])) {
    $cancel_booking_id = $_POST['cancel_booking_id'];

    // Update the booking status to 'cancelled'
    $updateBookingQuery = "UPDATE bookings SET status = 'cancelled' WHERE booking_id = '$cancel_booking_id'";
    mysqli_query($con, $updateBookingQuery);

    // Set the property as available by setting `booked` to 0
    $updatePropertyQuery = "UPDATE properties 
                            SET booked = 0 
                            WHERE id = (SELECT property_id FROM bookings WHERE booking_id = '$cancel_booking_id')";
    mysqli_query($con, $updatePropertyQuery);

    echo "<p>Booking has been successfully cancelled.</p>";
}

// SQL query to fetch bookings for the given user ID
$query = "SELECT b.booking_id, p.loc, p.price, p.hometype, b.booking_date, b.booking_from, b.status, p.id AS property_id
          FROM bookings b
          INNER JOIN properties p ON b.property_id = p.id
          WHERE b.customer_id = '$user_id'";

$result = mysqli_query($con, $query);

// Display bookings if found
if (mysqli_num_rows($result) > 0) {
    echo "<br><a href='user_dashboard.php?customer_id=$user_id' style='display: inline-block; padding: 10px 20px; margin: 10px 0; border: 2px solid #000; border-radius: 5px; text-decoration: none; color: #000; background-color: #f0f0f0;'>Return to Main Page</a>";
    echo "<table border='1'>
            <tr>
                <th>Booking ID</th>
                <th>Location</th>
                <th>Price</th>
                <th>Home Type</th>
                <th>Booking Date</th>
                <th>From Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['booking_id']}</td>
                <td>{$row['loc']}</td>
                <td>{$row['price']}</td>
                <td>{$row['hometype']}</td>
                <td>{$row['booking_date']}</td>
                <td>{$row['booking_from']}</td>
                <td>{$row['status']}</td>
                <td>";
        
        // Show Cancel button only if the status is 'booked'
        if ($row['status'] === 'booked') {
            echo "<form method='post' action=''>
                    <input type='hidden' name='cancel_booking_id' value='{$row['booking_id']}'>
                    <button type='submit'>Cancel Booking</button>
                  </form>";
        } else {
            echo "N/A"; // No action available if not 'booked'
        }

        echo "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No bookings found for this user.</p>";
}
    
} else {
    echo "Please log in.";
}



// Close the connection
mysqli_close($con);
?>

</body>
</html>



