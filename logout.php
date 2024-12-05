<?php
include("dbconfig.php");

// Define the home page URL
$home_page = 'index.html';

// Clear the user session cookie by setting its expiration time in the past
if (isset($_COOKIE['user_session'])) {
    setcookie('user_session', '', time() - 3600, '/'); // Expire the cookie
}

// Connect to the database and immediately disconnect
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);
if ($con) {
    mysqli_close($con);
}

// Inform the user they have successfully logged out
echo "<br>You have successfully logged out.<br>";
echo "<br><a href='$home_page'>Go to Project Home Page</a>";
?>
