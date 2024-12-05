<?php
include("dbconfig.php");

// $userId = $_GET['user_id'];
// $role = $_GET['role'];

if (isset($_COOKIE['user_session'])) {
    $userSession = json_decode($_COOKIE['user_session'], true);
    $userId = $userSession['user_id'];
    $role = $userSession['role'];
    $userName = $userSession['name'];
    echo "Welcome, $userName ($userRole)";

    if ($role !== 'admin') {
        echo "You must be an admin to access this page.";
        exit;
    }
    
    $con = mysqli_connect($db_hostname, $db_username, $db_password, $db_name)
        or die("<br>Cannot connect to DB");
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $loc = $_POST['loc'];
        $price = $_POST['price'];
        $hometype = $_POST['hometype'];
        $note = $_POST['note'];
        $purpose = $_POST['purpose']; // New variable for purpose
    
        $query = "INSERT INTO properties (loc, price, hometype, note, booked, created_by, purpose) 
                  VALUES ('$loc', $price, '$hometype', '$note', 0, $userId, '$purpose')";
    
        if (mysqli_query($con, $query)) {
            echo "<p>Property added successfully!</p>";
            header("Location: admin_dashboard.php");
            exit;
        } else {
            echo "<p>Error: " . mysqli_error($con) . "</p>";
        }
    }
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Add Property</title>
        <link rel='stylesheet' href='style.css'>
        <script>
            function validateForm() {
                const loc = document.getElementById('loc');
                const price = document.getElementById('price');
                const hometype = document.getElementById('hometype');
                let valid = true;
    
                if (loc.value.trim() === '') {
                    loc.style.border = '2px solid red';
                    valid = false;
                } else {
                    loc.style.border = '';
                }
    
                if (price.value.trim() === '' || price.value <= 0) {
                    price.style.border = '2px solid red';
                    valid = false;
                } else {
                    price.style.border = '';
                }
    
                if (hometype.value.trim() === '') {
                    hometype.style.border = '2px solid red';
                    valid = false;
                } else {
                    hometype.style.border = '';
                }
    
                if (!valid) {
                    alert('Please fill out all required fields correctly.');
                }
                return valid;
            }
        </script>
    </head>
    <body>";
    
    echo "<h2>Add New Property</h2>";
    
    echo "<form method='post' action='' onsubmit='return validateForm()'>
        <label for='loc'>Location:</label>
        <input type='text' id='loc' name='loc' required><br><br>
    
        <label for='price'>Price:</label>
        <input type='number' id='price' name='price' step='0.01' required><br><br>
    
        <label for='hometype'>Home Type:</label>
        <input type='text' id='hometype' name='hometype' required><br><br>
    
        <label for='purpose'>Purpose:</label>
        <select id='purpose' name='purpose' required>  <option value='rent'>Rent</option>
            <option value='sale'>Sale</option>
            <option value='both'>Both</option>
        </select><br><br>
    
        <label for='note'>Note:</label>
        <textarea id='note' name='note'></textarea><br><br>
    
        <input type='hidden' name='created_by' value='" . htmlspecialchars($userId) . "'>
    
        <button type='submit'>Add Property</button>
    </form>";
    
    echo "</body></html>";
    





} else {
    echo "Please log in.";
}



mysqli_close($con);
?>