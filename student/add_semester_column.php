<?php
require('../databaseConnector/connector.php');

// Check if semester column exists
$check_semester = "SHOW COLUMNS FROM `student_profiles` LIKE 'semester'";
$result_semester = mysqli_query($con, $check_semester);

if(mysqli_num_rows($result_semester) == 0) {
    // Add semester column if it doesn't exist
    $alter_semester = "ALTER TABLE `student_profiles` ADD `semester` VARCHAR(20) DEFAULT NULL";
    if(mysqli_query($con, $alter_semester)) {
        echo "Semester column added successfully!";
    } else {
        echo "Error adding semester column: " . mysqli_error($con);
    }
} else {
    echo "Semester column already exists!";
}
?> 