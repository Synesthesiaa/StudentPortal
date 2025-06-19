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

// Get total number of students in the class
$total_students_query = "SELECT COUNT(*) as total FROM student_profiles 
                        WHERE program = ? AND year_level = ? AND section = ?";
$total_stmt = $con->prepare($total_students_query);
$total_stmt->bind_param("sss", $program, $year_level, $section);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_students = $total_result->fetch_assoc()['total'];

// Get number of students with grades for this SPECIFIC subject and semester
$grades_query = "SELECT COUNT(DISTINCT sg.student_id) as count 
                FROM student_grades sg 
                JOIN student_profiles sp ON sg.student_id = sp.student_id
                WHERE sg.subject = ? AND sg.semester = ? 
                AND sp.program = ? AND sp.year_level = ? AND sp.section = ?";
$grades_stmt = $con->prepare($grades_query);
$grades_stmt->bind_param("sssss", $subject, $semester, $program, $year_level, $section);
$grades_stmt->execute();
$grades_result = $grades_stmt->get_result();
$grades_count = $grades_result->fetch_assoc()['count'];

// Return true only if all students have grades for this SPECIFIC subject and semester
echo json_encode([
    'exists' => ($grades_count > 0 && $grades_count === $total_students),
    'total_students' => $total_students,
    'students_with_grades' => $grades_count,
    'debug' => [
        'subject' => $subject,
        'semester' => $semester,
        'program' => $program,
        'year_level' => $year_level,
        'section' => $section
    ]
]);
?>