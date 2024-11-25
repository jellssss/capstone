<?php
// Start the session at the very top
session_start();

include 'config.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit(); // Stop further execution
}

// Handle add employee form submission
if (isset($_POST['add_employee'])) {
    // Get form data
    $employee_id = $_POST['employee_id'];
    $age_group = $_POST['age_group'];
    $distance_from_home = $_POST['distance_from_home'];
    $gender = $_POST['gender'];
    $job_category = $_POST['job_category'];
    $marital_status = $_POST['marital_status'];
    $salary = $_POST['salary'];
    $status = 'Not Resigned'; // Example status; you can modify as needed

    // Insert new employee record into database
    $stmt = $conn->prepare("INSERT INTO empreco (Employee_ID,  Age_Group, DistanceFromHome, Gender,  Job_Category, Marital_Status,  Salary, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $employee_id,  $age_group, $distance_from_home, $gender,  $job_category, $marital_status,  $salary, $status);

    if ($stmt->execute()) {
        $message[] = 'New employee record added successfully!';
    } else {
        $message[] = 'Error adding new employee: ' . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
	<style>
	
body {
    background-color: #f8f9fa;
    font-family: 'Arial', sans-serif;
}

.main {
    margin-left: 250px; /* Align content next to the sidebar */
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Full viewport height */
}

.container {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
    padding: 30px;
    width: 100%;
    max-width: 600px; /* Similar to update-profile */
}

h2 {
    color: #343a40;
    margin-bottom: 30px;
    text-align: center;
}

.form-group label {
    font-weight: bold;
    color: #495057;
}

.inputBox {
    margin-bottom: 15px;
}

.inputBox span {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    text-align: left; /* Align the span text to the left */
}

.form-control {
    width: 100%; /* Ensure full width */
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 10px;
}

.btn-primary {
    background-color: #4fc3f7; /* Updated to match */
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    width: 100%;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0288d1; /* Updated hover color */
}

.delete-btn {
    background-color: #f44336;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    margin-top: 10px;
    display: inline-block;
}

.delete-btn:hover {
    background-color: #c62828;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 20px;
}


	
	</style>
</head>
<body>
    <div class="container mt-5">
        <h2>Add New Employee Record</h2>
        <?php
        if (isset($message)) {
            foreach ($message as $msg) {
                echo '<div class="alert alert-info">' . htmlspecialchars($msg) . '</div>';
            }
        }
        ?>
        <form method="POST" action="">
            <div class="form-group">

                <label for="employee_id">Employee ID</label>
                <input type="text" class="form-control" id="employee_id" name="employee_id" required>
            </div>
           
			 <div class="form-group">
				<label for="age_group">Age Group</label>
				<select class="form-control" id="age_group" name="age_group" required>
					 <option value="select">- Select -</option>
					<option value="22-28">22-28</option>
					<option value="29-35">29-35</option>
					<option value="36-42">36-42</option>
					<option value="43-49">43-49</option>
					<option value="49-56">49-56</option>
					<option value="Above 56">Above 56</option>
				</select>
			</div>
           <div class="form-group">
				<label for="distance_from_home">Distance from Home</label>
				<select class="form-control" id="distance_from_home" name="distance_from_home" required>
				<option value="select">- Select -</option>
				<option value="0-136 km">0-136 km</option>
				<option value="137-272 km">137-272 km</option>
				<option value="273-408 km">273-408 km</option>
				<option value="409-544 km">409-544 km</option>
				<option value="545-680 km">545-680 km</option>
				<option value="Above 1360 km">Above 1360 km</option>
			</select>
		</div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="select">- Select -</option>
					<option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            
			<div class="form-group">
				<label for="job_category">Job Category</label>
				<select class="form-control" id="job_category" name="job_category" required>
				<option value="select">- Select -</option>
				<option value="Management/Project Roles">Management/Project Roles</option>
				<option value="Software Development/Engineering">Software Development/Engineering</option>
				<option value="Quality Assurance (QA)/Testing">Quality Assurance (QA)/Testing</option>
				<option value="Support/Administration Roles">Support/Administration Roles</option>
				<option value="Business/Analytical Roles">Business/Analytical Roles</option>
                <option value="Human Resources/Recruitment">Human Resources/Recruitment</option>
                <option value="DevOps/Cloud Roles">DevOps/Cloud Roles</option>
                <option value="Other/General Roles">Other/General Roles</option>
                <option value="Sales/Customer Support Roles">Sales/Customer Support Roles</option>
                <option value="Security/Identity Management">Security/Identity Management</option>
                <option value="System Analysis/Architecture Roles">System Analysis/Architecture Roles</option>
			</select>
		</div>

            <div class="form-group">
                <label for="marital_status">Marital Status</label>
                <select class="form-control" id="marital_status" name="marital_status" required>
                    <option value="select">- Select -</option>
					<option value="Single">Single</option>
                    <option value="Married">Married</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="salary">Salary</label>
                <select class="form-control" id="salary" name="salary" required>
                    <option value="select">- Select -</option>
					<option value="18,000 - 47,500" >18,000 - 47,500</option>
                    <option value="47,501 - 77,000">47,501 - 77,000</option>
                    <option value="77,001 - 106,500">77,001 - 106,500</option>
                    <option value="106,501 - 136,000">106,501 - 136,000</option>
                    <option value="136,001 - 165,500">136,001 - 165,500</option>
                    <option value="165,501 - 195,000">165,501 - 195,000</option>
                    <option value="Above 195,000">Above 195,000</option>
                </select>
            </div>
            <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
			<button type="button" onclick="window.location.href='activetwo.php'" class="delete-btn btn-block">Go back</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
