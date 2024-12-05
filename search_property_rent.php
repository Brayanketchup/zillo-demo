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
    // $minPrice = $_GET["min_price"] ?? 0;  // Default minimum price
    // $maxPrice = $_GET["max_price"] ?? 1000000;  // Arbitrary high default maximum price
    $minPrice = $_GET["min_price"] ?? null; // Default minimum price
    $maxPrice = $_GET["max_price"] ?? null;
    $hometype = $_GET["hometype"] ?? '';
    
    
    
    
    
    $sql_query = "SELECT * FROM properties WHERE booked = 0 AND (purpose = 'rent' OR purpose = 'both')";
    $sql_query .= $keyword ? " AND note LIKE '%$keyword%'" : "";
    $sql_query .= $location ? " AND loc LIKE '%$location%'" : "";
    // $sql_query .= ($minPrice or $maxPrice) ? " AND price BETWEEN $minPrice AND $maxPrice" : "";
    $sql_query .= $minPrice ? " AND price > '$minPrice'" : "";
    $sql_query .= $maxPrice ? " AND price < '$minPrice'" : "";
    $sql_query .= $hometype ? " AND hometype LIKE '%$hometype%'" : "";
    
    
    $result = mysqli_query($con, $sql_query);
    $propertiesFound = mysqli_num_rows($result);
    
    // Check if properties were found
    if ($propertiesFound > 0) {
        echo "Properties matching your criteria:";
        echo "<table border='1'>";
        echo "<tr><th>Property ID</th><th>Location</th><th>Price</th><th>Home Type</th><th>Note</th><th>Action</th></tr>";
    
        // Display each property with an option to "book"
        while ($row = mysqli_fetch_array($result)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['loc']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['hometype']}</td>
                    <td>{$row['note']}</td>
                    <td><a href='book_property.php?property_id={$row['id']}&customer_id=$User_id'>Book</a></td>
                  </tr>";
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
