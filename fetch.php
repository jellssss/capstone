<?php




// fetch.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "revised";



try {
    $pdo = new PDO("mysql:host=$servername;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get status from query parameter
    $status = isset($_GET['status']) ? $_GET['status'] : 'active'; // Default to active

    // Queries based on employee status
    if ($status === 'resigned') {
        $genderQuery = "SELECT gender, COUNT(*) AS count FROM emp_record WHERE status = 'resigned' GROUP BY gender";
        $ageQuery = "SELECT CASE 
                        WHEN age < 20 THEN '<20'
                        WHEN age BETWEEN 20 AND 30 THEN '20-30'
                        WHEN age BETWEEN 31 AND 40 THEN '31-40'
                        WHEN age BETWEEN 41 AND 50 THEN '41-50'
                        WHEN age BETWEEN 51 AND 60 THEN '51-60'
                        ELSE '61+' 
                     END AS age_range, COUNT(*) AS count 
                     FROM emp_record WHERE status = 'resigned' 
                     GROUP BY age_range";
        $departmentQuery = "SELECT department, COUNT(*) AS count FROM emp_record WHERE status = 'resigned' GROUP BY department";
        $jobRoleQuery = "SELECT job_role, COUNT(*) AS count FROM emp_record WHERE status = 'resigned' GROUP BY job_role";
        $yearsAtCompanyQuery = "SELECT CASE
                                    WHEN yearscompany < 1 THEN '<1 year'
                                    WHEN yearscompany BETWEEN 1 AND 3 THEN '1-3 years'
                                    WHEN yearscompany BETWEEN 4 AND 7 THEN '4-7 years'
                                    WHEN yearscompany BETWEEN 8 AND 10 THEN '8-10 years'
                                    ELSE '>10 years' 
                                END AS yearscompany, COUNT(*) AS count 
                                FROM emp_record WHERE status = 'resigned' 
                                GROUP BY yearscompany";
    } else {
        // Active employee queries
        $genderQuery = "SELECT gender, COUNT(*) AS count FROM emp_record WHERE status = 'active' GROUP BY gender";
        $ageQuery = "SELECT CASE 
                        WHEN age < 20 THEN '<20'
                        WHEN age BETWEEN 20 AND 30 THEN '20-30'
                        WHEN age BETWEEN 31 AND 40 THEN '31-40'
                        WHEN age BETWEEN 41 AND 50 THEN '41-50'
                        WHEN age BETWEEN 51 AND 60 THEN '51-60'
                        ELSE '61+' 
                     END AS age_range, COUNT(*) AS count 
                     FROM emp_record WHERE status = 'active' 
                     GROUP BY age_range";
        $departmentQuery = "SELECT department, COUNT(*) AS count FROM emp_record WHERE status = 'active' GROUP BY department";
        $jobRoleQuery = "SELECT job_role, COUNT(*) AS count FROM emp_record WHERE status = 'active' GROUP BY job_role";
        $yearsAtCompanyQuery = "SELECT CASE
                                    WHEN yearscompany < 1 THEN '<1 year'
                                    WHEN yearscompany BETWEEN 1 AND 3 THEN '1-3 years'
                                    WHEN yearscompany BETWEEN 4 AND 7 THEN '4-7 years'
                                    WHEN yearscompany BETWEEN 8 AND 10 THEN '8-10 years'
                                    ELSE '>10 years' 
                                END AS yearscompany, COUNT(*) AS count 
                                FROM emp_record WHERE status = 'active' 
                                GROUP BY yearscompany";
    }

    // Fetch data for charts
    $data = [
        'gender' => fetchChartData($pdo, $genderQuery),
        'age' => fetchChartData($pdo, $ageQuery),
        'department' => fetchChartData($pdo, $departmentQuery),
        'job_role' => fetchChartData($pdo, $jobRoleQuery),
        'yearscompany' => fetchChartData($pdo, $yearsAtCompanyQuery),
    ];

    header('Content-Type: application/json');
    echo json_encode($data);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

function fetchChartData($pdo, $query) {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

