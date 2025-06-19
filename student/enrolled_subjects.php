<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');
require_once('functions_student.php');

$query = "SELECT sp.*, sl.create_datetime 
          FROM student_profiles sp 
          JOIN signup_login sl ON sp.email = sl.email 
          WHERE sp.email = '{$_SESSION['email']}'";
$result = mysqli_query($con, $query);
$profile = mysqli_fetch_assoc($result);

// Fetch instructors for the student's classes
$instructors_query = "SELECT DISTINCT tp.*, sl.fname, sl.lname 
                     FROM teacher_profiles tp 
                     JOIN signup_login sl ON tp.email = sl.email 
                     WHERE tp.class_assigned LIKE ? 
                     AND tp.subjects IS NOT NULL 
                     LIMIT 3";
$stmt = $con->prepare($instructors_query);
$class_pattern = "%{$profile['program']}-{$profile['year_level']}{$profile['section']}%";
$stmt->bind_param("s", $class_pattern);
$stmt->execute();
$instructors_result = $stmt->get_result();
$instructors = [];
while ($row = $instructors_result->fetch_assoc()) {
    $instructors[] = $row;
}

// Fetch student's year level, program, and semester
$query = "SELECT student_id, year_level, program, semester, section FROM student_profiles WHERE email = '{$_SESSION['email']}'";
$result = mysqli_query($con, $query);
$profile_data = mysqli_fetch_assoc($result);
$year_level = $profile_data['year_level'] ?? '';
$program = $profile_data['program'] ?? '';
$semester = $profile_data['semester'] ?? '1st Semester';
$student_id = $profile_data['student_id'] ?? '';
$section = $profile_data['section'] ?? '';

// Define subjects for each program and year level
$subjects = [
    'BSIT' => [
        '1st Year' => [
            '1st Semester' => [
            ['code' => 'IT1-101', 'name' => 'Introduction to Computing', 'units' => 3],
            ['code' => 'IT1-102', 'name' => 'Computer Programming 1', 'units' => 3],
            ['code' => 'IT1-103', 'name' => 'Computer Programming 2', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'IT2-104', 'name' => 'Data Structures and Algorithms', 'units' => 3],
            ['code' => 'IT2-105', 'name' => 'Web Development', 'units' => 3],
            ['code' => 'IT2-106', 'name' => 'Database Management Systems', 'units' => 3],
            ]
        ],
        '2nd Year' => [
            '1st Semester' => [
            ['code' => 'IT1-201', 'name' => 'Database Management Systems', 'units' => 3],
            ['code' => 'IT1-202', 'name' => 'Object-Oriented Programming', 'units' => 3],
            ['code' => 'IT1-203', 'name' => 'Networking 1', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'IT2-204', 'name' => 'Systems Analysis and Design', 'units' => 3],
            ['code' => 'IT2-205', 'name' => 'Mobile Application Development', 'units' => 3],
            ['code' => 'IT2-206', 'name' => 'Web Technologies', 'units' => 3],
            ]
        ],
        '3rd Year' => [
            '1st Semester' => [
            ['code' => 'IT1-301', 'name' => 'Advanced Database Systems', 'units' => 3],
            ['code' => 'IT1-302', 'name' => 'Web Application Development', 'units' => 3],
            ['code' => 'IT1-303', 'name' => 'Networking 2', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'IT2-304', 'name' => 'Software Engineering', 'units' => 3],
            ['code' => 'IT2-305', 'name' => 'Information Security', 'units' => 3],
            ['code' => 'IT2-306', 'name' => 'Cloud Computing', 'units' => 3],
            ]
        ],
        '4th Year' => [
            '1st Semester' => [
            ['code' => 'IT1-401', 'name' => 'IT Project Management', 'units' => 3],
            ['code' => 'IT1-402', 'name' => 'Cloud Computing', 'units' => 3],
            ['code' => 'IT1-403', 'name' => 'Artificial Intelligence', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'IT2-404', 'name' => 'IT Capstone Project', 'units' => 3],
            ['code' => 'IT2-405', 'name' => 'IT Internship', 'units' => 3],
            ['code' => 'IT2-406', 'name' => 'Emerging Technologies', 'units' => 3],
            ]
        ]
    ],
    'BSCS' => [
        '1st Year' => [
            '1st Semester' => [
            ['code' => 'CS1-101', 'name' => 'Introduction to Computer Science', 'units' => 3],
            ['code' => 'CS1-102', 'name' => 'Programming Fundamentals', 'units' => 3],
            ['code' => 'CS1-103', 'name' => 'Discrete Mathematics', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CS2-104', 'name' => 'Computer Architecture', 'units' => 3],
            ['code' => 'CS2-105', 'name' => 'Data Structures', 'units' => 3],
            ['code' => 'CS2-106', 'name' => 'Object-Oriented Programming', 'units' => 3],
            ]
        ],
        '2nd Year' => [
            '1st Semester' => [
            ['code' => 'CS1-201', 'name' => 'Algorithms and Complexity', 'units' => 3],
            ['code' => 'CS1-202', 'name' => 'Operating Systems', 'units' => 3],
            ['code' => 'CS1-203', 'name' => 'Computer Networks', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CS2-204', 'name' => 'Software Engineering', 'units' => 3],
            ['code' => 'CS2-205', 'name' => 'Database Systems', 'units' => 3],
            ['code' => 'CS2-206', 'name' => 'Web Development', 'units' => 3],
            ]
        ],
        '3rd Year' => [
            '1st Semester' => [
            ['code' => 'CS1-301', 'name' => 'Artificial Intelligence', 'units' => 3],
            ['code' => 'CS1-302', 'name' => 'Machine Learning', 'units' => 3],
            ['code' => 'CS1-303', 'name' => 'Computer Graphics', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CS2-304', 'name' => 'Compiler Design', 'units' => 3],
            ['code' => 'CS2-305', 'name' => 'Computer Security', 'units' => 3],
            ['code' => 'CS2-306', 'name' => 'Distributed Systems', 'units' => 3],
            ]
        ],
        '4th Year' => [
            '1st Semester' => [
            ['code' => 'CS1-401', 'name' => 'Advanced AI', 'units' => 3],
            ['code' => 'CS1-402', 'name' => 'Distributed Systems', 'units' => 3],
            ['code' => 'CS1-403', 'name' => 'Computer Vision', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CS2-404', 'name' => 'CS Capstone Project', 'units' => 3],
            ['code' => 'CS2-405', 'name' => 'CS Internship', 'units' => 3],
            ['code' => 'CS2-406', 'name' => 'Advanced Topics in CS', 'units' => 3],
            ]
        ]
    ],
    'BSCE' => [
        '1st Year' => [
            '1st Semester' => [
            ['code' => 'CE1-101', 'name' => 'Introduction to Computer Engineering', 'units' => 3],
            ['code' => 'CE1-102', 'name' => 'Digital Logic Design', 'units' => 3],
            ['code' => 'CE1-103', 'name' => 'Computer Organization', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CE2-104', 'name' => 'Programming for Engineers', 'units' => 3],
            ['code' => 'CE2-105', 'name' => 'Circuit Analysis', 'units' => 3],
            ['code' => 'CE2-106', 'name' => 'Microprocessors', 'units' => 3],
            ]
        ],
        '2nd Year' => [
            '1st Semester' => [
            ['code' => 'CE1-201', 'name' => 'Microprocessors', 'units' => 3],
            ['code' => 'CE1-202', 'name' => 'Computer Architecture', 'units' => 3],
            ['code' => 'CE1-203', 'name' => 'Embedded Systems', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CE2-204', 'name' => 'Digital Systems', 'units' => 3],
            ['code' => 'CE2-205', 'name' => 'Computer Networks', 'units' => 3],
            ['code' => 'CE2-206', 'name' => 'VLSI Design', 'units' => 3],
            ]
        ],
        '3rd Year' => [
            '1st Semester' => [
            ['code' => 'CE1-301', 'name' => 'VLSI Design', 'units' => 3],
            ['code' => 'CE1-302', 'name' => 'Computer Security', 'units' => 3],
            ['code' => 'CE1-303', 'name' => 'Real-time Systems', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CE2-304', 'name' => 'Robotics', 'units' => 3],
            ['code' => 'CE2-305', 'name' => 'Computer Vision', 'units' => 3],
            ['code' => 'CE2-306', 'name' => 'IoT Systems', 'units' => 3],
            ]
        ],
        '4th Year' => [
            '1st Semester' => [
            ['code' => 'CE1-401', 'name' => 'Advanced Computer Architecture', 'units' => 3],
            ['code' => 'CE1-402', 'name' => 'IoT Systems', 'units' => 3],
            ['code' => 'CE1-403', 'name' => 'Hardware Security', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CE2-404', 'name' => 'CE Capstone Project', 'units' => 3],
            ['code' => 'CE2-405', 'name' => 'CE Internship', 'units' => 3],
            ['code' => 'CE2-406', 'name' => 'Advanced Digital Systems', 'units' => 3],
            ]
        ]
    ],
    'BSEE' => [
        '1st Year' => [
            '1st Semester' => [
            ['code' => 'EE1-101', 'name' => 'Introduction to Electrical Engineering', 'units' => 3],
            ['code' => 'EE1-102', 'name' => 'Circuit Analysis 1', 'units' => 3],
            ['code' => 'EE1-103', 'name' => 'Electronics 1', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'EE2-104', 'name' => 'Digital Electronics', 'units' => 3],
            ['code' => 'EE2-105', 'name' => 'Engineering Mathematics', 'units' => 3],
            ['code' => 'EE2-106', 'name' => 'Electromagnetics', 'units' => 3],
            ]
        ],
        '2nd Year' => [
            '1st Semester' => [
            ['code' => 'EE1-201', 'name' => 'Circuit Analysis 2', 'units' => 3],
            ['code' => 'EE1-202', 'name' => 'Electronics 2', 'units' => 3],
            ['code' => 'EE1-203', 'name' => 'Electromagnetics', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'EE2-204', 'name' => 'Power Systems', 'units' => 3],
            ['code' => 'EE2-205', 'name' => 'Control Systems', 'units' => 3],
            ['code' => 'EE2-206', 'name' => 'Power Electronics', 'units' => 3],
            ]
        ],
        '3rd Year' => [
            '1st Semester' => [
            ['code' => 'EE1-301', 'name' => 'Power Electronics', 'units' => 3],
            ['code' => 'EE1-302', 'name' => 'Electric Machines', 'units' => 3],
            ['code' => 'EE1-303', 'name' => 'Communication Systems', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'EE2-304', 'name' => 'Digital Signal Processing', 'units' => 3],
            ['code' => 'EE2-305', 'name' => 'Power Distribution', 'units' => 3],
            ['code' => 'EE2-306', 'name' => 'Renewable Energy', 'units' => 3],
            ]
        ],
        '4th Year' => [
            '1st Semester' => [
            ['code' => 'EE1-401', 'name' => 'Renewable Energy Systems', 'units' => 3],
            ['code' => 'EE1-402', 'name' => 'Smart Grid Technology', 'units' => 3],
            ['code' => 'EE1-403', 'name' => 'Power System Protection', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'EE2-404', 'name' => 'EE Capstone Project', 'units' => 3],
            ['code' => 'EE2-405', 'name' => 'EE Internship', 'units' => 3],
            ['code' => 'EE2-406', 'name' => 'Advanced Power Systems', 'units' => 3],
            ]
        ]
    ],
    'BSChem' => [
        '1st Year' => [
            '1st Semester' => [
            ['code' => 'CHEM1-101', 'name' => 'General Chemistry', 'units' => 3],
            ['code' => 'CHEM1-102', 'name' => 'Organic Chemistry 1', 'units' => 3],
            ['code' => 'CHEM1-103', 'name' => 'Physical Chemistry 1', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CHEM2-104', 'name' => 'Analytical Chemistry', 'units' => 3],
            ['code' => 'CHEM2-105', 'name' => 'Chemical Engineering Principles', 'units' => 3],
            ['code' => 'CHEM2-106', 'name' => 'Chemical Thermodynamics', 'units' => 3],
            ]
        ],
        '2nd Year' => [
            '1st Semester' => [
            ['code' => 'CHEM1-201', 'name' => 'Organic Chemistry 2', 'units' => 3],
            ['code' => 'CHEM1-202', 'name' => 'Physical Chemistry 2', 'units' => 3],
            ['code' => 'CHEM1-203', 'name' => 'Chemical Thermodynamics', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CHEM2-204', 'name' => 'Chemical Kinetics', 'units' => 3],
            ['code' => 'CHEM2-205', 'name' => 'Unit Operations', 'units' => 3],
            ['code' => 'CHEM2-206', 'name' => 'Process Control', 'units' => 3],
            ]
        ],
        '3rd Year' => [
            '1st Semester' => [
            ['code' => 'CHEM1-301', 'name' => 'Chemical Process Design', 'units' => 3],
            ['code' => 'CHEM1-302', 'name' => 'Transport Phenomena', 'units' => 3],
            ['code' => 'CHEM1-303', 'name' => 'Chemical Reaction Engineering', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CHEM2-304', 'name' => 'Process Control', 'units' => 3],
            ['code' => 'CHEM2-305', 'name' => 'Plant Design', 'units' => 3],
            ['code' => 'CHEM2-306', 'name' => 'Environmental Engineering', 'units' => 3],
            ]
        ],
        '4th Year' => [
            '1st Semester' => [
            ['code' => 'CHEM1-401', 'name' => 'Process Safety', 'units' => 3],
            ['code' => 'CHEM1-402', 'name' => 'Environmental Engineering', 'units' => 3],
            ['code' => 'CHEM1-403', 'name' => 'Plant Economics', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'CHEM2-404', 'name' => 'ChemE Capstone Project', 'units' => 3],
            ['code' => 'CHEM2-405', 'name' => 'ChemE Internship', 'units' => 3],
            ['code' => 'CHEM2-406', 'name' => 'Advanced Process Design', 'units' => 3],
            ]
        ]
    ],
    'BSME' => [
        '1st Year' => [
            '1st Semester' => [
            ['code' => 'ME1-101', 'name' => 'Introduction to Mechanical Engineering', 'units' => 3],
            ['code' => 'ME1-102', 'name' => 'Engineering Mechanics', 'units' => 3],
            ['code' => 'ME1-103', 'name' => 'Engineering Materials', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'ME2-104', 'name' => 'Engineering Drawing', 'units' => 3],
            ['code' => 'ME2-105', 'name' => 'Thermodynamics 1', 'units' => 3],
            ['code' => 'ME2-106', 'name' => 'Fluid Mechanics', 'units' => 3],
            ]
        ],
        '2nd Year' => [
            '1st Semester' => [
            ['code' => 'ME1-201', 'name' => 'Fluid Mechanics', 'units' => 3],
            ['code' => 'ME1-202', 'name' => 'Heat Transfer', 'units' => 3],
            ['code' => 'ME1-203', 'name' => 'Machine Design', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'ME2-204', 'name' => 'Manufacturing Processes', 'units' => 3],
            ['code' => 'ME2-205', 'name' => 'Thermodynamics 2', 'units' => 3],
            ['code' => 'ME2-206', 'name' => 'Mechanical Vibrations', 'units' => 3],
            ]
        ],
        '3rd Year' => [
            '1st Semester' => [
            ['code' => 'ME1-301', 'name' => 'Mechanical Vibrations', 'units' => 3],
            ['code' => 'ME1-302', 'name' => 'Control Systems', 'units' => 3],
            ['code' => 'ME1-303', 'name' => 'Power Plants', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'ME2-304', 'name' => 'Robotics', 'units' => 3],
            ['code' => 'ME2-305', 'name' => 'Automotive Engineering', 'units' => 3],
            ['code' => 'ME2-306', 'name' => 'Energy Systems', 'units' => 3],
            ]
        ],
        '4th Year' => [
            '1st Semester' => [
            ['code' => 'ME1-401', 'name' => 'Energy Systems', 'units' => 3],
            ['code' => 'ME1-402', 'name' => 'HVAC Systems', 'units' => 3],
            ['code' => 'ME1-403', 'name' => 'Renewable Energy', 'units' => 3],
            ],
            '2nd Semester' => [
            ['code' => 'ME2-404', 'name' => 'ME Capstone Project', 'units' => 3],
            ['code' => 'ME2-405', 'name' => 'ME Internship', 'units' => 3],
            ['code' => 'ME2-406', 'name' => 'Advanced Manufacturing', 'units' => 3],
            ]
        ]
    ]
];

// Validate and format year level
$valid_year_levels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
$year_level = trim($year_level); // Remove any whitespace

// Map various year level formats to standard format
$year_level_map = [
    '1' => '1st Year',
    '2' => '2nd Year',
    '3' => '3rd Year',
    '4' => '4th Year',
    '1st' => '1st Year',
    '2nd' => '2nd Year',
    '3rd' => '3rd Year',
    '4th' => '4th Year',
    '1st Year' => '1st Year',
    '2nd Year' => '2nd Year',
    '3rd Year' => '3rd Year',
    '4th Year' => '4th Year'
];

// Convert year level to standard format if needed
if (isset($year_level_map[$year_level])) {
    $year_level = $year_level_map[$year_level];
}

// Check if the year level is valid
if (!in_array($year_level, $valid_year_levels)) {
    $year_level = ''; // Reset to empty if invalid
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enrolled Subjects</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="scroll.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-white font-sans min-h-screen">

<!-- Mobile Sidebar Overlay -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300" id="sidebarOverlay"></div>

<div class="flex flex-col min-h-screen">

     <!-- Mobile Sidebar (visible on screens 1019px and below) -->
    <div class="fixed z-50 flex flex-col items-center bg-green-600 text-white rounded-2xl m-2 p-4 w-64 max-h-[500px] transition-all duration-300 -translate-x-full hidden overflow-hidden" id="sidebar" style="top: 0.5rem; left: 0;">
        <div class="mb-8">
            <img src="../photos/logo.png" class="px-4 items-center max-w-full h-auto" alt="Logo">
        </div>
        <div class="flex flex-col w-full h-full overflow-y-auto">
            <nav class="flex flex-col w-full space-y-2">
                <a href="student_dashboard.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition active:bg-white active:text-green-600 text-sm">
                    <i class="fas fa-home flex-shrink-0 text-xl"></i>
                    <span>Dashboard</span>
                </a>
                <a href="enrolled_subjects.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-book flex-shrink-0 text-xl"></i>
                    <span>Enrolled Subjects</span>
                </a>
                <a href="view_grades.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-graduation-cap flex-shrink-0 text-xl"></i>
                    <span>Grades</span>
                </a>
                <a href="schedule.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-calendar-alt flex-shrink-0 text-xl"></i>
                    <span>Schedule</span>
                </a>
                <a href="settings.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-cog flex-shrink-0 text-xl"></i>
                    <span>Settings</span>
                </a>
            </nav>
            <div class="my-20"></div>
            <a href="../login_registration/logout.php" class="mt-20 flex items-center space-x-3 px-4 py-2 rounded-lg font-semibold bg-white text-green-600 hover:bg-green-700 hover:text-white transition text-sm">
                <i class="fas fa-sign-out-alt flex-shrink-0 text-xl"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Desktop Sidebar (visible on screens 1020px and above) -->
    <div class="hidden xl:flex xl:fixed xl:z-50 xl:flex-col xl:items-center xl:bg-green-600 xl:text-white xl:rounded-2xl xl:m-5 xl:p-8 xl:w-56 xl:max-h-[600px] xl:overflow-hidden" id="desktopSidebar">
        <div class="mb-10">
            <img src="../photos/logo.png" class="px-4 items-center max-w-full h-auto" alt="Logo">
        </div>
        <div class="flex flex-col w-full h-full overflow-y-auto">
            <nav class="flex flex-col w-full space-y-2">

                <a href="student_dashboard.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition active:bg-white active:text-green-600 text-base">
                    <i class="fas fa-home flex-shrink-0 text-xl"></i>
                    <span>Dashboard</span>
                </a>
                <a href="enrolled_subjects.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
                    <i class="fas fa-book flex-shrink-0 text-xl"></i>
                    <span>Enrolled Subjects</span>
                </a>
                <a href="view_grades.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
                    <i class="fas fa-graduation-cap flex-shrink-0 text-xl"></i>
                    <span>Grades</span>
                </a>
                <a href="schedule.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
                    <i class="fas fa-calendar-alt flex-shrink-0 text-xl"></i>
                    <span>Schedule</span>
                </a>
                <a href="settings.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
                    <i class="fas fa-cog flex-shrink-0 text-xl"></i>
                    <span>Settings</span>
                </a>
            </nav>
            <div class="my-20"></div>
            <a href="../signup_login/logout.php" class="mt-20 flex items-center space-x-3 px-6 py-3 rounded-lg font-semibold bg-white text-green-600 hover:bg-green-700 hover:text-white transition text-base">
                <i class="fas fa-sign-out-alt flex-shrink-0 text-xl"></i>
                <span>Logout</span>
            </a>
            
        </div>
    </div>

    <!-- Main Content and Right Panel -->
    <div class="flex-1 flex flex-col xl:flex-row xl:ml-64">
        <!-- Main Content -->
        <div class="flex-1 flex flex-col m-2 lg:m-5">
            <!-- Topbar -->
            <div class="flex items-center justify-between mb-6 gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Mobile Sidebar Toggle Button (visible only on screens below 1020px) -->
                    <button class="xl:hidden text-2xl text-green-600 hover:text-green-700 transition-colors" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <img src="<?php echo !empty($profile['avatar']) ? '../' . htmlspecialchars($profile['avatar']) : '../photo/user-avatar-default.png'; ?>" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover border-2 border-green-600" alt="Profile Picture">
                    <div class="hidden sm:block min-w-0">
                        <div class="font-bold text-sm sm:text-base truncate"><?php echo $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?></div>
                        <div class="text-xs text-gray-500 truncate"><?php echo $_SESSION['email']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Welcome -->
            <div class="flex flex-col lg:flex-row items-center justify-between bg-green-600 text-white rounded-2xl p-4 lg:p-8 mb-6 lg:mb-8 shadow gap-4">
                <div class="w-full lg:w-auto text-center lg:text-left">
                    <div class="text-xs lg:text-sm opacity-80 mb-1"><?php echo date('F j, Y'); ?></div>
                    <h2 class="text-xl lg:text-2xl font-bold mb-1">Hello, <?php echo $_SESSION['fname']; ?>!</h2>
                    <div class="text-sm lg:text-base">Below is the list of your Enrolled Subjects in <?php echo htmlspecialchars($program); ?> - <?php echo htmlspecialchars($year_level); ?> program.</div>
                </div>
                <img src="../photos/cvsu-naic.jpg" class="w-full max-w-xs lg:w-80 lg:max-w-none rounded-2xl object-cover" alt="CvSU Naic Campus">
            </div>

            <!-- Enrolleed Subjects -->
        <div class="container mx-auto">
          <div class="bg-white rounded-xl shadow-lg p-6"> 
            <?php if (empty($year_level) || empty($program)): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
                    <p>Please set your year level and program in your profile settings to view your enrolled subjects.</p>
                    <p class="mt-2">Valid year levels are: 1, 2, 3, 4 or 1st Year, 2nd Year, 3rd Year, 4th Year</p>
                    <p class="mt-2">Current year level: <?php echo htmlspecialchars($profile['year_level'] ?? 'Not set'); ?></p>
                    <p class="mt-2">Current program: <?php echo htmlspecialchars($profile['program'] ?? 'Not set'); ?></p>
                </div>
            <?php else: ?>
                <div class="mb-6">
                    <?php
                    // Get subjects for the current semester
                    $semesterSubjects = isset($subjects[$program][$year_level][$semester]) ? $subjects[$program][$year_level][$semester] : [];
                    ?>
                    
                    <!-- Display Subjects based on Profile Semester -->
                    <div class="overflow-x-auto">
                        <h3 class="text-lg font-semibold mb-3"><?php echo htmlspecialchars($semester); ?> Subjects</h3>
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr class="bg-green-600 text-white">
                                    <th class="py-3 px-4 text-left">Subject Code</th>
                                    <th class="py-3 px-4 text-left">Subject Name</th>
                                    <th class="py-3 px-4 text-center">Units</th>
                                    <th class="py-3 px-4 text-center">Section</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($semesterSubjects)): ?>
                                    <?php foreach ($semesterSubjects as $subject): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-4"><?php echo htmlspecialchars($subject['code']); ?></td>
                                        <td class="py-3 px-4"><?php echo htmlspecialchars($subject['name']); ?></td>
                                        <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($subject['units']); ?></td>
                                            <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($section ? $program . '-' . $year_level[0] . $section : 'Not set'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-gray-500">
                                            No subjects found for <?php echo htmlspecialchars($semester); ?>.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
     </div>
        
        <!-- Right Panel -->
        <div class="w-full xl:w-80 flex-shrink-0 m-2 lg:m-5 xl:ml-4 space-y-4 lg:space-y-6">
            <!-- User Profile -->
            <div class="bg-white p-4 lg:p-6 shadow rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <button onclick="openProfileDialog()" class="flex items-center space-x-3 px-4 lg:px-6 py-2 lg:py-3 rounded-lg font-medium hover:bg-gray-100 transition text-sm lg:text-base">
                        <i class="fas fa-user text-green-600"></i>
                        <span>User Profile</span>
                    </button>
                    <button onclick="openEditProfileDialog()" class="text-green-600 hover:text-green-700 transition-colors p-2">
                        <i class="fas fa-edit text-xl"></i>
                    </button>
                </div>
                
                <button onclick="openProfileDialog()" class="block hover:opacity-90 transition-opacity w-full">
                    <div class="flex items-center justify-center mb-4">
                        <img src="<?php echo !empty($profile['avatar']) ? '../' . htmlspecialchars($profile['avatar']) : '../photo/user-avatar-default.png'; ?>" class="w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 rounded-full border-4 border-white shadow object-cover" alt="Profile Picture">
                    </div>
                    <div class="text-center text-sm text-gray-600">
                        Year Level: <?php echo htmlspecialchars($profile['year_level'] ?? 'Not set'); ?>
                    </div>
                </button>
            </div>

            <!-- Profile Dialog -->
            <div id="profileDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
                <div class="bg-white rounded-xl p-6 w-full max-w-md">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">User Profile</h3>
                        <button onclick="closeProfileDialog()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="text-center mb-6">
                        <img src="<?php echo !empty($profile['avatar']) ? '../' . htmlspecialchars($profile['avatar']) : '../photo/user-avatar-default.png'; ?>" 
                             class="w-32 h-32 rounded-full border-4 border-green-500 mx-auto mb-4 object-cover" 
                             alt="Profile Picture">
                        <h4 class="text-xl font-semibold"><?php echo $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?></h4>
                        <p class="text-gray-600"><?php echo $_SESSION['email']; ?></p>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Student ID:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($student_id ?: 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Program:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($program ?: 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Section:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($section ?: 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Year Level:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($year_level ?: 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Semester:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($semester ?: 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Gender:</span>
                            <span class="font-medium"><?php echo ucfirst(htmlspecialchars($profile['gender'] ?? 'Not set')); ?></span>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button onclick="closeProfileDialog()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Close
                        </button>
                        <button onclick="openEditProfileDialog()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Dialog -->
            <div id="editProfileDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
                <div class="bg-white rounded-xl p-6 w-full max-w-4xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Edit Profile</h3>
                        <button onclick="closeEditProfileDialog()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <!-- Profile Picture -->
                        <div class="text-center mb-6">
                            <img src="<?php echo !empty($profile['avatar']) ? '../' . htmlspecialchars($profile['avatar']) : '../photo/user-avatar-default.png'; ?>" 
                                 class="w-32 h-32 rounded-full border-4 border-green-500 mx-auto mb-4 object-cover" 
                                 alt="Profile Picture" id="previewAvatar">
                            <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden" onchange="previewImage(this)">
                            <button type="button" onclick="document.getElementById('avatar').click()" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                Change Photo
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-green-600 border-b pb-2">Personal Information</h4>
                                
                                <!-- Birthday -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Birthday</label>
                                    <input type="date" name="birthday" value="<?php echo htmlspecialchars($profile['birthday'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                           placeholder="Enter your phone number">
                                </div>

                                <!-- Emergency Contact -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
                                    <input type="text" name="emergency_contact" value="<?php echo htmlspecialchars($profile['emergency_contact'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                           placeholder="Enter emergency contact">
                                </div>

                                <!-- Address -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <textarea name="address" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                              placeholder="Enter your address"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-green-600 border-b pb-2">Academic Information</h4>

                        <!-- Year Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                            <select name="year_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select Year Level</option>
                                <option value="1st Year" <?php echo ($profile['year_level'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo ($profile['year_level'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo ($profile['year_level'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo ($profile['year_level'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                            </select>
                        </div>

                                <!-- Semester -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                    <select name="semester" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Select Semester</option>
                                        <option value="1st Semester" <?php echo ($profile['semester'] == '1st Semester') ? 'selected' : ''; ?>>1st Semester</option>
                                        <option value="2nd Semester" <?php echo ($profile['semester'] == '2nd Semester') ? 'selected' : ''; ?>>2nd Semester</option>
                            </select>
                        </div>

                        <!-- Program -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                            <select name="program" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select Program</option>
                                <option value="BSIT" <?php echo ($profile['program'] == 'BSIT') ? 'selected' : ''; ?>>BSIT - Bachelor of Science in Information Technology</option>
                                <option value="BSCS" <?php echo ($profile['program'] == 'BSCS') ? 'selected' : ''; ?>>BSCS - Bachelor of Science in Computer Science</option>
                                <option value="BSCE" <?php echo ($profile['program'] == 'BSCE') ? 'selected' : ''; ?>>BSCE - Bachelor of Science in Computer Engineering</option>
                                <option value="BSEE" <?php echo ($profile['program'] == 'BSEE') ? 'selected' : ''; ?>>BSEE - Bachelor of Science in Electrical Engineering</option>
                                <option value="BSChem" <?php echo ($profile['program'] == 'BSChem') ? 'selected' : ''; ?>>BSChem - Bachelor of Science in Chemical Engineering</option>
                                <option value="BSME" <?php echo ($profile['program'] == 'BSME') ? 'selected' : ''; ?>>BSME - Bachelor of Science in Mechanical Engineering</option>
                                <option value="BSChemE" <?php echo ($profile['program'] == 'BSChemE') ? 'selected' : ''; ?>>BSChemE - Bachelor of Science in Chemical Engineering</option>
                            </select>
                        </div>

                        <!-- Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                            <select name="section" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Select Section</option>
                                <option value="A" <?php echo ($profile['section'] == 'A') ? 'selected' : ''; ?>>Section A</option>
                                <option value="B" <?php echo ($profile['section'] == 'B') ? 'selected' : ''; ?>>Section B</option>
                                <option value="C" <?php echo ($profile['section'] == 'C') ? 'selected' : ''; ?>>Section C</option>
                                <option value="D" <?php echo ($profile['section'] == 'D') ? 'selected' : ''; ?>>Section D</option>
                                <option value="E" <?php echo ($profile['section'] == 'E') ? 'selected' : ''; ?>>Section E</option>
                            </select>
                        </div>
                        </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeEditProfileDialog()" 
                                    class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Calendar Widget -->
            <div class="bg-white p-4 lg:p-6 shadow rounded-xl">
                <h3 class="text-center mb-3 text-base lg:text-lg font-semibold">Calendar</h3>
                <div id="calendar-datetime" class="font-semibold mb-2 text-center text-xs lg:text-base"></div>
                <div id="calendar-mini" class="overflow-x-auto"></div>
                <div class="text-right mt-2">
                    <button id="expandCalendarBtn" class="text-green-600 font-semibold hover:underline text-xs lg:text-base">Show Full Calendar</button>
                </div>
            </div>
            
            <!-- Instructors Profile Pictures Box -->
            <div class="bg-white p-4 lg:p-6 shadow rounded-xl">
                <h3 class="text-lg font-semibold mb-4 text-center">Instructors</h3>
                <?php if (!empty($instructors)): ?>
                    <div class="flex flex-col space-y-4">
                        <?php foreach ($instructors as $index => $instructor): ?>
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <img src="<?php echo !empty($instructor['avatar']) ? '../' . htmlspecialchars($instructor['avatar']) : '../photo/user-avatar-default.png'; ?>" 
                                         class="w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 border-<?php echo $index === 0 ? 'green' : ($index === 1 ? 'blue' : 'purple'); ?>-500 object-cover" 
                                         alt="<?php echo htmlspecialchars($instructor['fname'] . ' ' . $instructor['lname']); ?>">
                                    <span class="absolute -bottom-1 -right-1 bg-<?php echo $index === 0 ? 'green' : ($index === 1 ? 'blue' : 'purple'); ?>-500 text-white text-xs rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center">
                                        <?php echo $index + 1; ?>
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm sm:text-base truncate">
                                        <?php echo htmlspecialchars($instructor['fname'] . ' ' . $instructor['lname']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-500">
                        No instructors assigned
                    </div>
                <?php endif; ?>
            </div>
        </div>
            </div>
        </div>
        
<!-- Full Calendar Modal -->
<div id="calendar-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Full Calendar</h3>
            <button id="closeCalendarBtn" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="calendar-full"></div>
    </div>
</div>
<script src="../js/student.js"></script>
</body>
</html>