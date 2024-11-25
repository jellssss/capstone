<?php
// fetch_chart_info.php

header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "revised";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch data and return as an associative array
function fetchData($conn, $sql, $groupByColumn) {
    $result = $conn->query($sql);
    $data = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                $groupByColumn => $row[$groupByColumn],
                'count' => (int) $row['count']
            ];
        }
    }

    return $data;
}

// Fetch data for different charts
// Gender distribution
$sqlGender = "SELECT Gender, COUNT(*) as count FROM empreco WHERE status = 'Not Resigned' GROUP BY Gender";
$genderData = fetchData($conn, $sqlGender, 'Gender');

// Age range distribution
$sqlAge = "SELECT Age_Group, COUNT(*) as count FROM empreco WHERE status = 'Not Resigned' GROUP BY Age_Group";
$ageData = fetchData($conn, $sqlAge, 'Age_Group');

// Department distribution
$sqlDepartment = "SELECT Department, COUNT(*) as count FROM empreco WHERE status = 'Not Resigned' GROUP BY Department";
$departmentData = fetchData($conn, $sqlDepartment, 'Department');

// Job category distribution
$sqlJobCategory = "SELECT Job_Category, COUNT(*) as count FROM empreco WHERE status = 'Not Resigned' GROUP BY Job_Category";
$jobCategoryData = fetchData($conn, $sqlJobCategory, 'Job_Category');

// Total months at company distribution
$sqlTotalMonthsAtCompany = "SELECT TotalMonthsAtCompany, COUNT(*) as count FROM empreco WHERE status = 'Not Resigned' GROUP BY TotalMonthsAtCompany";
$totalmonthsAtCompanyData = fetchData($conn, $sqlTotalMonthsAtCompany, 'TotalMonthsAtCompany');

// Compile all data into a single array
$response = [
    'gender' => $genderData,
    'age' => $ageData,
    'department' => $departmentData,
    'job_category' => $jobCategoryData,
    'TotalMonthsAtCompany' => $totalmonthsAtCompanyData,
];

// Close the database connection
$conn->close();

// Output the JSON data
echo json_encode($response);
?>
