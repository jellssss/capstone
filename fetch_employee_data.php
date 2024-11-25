<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "revised";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$empID = $_GET['empID'];
$sql = "SELECT * FROM empreco WHERE empID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $empID);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

echo json_encode([
    'success' => $record ? true : false,
    'record' => $record
]);

$stmt->close();
$conn->close();
?>
