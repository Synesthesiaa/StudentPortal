<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['email'])) {
        header("Location: upload_grades.php?error=session");
        exit();
    }

    $teacher_email = $_SESSION['email'];
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $semester = $_POST['semester'];
    $scores = $_POST['scores'];

    // Start transaction
    $con->begin_transaction();

    try {
        // Check for existing grades for the SPECIFIC subject and semester combination
        $check_query = "SELECT COUNT(*) as count FROM student_grades 
                       WHERE student_id = ? AND subject = ? AND semester = ?";
        $check_stmt = $con->prepare($check_query);
        if (!$check_stmt) {
            throw new Exception("Prepare failed for check query: " . $con->error);
        }

        $debug_output = "<div style='background:#f8f8f8;border:1px solid #ccc;padding:10px;'><strong>DEBUG OUTPUT:</strong><br>";
        foreach ($scores as $student_id => $components) {
            $is_incomplete = isset($components['incomplete']) && $components['incomplete'] == '1';

            // For debug: what will be set?
            $debug_output .= "Student ID: $student_id | incomplete: " . ($is_incomplete ? '1' : '0');

            if ($is_incomplete) {
                $final_grade = 'INC';
                $debug_output .= " | final_grade: INC<br>";
                // Set incomplete grade
                $final_computation = 0.0; // Use 0.0 instead of null for database compatibility
                $quizzes = 0;
                $attendance = 0;
                $activities = 0;
                $participation = 0;
                $final_project = 0;
                $midterm_exam = 0;
                $final_exam = 0;
            } else {
                // Calculate final computation
                $weights = [
                    'quizzes' => 0.15,
                    'attendance' => 0.10,
                    'activities' => 0.10,
                    'participation' => 0.10,
                    'final_project' => 0.15,
                    'midterm_exam' => 0.20,
                    'final_exam' => 0.20
                ];

                $final_computation = 0;
                $all_scores_present = true;

                foreach ($weights as $component => $weight) {
                    $score = floatval($components[$component] ?? 0);
                    if ($score === 0) {
                        $all_scores_present = false;
                    }
                    $final_computation += ($score * $weight);
                }

                // Calculate final grade (1-5 scale)
                $final_grade = null;
                if ($all_scores_present) {
                    if ($final_computation >= 90) $final_grade = 1;
                    else if ($final_computation >= 85) $final_grade = 1.5;
                    else if ($final_computation >= 80) $final_grade = 2;
                    else if ($final_computation >= 75) $final_grade = 2.5;
                    else if ($final_computation >= 70) $final_grade = 3;
                    else if ($final_computation >= 65) $final_grade = 3.5;
                    else if ($final_computation >= 60) $final_grade = 4;
                    else $final_grade = 5;
                }
                $debug_output .= " | final_grade: $final_grade<br>";
                // Convert values to proper types
                $quizzes = floatval($components['quizzes']);
                $attendance = floatval($components['attendance']);
                $activities = floatval($components['activities']);
                $participation = floatval($components['participation']);
                $final_project = floatval($components['final_project']);
                $midterm_exam = floatval($components['midterm_exam']);
                $final_exam = floatval($components['final_exam']);
            }

            // Ensure final_computation is always a float
            $final_computation = floatval($final_computation);
            
            // Ensure final_grade is properly set
            if ($final_grade === null) {
                $final_grade = 0; // Default value when no grade can be calculated
            }

            // Check if grades already exist for this student, subject, and semester
            if (!$check_stmt->bind_param("sss", $student_id, $subject, $semester)) {
                throw new Exception("Binding parameters failed for check: " . $check_stmt->error);
            }

            if (!$check_stmt->execute()) {
                throw new Exception("Execute failed for check: " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];

            if ($count > 0) {
                // Update existing grades for this specific subject and semester
                $update_query = "UPDATE student_grades SET 
                    quizzes = ?, 
                    attendance = ?, 
                    activities = ?, 
                    participation = ?, 
                    final_project = ?, 
                    midterm_exam = ?, 
                    final_exam = ?, 
                    final_computation = ?, 
                    final_grade = ?, 
                    date_uploaded = NOW() 
                    WHERE student_id = ? AND subject = ? AND semester = ?";
                
                $update_stmt = $con->prepare($update_query);
                if (!$update_stmt) {
                    throw new Exception("Prepare failed for update query: " . $con->error);
                }

                if (!$update_stmt->bind_param("ddddddddssss", 
                    $quizzes, $attendance, $activities, $participation,
                    $final_project, $midterm_exam, $final_exam,
                    $final_computation, $final_grade,
                    $student_id, $subject, $semester
                )) {
                    throw new Exception("Binding parameters failed for update: " . $update_stmt->error);
                }

                if (!$update_stmt->execute()) {
                    throw new Exception("Execute failed for update: " . $update_stmt->error);
                }
            } else {
                // Insert new grades for this subject and semester using a simpler approach
                $insert_query = sprintf("INSERT INTO student_grades 
                (student_id, teacher_email, class_section, subject, semester, 
                quizzes, attendance, activities, participation, final_project, 
                midterm_exam, final_exam, final_computation, final_grade, date_uploaded) 
                VALUES ('%s', '%s', '%s', '%s', '%s', %f, %f, %f, %f, %f, %f, %f, %f, '%s', NOW())",
                    $con->real_escape_string($student_id),
                    $con->real_escape_string($teacher_email),
                    $con->real_escape_string($class),
                    $con->real_escape_string($subject),
                    $con->real_escape_string($semester),
                    $quizzes,
                    $attendance,
                    $activities,
                    $participation,
                    $final_project,
                    $midterm_exam,
                    $final_exam,
                    $final_computation,
                    $con->real_escape_string($final_grade)
                );

                // Debug: Show the query being executed
                $debug_output .= "DEBUG: Executing query for student $student_id<br>";
                $debug_output .= "Query: " . htmlspecialchars($insert_query) . "<br>";

                if (!$con->query($insert_query)) {
                    throw new Exception("Execute failed for insert: " . $con->error);
                }
            }
        }
        $debug_output .= "</div>";
        echo $debug_output;

        // Commit transaction
        $con->commit();
        
        // Redirect back with success message
        header("Location: upload_grades.php?success=1");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $con->rollback();
        error_log("Error processing grades: " . $e->getMessage());
        
        // Redirect back with error message
        header("Location: upload_grades.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // If not POST request, redirect back
    header("Location: upload_grades.php");
    exit();
}
?> 