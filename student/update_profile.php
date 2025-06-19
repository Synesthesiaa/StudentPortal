<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    
    // Get current profile data
    $current_query = "SELECT * FROM student_profiles WHERE email = '$email'";
    $current_result = mysqli_query($con, $current_query);
    $current_profile = mysqli_fetch_assoc($current_result);
    
    // Only update fields that are provided and not empty
    $year_level = !empty($_POST['year_level']) ? mysqli_real_escape_string($con, $_POST['year_level']) : $current_profile['year_level'];
    $program = !empty($_POST['program']) ? mysqli_real_escape_string($con, $_POST['program']) : $current_profile['program'];
    $section = !empty($_POST['section']) ? mysqli_real_escape_string($con, $_POST['section']) : $current_profile['section'];
    $semester = !empty($_POST['semester']) ? mysqli_real_escape_string($con, $_POST['semester']) : $current_profile['semester'];
    $phone = !empty($_POST['phone']) ? mysqli_real_escape_string($con, $_POST['phone']) : $current_profile['phone'];
    $emergency_contact = !empty($_POST['emergency_contact']) ? mysqli_real_escape_string($con, $_POST['emergency_contact']) : $current_profile['emergency_contact'];
    $address = !empty($_POST['address']) ? mysqli_real_escape_string($con, $_POST['address']) : $current_profile['address'];
    $birthday = !empty($_POST['birthday']) ? mysqli_real_escape_string($con, $_POST['birthday']) : $current_profile['birthday'];

    // Handle file upload
    $avatar_path = '';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/avatars/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Set proper permissions for the directory
        chmod($upload_dir, 0777);

        $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($file_extension, $allowed_extensions)) {
            // Generate unique filename using timestamp and random string
            $new_filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            // Check file size (max 5MB)
            if ($_FILES['avatar']['size'] <= 5 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    // Set proper permissions for the uploaded file
                    chmod($upload_path, 0644);
                    $avatar_path = 'uploads/avatars/' . $new_filename;
                } else {
                    $_SESSION['error_message'] = "Error uploading file. Please try again.";
                }
            } else {
                $_SESSION['error_message'] = "File size too large. Maximum size is 5MB.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type. Allowed types: JPG, JPEG, PNG, GIF";
        }
    }

    // Update query
    $query = "UPDATE student_profiles SET 
              year_level = '$year_level',
              program = '$program',
              section = '$section',
              semester = '$semester',
              phone = '$phone',
              emergency_contact = '$emergency_contact',
              address = '$address',
              birthday = '$birthday'";

    // Add avatar to update if one was uploaded
    if (!empty($avatar_path)) {
        $query .= ", avatar = '$avatar_path'";
    }

    $query .= " WHERE email = '$email'";

    if (mysqli_query($con, $query)) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        // Get the referring page
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'student_dashboard.php';
        // Extract the page name from the URL
        $page = basename(parse_url($referer, PHP_URL_PATH));
        // If page is not a valid student page, default to dashboard
        $valid_pages = ['student_dashboard.php', 'enrolled_subjects.php', 'view_grades.php', 'schedule.php', 'settings.php'];
        if (!in_array($page, $valid_pages)) {
            $page = 'student_dashboard.php';
        }
        header("Location: $page");
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . mysqli_error($con);
        // Get the referring page
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'student_dashboard.php';
        // Extract the page name from the URL
        $page = basename(parse_url($referer, PHP_URL_PATH));
        // If page is not a valid student page, default to dashboard
        $valid_pages = ['student_dashboard.php', 'enrolled_subjects.php', 'view_grades.php', 'schedule.php', 'settings.php'];
        if (!in_array($page, $valid_pages)) {
            $page = 'student_dashboard.php';
        }
        header("Location: $page");
    }
    exit();
} else {
    // If not POST request, redirect to dashboard
    header("Location: student_dashboard.php");
    exit();
}
?> 