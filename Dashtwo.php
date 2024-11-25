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
</head>
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
		padding: 20px;
	}

	.dashboard .title {
		font-size: 28px;
		font-weight: 600;
		color: #23A7AB; 
		margin-bottom: 20px;
	}

	main .data{
		margin-top: 36px;
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
		grid-gap: 20px;
	}
	main .chart-container{
		height: 430px;
		width: auto;
		padding: 20px;
		border-radius: 10px;
		background: #ffffff;
		box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.1);
		  
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		align-items: center; /* Center content horizontally */
		position: relative;
	}
	main .chart-container .head{
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 15px;
	}
	main .chart-container .head h3 {
		font-size: 20px;
		font-weight: 600;
		color: #333;
	}
	main .chart-container .chart {
		width: 100%;
		max-width: 300px; /* Set a maximum width */
		height: auto;
		margin: auto;
	}
	#genderChart{
	  height: 100%;
	  width: 100%;
	}
	#ageChart{
	  height: 100%;
	  width: 100%;
	}
	#departmentChart{
	  height: 100%;
	  width: 100%;
	}
	#jobRoleChart{
		height: 100%;
		width: 100%;
	}
	#yearsAtCompanyChart{
		height: 100%;
		width: 100%;
	}
	main .chart-container .yearschart{
		width: 100%;
		max-width: 600px; /* Set a maximum width */
		height: auto;
		margin: auto;
	}

    
/* Container for dropdown and label */
#employee-filter-container {
    display: flex; /* Use Flexbox for alignment */
    justify-content: flex-end; /* Align items to the right */
    align-items: center; /* Center items vertically */
    margin-bottom: 20px;
    font-family: 'Arial', sans-serif;
}

/* Styling the label */
#employee-filter-container label {
    font-size: 17px;
    color: #333;
    margin-right: 10px; /* Space between label and dropdown */
    font-weight: bold;
}

/* Styling the dropdown */
#employee-filter {
    width: 200px;
    height: 34px;
    font-size: 16px;
    color: #444;
    border: 2px solid #23A7AB; 
    border-radius: 3px;
    padding-left: 16px;
    outline: none;
    transition: border-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

/* Dropdown hover effect */
#employee-filter:hover {
    border-color: #007BFF;
}

/* Dropdown focus effect */
#employee-filter:focus {
    border-color: #007BFF;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Optional: Styling the dropdown options */
#employee-filter option {
    background-color: #fff;
    color: #333;
    padding: 10px;
}

    .chart-container {
    margin: 20px 10px; /* Adjust margins for spacing between charts */
   
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    display: inline-block; /* Allow charts to be side by side */
    width: calc(50% - 20px); /* Ensure two charts fit in one line */
    vertical-align: top; /* Align charts at the top */
}

canvas {
    max-width: 100%; /* Ensure the canvas is responsive */
}

/* Style for the dropdown container */
.dropdown {
  position: relative;
  display: inline-block;
}

/* Dropdown content (hidden by default) */
.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f9f9f9;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

/* Links inside the dropdown */
.dropdown-content a {
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}

/* Change color of links on hover */
.dropdown-content a:hover {background-color: #f1f1f1}

/* Show the dropdown menu on hover */
.dropdown:hover .dropdown-content {
  display: block;
}

/* Change color of the button on hover */
.dropdown:hover .dropbtn {
  background-color: #3e8e41;
}
</style>
<body>


	<?php
	// Debugging line to get the current page for active link highlighting
	$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

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
		<a href="Dashtwo.php" class="<?= $current_page == 'Dashtwo.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i>&nbsp;&nbsp;&nbsp; DASHBOARD</a>


            <a href="activetwo.php" class="<?= $current_page == 'activetwo.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>&nbsp;&nbsp;&nbsp; ACTIVE EMPLOYEES
            </a>
            <a href="resignedtwo.php" class="<?= $current_page == 'resignedtwo.php' ? 'active' : '' ?>">
            <i class="fas fa-users-slash"></i>&nbsp;&nbsp;&nbsp; RESIGNED EMPLOYEES
            </a>
            
         <div class="dropdown">
  <a href="" class="<?= $current_page == 'archive.php' ? 'active' : '' ?>">
    <i class="fas fa-archive"></i>&nbsp;&nbsp;&nbsp; ARCHIVE EMPLOYEES
  </a>
  <div class="dropdown-content">
    <a href="archive.php?status=resigned">Resigned</a>
    <a href="archive_two.php?status=active">Active</a>
  </div>
</div>



		<div class="sidebar-footer mt-auto">
			<a href="usertwo.php" class="<?= $current_page == 'usertwo.php' ? 'active' : '' ?>"><i class="fas fa-user-circle"></i>&nbsp;&nbsp;&nbsp;&nbsp; USER DETAILS</a>
			<a href="logout.php"><i class="fas fa-sign-out-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp; LOGOUT</a>
		</div>
	</div>

    
	<?php include 'timer.php'; ?>

<div class="dashboard">
  <main>
    <h1 class="title">Dashboard</h1>

   <div id="employee-filter-container">
        <label for="employee-filter">Select Employee Status:</label>
        <select id="employee-filter">
            <option value="active">Active Employees</option>
            <option value="resigned">Resigned Employees</option>
        </select>
    </div>

		<div class="data">
			<div class="chart-container">
				<div class="head">
					<h3>by Gender</h3>
				</div>
				<div class="chart">
					<canvas id="genderChart"></canvas>
				</div>
			</div>

			<div class="chart-container">
				<div class="head">
					<h3>by Age</h3>
				</div>
				<div class="chart">
					<canvas id="ageChart" style="height: 300px;"></canvas>
				</div>
			</div>

			<div class="chart-container">
				<div class="head">
					<h3>by Department</h3>
				</div>
				<div class="chart">
					<canvas id="departmentChart" style="height: 300px;"></canvas>
				</div>
			</div>
		</div>
		
		<div class="data">
			<div class="chart-container">
				<div class="head">
					<h3>by Job Role</h3>
				</div>
				<div class="chart">
					<canvas id="jobRoleChart" style="height: 300px;"></canvas>
				</div>
			</div>

			<div class="chart-container">
				<div class="head">
					<h3>by Years at Company</h3>
				</div>
				<div class="yearschart">
					<canvas id="yearsAtCompanyChart" style="height: 300px;"></canvas>
				</div>
			</div>
		</div>
		
		<div class="footer">
			
		</div>
	</main>
</div>




<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Chart.js instances
           // Gender Chart
           // Gender Chart
const genderChart = new Chart(document.getElementById('genderChart').getContext('2d'), {
    type: 'pie', // Changed from 'bar' to 'pie'
    data: {
        labels: [], // Add gender labels here (e.g., ['Male', 'Female'])
        datasets: [{
            label: 'Gender Distribution', // This label won't be displayed in a pie chart
            data: [], // Add the corresponding data (e.g., [60, 40])
            backgroundColor: ['#36a2eb', '#00e396'] // Blue for Male, Pink for Female
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false // Pie chart typically shows legend by default
            }
        }
    }
});


// Age Chart
const ageChart = new Chart(document.getElementById('ageChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Age Distribution', // This label won't be displayed
            data: [],
            backgroundColor: ['#70cfe3', '#00bce1', '#0291c9']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false // Hide the legend
            }
        },
        scales: {
            x: {
                display: true, // Hide X-axis labels
                grid: {
                    display: false // Hide X-axis grid lines
                }
            },
            y: {
                display: false, // Hide Y-axis labels
                grid: {
                    display: false // Hide Y-axis grid lines
                },
                beginAtZero: true
            }
        }
    }
});

// Department Chart
const departmentChart = new Chart(document.getElementById('departmentChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Department Distribution', // This label won't be displayed
            data: [],
            backgroundColor: ['#70cfe3', '#00bce1', '#0291c9', '#037aa5', '#006079']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false // Hide the legend
            }
        },
        scales: {
            x: {
                display: true, // Hide X-axis labels
                grid: {
                    display: false // Hide X-axis grid lines
                }
            },
            y: {
                display: false, // Hide Y-axis labels
                grid: {
                    display: false // Hide Y-axis grid lines
                },
                beginAtZero: true
            }
        }
    }
});

// Job Role Chart
const jobRoleChart = new Chart(document.getElementById('jobRoleChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Job Role Distribution', // This label won't be displayed
            data: [],
            backgroundColor: ['#0291c9','#006079']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false // Hide the legend
            }
        },
        scales: {
            x: {
                display: true, // Hide X-axis labels
                grid: {
                    display: false // Hide X-axis grid lines
                }
            },
            y: {
                display: false, // Hide Y-axis labels
                grid: {
                    display: false // Hide Y-axis grid lines
                },
                beginAtZero: true
            }
        }
    }
});

// Years at Company Chart
const yearsAtCompanyChart = new Chart(document.getElementById('yearsAtCompanyChart').getContext('2d'), {
    type: 'line', // Change to 'line'
    data: {
        labels: [], // Add years or intervals
        datasets: [{
            label: 'Employees',
            data: [], // Number of employees for each year or range
            borderColor: '#36a2eb',
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false // Show the legend
            }
        },
        scales: {
            x: {
                grid: {
                    display: true // Display X-axis grid lines
                }
            },
            y: {
                beginAtZero: true
            }
        }
    }
});
            // Fetch chart data based on employee status
            function fetchChartData(status) {
                return fetch(`fetch.php?status=${status}`).then(response => response.json());
            }

            // Function to update the dashboard with new data
            function updateDashboard(status) {
                fetchChartData(status).then(data => {
                    // Update gender chart
                    const genderLabels = data.gender.map(item => item.gender);
                    const genderCounts = data.gender.map(item => item.count);
                    updateChart(genderChart, genderLabels, genderCounts);

                    // Update age chart
                    const ageLabels = data.age.map(item => item.age_range);
                    const ageCounts = data.age.map(item => item.count);
                    updateChart(ageChart, ageLabels, ageCounts);

                    // Update department chart
                    const departmentLabels = data.department.map(item => item.department);
                    const departmentCounts = data.department.map(item => item.count);
                    updateChart(departmentChart, departmentLabels, departmentCounts);

                    // Update job role chart
                    const jobRoleLabels = data.job_role.map(item => item.job_role);
                    const jobRoleCounts = data.job_role.map(item => item.count);
                    updateChart(jobRoleChart, jobRoleLabels, jobRoleCounts);

                    // Update years at company chart
                    const yearsAtCompanyLabels = data.yearscompany.map(item => item.yearscompany);
                    const yearsAtCompanyCounts = data.yearscompany.map(item => item.count);
                    updateChart(yearsAtCompanyChart, yearsAtCompanyLabels, yearsAtCompanyCounts);
                });
            }

            // Function to update each chart
            function updateChart(chart, labels, data) {
                chart.data.labels = labels;
                chart.data.datasets[0].data = data;
                chart.update();
            }

            // Handle filter change
            $('#employee-filter').on('change', function() {
                const selectedStatus = $(this).val();
                updateDashboard(selectedStatus);
            });

            // Initial load for active employees
            updateDashboard('active');

            // Data for resignation rates from 2019 to 2024
            var resignationOptions = {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Resignation Rate',
                    data: [15, 24, 30, 42, 17, 10] // Replace these numbers with actual data
                }],
                xaxis: {
                    categories: ['2019', '2020', '2021', '2022', '2023', '2024'],
                    title: {
                        text: 'Year'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Resignation Rate (%)',
                        formatter: function (val) {
                            return val + "%";
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false
                    }
                },
                fill: {
                    colors: ['#1E90FF']
                },
                dataLabels: {
                    enabled: false
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + "%";
                        }
                    }
                }
            };

            // Initialize the resignation rates chart using ApexCharts
            var resignationChart = new ApexCharts(document.querySelector("#resignation-rates-chart"), resignationOptions);
            resignationChart.render();
        });
    </script>

</body>
</html>