<?php
include 'config.php';
session_start();
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
  <title>User Profile</title>
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
      margin-left: 250px; /* Align content next to the sidebar */
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .update-profile {
      background-color: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 600px;
    }

    .update-profile img {
      width: 150px;
      border-radius: 50%;
      margin-bottom: 20px;
    }

    .update-profile .inputBox {
      margin-bottom: 15px;
    }

    .update-profile .inputBox span {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
      text-align: left; /* Align the span text to the left */
    }

    .update-profile .box {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    .update-profile .btn {
      background-color: #4fc3f7;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .update-profile .btn:hover {
      background-color: #0288d1;
    }

    .update-profile .delete-btn {
      background-color: #f44336;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      margin-top: 10px;
      display: inline-block;
    }

    .update-profile .delete-btn:hover {
      background-color: #c62828;
    }

  </style>
</head>
<body>


<?php
// Debugging line
$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
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
<?php include 'timer.php'; ?>




  <div class="main">
    <section class="update-profile text-center">
      <?php
         $select = mysqli_query($conn, "SELECT * FROM `admin_login` WHERE id = '$user_id'") or die('Query failed');
         if(mysqli_num_rows($select) > 0){
            $fetch = mysqli_fetch_assoc($select);
         }
      ?>

      <form action="" method="post" enctype="multipart/form-data">
         <?php
            if($fetch['image'] == ''){
               echo '<img src="images/default-avatar.png" alt="Default Avatar">';
            } else {
               echo '<img src="uploaded_img/'.$fetch['image'].'" alt="User Image">';
            }
            if(isset($message)){
               foreach($message as $msg){
                  echo '<div class="message">'.$msg.'</div>';
               }
            }
         ?>
         <div class="row">
            <div class="col-md-6">
               <div class="inputBox">
                  <span>Username:</span>
                  <input type="text" name="update_name" value="<?php echo $fetch['name']; ?>" class="box">
                  <span>Your Email:</span>
                  <input type="email" name="update_email" value="<?php echo $fetch['email']; ?>" class="box" readonly>
                  <span>Update your pic:</span>
                  <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
               </div>
            </div>
            <div class="col-md-6">
               <div class="inputBox">
                  <span>Contact number:</span>
                  <input type="text" name="update_contact" value="<?php echo $fetch['contact']; ?>" class="box">
                  <span>Department:</span>
                  <input type="text" name="update_department" value="<?php echo $fetch['department']; ?>" class="box">
                  <span>Job Role:</span>
                  <input type="text" name="update_role" value="<?php echo $fetch['jobrole']; ?>" class="box">
               </div>
            </div>
         </div>
         <input type="submit" value="Update Profile" name="update_profile" class="btn btn-primary btn-block">
         <a href="Dash.php" class="delete-btn btn-block">Go back</a>
      </form>
    </section>
  </div>

  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
    $(document).ready(function(){
    // When the collapse is shown or hidden, toggle the caret icon class
    $('#employeeRecords').on('shown.bs.collapse', function () {
        $('#employeeRecordsIcon').addClass('rotate-icon');
    }).on('hidden.bs.collapse', function () {
        $('#employeeRecordsIcon').removeClass('rotate-icon');
    });
});

  </script>

</body>
</html>
