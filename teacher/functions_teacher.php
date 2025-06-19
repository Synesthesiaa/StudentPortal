<?php
// Utility functions for teacher pages

function getNumericYear($year) {
    $year_map = [
        '1st Year' => '1',
        '2nd Year' => '2',
        '3rd Year' => '3',
        '4th Year' => '4'
    ];
    return $year_map[$year] ?? $year;
}

function getRegisteredStudentsCount($con, $program, $year, $section, $subject_code = '') {
    $query = "SELECT COUNT(*) as count FROM student_profiles 
              WHERE program = ? AND year_level = ? AND section = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sss", $program, $year, $section);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] ?? 0;
}

function getUniqueSections($con, $program, $year) {
    $query = "SELECT DISTINCT section FROM student_profiles 
              WHERE program = ? AND year_level = ? 
              ORDER BY section";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ss", $program, $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $sections = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sections[] = $row['section'];
    }
    return $sections;
}

function matchesAssignedClassAndSubject($program, $year, $section, $subject_code, $assigned_classes, $assigned_subjects) {
    $numeric_year = getNumericYear($year);
    $class_format = $program . '-' . $numeric_year . $section;
    $assigned_classes_array = array_map('trim', explode(',', $assigned_classes));
    $assigned_subjects_array = array_map('trim', explode(',', $assigned_subjects));
    $class_match = in_array($class_format, $assigned_classes_array);
    $subject_match = in_array($subject_code, $assigned_subjects_array);
    return $class_match && $subject_match;
} 