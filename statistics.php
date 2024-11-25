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
  <title>Statistics</title>
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

/* Statistics Section */

.statistics {
    margin-left: 250px; /* Sidebar width */
    transition: margin-left 0.3s;
    padding: 20px;
   
    
}

.statistics .title {
    font-size: 15px;
    font-weight: 600;
    color: #23A7AB; 
    margin-bottom: 20px;
}
.statistics .sub-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    margin-top: 30px;
}


.statistics .section-title{
	font-size: 25px;
    font-weight: 500;
    color: #23A7AB; 
    
}
.right-btns {
    display: flex;
    align-items: center;
    gap: 30px;
}
.add {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    outline: none;
    border: none;
    background-color: #23A7AB; 
    color: #fff;
    border-radius: 5px;
    cursor: pointer;
    transition: .3s;
}
.add:hover {
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
}
.add i {
    margin-right: 10px;
    padding: 5px;
    color: #white;
}

main .pred-title{
	font-size: 20px;
    font-weight: 500;
    color: #23A7AB; 
	margin-top: 30px;
    margin-bottom: 20px;
}

.insightt{
	font-size: 20px;
    font-weight: 500;
    color: #23A7AB; 
	margin-top: 30px;
    margin-bottom: 20px;
}

main .data {
    margin-top: 36px;
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
	grid-gap: 20px;
}


.info-data {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    width: 23%;
    height:130px;
    background: #fffff;
    position: relative; /* Ensure pseudo-element is positioned relative to the card */
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
    width: 100%;
    height: 10px;
    background: #e0e0e0;
    border-radius: 5px;
    margin: 10px 0;
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
    height: 100%;
    background: #23A7AB;
    width: 0;
    transition: width 0.5s;
    border-radius: 5px;
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
    height: 100%;
    background: #23A7AB;
    width: 3.4%;
	border-radius: 10px;
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
      form {
            position: absolute;
            width: 60%;
            height: 100%;
            padding: 0px 40px;
            transition: all 0.6s ease-in-out;
        }
        
        
       
        h1 {
            color: var(--grad-clr1);
        }
        
        .overlay h1 span {
            font-size: 30px;
            color: white;
        }
        
        
        .infield {
            position: relative;
            margin: 8px 0px;
            width: 60%;
        }
        
        input {
            width: 60%;
            padding: 12px 8px;
            background-color: #f3f3f3;
            border: none;
            outline: none;
        }
        
        label {
            position: absolute;
            left: 50%;
            top 100%;
            width: 0%;
            height: 2px;
        }
        
        input:focus ~ label {
            width: 60%;
        }
        
        .-btn {
            border-radius: 20px;
            border: 1px;
            color: #FFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            background-color: #23A7AB;
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

<?php include 'timer.php'; ?>

<div class="statistics">
<main>	
	<div class="sub-title">
        <h2 class="section-title">STATISTICS AND FORECASTS</h2>
        <div class="right-btns">
            <button class="add" id="print-pdf"><i class='bx bx-download'></i>Print</button>
        </div>
    </div>


  
<div class="info-data">
        <div class="card">
            <div class="head">
                <div>
                    <h2 id="resigned-employees"><i class="bx bx-user"></i> 175</h2>
                    <p>Total of Active Employees</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="head">
                <div>
                    <h2><p>Likely to Resign</p></h2>
                </div>
                <i class='bx bx-trending-down icon'></i>
            </div>
            <div class="progress">
                <div class="progress-bar" id="resignation-rate"></div>
            </div>
            <span class="label" id="resignation-rate-label">4.6%</span>
        </div>
		<div class="card">
            <div class="head">
                <div>
                    <h2><p>Not Likely to Resign</p></h2>
                </div>
                <i class='bx bx-trending-up icon'></i>
            </div>
            <div class="progress2">
                <div class="progress-bar" id="resignation-rate"></div>
            </div>
            <span class="label" id="resignation-rate-label">95.4%</span>
        </div>  
        <div class="card">
            <div class="head">
                <div>
                    <h2 id="top-department"><i class="bx bx-building"></i>Salary</h2>
                    <p>Top factors for Resignation</p>
                </div>
            </div>
        </div>
</div>

</div>
<br>

  
    <div class="container" id="container">
        
        <div>
            <form >
                <h1>Fill</h1>
                
                <div class="infield">
                    <input type="text" placeholder="Age" name="age">
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="gender" name="gender"/>
                    <label></label>
                </div>
                
                <p>
                <input class="-btn" type="submit" name="submit" value="Login">
				</p>
            </form>
        </div>
   
    
  </div>
	</main>
</div>


  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <script>
    $(document).ready(function(){
    // When the collapse is shown or hidden, toggle the caret icon class
    $('#employeeRecords').on('shown.bs.collapse', function () {
        $('#employeeRecordsIcon').addClass('rotate-icon');
    }).on('hidden.bs.collapse', function () {
        $('#employeeRecordsIcon').removeClass('rotate-icon');
    });
});

// prediction
var options = {
    series: [8,167], 
    chart: {
        width: '100%', // Make the width responsive
        type: 'pie',
        toolbar: {
            show: true, // Enable the toolbar
            tools: {
                download: true, // Enable the download menu
                selection: false,
                zoom: false,
                zoomin: false,
                zoomout: false,
                pan: false,
                reset: false,
                customIcons: []
            }
        }
    },
    labels: ['Likely Resign', 'Stayed'],
  colors: ['#00b4d8', '#00e396', '#007bff'],
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                height: 300
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            }
        }
    }],
};

var chart = new ApexCharts(document.querySelector("#prediction"), options);
chart.render();

var factorOptions = {
    series: [{
        data: [
            {x: 'Salary', y: 0.213620},
            {x: 'Tenure(Months)', y: 0.174943},
            {x: 'HiredByYear', y: 0.153833},
            {x: 'Department', y: 0.132548},
            {x: 'DistanceFromHome', y: 0.085165},
            {x: 'HiredByMonth', y: 0.080471},
            {x: 'Age', y: 0.069872},
            {x: 'Job_Category', y: 0.064741},
            {x: 'Marital Status', y: 0.012661},
            {x: 'Gender', y: 0.012147}
        ]
    }],
    chart: {
        type: 'bar',
        width: '100%',
        height: 300,
        toolbar: {
            show: true,  // Display the toolbar
            tools: {
                download: true,   // Enable the download option
                selection: false,
                zoom: false,
                zoomin: false,
                zoomout: false,
                pan: false,
                reset: false,
                customIcons: []
            }
        }
    },
    colors: ['#00b4d8'],
    plotOptions: {
        bar: {
            horizontal: true,
            barHeight: '70%'
        }
    },
    xaxis: {
        min: 0.00,
        max: 0.30,
        tickAmount: 5,  // Control tick intervals
        title: {
            text: ''
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: ['#000']
            }
        }
    },
    tooltip: {
        y: {
            formatter: function(val) {
                return val.toFixed(3);
            }
        }
    },
    dataLabels: {
        enabled: true,
        formatter: function(val) {
            return val.toFixed(3);
        },
        style: {
            colors: ['#000']
        }
    }
};

var factorsChart = new ApexCharts(document.querySelector("#factors"), factorOptions);
factorsChart.render();


document.getElementById('print-pdf').addEventListener('click', function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4'); // Create jsPDF instance in portrait mode, A4 page size

    // Add centered title
    doc.setFontSize(20);
    const title = "Statistics and Forecasts Summary";
    const pageWidth = doc.internal.pageSize.width;
    const titleWidth = doc.getTextWidth(title);
    const centerX = (pageWidth - titleWidth) / 2; // Calculate center position
    doc.text(title, centerX, 30); // Draw the title

    // KPI Summary Data
    const kpiSummary = [
        "Total Active Employees = 175",
        "Likely to Resign = 4.6%",
        "Not Likely to Resign = 95.4%",
        "Top Factors for Resignation = Salary"
    ];

    // Add KPI Summary to the PDF
    doc.setFontSize(15);
    doc.text("KPI Summary", 20, 60); // Adjust Y position to be below the title

    const lineHeight = 15; // Adjust this value for more or less space

    kpiSummary.forEach((item, index) => {
        doc.setFontSize(12);
        doc.text(item, 20, 80 + (index * lineHeight)); // Adjust spacing based on lineHeight
    });

    // Employee IDs of those likely to resign
    const likelyToResignIds = [262, 307, 316, 404, 405, 408, 409,415]; // Example IDs
    const employeeIdsString = `Employee IDs Likely to Resign: ${likelyToResignIds.join(', ')}`;
    
    // Add Employee IDs to the PDF
    doc.setFontSize(14);
    
    doc.setFontSize(12);
    doc.text(employeeIdsString, 20, 100 + (kpiSummary.length * lineHeight)); // Employee IDs

    // Increase the Y position to add more space after Employee IDs
    let yPosition = 120 + (lineHeight * (kpiSummary.length + 1)); // Y position for charts starting just below Employee IDs

    const charts = [
        { id: 'prediction', title: 'Resignation Prediction' },
        { id: 'factors', title: 'Top Resignation Factors' }
    ];

    let promises = [];

    // Function to add images (charts) to the PDF
    const addImageToPDF = (canvas, title) => {
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 480; // Set desired width of the image in the PDF
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio

        // Center the chart on the page
        const pageWidth = doc.internal.pageSize.width;
        const centerX = (pageWidth - imgWidth) / 2;

        doc.setFontSize(14);
        
        // Add more space above the chart title
        yPosition += 20; // Adjust this value for more space above the title
        doc.text(title, centerX + imgWidth / 2, yPosition, { align: 'center' }); // Centered title

        // Add image below title with some additional space
        doc.addImage(imgData, 'PNG', centerX, yPosition + 10, imgWidth, imgHeight); // Add image below title
        yPosition += imgHeight + 30; // Update Y position for the next chart with extra space

        // Check if new page is needed
        if (yPosition + imgHeight > doc.internal.pageSize.height) {
            doc.addPage();
            yPosition = 20; // Reset Y position for new page
        }
    };

    // Capture each chart
    charts.forEach((chart) => {
        let chartElement = document.getElementById(chart.id);
        if (chartElement) {
            let promise = html2canvas(chartElement, { scale: 2, useCORS: true }).then((canvas) => {
                addImageToPDF(canvas, chart.title);
            }).catch(error => {
                console.error(`Error capturing chart ${chart.id}:`, error);
            });
            promises.push(promise);
        } else {
            console.warn(`Chart element with ID ${chart.id} not found`);
        }
    });

    // Once all content is rendered, save the PDF
    Promise.all(promises).then(() => {
        doc.save('statistics_summary.pdf');
    }).catch(error => {
        console.error("Error generating PDF: ", error);
    });
});

document.getElementById('riskFilter').addEventListener('change', function () {
    const filterValue = this.value.toLowerCase();
    const tableBody = document.getElementById('employeeTableBody');
    const rows = tableBody.getElementsByTagName('tr');

    // Loop through the rows and hide/show based on the filter value
    for (let i = 0; i < rows.length; i++) {
        const riskCell = rows[i].getElementsByTagName('td')[8]; // Get the "Risk Level" column
        const riskText = riskCell ? riskCell.textContent.toLowerCase() : '';

        if (filterValue === 'all' || riskText === filterValue) {
            rows[i].style.display = ''; // Show the row
        } else {
            rows[i].style.display = 'none'; // Hide the row
        }
    }
});



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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.0.0/html2canvas.min.js"></script>


</body>
</html>