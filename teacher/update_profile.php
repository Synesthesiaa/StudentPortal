<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $phone = $_POST['phone'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $department = $_POST['department'] ?? '';
    $designation = $_POST['designation'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $subjects = $_POST['subjects'] ?? '';
    $class_assigned = $_POST['class_assigned'] ?? '';
    
    // Handle file upload
    $avatar_path = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/avatars/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('teacher_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                $avatar_path = 'uploads/avatars/' . $new_filename;
            }
        }
    }
    
    // First check if profile exists
    $check_query = "SELECT * FROM teacher_profiles WHERE email = ?";
    $check_stmt = $con->prepare($check_query);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Profile doesn't exist, create it
        $insert_query = "INSERT INTO teacher_profiles (email, phone, emergency_contact, birthday, department, designation, experience, subjects, class_assigned";
        $insert_values = "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?";
        $insert_params = array($email, $phone, $emergency_contact, $birthday, $department, $designation, $experience, $subjects, $class_assigned);
        $insert_types = "sssssssss";
        
        if ($avatar_path) {
            $insert_query .= ", avatar";
            $insert_values .= ", ?";
            $insert_params[] = $avatar_path;
            $insert_types .= "s";
        }
        
        $insert_query .= ") " . $insert_values . ")";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param($insert_types, ...$insert_params);
    } else {
        // Profile exists, update it
        $update_query = "UPDATE teacher_profiles SET 
                        phone = ?,
                        emergency_contact = ?,
                        birthday = ?,
                        department = ?, 
                        designation = ?,
                        experience = ?,
                        subjects = ?,
                        class_assigned = ?";
        
        $params = array($phone, $emergency_contact, $birthday, $department, $designation, $experience, $subjects, $class_assigned);
        $types = "ssssssss";
        
        if ($avatar_path) {
            $update_query .= ", avatar = ?";
            $params[] = $avatar_path;
            $types .= "s";
        }
        
        $update_query .= " WHERE email = ?";
        $params[] = $email;
        $types .= "s";
        
        $stmt = $con->prepare($update_query);
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        // Get the referring page
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'teacher_dashboard.php';
        // Extract the page name from the URL
        $page = basename(parse_url($referer, PHP_URL_PATH));
        // If page is not a valid teacher page, default to dashboard
        $valid_pages = ['teacher_dashboard.php', 'manage_students.php', 'manage_grades.php', 'manage_schedule.php', 'settings.php'];
        if (!in_array($page, $valid_pages)) {
            $page = 'teacher_dashboard.php';
        }
        header("Location: $page");
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . mysqli_error($con);
        // Get the referring page
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'teacher_dashboard.php';
        // Extract the page name from the URL
        $page = basename(parse_url($referer, PHP_URL_PATH));
        // If page is not a valid teacher page, default to dashboard
        $valid_pages = ['teacher_dashboard.php', 'manage_students.php', 'manage_grades.php', 'manage_schedule.php', 'settings.php'];
        if (!in_array($page, $valid_pages)) {
            $page = 'teacher_dashboard.php';
        }
        header("Location: $page");
    }
    exit();
} else {
    // If not POST request, redirect to dashboard
    header("Location: teacher_dashboard.php");
    exit();
}
?> 