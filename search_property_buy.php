<?php
include("dbconfig.php");

$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("<br>Cannot connect to DB");

// Get search criteria from user input
// $User_id = $_GET["customer_id"];
if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $User_id = $userSession['user_id'];
    $userRole = $userSession['role'];
    $userName = $userSession['name'];
    echo "Welcome, $userName ($userRole)";

    $keyword = $_GET["keyword"] ?? '';
    $location = $_GET["location"] ?? '';
    $minPrice = $_GET["min_price"] ?? null;
    $maxPrice = $_GET["max_price"] ?? null;
    $hometype = $_GET["hometype"] ?? '';
    
    // Build the search query
    $sql_query = "SELECT * FROM properties WHERE booked = 0 AND (purpose = 'sale' OR purpose = 'both')";
    $sql_query .= $keyword ? " AND note LIKE '%$keyword%'" : "";
    $sql_query .= $location ? " AND loc LIKE '%$location%'" : "";
    $sql_query .= $minPrice ? " AND price >= '$minPrice'" : "";
    $sql_query .= $maxPrice ? " AND price <= '$maxPrice'" : "";
    $sql_query .= $hometype ? " AND hometype LIKE '%$hometype%'" : "";
    
    $result = mysqli_query($con, $sql_query);
    $propertiesFound = mysqli_num_rows($result);
    
    // Check if properties were found
    if ($propertiesFound > 0) {
        echo "Properties matching your criteria:";
        echo "<table border='1'>";
        echo "<tr><th>Property ID</th><th>Location</th><th>Price</th><th>Home Type</th><th>Note</th><th>Action</th></tr>";
    
        // Display each property with the appropriate action
        while ($row = mysqli_fetch_array($result)) {
            $property_id = $row['id'];
            
            // Check if an appointment is already scheduled for this property and user
            $appointment_query = "SELECT appointment_date FROM appointments WHERE property_id = $property_id AND customer_id = $User_id";
            $appointment_result = mysqli_query($con, $appointment_query);
            $appointment = mysqli_fetch_assoc($appointment_result);
    
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['loc']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['hometype']}</td>
                    <td>{$row['note']}</td>";
    
            // Display appointment status or booking option based on whether an appointment exists
            if ($appointment) {
                $appointment_date = $appointment['appointment_date'];
                echo "<td>Appointment scheduled for: $appointment_date</td>";
            } else {
                echo "<td><a href='make_appointment.php?property_id={$row['id']}&customer_id=$User_id'>Schedule an  appointment</a></td>";
            }
    
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<br>No properties found matching your criteria.";
    }
    



} else {
    echo "Please log in.";
}

// Close the database connection
mysqli_close($con);
?>
