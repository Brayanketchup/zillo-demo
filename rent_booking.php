<?php
echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>User Dashboard</title>\n";
echo "    <link rel='stylesheet' href='style.css'>\n"; // Optional: Link to a CSS file for styling
echo "</head>\n";
echo "<body>\n";

include("dbconfig.php");

// Get the user ID from URL parameters
// $userId = $_GET['customer_id'];
if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $userId = $userSession['user_id'];
    $userRole = $userSession['role'];
    $userName = $userSession['name'];
    // echo "Welcome, $userName ($userRole)";





    echo "<h1>Search For Properties for renting</h1>\n";

    // Link to view user's bookings
    echo "<p><a href='my_bookings.php'>My Bookings</a></p>\n";
    echo "<p><a href='search_property_rent.php'>Browse All Properties</a></p>\n";
    echo "<a href='logout.php'>Logout</a>";
    
    
    // Form for searching properties
    echo "<h2>Search Properties</h2>\n";
    echo "<form action='search_property_rent.php' method='get'>\n";
    echo "    <input type='hidden' name='customer_id' value='$userId'>\n";
    echo "    <label for='keyword'>Keyword:</label>\n";
    echo "    <input type='text' id='keyword' name='keyword' placeholder='Enter keyword...'><br>\n";
    echo "    <label for='location'>Location:</label>\n";
    echo "    <input type='text' id='location' name='location' placeholder='Enter location...'><br>\n";
    echo "    <label for='hometype'>Home Type:</label>\n";
    echo "    <input type='text' id='hometype' name='hometype' placeholder='Enter home type...'><br>\n";
    echo "    <label for='min_price'>Min Price:</label>\n";
    echo "    <input type='number' id='min_price' name='min_price' placeholder='Minimum price...'><br>\n";
    echo "    <label for='max_price'>Max Price:</label>\n";
    echo "    <input type='number' id='max_price' name='max_price' placeholder='Maximum price...'><br>\n";
    echo "    <button type='submit'>Search</button>\n";
    echo "</form>\n";
    
    echo "</body>\n";
    echo "</html>\n";

} else {
    echo "Please log in.";
}

?>