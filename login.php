<?php
echo "<HTML>\n";

include("dbconfig.php");
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("<br>Cannot connect to DB");

// Retrieve user credentials from URL parameters
$login = mysqli_real_escape_string($con, $_GET["login"]);
$password = mysqli_real_escape_string($con, $_GET["password"]);

// SQL query to authenticate user
$sql = "SELECT * FROM Users WHERE login = '$login' AND password = '$password'";
$result = mysqli_query($con, $sql);
$num = mysqli_num_rows($result);

// Check if credentials are valid
if ($num > 0) {
    $userDetails = mysqli_fetch_assoc($result);
    $userId = $userDetails["user_id"];
    $userName = $userDetails["name"];
    $userRole = $userDetails["role"];

    // Set a cookie to store user information
    $cookieName = "user_session";
    $cookieValue = json_encode([
        "user_id" => $userId,
        "role" => $userRole,
        "name" => $userName,
    ]);
    setcookie($cookieName, $cookieValue, time() + (86400 * 7), "/"); // Expires in 7 days, available site-wide

    echo "<br>Welcome, <strong>$userName</strong>!<br>";
    echo "<br><a href='logout.php'>Logout</a>\n";

    // Redirect to dashboard based on user role
    if ($userRole === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
} else {
    echo "<br>Login failed. Please check your username and password.";
    mysqli_close($con);
}

mysqli_close($con);
echo "</HTML>\n";
?>
