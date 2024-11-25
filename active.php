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
  <title>Active Employee</title>
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
      color: black; /* Slightly darker text color for section headers */
      padding: 10px 15px;
      font-size: 0.9rem;
    }

    .main .section--title{
      color:#23A7AB;

    }

    .sidebar-footer {
      margin-top: auto;
    }


.user-profile img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.user-profile p {
    font-size: 14px;
    color: #333;
}

.rotate-icon {
    transform: rotate(180deg); /* Rotates the icon 180 degrees */
    transition: transform 0.3s ease; /* Smooth rotation */
}


    .main {
      margin-left: 120px;
      padding: 20px;
    }

    .header-title {
      font-size: 2rem;
      color: #333;
      margin-bottom: 20px;
    }

    .table-container {
      padding: 10px;
      border-radius: 5px; /* Rounded corners for a soft look */
      max-height: 500px; /* Set a maximum height for the table */
      overflow-y: auto; /* Enable vertical scrolling */
    }

    .search-bar {
      margin-bottom: 20px;
      display: flex;
      justify-content: flex-end;
    }

    .search-bar input {
      width: 200px;
      border-radius: 5px;
      border: 1px solid #ddd;
      padding: 5px;
    }

    .modal-content {
      padding: 20px;
    }

    .modal-footer .btn {
      margin-left: 10px;
    }

    .table {
      border-radius: 0;
      font-size: 14px;
      margin-top: 20px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    table {
      padding: 5px;
      width: 100%;
      text-align: center;
      border-collapse: collapse;
    }

    thead {
      background-color: #6DB8BB;
      color: #FFF;
    }

    tbody tr:nth-child(even) {
      background-color: #F6F5FF;
    }

    tbody tr:hover {
      background-color: #E5F2F0;
    }

    .edit, .delete {
      cursor: pointer;
      transition: color 0.3s ease;
    }

    .edit:hover, .delete:hover {
      color: #70d7a5;
    }

    .custom-width {
  max-width: 50%; /* Adjust this value as needed */
  width: auto;
}

.form{
    text-align:right;
}

/* General Layout and Spacing */
.overview {
    margin-left: 120px;
    
    
}

.title {
    margin-bottom: 20px;
}

.section--title {
    font-size: 24px;
    color: #333;
    text-align: left;
}

/* Card Styling */
/* Slider container */
.slider-container {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 100%;
    overflow: hidden; /* Hide overflow for horizontal scrolling */
    padding: 20px 0;
}

/* Cards slider wrapper */
.cards-slider {
    overflow: hidden; /* Prevents scrollbar */
    display: flex;
    gap: 20px;
    width: 80%; /* Adjust this value for card display */
    /* You can implement other methods to slide cards using JS */
}

.cards {
    display: flex;
    gap: 20px; /* Space between cards */
}

.card {
    flex: 1 0 300px; /* Set card width for the slider */
    min-width: 350px;
    max-width: 450px;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    overflow: hidden;
    position: relative;
    transition: all 0.3s ease;
}

.card h3 {
    font-size: 20px;
    margin-bottom: 15px;
    margin: 0 0 10px 0;
    transition: opacity 0.3s ease;
    text-align: center; /* Center title */
}

.chart-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 66.67%; /* 16:9 aspect ratio */
    margin: 0 auto;
}

.chart-container canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: block;
}

/* Slider buttons */
.slider-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: #00b4d8;
    color: white;
    border: none;
    cursor: pointer;
    padding: 10px;
    font-size: 20px;
    z-index: 10;
}

.slider-button.left {
    left: 10px;
}

.slider-button.right {
    right: 10px;
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
    <!-- Logo Section -->
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
<?php include 'timer.php'; ?>
 <section class="main">
        <div class="main--content">
         <div class="overview">
        <div class="title">
            <h2 class="section--title">Active Employee Overview and Records</h2>
        </div>

        <div class="slider-container">
    <button class="slider-button left">&lt;</button> <!-- Left arrow button -->
    <div class="cards-slider">
        <div class="cards">
            <div class="card">
                <h3>Age Distribution</h3>
                <div class="chart-container">
                    <canvas id="ageBarChart"></canvas>
                </div>
            </div>
            
            <div class="card">
                <h3>Gender Distribution</h3>
                <div class="chart-container">
                    <canvas id="genderPieChart"></canvas>
                </div>
            </div>

            <div class="card">
                <h3>Job Category Distribution</h3>
                <div class="chart-container">
                    <canvas id="jobCategoryBarChart"></canvas>
                </div>
            </div>


        </div>
    </div>
    <button class="slider-button right">&gt;</button> <!-- Right arrow button -->
</div>
    </div>

  <section class="main">
    <div class="main--content">
      <div class="recent--patients">

        <div class="search-bar">
          <input type="text" id="searchInput" class="form-control" placeholder="Search by id...">
          <div id="noResultsMessage" style="display: none; color: red;">Employee ID not found.</div>
        </div>

 
        <div class="table-container">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Employee_ID</th>
                <th>Status</th>                               
                <th>Age Group</th>
                <th>Distance from Home</th>
                <th>Gender</th>
                <th>Job Category</th>
                <th>Marital Status</th>
                <th>Salary</th>									
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="employeeTableBody">
              <?php
               $servername = "localhost";
               $username = "root";
               $password = "";
               $dbname = "revised";
               
               $conn = new mysqli($servername, $username, $password, $dbname);
               
               if ($conn->connect_error) {
                   die("Connection failed: " . $conn->connect_error);
               }


              $sql = "SELECT * FROM empreco WHERE status = 'Not Resigned'"; // Added WHERE clause to filter active employees
              $result = mysqli_query($conn, $sql);
        
              while ($row = mysqli_fetch_assoc($result)) {
              ?>

              <tr>
              <td><?php echo htmlspecialchars($row['Employee_ID']); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                <td><?php echo htmlspecialchars($row['Age_Group']); ?></td>
                <td><?php echo htmlspecialchars($row['DistanceFromHome']); ?></td>
                <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                <td><?php echo htmlspecialchars($row['Job_Category']); ?></td>
                <td><?php echo htmlspecialchars($row['Marital_Status']); ?></td>
                <td><?php echo htmlspecialchars($row['Salary']); ?></td>               
              <td class="text-center">
              <button class="btn btn-link p-0" onclick="viewEmployee(<?php echo $row['Employee_ID']; ?>)" title="View Employee">
                <i class="fas fa-eye" style="font-size: 0.8rem;"></i>
              </button>
              
              
            </td>



              </tr>

              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
  

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewEmployeeModalLabel">Employee Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Employee ID:</strong> <span id="viewEmployeeID"></span></p>
        <p><strong>Status:</strong> <span id="viewStatus"></span></p>
        <p><strong>Age Group:</strong> <span id="viewAgeGroup"></span></p>
        <p><strong>Distance from Home:</strong> <span id="viewDistanceFromHome"></span></p>
        <p><strong>Gender:</strong> <span id="viewGender"></span></p>
        <p><strong>Job Category:</strong> <span id="viewJobCategory"></span></p>
        <p><strong>Marital Status:</strong> <span id="viewMaritalStatus"></span></p>
        <p><strong>Salary:</strong> <span id="viewSalary"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<script>
function viewEmployee(employeeID) {
  // Fetch employee details from the database
  fetch(`get_employee_details.php?id=${employeeID}`)
    .then(response => response.json())
    .then(data => {
      // Fill in the modal with employee details
      document.getElementById('viewEmployeeID').innerText = data.Employee_ID;
      document.getElementById('viewStatus').innerText = data.Status;
      document.getElementById('viewAgeGroup').innerText = data.Age_Group;
      document.getElementById('viewDistanceFromHome').innerText = data.DistanceFromHome;
      document.getElementById('viewGender').innerText = data.Gender;
      document.getElementById('viewJobCategory').innerText = data.Job_Category;
      document.getElementById('viewMaritalStatus').innerText = data.Marital_Status;
      document.getElementById('viewSalary').innerText = data.Salary;

      // Show the modal
      $('#viewEmployeeModal').modal('show');
    });
}


</script>


  
<script>
  document.getElementById('searchInput').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') { // Check if Enter key was pressed
      const search = this.value.trim();
      const rows = document.querySelectorAll('#employeeTableBody tr');
      let found = false; // Flag to track if any match was found

      // Reset all rows to visible before checking for matches
      rows.forEach(row => row.style.display = '');

      rows.forEach(row => {
        const idCell = row.querySelector('td:first-child'); // First column (ID)
        const matched = idCell && idCell.textContent.trim() === search; // Exact match

        if (matched) found = true; // Set flag if a match is found
        row.style.display = matched || search === '' ? '' : 'none'; // Show row if match or input is empty
      });

      // Show an alert if no match is found and the search is not empty
      if (!found && search !== '') {
        alert("Employee ID does not exist.");
        
        // Reset input value and show all rows again
        this.value = ''; // Clear the search input
        rows.forEach(row => row.style.display = ''); // Show all rows
        // Optionally scroll to the statistics section if needed
        document.getElementById('table-container').scrollIntoView({ behavior: 'smooth' });
      }
    }
  });
</script>


<script>
    $(document).ready(function(){
    // When the collapse is shown or hidden, toggle the caret icon class
    $('#employeeRecords').on('shown.bs.collapse', function () {
        $('#employeeRecordsIcon').addClass('rotate-icon');
    }).on('hidden.bs.collapse', function () {
        $('#employeeRecordsIcon').removeClass('rotate-icon');
    });
});

  </script>

<script>
    const body = document.querySelector('body'),
          sidebar = body.querySelector('.sidebar'),
          toggle = body.querySelector(".toggle"),
          modeSwitch = body.querySelector(".toggle-switch"),
          modeText = body.querySelector(".mode-text");

    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
    });
</script>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('fetch_chart_info.php')
                .then(response => response.json())
                .then(data => {
                    function createPieChart(canvasId, chartData, chartTitle) {
                        const ctx = document.getElementById(canvasId).getContext('2d');
                        const labels = chartData.map(item => item[Object.keys(item)[0]]);
                        const dataValues = chartData.map(item => item.count);

                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: dataValues,
                                    backgroundColor: [
                                        '#00b4d8', '#00e396', '#007bff'
                                    ],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                return `${tooltipItem.label}: ${tooltipItem.raw}`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

function createBarChart(canvasId, chartData, chartTitle) {
    const ctx = document.getElementById(canvasId).getContext('2d');

    // Sort the data based on count in descending order
    const sortedData = chartData.sort((a, b) => b.count - a.count);
    
    // Get the top 5 entries
    const top5Data = sortedData.slice(0, 5);

    const labels = top5Data.map(item => item[Object.keys(item)[0]]);
    const dataValues = top5Data.map(item => item.count);

    // Define colors for blue and green shades similar to the specified colors
    const backgroundColors = [
        '#00b4d8', // Blue shade
        '#00e396', // Green shade
        '#007bff', // Another Blue shade
        '#00b4d8', // Repeat Blue shade for more bars if needed
        '#00e396'  // Repeat Green shade for more bars if needed
    ];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: chartTitle,
                data: dataValues,
                backgroundColor: backgroundColors.slice(0, top5Data.length), // Limit colors to the number of bars
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return `${tooltipItem.label}: ${tooltipItem.raw}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}


                    // Create charts
                    createPieChart('genderPieChart', data.gender, 'Gender Distribution');
                    createBarChart('ageBarChart', data.age, 'Age Range Distribution');
                    createBarChart('jobCategoryBarChart', data.job_category, 'Job Category Distribution');

                })
                .catch(error => console.error('Error fetching chart data:', error));
        });
</script>

<script>
    const leftButton = document.querySelector('.slider-button.left');
const rightButton = document.querySelector('.slider-button.right');
const slider = document.querySelector('.cards-slider');

leftButton.addEventListener('click', () => {
    slider.scrollBy({
        left: -300, // Scroll left by 300px
        behavior: 'smooth'
    });
});

rightButton.addEventListener('click', () => {
    slider.scrollBy({
        left: 300, // Scroll right by 300px
        behavior: 'smooth'
    });
});
</script>

<script>
    const body = document.querySelector('body'),
          sidebar = body.querySelector('.sidebar'),
          toggle = body.querySelector(".toggle"),
          modeSwitch = body.querySelector(".toggle-switch"),
          modeText = body.querySelector(".mode-text");

    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
    });
</script>






  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


