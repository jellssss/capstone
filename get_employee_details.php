<?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "revised";
            
            $conn = new mysqli($servername, $username, $password, $dbname);
            
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

if (isset($_GET['id'])) {
    $employeeID = $_GET['id'];
    $sql = "SELECT * FROM empreco WHERE Employee_ID = '$employeeID' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}

$conn->close();
?>
