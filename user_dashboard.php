<?php
echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>User Dashboard</title>\n";
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

echo "<h1>Welcome to Your Dashboard</h1>\n";

// Link to view user's bookings

echo "<p>Would you Like to rent a property?<br> Find that property of your dreams here at: <br><a href='rent_booking.php'>Rent a property</a></p>\n";
echo "<p>Interesting in buying a property? <br> check out our properties and make an appointment to meet with the agent <br> <a href='buy_booking.php?customer_id=$userId'> Buy property</a></p>\n";
echo "<p><a href='search_property.php'>Browse All Properties</a></p>\n";
echo "<a href='logout.php'>Logout</a>";

echo "</body>\n";
echo "</html>\n";


    
} else {
    echo "Please log in.";
}


?>


<!-- customer_id=$userId -->