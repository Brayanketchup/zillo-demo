<?php
include("dbconfig.php");

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("<br>Cannot connect to DB");

    if (isset($_COOKIE['user_session'])) {
        $userSession = json_decode($_COOKIE['user_session'], true);
        $customer_id = $userSession['user_id'];
        $userRole = $userSession['role'];
        $userName = $userSession['name'];
        echo "Welcome, $userName ($userRole)";

// Retrieve property ID and customer ID from the query string
$property_id = $_GET["property_id"];
// $customer_id = $_GET["customer_id"];

// Fetch property details
$property_query = "SELECT loc, price, hometype, note FROM properties WHERE id = $property_id";
$property_result = mysqli_query($con, $property_query);
$property = mysqli_fetch_assoc($property_result);

if (!$property) {
    echo "<p>Property not found.</p>";
    exit;
}

// Check if the form has been submitted with an appointment date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_date'])) {
    $appointment_date = $_POST['appointment_date'];
    
    // Insert the appointment record with the specified date
    $insert_query = "INSERT INTO appointments (property_id, customer_id, agent_id, appointment_date) 
                     VALUES ($property_id, $customer_id, (SELECT user_id FROM Users WHERE role = 'admin' LIMIT 1), '$appointment_date')";
    
    if (mysqli_query($con, $insert_query)) {
        echo "<p>Appointment successfully scheduled for <strong>$appointment_date</strong>!</p>";
        echo "<br><a href='user_dashboard.php'>Return to Dashboard</a>";
    } else {
        echo "<p>Error scheduling appointment: " . mysqli_error($con) . "</p>";
    }
}

// Display property details and appointment form if no valid date is submitted
echo "<h2>Schedule a Viewing</h2>";
echo "<p><strong>Location:</strong> {$property['loc']}</p>";
echo "<p><strong>Price:</strong> \${$property['price']}</p>";
echo "<p><strong>Home Type:</strong> {$property['hometype']}</p>";
echo "<p><strong>Notes:</strong> {$property['note']}</p>";

echo "<h3>Select Appointment Date</h3>";
echo "<form method='post' action=''>
        <label for='appointment_date'>Date:</label>
        
        <input type='date' id='appointment_date' name='appointment_date' required min='" . date("Y-m-d") . "'><br><br>
        
        <button type='submit'>Schedule Appointment</button>
      </form>";



    } else {
        echo "Please log in.";
    }
    

// Close the database connection
mysqli_close($con);
?>
