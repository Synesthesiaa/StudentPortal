<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

header('Content-Type: application/json');

if (!isset($_GET['subject']) || !isset($_GET['semester']) || !isset($_GET['program']) || !isset($_GET['year_level']) || !isset($_GET['section'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$subject = mysqli_real_escape_string($con, $_GET['subject']);
$semester = mysqli_real_escape_string($con, $_GET['semester']);
$program = mysqli_real_escape_string($con, $_GET['program']);
$year_level = mysqli_real_escape_string($con, $_GET['year_level']);
$section = mysqli_real_escape_string($con, $_GET['section']);

try {
    // Get existing grades for the specified class, subject, and semester
    $query = "SELECT sg.*, sp.student_id 
              FROM student_grades sg 
              JOIN student_profiles sp ON sg.student_id = sp.student_id
              WHERE sg.subject = ? AND sg.semester = ? 
              AND sp.program = ? AND sp.year_level = ? AND sp.section = ?
              ORDER BY sp.student_id";
    
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
    
    echo json_encode([
        'success' => true,
        'grades' => $grades,
        'count' => count($grades)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?> 