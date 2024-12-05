<?php
include("dbconfig.php");

// Connect to the database
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("<br>Cannot connect to DB");

// Retrieve the customer ID from the query string
// $customer_id = isset($_GET["customer_id"]) ? $_GET["customer_id"] : "";
if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $customer_id = $userSession['user_id'];
    $userRole = $userSession['role'];
    $userName = $userSession['name'];
    // echo "Welcome, $userName ($userRole)";


    
// Get today's date for comparison
$current_date = date('Y-m-d');

// Retrieve upcoming appointments
$upcoming_query = "SELECT a.appointment_id, a.appointment_date, p.loc, p.price, p.hometype, p.note 
                   FROM appointments a
                   JOIN properties p ON a.property_id = p.id
                   WHERE a.customer_id = '$customer_id' AND a.appointment_date >= '$current_date'
                   ORDER BY a.appointment_date ASC";
$upcoming_result = mysqli_query($con, $upcoming_query);

// Retrieve past appointments
$past_query = "SELECT a.appointment_id, a.appointment_date, p.loc, p.price, p.hometype, p.note 
               FROM appointments a
               JOIN properties p ON a.property_id = p.id
               WHERE a.customer_id = '$customer_id' AND a.appointment_date < '$current_date'
               ORDER BY a.appointment_date DESC";
$past_result = mysqli_query($con, $past_query);

// Display the appointments
echo "<h2>Your Appointments</h2>";

// Display upcoming appointments
if (mysqli_num_rows($upcoming_result) > 0) {
    echo "<h3>Upcoming Appointments</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Date</th><th>Location</th><th>Price</th><th>Home Type</th><th>Note</th><th>Action</th></tr>";
    
    while ($row = mysqli_fetch_assoc($upcoming_result)) {
        echo "<tr>
                <td>{$row['appointment_date']}</td>
                <td>{$row['loc']}</td>
                <td>{$row['price']}</td>
                <td>{$row['hometype']}</td>
                <td>{$row['note']}</td>
                <td>
                    <form method='post' action='cancel_appointment.php'>
                        <input type='hidden' name='appointment_id' value='{$row['appointment_id']}'>
                        <input type='hidden' name='customer_id' value='{$customer_id}'>
                        <button type='submit' onclick='return confirm(\"Are you sure you want to cancel this appointment?\")'>Cancel</button>
                    </form>
                </td>
              </tr>";
    }
    echo "</table><br>";
} else {
    echo "<p>No upcoming appointments.</p>";
}

// Display past appointments
if (mysqli_num_rows($past_result) > 0) {
    echo "<h3>Past Appointments</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Date</th><th>Location</th><th>Price</th><th>Home Type</th><th>Note</th></tr>";
    
    while ($row = mysqli_fetch_assoc($past_result)) {
        echo "<tr>
                <td>{$row['appointment_date']}</td>
                <td>{$row['loc']}</td>
                <td>{$row['price']}</td>
                <td>{$row['hometype']}</td>
                <td>{$row['note']}</td>
              </tr>";
    }
    echo "</table><br>";
} else {
    echo "<p>No past appointments.</p>";
}





} else {
    echo "Please log in.";
}

// Close the database connection
mysqli_close($con);
?>
