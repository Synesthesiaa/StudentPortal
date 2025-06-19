<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');
require_once('functions_teacher.php');

// Fetch teacher profile data including avatar
$query = "SELECT * FROM teacher_profiles WHERE email = '{$_SESSION['email']}'";
$result = mysqli_query($con, $query);
$profile = mysqli_fetch_assoc($result);

// If no profile exists, create one
if (!$profile) {
    $insert_query = "INSERT INTO teacher_profiles (email) VALUES (?)";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("s", $_SESSION['email']);
    
    if ($stmt->execute()) {
        // Fetch the newly created profile
        $result = mysqli_query($con, $query);
        $profile = mysqli_fetch_assoc($result);
    }
}

$assigned_classes = $profile['class_assigned'] ?? '';

// Debug information
echo "<!-- Debug Information -->";
echo "<!-- Teacher Profile Data: ";
print_r($profile);
echo " -->";


$classes = [
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

// Get teacher's department, assigned classes, and assigned subjects
$department = $profile['department'] ?? '';
$assigned_classes = $profile['class_assigned'] ?? '';
$assigned_subjects = $profile['subjects'] ?? '';

// Debug information
echo "<!-- Department: " . htmlspecialchars($department) . " -->";
echo "<!-- Assigned Classes: " . htmlspecialchars($assigned_classes) . " -->";
echo "<!-- Assigned Subjects: " . htmlspecialchars($assigned_subjects) . " -->";

// Filter classes based on assigned classes and subjects
$filtered_classes = [];
$total_classes = 0;
$total_students = 0;

if (!empty($assigned_classes)) {
    // Split assigned classes and subjects into arrays
    $assigned_classes_array = array_map('trim', explode(',', $assigned_classes));
    $assigned_subjects_array = !empty($assigned_subjects) ? array_map('trim', explode(',', $assigned_subjects)) : [];

    foreach ($classes as $program => $year_levels) {
        foreach ($year_levels as $year => $semesters) {
            // Get unique sections for this program and year
            $sections = getUniqueSections($con, $program, $year);
            
            foreach ($sections as $section) {
                $numeric_year = getNumericYear($year);
                $class_format = $program . '-' . $numeric_year . $section;
                
                if (in_array($class_format, $assigned_classes_array)) {
                    if (!isset($filtered_classes[$program])) {
                        $filtered_classes[$program] = [];
                    }
                    if (!isset($filtered_classes[$program][$year])) {
                        $filtered_classes[$program][$year] = [];
                    }
                    
                    // Get students for this section
                    $student_count = getRegisteredStudentsCount($con, $program, $year, $section, '');
                    
                    if ($student_count > 0) {
                        // Process both semesters
                        foreach ($semesters as $semester => $subjects) {
                        foreach ($subjects as $subject) {
                            // If no subjects are assigned, show all subjects for the class
                            // If subjects are assigned, only show those specific subjects
                            if (empty($assigned_subjects_array) || in_array($subject['code'], $assigned_subjects_array)) {
                                $filtered_classes[$program][$year][] = [
                                    'code' => $subject['code'],
                                    'name' => $subject['name'],
                                        'units' => $subject['units'],
                                    'section' => $section,
                                        'semester' => $semester,
                                    'student_count' => $student_count
                                ];
                                
                                // Update totals
                                $total_classes++;
                                $total_students += $student_count;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Schedule</title>
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
                <a href="teacher_dashboard.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition active:bg-white active:text-green-600 text-sm">
                    <i class="fas fa-home flex-shrink-0 text-xl"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my_classes.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-book flex-shrink-0 text-xl"></i>
                    <span>My Classes</span>
                </a>
                <a href="schedule.php"class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-calendar-alt flex-shrink-0 text-xl"></i>
                    <span>Schedule</span>
                </a>
                <a href="upload_grades.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-chart-bar flex-shrink-0 text-xl"></i>
                    <span>Upload Grades</span>
                </a>
                <a href="settings.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-cog flex-shrink-0 text-xl"></i>
                    <span>Settings</span>
                </a>
            </nav>
            <div class="my-20"></div>
            <a href="../signup_login/logout.php" class="mt-20 flex items-center space-x-3 px-4 py-2 rounded-lg font-semibold bg-white text-green-600 hover:bg-green-700 hover:text-white transition text-sm">
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
                <a href="teacher_dashboard.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition active:bg-white active:text-green-600 text-base">
                    <i class="fas fa-home flex-shrink-0 text-xl"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my_classes.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
                    <i class="fas fa-book flex-shrink-0 text-xl"></i>
                    <span>My Classes</span>
                </a>
                <a href="schedule.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
                    <i class="fas fa-calendar-alt flex-shrink-0 text-xl"></i>
                    <span>Schedule</span>
                </a>
                <a href="upload_grades.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
                    <i class="fas fa-chart-bar flex-shrink-0 text-xl"></i>
                    <span>Upload Grades</span>
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
                    <h2 class="text-xl lg:text-2xl font-bold mb-1">Welcome, <?php echo $_SESSION['fname']; ?>!</h2>
                    <div class="text-sm lg:text-base">You can view all your assigned classes.</div>
                </div>
                <img src="../photos/cvsu-naic.jpg" class="w-full max-w-xs lg:w-80 lg:max-w-none rounded-2xl object-cover" alt="CvSU Naic Campus">
            </div>

            <!-- CLASSES -->
            <div class="px-4 py-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <?php if (empty($department) || empty($assigned_classes) || empty($assigned_subjects)): ?>
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
                            <p>Please set your department, assigned classes, and assigned subjects in your profile settings.</p>
                            <p class="mt-2">Current department: <?php echo htmlspecialchars($department); ?></p>
                            <p class="mt-2">Current assigned classes: <?php echo htmlspecialchars($assigned_classes); ?></p>
                            <p class="mt-2">Current assigned subjects: <?php echo htmlspecialchars($assigned_subjects); ?></p>
                        </div>
                    <?php else: ?>
                        <!-- Class Overview -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-green-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-green-600">Total Classes</h3>
                                        <p class="text-2xl font-bold text-green-700"><?php echo $total_classes; ?></p>
                                    </div>
                                    <i class="fas fa-chalkboard-teacher text-3xl text-green-500"></i>
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-blue-600">Total Students</h3>
                                        <p class="text-2xl font-bold text-blue-700"><?php echo $total_students; ?></p>
                                    </div>
                                    <i class="fas fa-users text-3xl text-blue-500"></i>
                                </div>
                            </div>
                        </div>
                        <!-- Classes List -->
                        <div class="space-y-6">
                            <?php if (empty($filtered_classes)): ?>
                                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                                    <p>No classes found for your assigned classes: <?php echo htmlspecialchars($assigned_classes); ?></p>
                                    <p class="mt-2">Please make sure your assigned classes match the format: PROGRAM-YEAR-SECTION (e.g., BSCS-2A)</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($filtered_classes as $program => $year_levels): ?>
                                    <div class="border rounded-lg overflow-hidden">
                                        <div class="bg-gray-50 px-6 py-4 border-b">
                                            <h2 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($program); ?></h2>
                                        </div>
                                        <?php foreach ($year_levels as $year => $subjects): ?>
                                            <div class="p-6">
                                                <h3 class="text-lg font-medium text-gray-700 mb-4"><?php echo htmlspecialchars($year); ?></h3>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Code</th>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Units</th>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            <?php foreach ($subjects as $subject): ?>
                                                                <tr class="hover:bg-gray-50">
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">
                                                                        <?php echo htmlspecialchars($subject['code']); ?>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                                        <?php echo htmlspecialchars($subject['name']); ?>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                                        <?php echo htmlspecialchars($subject['units']); ?>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                                        <?php echo htmlspecialchars($subject['section']); ?>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                                        <?php echo htmlspecialchars($subject['semester']); ?>
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                                                        <?php echo htmlspecialchars($subject['student_count']); ?> students
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                        Department: <?php echo htmlspecialchars($profile['department'] ?? 'Not set'); ?>
                    </div>
                </button>
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
            
               <!-- Recent Sections Box -->
               <div class="bg-white p-4 lg:p-6 shadow rounded-xl">
                <h3 class="text-lg font-semibold mb-4 text-center">Recent Sections</h3>
                <div class="flex justify-center space-x-4">
                    <?php
                    // Get unique sections from student_profiles for the teacher's assigned classes
                    if (!empty($assigned_classes)) {
                        $sections = [];
                        // Convert assigned_classes string to array if it's not already
                        $assigned_classes_array = is_array($assigned_classes) ? $assigned_classes : array_map('trim', explode(',', $assigned_classes));
                        
                        foreach ($assigned_classes_array as $class) {
                            $parts = explode('-', trim($class));
                            if (count($parts) === 2) {
                                $program = $parts[0];
                                $year_section = $parts[1];
                                $year = substr($year_section, 0, -1);
                                $section = substr($year_section, -1);
                                
                                $query = "SELECT DISTINCT sp.section, sp.program, sp.year_level, 
                                         COUNT(sp.student_id) as student_count 
                                         FROM student_profiles sp 
                                         WHERE sp.program = ? 
                                         AND sp.year_level = ? 
                                         AND sp.section = ? 
                                         GROUP BY sp.section, sp.program, sp.year_level";
                                
                                $stmt = $con->prepare($query);
                                $stmt->bind_param("sss", $program, $year, $section);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                while ($row = $result->fetch_assoc()) {
                                    $sections[] = $row;
                                }
                            }
                        }
                        
                        // Display up to 3 most recent sections
                        $colors = ['green', 'blue', 'purple'];
                        $count = 0;
                        foreach ($sections as $section) {
                            if ($count >= 3) break;
                            $color = $colors[$count];
                            ?>
                            <div class="relative">
                                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 border-<?php echo $color; ?>-500 bg-white flex items-center justify-center text-sm font-semibold">
                                    <?php echo htmlspecialchars($section['program'] . '-' . $section['year_level'] . $section['section']); ?>
                                </div>
                                <span class="absolute -bottom-1 -right-1 bg-<?php echo $color; ?>-500 text-white text-xs rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center">
                                    <?php echo $section['student_count']; ?>
                                </span>
                            </div>
                            <?php
                            $count++;
                        }
                        
                        // If no sections found
                        if (empty($sections)) {
                            echo '<div class="text-gray-500 text-center">No sections assigned</div>';
                        }
                    } else {
                        echo '<div class="text-gray-500 text-center">No classes assigned</div>';
                    }
                    ?>
                </div>
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
                            <span class="text-gray-600">Department:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['department'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Designation:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['designation'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Subjects:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['subjects'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Class Assigned:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['class_assigned'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Gender:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['gender'] ?? 'Not set'); ?></span>
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
                <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                    <div class="flex justify-between items-center p-6 border-b sticky top-0 bg-white z-10">
                        <h3 class="text-lg font-semibold">Edit Profile</h3>
                        <button onclick="closeEditProfileDialog()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="overflow-y-auto flex-1 p-6 custom-scrollbar">
                        <form action="update_profile.php" method="POST" enctype="multipart/form-data" id="editProfileForm" class="space-y-6">
                            <!-- Profile Picture Section -->
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

                            <!-- Two Column Layout -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Academic Information -->
                                <div class="space-y-4">
                                    <h4 class="text-lg font-semibold text-green-600 border-b pb-2 sticky top-0 bg-white z-10">Academic Information</h4>
                                    
                                    <!-- Department -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                        <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <option value="">Select Department</option>
                                            <option value="Computer Science" <?php echo ($profile['department'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                            <option value="Information Technology" <?php echo ($profile['department'] == 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                                            <option value="Civil Engineering" <?php echo ($profile['department'] == 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                                            <option value="Electrical Engineering" <?php echo ($profile['department'] == 'Electrical Engineering') ? 'selected' : ''; ?>>Electrical Engineering</option>
                                            <option value="Chemical Engineering" <?php echo ($profile['department'] == 'Chemical Engineering') ? 'selected' : ''; ?>>Chemical Engineering</option>
                                            <option value="Mechanical Engineering" <?php echo ($profile['department'] == 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        </select>
                                    </div>

                                    <!-- Designation -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                                        <select name="designation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <option value="">Select Designation</option>
                                            <option value="Professor" <?php echo ($profile['designation'] == 'Professor') ? 'selected' : ''; ?>>Professor</option>
                                            <option value="Associate Professor" <?php echo ($profile['designation'] == 'Associate Professor') ? 'selected' : ''; ?>>Associate Professor</option>
                                            <option value="Assistant Professor" <?php echo ($profile['designation'] == 'Assistant Professor') ? 'selected' : ''; ?>>Assistant Professor</option>
                                            <option value="Instructor" <?php echo ($profile['designation'] == 'Instructor') ? 'selected' : ''; ?>>Instructor</option>
                                        </select>
                                    </div>

                                    <!-- Experience -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Experience (years)</label>
                                        <input type="number" name="experience" value="<?php echo htmlspecialchars($profile['experience'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                               min="0" max="50">
                                    </div>

                                    <!-- Subjects -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Subjects</label>
                                        <input type="text" name="subjects" value="<?php echo htmlspecialchars($profile['subjects'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                               placeholder="e.g., Mathematics, Physics">
                                    </div>

                                    <!-- Class Assigned -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Class Assigned</label>
                                        <input type="text" name="class_assigned" value="<?php echo htmlspecialchars($profile['class_assigned'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                               placeholder="e.g., BSIT 2A, BSCS 3B">
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div class="space-y-4">
                                    <h4 class="text-lg font-semibold text-green-600 border-b pb-2 sticky top-0 bg-white z-10">Personal Information</h4>
                                    
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
                                        <input type="tel" name="emergency_contact" value="<?php echo htmlspecialchars($profile['emergency_contact'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                               placeholder="Enter emergency contact">
                                    </div>

                                    <!-- Birthday -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Birthday</label>
                                        <input type="date" name="birthday" value="<?php echo htmlspecialchars($profile['birthday'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="p-6 border-t bg-gray-50 sticky bottom-0">
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeEditProfileDialog()" 
                                    class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                Cancel
                            </button>
                            <button type="submit" form="editProfileForm"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
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

<script src="../js/teacher.js"></script>
</body>
</html> 