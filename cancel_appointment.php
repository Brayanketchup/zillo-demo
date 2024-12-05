<?php
include("dbconfig.php");

// Check if the 'user_session' cookie is set
if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $userId = $userSession['user_id'];
    $userRole = $userSession['role'];
    $userName = $userSession['name'];

    echo "Welcome, $userName ($userRole)<br>";

    // Connect to the database
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
        or die("<br>Cannot connect to DB");

    // Check if the required POST data is provided
    if (isset($_POST["appointment_id"])) {
        $appointment_id = mysqli_real_escape_string($con, $_POST["appointment_id"]);

        // Delete the appointment
        $delete_query = "DELETE FROM appointments WHERE appointment_id = '$appointment_id' AND customer_id = '$userId'";
        if (mysqli_query($con, $delete_query)) {
            echo "Appointment successfully canceled.";
        } else {
            echo "Error canceling appointment: " . mysqli_error($con);
        }

        // Redirect back to the main page
        header("Location: buy_booking.php");
        exit;
    } else {
        echo "Invalid request.";
    }

    // Close the database connection
    mysqli_close($con);
} else {
    echo "Please log in to access this page.";
    echo "<br><a href='login.php'>Log in</a>";
}
?>
