<?php
include("dbconfig.php");
$con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
    or die("Cannot connect to the database.");

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password']; // For educational purposes, storing as plain text
    $name = $_POST['name'];
    $role = $_POST['role'];

    $sql = "INSERT INTO Users (login, password, name, role) VALUES ('$login', '$password', '$name', '$role')";
    $result = mysqli_query($con, $sql);

    if ($result) {
        echo "Registration successful!";
    } else {
        echo "An error occurred: " . mysqli_error($con);
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>User Registration</h2>
    <form method="POST" action="register.php">
        Login ID: <input type="text" name="login" required><br>
        Password: <input type="password" name="password" required><br>
        Name: <input type="text" name="name" required><br>
        Role: <select name="role" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>
