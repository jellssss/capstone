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

// Archive employee functionality
if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);
    
    // Update the employee status to 'Archived'
    $stmt = $conn->prepare("UPDATE empreco SET status = 'Archived' WHERE Employee_ID = ?");
    $stmt->bind_param("i", $employee_id);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error: ' . $conn->error;
    }

    $stmt->close();
}

// Fetch archived employees
$archived_sql = "SELECT * FROM empreco WHERE status = 'Archived'";
$archived_result = $conn->query($archived_sql);

// Restore employee functionality
if (isset($_GET['restore_id'])) {
  $restore_id = intval($_GET['restore_id']);
  
  // Fetch the original status of the employee
  $status_query = $conn->prepare("SELECT status FROM empreco WHERE Employee_ID = ?");
  $status_query->bind_param("i", $restore_id);
  $status_query->execute();
  $status_result = $status_query->get_result();
  
  if ($status_result->num_rows > 0) {
      $original_status = $status_result->fetch_assoc()['status'];
      
      // Determine new status based on original status
      if ($original_status !== 'Archived') {
          $new_status = $original_status; // Restore to original status
      } else {
          $new_status = 'Resigned'; // Default to 'Resigned' if needed
      }
      
      // Update the employee status
      $stmt = $conn->prepare("UPDATE empreco SET status = ? WHERE Employee_ID = ?");
      $stmt->bind_param("si", $new_status, $restore_id);
      
      if ($stmt->execute()) {
          $message[] = 'Employee restored successfully!';
      } else {
          $message[] = 'Error restoring employee: ' . $conn->error;
      }

      $stmt->close();
  } else {
      $message[] = 'Employee not found.';
  }

  $status_query->close();
  
  // Redirect to refresh the page and fetch updated data
  header('Location: archive.php');
  exit();
}



?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bootstrap Sidebar with Dashboard</title>
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
      margin-left: 250px;
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
</head>
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
    <!-- Logo Section -->
    <div class="user-info">
    <img src="<?php echo !empty($user_details['image']) ? 'uploaded_img/'.$user_details['image'] : 'images/default-avatar.png'; ?>" alt="User Image">
    <p><?php echo htmlspecialchars($user_details['name']); ?></p>
</div>


<?php include 'timer.php'; ?>
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


 <section class="main">
    <div class="main--content">
        <div class="recent--patients">
            <h2 class="section--title">Archived Resigned Employees</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
            <th>Employee ID</th>
            <th>Age Group</th>
            <th>Distance from Home</th>
            <th>Gender</th>
            <th>Job Category</th>
            <th>Marital Status</th>
            <th>Salary</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tbody>
  <?php
  if ($archived_result->num_rows > 0) {
      while ($row = $archived_result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . htmlspecialchars($row['Employee_ID']) . "</td>";
          echo "<td>" . htmlspecialchars($row['Age_Group']) . "</td>";
          echo "<td>" . htmlspecialchars($row['DistanceFromHome']) . "</td>";
          echo "<td>" . htmlspecialchars($row['Gender']) . "</td>";
          echo "<td>" . htmlspecialchars($row['Job_Category']) . "</td>";
          echo "<td>" . htmlspecialchars($row['Marital_Status']) . "</td>";
          echo "<td>" . htmlspecialchars($row['Salary']) . "</td>";
          echo "<td><a href='archive.php?restore_id=" . $row['Employee_ID'] . "' class='btn btn-primary'>Restore</a></td>"; // Restore button
          echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='12' class='text-center'>No archived employees found.</td></tr>";
  }
  ?>


        </tbody>
      </table>
    </div>
  </div>
</section>


<!-- View Employee Record Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-width" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">View Employee Record</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editForm">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="empID">Employee ID</label>
              <input type="text" class="form-control" id="empID" name="empID" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="last">Lastname</label>
              <input type="text" class="form-control" id="last" name="last" disabled>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="first">Firstname</label>
              <input type="text" class="form-control" id="first" name="first" disabled>
            </div>
            <div class="form-group col-md-6">
              <label for="age">Age</label>
              <input type="number" class="form-control" id="age" name="age" disabled>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="gender">Gender</label>
              <select class="form-control" id="gender" name="gender" disabled>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="birth">Birthday</label>
              <input type="date" class="form-control" id="birth" name="birth" disabled>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="address">Address</label>
              <input type="text" class="form-control" id="address" name="address" disabled>
            </div>
            <div class="form-group col-md-6">
              <label for="contact">Contact No.</label>
              <input type="text" class="form-control" id="contact" name="contact" disabled>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="department">Department</label>
              <input type="text" class="form-control" id="department" name="department" disabled>
            </div>
            <div class="form-group col-md-6">
              <label for="job_role">Job Role</label>
              <input type="text" class="form-control" id="job_role" name="job_role" disabled>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="hired_date">Hired Date</label>
              <input type="date" class="form-control" id="hired_date" name="hired_date" disabled>
            </div>
            <div class="form-group col-md-6">
              <label for="yearscompany">Years of Service</label>
              <input type="number" class="form-control" id="yearscompany" name="yearscompany" disabled>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>





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


