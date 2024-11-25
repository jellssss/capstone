<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];
$employee_id = $_GET['id'];

// Fetch employee details for editing
$sql = "SELECT * FROM empreco WHERE Employee_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
if (isset($_POST['update_employee'])) {
    // Get and sanitize form data
    $update_age_group = $_POST['update_age_group'];
    $update_distance = $_POST['update_distance'];
    $update_salary = $_POST['update_salary'];
    $update_gender = $_POST['update_gender'];
    $update_marital = $_POST['update_marital'];
    $update_job = $_POST['update_job'];
    
    
    // Update employee details in the database
    $update_sql = "UPDATE empreco SET Gender = ?, Job_Category =?, Marital_Status = ?, Salary = ?, Age_Group = ?, DistanceFromHome = ? WHERE Employee_ID = ?";
    $update_stmt = $conn->prepare($update_sql);

    // Bind parameters, making sure that $update_job is properly handled as NULL
    $update_stmt->bind_param("ssssssi", $update_gender, $update_job, $update_marital, $update_salary, $update_age_group, $update_distance, $employee_id);

    if ($update_stmt->execute()) {
        $message = "Employee details updated successfully!";
    } else {
        $message = "Error updating employee details: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
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
        <h2>Edit Employee Details</h2>
        <?php if (isset($message)) { echo "<div class='alert alert-info'>$message</div>"; } ?>
        <form action="" method="POST">
            <div class="form-group">
			
			<div class="row">
			<div class="col-md-6">
            <label for="gender">Gender</label>
                  <select class="form-control" name="update_gender" required>
                    <option value="F" <?php if ($employee['Gender'] == 'F') echo 'selected'; ?>>Female</option>
                    <option value="M" <?php if ($employee['Gender'] == 'M') echo 'selected'; ?>>Male</option>
                  </select>
               
				<label for="marital_status">Marital Status</label>
                <label for="marital_status">Marital Status</label>
                  <select class="form-control" name="update_marital" required>
                    <option value="Married" <?php if ($employee['Marital_Status'] == 'Married') echo 'selected'; ?>>Married</option>
                    <option value="Single" <?php if ($employee['Marital_Status'] == 'Single') echo 'selected'; ?>>Single</option>
                  </select>



                  <label for="salary">Salary</label>
				<select class="form-control" name="update_salary" required>
					<option value="18,000 - 47,500" <?php if($employee['Salary'] == '18,000 - 47,500') echo 'selected'; ?>>18,000 - 47,500</option>
					<option value="47,501 - 77,000" <?php if($employee['Salary'] == '47,501 - 77,000') echo 'selected'; ?>>47,501 - 77,000</option>
					<option value="77,001 - 106,500" <?php if($employee['Salary'] == '77,001 - 106,500') echo 'selected'; ?>>77,001 - 106,500</option>
					<option value="106,501 - 136,000" <?php if($employee['Salary'] == '106,501 - 136,000') echo 'selected'; ?>>106,501 - 136,000</option>
					<option value="136,001 - 165,500" <?php if($employee['Salary'] == '136,001 - 165,500') echo 'selected'; ?>>136,001 - 165,500</option>
					<option value="165,501 - 195,000" <?php if($employee['Salary'] == '165,501 - 195,000') echo 'selected'; ?>>165,501 - 195,000</option>
                    <option value="Above 195,000" <?php if($employee['Salary'] == 'Above 195,000') echo 'selected'; ?>>Above 195,000</option>
				</select>
               
				
			</div>
			<div class="col-md-6">

          
				
                <label for="age_group">Age Group</label>
				<select class="form-control" name="update_age_group" required>
					<option value="22-28" <?php if($employee['Age_Group'] == '22-28') echo 'selected'; ?>>22-28</option>
					<option value="29-35" <?php if($employee['Age_Group'] == '29-35') echo 'selected'; ?>>29-35</option>
					<option value="36-42" <?php if($employee['Age_Group'] == '36-42') echo 'selected'; ?>>36-42</option>
					<option value="43-49" <?php if($employee['Age_Group'] == '43-49') echo 'selected'; ?>>43-49</option>
					<option value="49-56" <?php if($employee['Age_Group'] == '49-56') echo 'selected'; ?>>49-56</option>
					<option value="Above 56" <?php if($employee['Age_Group'] == 'Above 56') echo 'selected'; ?>>Above 56</option>
				</select>

                <label for="distance_from_home">Distance from Home</label>
				<select class="form-control" name="update_distance" required>
					<option value="0-136 km" <?php if($employee['DistanceFromHome'] == '0-136 km') echo 'selected'; ?>>0-136 km</option>
					<option value="137-272 km" <?php if($employee['DistanceFromHome'] == '137-272 km') echo 'selected'; ?>>137-272 km</option>
					<option value="273-408 km" <?php if($employee['DistanceFromHome'] == '273-408 km') echo 'selected'; ?>>273-408 km</option>
					<option value="409-544 km" <?php if($employee['DistanceFromHome'] == '409-544 km') echo 'selected'; ?>>409-544 km</option>
					<option value="545-680 km" <?php if($employee['DistanceFromHome'] == '544-680 km') echo 'selected'; ?>>545-680 km</option>
					<option value="Above 1360" <?php if($employee['DistanceFromHome'] == 'Above 1360') echo 'selected'; ?>>Above 1360 km</option>
				</select>
				
				<label for="job_category">Job Category</label>
				<select class="form-control" name="update_job" required>
					<option value="Management/Project Roles" <?php if($employee['Job_Category'] == 'Management/Project Roles') echo 'selected'; ?>>Management/Project Roles</option>
					<option value="Software Development/Engineering" <?php if($employee['Job_Category'] == 'Software Development/Engineering') echo 'selected'; ?>>Software Development/Engineering</option>
					<option value="Quality Assurance (QA)/Testing" <?php if($employee['Job_Category'] == 'Quality Assurance (QA)/Testing') echo 'selected'; ?>>Quality Assurance (QA)/Testing</option>
					<option value="Support/Administration Roles" <?php if($employee['Job_Category'] == 'Support/Administration Roles') echo 'selected'; ?>>Support/Administration Roles</option>
					<option value="Business/Analytical Roles" <?php if($employee['Job_Category'] == 'Business/Analytical Roles') echo 'selected'; ?>>Business/Analytical Roles</option>
                    <option value="Human Resources/Recruitment" <?php if($employee['Job_Category'] == 'Human Resources/Recruitment') echo 'selected'; ?>>Human Resources/Recruitment</option>
                    <option value="DevOps/Cloud Roles" <?php if($employee['Job_Category'] == 'DevOps/Cloud Roles') echo 'selected'; ?>>DevOps/Cloud Roles</option>
                    <option value="Other/General Roles" <?php if($employee['Job_Category'] == 'Other/General Roles') echo 'selected'; ?>>Other/General Roles</option>
                    <option value="Sales/Customer Support Roles" <?php if($employee['Job_Category'] == 'Sales/Customer Support Roles') echo 'selected'; ?>>Sales/Customer Support Roles</option>
                    <option value="Security/Identity Management" <?php if($employee['Job_Category'] == 'Security/Identity Management') echo 'selected'; ?>>Security/Identity Management</option>
                    <option value="System Analysis/Architecture Roles" <?php if($employee['Job_Category'] == 'System Analysis/Architecture Roles') echo 'selected'; ?>>System Analysis/Architecture Roles</option>
                    
				</select>
			</div>

			</div>
            </div>
			<input type="submit" value="Update Employee" name="update_employee" class="btn btn-primary btn-block">
			<button type="button" onclick="window.location.href='activetwo.php'" class="delete-btn btn-block">Go back</button>


        </form>
    </div>
</body>
</html>
