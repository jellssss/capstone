<?php
// Start the session at the very top
session_start();

include 'config.php';

$user_id = $_SESSION['user_id'];

// Redirect to login page if user is not logged in
if(!isset($user_id)){
   header('location:login.php');
   exit(); // Stop further execution
}

// Logout functionality
if(isset($_GET['logout'])){
   unset($_SESSION['user_id']);
   session_destroy();
   header('location:login.php');
   exit(); // Stop further execution
}

// Handle profile update form submission
if(isset($_POST['update_profile'])){
    // Get form data
    $update_name = $_POST['update_name'];
    $update_email = $_POST['update_email'];
    $update_department = $_POST['update_department'];
    $update_contact = $_POST['update_contact'];
    $update_role = $_POST['update_role'];

    // Update user profile data
    $stmt = $conn->prepare("UPDATE `admin_login` SET name = ?, email = ?, department = ?, jobrole = ?, contact = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $update_name, $update_email, $update_department, $update_role, $update_contact, $user_id);
    if($stmt->execute()){
        $profile_updated = true;
    } else {
        $message[] = 'Error updating profile: ' . $conn->error;
    }

    // Handle image upload
    $update_image = $_FILES['update_image']['name'];
    $update_image_size = $_FILES['update_image']['size'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_folder = 'uploaded_img/'.$update_image;

    if(!empty($update_image)){
        if($update_image_size > 2000000){
            $message[] = 'Image is too large';
        } else {
            // Move uploaded image file
            if(move_uploaded_file($update_image_tmp_name, $update_image_folder)){
                // Update image path in database
                $stmt = $conn->prepare("UPDATE `admin_login` SET image = ? WHERE id = ?");
                $stmt->bind_param("si", $update_image, $user_id);
                if(!$stmt->execute()){
                    $message[] = 'Error updating image: ' . $conn->error;
                }
            } else {
                $message[] = 'Error moving uploaded image file';
            }
        }
    }

    // If profile update was successful and no image error messages were added
    if(isset($profile_updated) && empty($message)){
        $message[] = 'Profile updated successfully!';
    }
}

// Fetch user details
$user_query = $conn->prepare("SELECT image, name FROM `admin_login` WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_details = $user_result->fetch_assoc();
?>



	<!DOCTYPE html>
	<html lang="en">
	<head>
	  <meta charset="UTF-8">
	  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	  <title>Dashboard</title>
	  <!-- Bootstrap CSS -->
	  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
	  <!-- Font Awesome CSS -->
	  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
	  <!-- Boxicons CSS -->
	  <link href='https://unpkg.com/boxicons@latest/css/boxicons.min.css' rel='stylesheet'>
	  <style>
	body {
		background-color: #ffff; /* Light blue background for the page */
	}

	.sidebar {
		height: 100vh;
		width: 240px;
		position: fixed;
		top: 0;
		left: 0;
		background-color: #23A7AB; /* White sidebar background color */
		padding-top: 20px;
		display: flex;
		flex-direction: column;
		border-right: 1px solid #ddd; /* Light gray border for subtle separation */
		box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Subtle shadow for the sidebar */
	}

	.sidebar .logo {
		display: flex;
		align-items: center;
		padding: 15px;
		color: #000;
	}

	.sidebar .logo img {
		height: 40px;
		margin-right: 10px;
	}

	.sidebar .logo h1 {
		font-size: 1.25rem;
		margin: 0;
	}

	.sidebar .user-info {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 15px;
		margin-top: 20px;
		margin-bottom: 20px;
	}

	.sidebar .user-info img {
		height: 120px;
		width: 120px;
		border-radius: 50%;
		margin-bottom: 10px; /* Space between the image and email */
	}

	.sidebar .user-info p {
		margin: 0;
		color: #ffff;
		text-align: center; /* Center the email text */
	}

	.sidebar a {
		color: white; /* Dark text color for the links */
		text-decoration: none;
		display: block;
		padding: 10px 15px;
	}

	.sidebar a:hover {
		background-color: #2ba9a4; /* Light blue background on hover */
		color: #000;
	}

	.sidebar .active {
		background-color: #f7f7f7; /* Light blue color for active link */
		color: black;
	}

	.sidebar .section-header {
		color: white; /* Slightly darker text color for section headers */
		padding: 10px 15px;
		font-size: 0.9rem;
	}

	.sidebar-footer {
		margin-top: auto;
	}

	.dashboard {
		margin-left: 250px; /* Sidebar width */
		transition: margin-left 0.3s;
		padding: 20px;
	}

	.dashboard .title {
		font-size: 28px;
		font-weight: 600;
		color: #23A7AB; 
		margin-bottom: 20px;
	}

	.info-data {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
}
	

    .card {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    width: 23%;
    height:120px;
    background: #fffff;
    position: relative; /* Ensure pseudo-element is positioned relative to the card */
    margin-bottom:10px;
}
.card::before {
    content: ''; /* Empty content for pseudo-element */
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 5px; /* Width of the border line */
    background-color: #23A7AB; /* Color of the left border */
    border-radius: 5px 0 0 5px; /* Rounded corners on the left side */
    z-index: 1; /* Ensure it's behind the text */
}
.card .head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative; /* Ensure text is on top of pseudo-element */
    z-index: 2; /* Ensure it's on top of the pseudo-element */
}

.progress {
    display: block;
    width: 90%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 5px;
    margin: 5px 0;
    position: relative;
}
.progress2 {
    display: block;
    width: 100%;
    height: 10px;
    background: #e0e0e0;
    border-radius: 5px;
    margin: 10px 0;
    position: relative;
}

.progress-bar {
    height: 80%;
    background: #23A7AB;
    width: 0;
    transition: width 0.5s;
    border-radius: 2px;
}

.label {
    display: block;
    text-align: right;
    font-weight: bold;
}
.card .progress::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 90%;
    background: #23A7AB;
    width: 3.4%;
	border-radius: 8px;
}
.card .progress2::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: #23A7AB;
    width: 96.6%;
	border-radius: 10px;
}
.card .head h2 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.card .head h2 p {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}
	.sidebar.close ~ .dashboard {
		margin-left: 70px;
	}

main .data6 {
    display: flex;
    gap: 10px; /* Adjusted spacing between columns */
    margin-top: 15px;
    flex-wrap: wrap;
    width: 100%;
}

main .data6 .content-data3,
main .data6 .content-data7,
main .data6 .content-data8,
main .data6 .content-data4 {
    flex-grow: 1;
    flex-basis: 200px; /* Adjusted base size for better responsiveness */
    padding: 25px; /* Increased padding for more spacing */
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px; /* Space between rows */
}

main .data6 .content-data3 .head,
main .data6 .content-data7 .head,
main .data6 .content-data8 .head,
main .data6 .content-data4 .head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px; /* Increased space below title */
}

main .data6 .content-data3 .head h3,
main .data6 .content-data7 .head h3,
main .data6 .content-data8 .head h3,
main .data6 .content-data4 .head h3 {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

main .data6 .content-data3 .chart3,
main .data6 .content-data7 .chart5,
main .data6 .content-data8 .chart8,
main .data6 .content-data4 .chart13 {
    width: 100%;
    height: 150px; /* Reduced height for balanced look */
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 10px; /* Consistent spacing */
}

main .data6 .content-data3 .chart3 canvas,
main .data6 .content-data7 .chart5 canvas,
main .data6 .content-data8 .chart8 canvas,
main .data6 .content-data4 .chart13 canvas{
    width: 100% !important; /* Increase canvas width */
    height: 100% !important; /* Adjust canvas height for more balance */
}
  main .data7 {
            display: flex;
            gap: 10px; /* Space between columns */
            margin-top: 1px;
            flex-wrap: wrap; /* Allows wrapping on smaller screens */
            width: 100%;
            max-width: 1200px; /* Set a maximum width for the container */
            margin-left: auto; /* Center align the container */
            margin-right: auto; /* Center align the container */
            padding: 10px;
        }

        main .data7 .content-data5, 
        main .data7 .content-data6 {
            flex-grow: 1;
            flex-basis: calc(50% - 10px); /* Make items take 50% of the container width */
            padding: 20px; /* Adjusted padding for smaller spacing */
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px; /* Space between rows */
        }

        main .data7 .content-data5 .head, 
        main .data7 .content-data6 .head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px; /* Consistent spacing below titles */
        }

        main .data7 .content-data5 .head h3, 
        main .data7 .content-data6 .head h3 {
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }

        main .data7 .content-data5 .chart14, 
        main .data7 .content-data6 .chart15 {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px; /* Consistent top margin */
        }

        main .data7 .content-data5 .chart14 {
            height: 300px; /* Specific height for smaller charts */
        }

        main .data7 .content-data5 .chart14 canvas {
            width: 100% !important; /* Responsive canvas width */
            height: 100% !important; /* Responsive canvas height */
        }

        main .data7 .content-data6 .chart15 {
            height: 350px; /* Larger height for detailed charts */
        }

        main .data7 .content-data6 .chart15 #resignation-rates-chart {
            width: 100%;
            height: 80%; /* Scales with container */
        }

        @media screen and (max-width: 768px) {
            main .data7 .content-data5, 
            main .data7 .content-data6 {
                flex-basis: 100%; /* On smaller screens, take full width */
            }

	.user-profile p {
		font-size: 14px;
		color: #333;
	}

	.rotate-icon {
		transform: rotate(180deg); /* Rotates the icon 180 degrees */
		transition: transform 0.3s ease; /* Smooth rotation */
	}

	.add-section {
		width: 100%;
		background: #fff;
		margin: 0px auto;
		padding: 0px;
		border-radius: 5px;
	}
	  
	  .add-section input {
		display: block;
		width: 95%;
		height: 40px;
		margin: 10px auto;
		border: 2px solid #ccc;
		font-size: 16px;
		border-radius: 5px;
		padding: 0px 5px;
	  }
	  
	  .add-section button {
		display: block;
		width: 95%;
		height: 40px;
		margin: 0px auto;
		border: none;
		outline: none;
		background: #0088FF;
		color: #fff;
		font-family: sans-serif;
		font-size: 16px;
		border-radius: 5px;
		cursor: pointer;
	  }
	  
	  .add-section button:hover {
		box-shadow: 0 2px 2px 0 #ccc, 0 2px 3px 0 #ccc;
		opacity: 0.7;
	  }
	  
	  .add-section button span {
		border: 1px solid #fff;
		border-radius: 50%;
		display: inline-block;
		width: 18px;
		height: 18px;
	  }
	  
	  #errorMes {
		display: block;
		background: #f2dede;
		width: 95%;
		margin: 0px auto;
		color: rgb(139, 19, 19);
		padding: 10px;
		height: 35px;
	  }

	.show-todo-section {
		width: 100%;
		height: 302px;
		overflow-y: auto;
		background: #fff;
		margin: 8px auto;
		padding: 10px;
		border-radius: 5px;
		
	  }
	  
	  .todo-item {
		width: 100%;
		height: auto;
		margin: 10px auto;
		padding: 20px 10px;
		box-shadow: 0 4px 8px 0 #ccc, 0 6px 20px 0 #ccc;
		border-radius: 5px;
		overflow-y: hidden;
	  }
	  
	  .todo-item h2 {
		display: inline-block;
		padding: 5px 0px;
		font-size: 17px;
		font-family: sans-serif;
		color: #555;
	  }
	  
	  .todo-item small {
		display: block;
		width: 100%;
		padding: 5px 0px;
		color: #888;
		padding-left: 30px;
		font-size: 14px;
		font-family: sans-serif;
	  }
	  
	  .remove-to-do {
		display: block;
		float: right;
		width: 20px;
		height: 20px;
		font-family: sans-serif;
		color: rgb(139, 97, 93);
		text-decoration: none;
		text-align: right;
		padding: 0px 5px 8px 0px;
		border-radius: 50%;
		transition: background 0.3s;
		cursor: pointer;
		margin-top: 10px;
	  }
	  
	  .remove-to-do:hover {
		background: rgb(139, 97, 93);
		color: #fff;
	  }
	  
	  .checked {
		color: #999 !important;
		text-decoration: line-through;
	  }
	  
	  .todo-item input {
		margin: 0px 5px;
	  }
	.empty {
		display: flex;
		font-family: sans-serif;
		font-size: 16px;
		text-align: center;
		color: #cccc;
		height: 234px;
		align-items: center;
		justify-content: center;
	  }

	.checkbox-container {
		display: flex;
		align-items: center;
		justify-content: flex-start; 
		padding: 5px 0; 
	}

	.checkbox-container input[type="checkbox"] {
		width: 16px;  /* Adjust the size of the checkbox */
		height: 16px; /* Adjust the size of the checkbox */
		margin-right: 10px; /* Space between checkbox and h2 */
	}

 







	  </style>
	</head>
	<body>
   


	<?php
	// Debugging line to get the current page for active link highlighting
	$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

	// Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "revised";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


	// Check if the user is logged in
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
	} else {
		die("User not logged in.");
	}

	// Now you can perform your query for the sidebar
	$sql = "SELECT * FROM `admin_login` WHERE `ID` = '$user_id'";
	$result = $conn->query($sql);

	// Check if the query was successful and fetch data
	if ($result) {
		$user = $result->fetch_assoc();
	} else {
		echo "Error: " . $conn->error;
	}

	// Always close the database connection when you're done
	$conn->close();
	?>

   

	<div class="sidebar">
    
		

	<div class="user-info">
		<img src="<?php echo !empty($user_details['image']) ? 'uploaded_img/'.$user_details['image'] : 'images/default-avatar.png'; ?>" alt="User Image">
		<p><?php echo htmlspecialchars($user_details['name']); ?></p>
	</div>


	<?php include 'timer.php'; ?>

    

		<!-- Sidebar Links -->
		<a href="Dash.php" class="<?= $current_page == 'Dash.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i>&nbsp;&nbsp;&nbsp; DASHBOARD</a>

		<a href="#employeeRecords" data-toggle="collapse" 
	   class="<?= $current_page == 'active.php' || $current_page == 'resigned.php' ? 'active' : '' ?>" 
	   aria-expanded="false">
	   <i class="fas fa-users"></i>&nbsp;&nbsp;&nbsp; EMPLOYEE RECORDS
	   <!-- Caret icon for dropdown -->
	   &nbsp;&nbsp;<i class="fas fa-caret-down" id="employeeRecordsIcon"></i>
	</a>
	<div id="employeeRecords" class="collapse">
		<a href="active.php" class="section-header <?= $current_page == 'active.php' ? 'active' : '' ?>">&nbsp;&nbsp;&nbsp;ACTIVE EMPLOYEES</a>
		<a href="resigned.php" class="section-header <?= $current_page == 'resigned.php' ? 'active' : '' ?>">&nbsp;&nbsp;&nbsp;RESIGNED EMPLOYEES</a>
	</div>


		<a href="statistics.php" class="<?= $current_page == 'statistics.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp;STATISTICS</a>

		<div class="sidebar-footer mt-auto">
			<a href="user.php" class="<?= $current_page == 'user.php' ? 'active' : '' ?>"><i class="fas fa-user-circle"></i>&nbsp;&nbsp;&nbsp;&nbsp; USER DETAILS</a>
			<a href="logout.php"><i class="fas fa-sign-out-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp; LOGOUT</a>
		</div>
	</div>

    
	

<div class="dashboard">

  
  <main>

  <div>
  
    <h1 class="title">DASHBOARD</h1>
<!-- Year Selection Dropdown with Container -->


</div>


<div class="info-data">
        <div class="card">
            <div class="head">
                <div>
                    <h2 id="resigned-employees"><i class="bx bx-user"></i>254</h2>
                    <p>No. of Resigned Employees</p>
                </div>
               <!---- <i class='bx bx-trending-up icon'></i>--->
            </div>
        </div>
        <div class="card">
            <div class="head">
                <div>
                    <h2 id="top-job-rol"><i class="bx bx-briefcase"></i>Quality Assurance (QA)/Testing</h2>
                    <p>Top Job role Resignation</p>
                </div>
               <!---- <i class='bx bx-trending-down icon down'></i>--->
            </div>
        </div>
        <div class="card">
            <div class="head">
                <div>
                    <h2><p>Resignation Rate</p></h2>
                </div>
               <!---- <i class='bx bx-trending-up icon'></i>--->
            </div>
            <div class="progress">
                <div class="progress-bar" id="resignation-rate"></div>
            </div>
            <span class="label" id="resignation-rate-label">27%</span>
        </div>
        <div class="card">
            <div class="head">
                <div>
                    <h2 id="top-department"><i class="bx bx-building"></i>IT Staff Augmentation</h2>
                    <p>Top department Resignation</p>
                </div>
                <!----  <i class='bx bx-trending-up icon'></i>--->
            </div>
        </div>
    </div>


<div class="data6">
  <div class="content-data7">
    <div class="head">
      <h3>Resigned and Active Employees Distribution</h3>
    </div>
    <div class="chart5">
      <canvas id="resigned-active-chart"></canvas>
    </div>
  </div>

  <div class="content-data8">
    <div class="head">
      <h3>Resignations by Department</h3>
    </div>
    <div class="chart8">
      <canvas id="department-status-bar-chart"></canvas>
    </div>
  </div>

  <div class="content-data3">
    <div class="head">
      <h3>Resignation By Age Group</h3>
    </div>
    <div class="chart3">
      <canvas id="age-chart"></canvas>
    </div>
  </div>
  <div class="content-data4">
    <div class="head">
      <h3>Distribution of Salary</h3>
    </div>
    <div class="chart13">
      <canvas id="salaryChart"></canvas>
    </div>
  </div>
</div>

   <div class="data7">
    <!-- Chart 1 -->
    <div class="content-data5">
        <div class="head">
            <h3>Distribution of Tenure (Months)</h3>
        </div>
        <div class="chart14">
            <canvas id="tenureChart"></canvas>
        </div>
    </div>

    <!-- Chart 2 -->
    <div class="content-data6">
        <div class="head">
            <h3>Resignation Rates by Job Category</h3>
        </div>
        <div class="chart15">
            <div id="resignation-rates-chart"></div>
        </div>
    </div>
</div>

</div>

  
   

  </main>
</div>




        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-boxplot@3.0.0"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
         <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix"></script>
         <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
		 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@1.0.0/dist/chartjs-chart-matrix.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    	<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-3d"></script>





	
<script>
    $(document).ready(function(){
        // Labels for the X-axis (Age Groups)
        const ageLabels = ['22-28', '29-35', '36-42', '43-49', '49-56', 'Above 56'];

        // Data for each age group
        const resignationData = [113, 113, 19, 7, 2, 0]; // Resigned data for each age group
        const activeData = [83, 56, 19, 11, 5, 1]; // Example Active data for each age group

        // Function to render the chart with the new data structure
        function renderChart() {
            const chartData = {
                labels: ageLabels,
                datasets: [
                    {
                        label: 'Resigned',
                        data: resignationData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', // Blue for Resigned
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Not Resigned',
                        data: activeData,
                        backgroundColor: '#00e396', // Green for Active
                        borderColor: '#00e396',
                        borderWidth: 1
                    }
                ]
            };

            const configClusteredBar = {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 10 // Adjust legend font size
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 10 // Adjust X-axis font size
                                }
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 10 // Adjust Y-axis font size
                                }
                            }
                        }
                    }
                }
            };

            // If a chart instance exists, destroy it before rendering the new one
            if (window.chartInstance) {
                window.chartInstance.destroy();
            }

            // Render the new chart
            const ctx = document.getElementById('age-chart').getContext('2d');
            window.chartInstance = new Chart(ctx, configClusteredBar);
        }

        // Initial chart rendering
        renderChart();
    });
</script>






<script>
    // Data for each year
    const yearData = {
        
        2021: [6, 3, 10, 6, 7, 7, 4, 3, 4, 5, 7, 6]
     
    };

    // Initialize chart
    let ctx = document.getElementById('monthly-resignation-chart').getContext('2d');
    let monthlyResignationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            datasets: [{
                label: 'Resignations',
                data: yearData[2021], // Default data for 2023
                backgroundColor: 'rgba(0, 227, 150, 0.3)', // Lighter color
						borderColor: 'rgba(0, 227, 150, 1)', // Darker color
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false // Hide the legend
                }
            },
 
    }
});

    // Update chart when year is selected
    document.getElementById('year-selector').addEventListener('change', function() {
        const selectedYear = this.value;
        monthlyResignationChart.data.datasets[0].data = yearData[selectedYear];
        monthlyResignationChart.update();
    });
</script>




<script>
    document.addEventListener("DOMContentLoaded", function () {
    var options = {
        chart: {
            type: 'bar',
            height: '80%', // Dynamically fill the container's height
            width: '100%',  // Dynamically fill the container's width
            toolbar: { show: false } // Hide toolbar for a clean look
        },
        series: [
            {
                name: 'Resigned',
                data: [14, 4, 4, 9, 0, 107, 0, 1, 66, 47, 2]
            },
            {
                name: 'Not Resigned',
                data: [12, 8, 6, 15, 3, 23, 8, 0, 60, 29, 11]
            }
        ],
        xaxis: {
            categories: [
                'Business/Analytical Roles', 'DevOps/Cloud Roles', 'Human Resources/Recruitment',
                'Management/Project Roles', 'Other/General Roles', 'Quality Assurance (QA)/Testing',
                'Sales/Customer Support Roles', 'Security/Identity Management',
                'Software Development/Engineering', 'Support/Administration Roles', 'System Analysis/Architecture Roles'
            ],
            labels: {
                rotate: -45, // Rotate labels for readability
                style: {
                    fontSize: '10px', // Font size for axis labels
                    colors: '#333'   // Color of labels
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '10px',
                    colors: '#333'
                }
            }
        },
        grid: {
            show: true, // Enable grid lines for better readability
            borderColor: '#f1f1f1'
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%', // Adjust column width for better spacing
                dataLabels: { position: 'top' }
            }
        },
        fill: { colors: ['#36A2EB', '#00e396'] },
        dataLabels: {
            enabled: true,
            formatter: function (val) { return val; }, // Show values above bars
            offsetY: -10,
            style: {
                fontSize: '10px',
                colors: ["#304758"]
            }
        },
        legend: {
            position: 'top', // Move legend to the top
            horizontalAlign: 'center'
        },
        tooltip: {
            y: {
                formatter: function (val) { return val + " employees"; }
            }
        }
    };

    // Render chart inside the specified container
    var chart = new ApexCharts(document.querySelector("#resignation-rates-chart"), options);
    chart.render();
});
</script>








<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('department-status-bar-chart').getContext('2d');

        var data = {
            labels: ['IT Staff Augmentation', 'IN-HOUSE'], // Department labels on the X-axis
            datasets: [
                {
                    label: 'Resigned', // Bar for Active employees
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Blue for Active
                    borderColor: 'rgba(54, 162, 235, 1)', // Blue border
                    borderWidth: 1,
                    data: [254, 0] // Data for Active across IT Staff Augmentation and IN-HOUSE
                },
                {
                    label: 'Not Resigned', // Bar for Resigned employees
                    backgroundColor: '#00e396', // Green for Resigned
                    borderColor: '#00e396', // Green border
                    borderWidth: 1,
                    data: [122, 53] // Data for Resigned across IT Staff Augmentation and IN-HOUSE
                }
            ]
        };

        var options = {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top', // Legend at the top for Active and Resigned
                    labels: {
                        font: {
                            size: 10 // Smaller font size for legend
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: 10 // Smaller font size for X-axis labels
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: 10 // Smaller font size for Y-axis labels
                        }
                    }
                }
            }
        };

        var departmentStatusBarChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
    });
</script>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('resigned-active-chart').getContext('2d');

        const labels = ['Resigned', 'Active'];
        const data = [254, 175]; // Replace with actual values for resigned and active employees

        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Employee Status',
                data: data,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)', // Blue for Resigned
                    '#00e396'  // Green for Active
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',   // Blue border for Resigned
                    '#00e396'   // Green border for Active
                ],
                borderWidth: 1
            }]
        };

        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false // Hide the legend
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                size: 10 // Smaller font size for X-axis labels
                            }
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 10 // Smaller font size for Y-axis labels
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<script>
  // Data for the Tenure chart
  const tenureData = {
    labels: ["0-24", "25-50", "51-75", "76-100", "101-125", "126-150", "151-175", "176-200"],
    datasets: [
      {
        label: 'Resigned',
        data: [104, 19, 7, 1, 0, 0, 0, 0],
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        stack: 'Stack 0'
      },
      {
        label: 'Not Resigned',
        data: [77, 13, 13, 9, 1, 3, 2, 1],
        backgroundColor: '#00e396',
        stack: 'Stack 0'
      }
    ]
  };

  // Data for the Salary chart
  const salaryData = {
    labels: [
      "18,000 - 47,500", 
      "47,501 - 77,000", 
      "77,001 - 106,500", 
      "106,501 - 136,000", 
      "136,001 - 165,500", 
      "165,501 - 195,000"
    ],
    datasets: [
      {
        label: 'Resigned',
        data: [196, 33, 14, 3, 0, 8],
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        stack: 'Stack 1'
      },
      {
        label: 'Not Resigned',
        data: [97, 34, 16, 10, 7, 11],
        backgroundColor: '#00e396',
        stack: 'Stack 1'
      }
    ]
  };

  // Config and render for Tenure chart
  new Chart(document.getElementById('tenureChart'), {
    type: 'bar',
    data: tenureData,
    options: {
      responsive: true,
      maintainAspectRatio: false, // Needed to control height
      plugins: {
        legend: {
          position: 'top',
        }
      }
    }
  });

  // Config and render for Salary chart
  new Chart(document.getElementById('salaryChart'), {
    type: 'bar',
    data: salaryData,
    options: {
      responsive: true,
      maintainAspectRatio: false, // Needed to control height
      plugins: {
        legend: {
          position: 'top',
          labels: {
            font: {
              size: 12 // Smaller font size for legend
            }
          }
        }
      },
      scales: {
        x: {
          ticks: {
            font: {
              size: 10 // Smaller font size for X-axis labels
            }
          }
        },
        y: {
          ticks: {
            font: {
              size: 10 // Smaller font size for Y-axis labels
            }
          }
        }
      }
    }
  });

  // Set height of canvas elements
  document.getElementById('tenureChart').style.height = '250px';
  document.getElementById('salaryChart').style.height = '250px';
</script>







</body>
</html>