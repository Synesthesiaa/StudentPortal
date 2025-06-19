<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

header('Content-Type: application/json');

if (!isset($_GET['subject']) || !isset($_GET['semester']) || !isset($_GET['class'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$subject = mysqli_real_escape_string($con, $_GET['subject']);
$semester = mysqli_real_escape_string($con, $_GET['semester']);
$class = mysqli_real_escape_string($con, $_GET['class']);

// Parse the class value to get program, year_level, and section
$parts = explode('-', $class);
if (count($parts) !== 2) {
    echo json_encode(['error' => 'Invalid class format']);
    exit();
}

$program = $parts[0];
$year_section = $parts[1];
$year_level = substr($year_section, 0, -1);
$section = substr($year_section, -1);

try {
    // Fetch existing grades for the specified class, subject, and semester
    $query = "SELECT sg.*, sl.fname, sl.lname 
             FROM student_grades sg 
             JOIN student_profiles sp ON sg.student_id = sp.student_id
             JOIN signup_login sl ON sp.email = sl.email
             WHERE sg.subject = ? AND sg.semester = ? 
             AND sp.program = ? AND sp.year_level = ? AND sp.section = ?
             ORDER BY sl.lname, sl.fname";
    
    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }
    
    if (!$stmt->bind_param("sssss", $subject, $semester, $program, $year_level, $section)) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Getting result failed: " . $stmt->error);
    }
    
    $grades = array();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    
    echo json_encode($grades);
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error occurred: ' . $e->getMessage()]);
}
?> 