<?php
include("dbconfig.php");

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("<br>Cannot connect to DB");

// Retrieve property ID and customer ID from the query string
$property_id = $_GET["property_id"];
// $customer_id = $_GET["customer_id"];


if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $customer_id = $userSession['user_id'];
    $userRole = $userSession['role'];
    $userName = $userSession['name'];
    // echo "Welcome, $userName ($userRole)";


// Fetch property details
$property_query = "SELECT loc, price, hometype, note FROM properties WHERE id = $property_id";
$property_result = mysqli_query($con, $property_query);
$property = mysqli_fetch_assoc($property_result);

if (!$property) {
    echo "<p>Property not found.</p>";
    exit;
}

// Check if the form has been submitted with dates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_from'])) {

    $booking_from = $_POST['booking_from'];
    

    // Validate that the "To Date" is not earlier than the "From Date"

        // Begin transaction
        mysqli_begin_transaction($con);

        try {
            // Insert the booking record with the specified dates
            $insert_query = "INSERT INTO bookings (property_id, customer_id, booking_from, booking_date) 
                             VALUES ($property_id, $customer_id, '$booking_from', CURDATE())";
            mysqli_query($con, $insert_query);

            // Update property status to booked
            $update_query = "UPDATE properties SET booked = 1 WHERE id = $property_id";
            mysqli_query($con, $update_query);

            // Commit the transaction
            mysqli_commit($con);

            echo "<p>Property successfully booked from <strong>$booking_from</strong></p>";
            echo "<br><a href='user_dashboard.php'>Return to Dashboard</a>";
        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            mysqli_rollback($con);
            echo "<p>Error booking property: " . $e->getMessage() . "</p>";
        }
    }


// Display property details and booking form if no valid dates submitted
if (!isset($booking_from)) {
    echo "<h2>Book Property</h2>";
    echo "<p><strong>Location:</strong> {$property['loc']}</p>";
    echo "<p><strong>Price:</strong> \${$property['price']}</p>";
    echo "<p><strong>Home Type:</strong> {$property['hometype']}</p>";
    echo "<p><strong>Notes:</strong> {$property['note']}</p>";

    echo "<h3>Select Booking Dates</h3>";
    echo "<form method='post' action=''>
            <label for='booking_from'>From Date:</label>
            <input type='date' id='booking_from' name='booking_from' required><br><br>
            
            
            <button type='submit'>Book</button>
          </form>";
}



} else {
    echo "Please log in.";
}


// Close the database connection
mysqli_close($con);
?>
