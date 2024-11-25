<?php




session_start();
date_default_timezone_set('Asia/Manila');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "revised";





if(isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];
    $conn = new mysqli($servername, $username, $password, $dbname);
    if($conn->connect_error) {
        die("Connection Failed" . $conn->connect_error);
    }
    $stmt = $conn->prepare("UPDATE admin_login SET user_status = 'offline', time_joined = NULL WHERE ID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
}

// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
</head>
<body>

<script>
    // Clear the start time from localStorage
    localStorage.removeItem('startTime');
    // Redirect the user to the login page after a short delay
    setTimeout(function() {
        window.location.href = "index.php";
    }, 1000); // Redirect after 1 second (1000 milliseconds)
</script>

</body>
</html>
