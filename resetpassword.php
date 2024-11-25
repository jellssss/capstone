<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "revised";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(!isset($_GET["code"])){
	exit("Page Not Found...");
}

$code = $_GET["code"];

$getEmailQuery = mysqli_query($conn, "SELECT email FROM resetpasswords WHERE code='$code'");
if(mysqli_num_rows($getEmailQuery) == 0){
	exit("Page Not Found...");
}
if(isset($_POST["password"])){
	$pw = $_POST["password"];
	
	$row = mysqli_fetch_array($getEmailQuery);
	$email = $row["email"];
	
	$query = mysqli_query($conn, "UPDATE admin_login SET password='$pw' WHERE email='$email'");
	
	if($query){
		$query = mysqli_query($conn, "DELETE FROM resetpasswords WHERE code='$code'");
		exit("Password has been successfully updated, Please login again");
	}
	else{
		exit("Something went wrong, please try again...");
	}
}
?>
<form method="POST">
	<input type="password" name="password" placeholder="New Password" required>
	<br>
	<input type="submit" name="submit" value="Update Password">
</form>