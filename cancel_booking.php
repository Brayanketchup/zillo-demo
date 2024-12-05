<?php
include("dbconfig.php");

// Connect to the database
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("<br>Cannot connect to DB");

// Get the booking ID from URL parameters
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// SQL to cancel the booking
if ($booking_id > 0) {
    $query = "UPDATE bookings SET status='cancelled' WHERE booking_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'i', $booking_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<script>alert('Booking cancelled successfully!'); window.location.href='my_bookings.php';</script>";
} else {
    echo "Invalid booking ID.";
}

mysqli_close($con);
?>
