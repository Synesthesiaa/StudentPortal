<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

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

// Fetch all INC grades with their upload dates
$inc_query = "SELECT sg.*, sl.fname as teacher_fname, sl.lname as teacher_lname 
    FROM student_grades sg 
    JOIN signup_login sl ON sg.teacher_email = sl.email 
    WHERE sg.student_id = ? AND sg.final_grade = 'INC'
    ORDER BY sg.subject, sg.class_section, sg.date_uploaded DESC";
$inc_stmt = $con->prepare($inc_query);
$inc_stmt->bind_param("s", $profile['student_id']);
$inc_stmt->execute();
$inc_result = $inc_stmt->get_result();

$incomplete_subjects = [];
while ($row = $inc_result->fetch_assoc()) {
    $incomplete_subjects[] = $row;
}

// Fetch student's failed subjects
$failed_subjects_query = "SELECT sg.*, sl.fname as teacher_fname, sl.lname as teacher_lname 
                         FROM student_grades sg 
                         JOIN signup_login sl ON sg.teacher_email = sl.email 
                         WHERE sg.student_id = ? AND sg.final_grade = 5";
$failed_stmt = $con->prepare($failed_subjects_query);
$failed_stmt->bind_param("s", $profile['student_id']);
$failed_stmt->execute();
$failed_result = $failed_stmt->get_result();
$failed_subjects = [];
while ($row = $failed_result->fetch_assoc()) {
    $failed_subjects[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Dashboard</title>
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
                <a href="student_dashboard.php" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition active:bg-green active:text-green-600 text-base border-solid border-white">
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
                    <h2 class="text-xl lg:text-2xl font-bold mb-1">Welcome back, <?php echo $_SESSION['fname']; ?>!</h2>
                    <div class="text-sm lg:text-base">Always stay updated in your student portal</div>
                </div>
                <img src="../photos/cvsu-naic.jpg" class="w-full max-w-xs lg:w-80 lg:max-w-none rounded-2xl object-cover" alt="CvSU Naic Campus">
            </div>

            <!-- Subject Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6 mb-6 lg:mb-8">
                <div class="bg-white rounded-xl p-4 lg:p-6 shadow text-center cursor-pointer hover:bg-gray-50 transition-colors" onclick="openIncompleteSubjectsDialog()">
                    <div class="text-2xl lg:text-3xl text-yellow-500 font-bold mb-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="text-2xl lg:text-3xl text-yellow-500 font-bold"><?php echo count($incomplete_subjects); ?></div>
                    <div class="text-gray-500 text-sm lg:text-base">Incomplete</div>
                </div>
                <div class="bg-white rounded-xl p-4 lg:p-6 shadow text-center cursor-pointer hover:bg-gray-50 transition-colors" onclick="openFailedSubjectsDialog()">
                    <div class="text-2xl lg:text-3xl text-red-500 font-bold mb-2">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="text-2xl lg:text-3xl text-red-500 font-bold"><?php echo count($failed_subjects); ?></div>
                    <div class="text-gray-500 text-sm lg:text-base">Failed Subjects</div>
                </div>
            </div>

            <!-- Failed Subjects Dialog -->
            <div id="failedSubjectsDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">Failed Subjects</h3>
                        <button onclick="closeFailedSubjectsDialog()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <?php if (!empty($failed_subjects)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($failed_subjects as $subject): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($subject['subject']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php
                                                // Get subject name based on subject code
                                                $subject_code = $subject['subject'];
                                                $subject_name = '';
                                                
                                                // Define subject names
                                                $subject_names = [
                                                    // BSCS Subjects
                                                    'CS1-101' => 'Introduction to Computer Science',
                                                    'CS1-102' => 'Programming Fundamentals',
                                                    'CS1-103' => 'Discrete Mathematics',
                                                    'CS2-104' => 'Computer Architecture',
                                                    'CS2-105' => 'Data Structures',
                                                    'CS2-106' => 'Object-Oriented Programming',
                                                    'CS1-201' => 'Algorithms and Complexity',
                                                    'CS1-202' => 'Operating Systems',
                                                    'CS1-203' => 'Computer Networks',
                                                    'CS2-204' => 'Software Engineering',
                                                    'CS2-205' => 'Database Systems',
                                                    'CS2-206' => 'Web Development',
                                                    'CS1-301' => 'Artificial Intelligence',
                                                    'CS1-302' => 'Machine Learning',
                                                    'CS1-303' => 'Computer Graphics',
                                                    'CS2-304' => 'Compiler Design',
                                                    'CS2-305' => 'Computer Security',
                                                    'CS2-306' => 'Distributed Systems',
                                                    'CS1-401' => 'Advanced AI',
                                                    'CS1-402' => 'Distributed Systems',
                                                    'CS1-403' => 'Computer Vision',
                                                    'CS2-404' => 'CS Capstone Project',
                                                    'CS2-405' => 'CS Internship',
                                                    'CS2-406' => 'Advanced Topics in CS',
                                                    // BSIT Subjects
                                                    'IT1-101' => 'Introduction to Computing',
                                                    'IT1-102' => 'Computer Programming 1',
                                                    'IT1-103' => 'Computer Programming 2',
                                                    'IT2-104' => 'Data Structures and Algorithms',
                                                    'IT2-105' => 'Web Development',
                                                    'IT2-106' => 'Database Management Systems',
                                                    'IT1-201' => 'Database Management Systems',
                                                    'IT1-202' => 'Object-Oriented Programming',
                                                    'IT1-203' => 'Networking 1',
                                                    'IT2-204' => 'Systems Analysis and Design',
                                                    'IT2-205' => 'Mobile Application Development',
                                                    'IT2-206' => 'Web Technologies',
                                                    'IT1-301' => 'Advanced Database Systems',
                                                    'IT1-302' => 'Web Application Development',
                                                    'IT1-303' => 'Networking 2',
                                                    'IT2-304' => 'Software Engineering',
                                                    'IT2-305' => 'Information Security',
                                                    'IT2-306' => 'Cloud Computing',
                                                    'IT1-401' => 'IT Project Management',
                                                    'IT1-402' => 'Cloud Computing',
                                                    'IT1-403' => 'Artificial Intelligence',
                                                    'IT2-404' => 'IT Capstone Project',
                                                    'IT2-405' => 'IT Internship',
                                                    'IT2-406' => 'Emerging Technologies',
                                                    // BSCE Subjects
                                                    'CE1-101' => 'Introduction to Computer Engineering',
                                                    'CE1-102' => 'Digital Logic Design',
                                                    'CE1-103' => 'Computer Organization',
                                                    'CE2-104' => 'Programming for Engineers',
                                                    'CE2-105' => 'Circuit Analysis',
                                                    'CE2-106' => 'Microprocessors',
                                                    'CE1-201' => 'Microprocessors',
                                                    'CE1-202' => 'Computer Architecture',
                                                    'CE1-203' => 'Embedded Systems',
                                                    'CE2-204' => 'Digital Systems',
                                                    'CE2-205' => 'Computer Networks',
                                                    'CE2-206' => 'VLSI Design',
                                                    'CE1-301' => 'VLSI Design',
                                                    'CE1-302' => 'Computer Security',
                                                    'CE1-303' => 'Real-time Systems',
                                                    'CE2-304' => 'Robotics',
                                                    'CE2-305' => 'Computer Vision',
                                                    'CE2-306' => 'IoT Systems',
                                                    'CE1-401' => 'Advanced Computer Architecture',
                                                    'CE1-402' => 'IoT Systems',
                                                    'CE1-403' => 'Hardware Security',
                                                    'CE2-404' => 'CE Capstone Project',
                                                    'CE2-405' => 'CE Internship',
                                                    'CE2-406' => 'Advanced Digital Systems',
                                                    // BSEE Subjects
                                                    'EE1-101' => 'Introduction to Electrical Engineering',
                                                    'EE1-102' => 'Circuit Analysis 1',
                                                    'EE1-103' => 'Electronics 1',
                                                    'EE2-104' => 'Digital Electronics',
                                                    'EE2-105' => 'Engineering Mathematics',
                                                    'EE2-106' => 'Electromagnetics',
                                                    'EE1-201' => 'Circuit Analysis 2',
                                                    'EE1-202' => 'Electronics 2',
                                                    'EE1-203' => 'Electromagnetics',
                                                    'EE2-204' => 'Power Systems',
                                                    'EE2-205' => 'Control Systems',
                                                    'EE2-206' => 'Power Electronics',
                                                    'EE1-301' => 'Power Electronics',
                                                    'EE1-302' => 'Electric Machines',
                                                    'EE1-303' => 'Communication Systems',
                                                    'EE2-304' => 'Digital Signal Processing',
                                                    'EE2-305' => 'Power Distribution',
                                                    'EE2-306' => 'Renewable Energy',
                                                    'EE1-401' => 'Renewable Energy Systems',
                                                    'EE1-402' => 'Smart Grid Technology',
                                                    'EE1-403' => 'Power System Protection',
                                                    'EE2-404' => 'EE Capstone Project',
                                                    'EE2-405' => 'EE Internship',
                                                    'EE2-406' => 'Advanced Power Systems',
                                                    // BSChem Subjects
                                                    'CHEM1-101' => 'General Chemistry',
                                                    'CHEM1-102' => 'Organic Chemistry 1',
                                                    'CHEM1-103' => 'Physical Chemistry 1',
                                                    'CHEM2-104' => 'Analytical Chemistry',
                                                    'CHEM2-105' => 'Chemical Engineering Principles',
                                                    'CHEM2-106' => 'Chemical Thermodynamics',
                                                    'CHEM1-201' => 'Organic Chemistry 2',
                                                    'CHEM1-202' => 'Physical Chemistry 2',
                                                    'CHEM1-203' => 'Chemical Thermodynamics',
                                                    'CHEM2-204' => 'Chemical Kinetics',
                                                    'CHEM2-205' => 'Unit Operations',
                                                    'CHEM2-206' => 'Process Control',
                                                    'CHEM1-301' => 'Chemical Process Design',
                                                    'CHEM1-302' => 'Transport Phenomena',
                                                    'CHEM1-303' => 'Chemical Reaction Engineering',
                                                    'CHEM2-304' => 'Process Control',
                                                    'CHEM2-305' => 'Plant Design',
                                                    'CHEM2-306' => 'Environmental Engineering',
                                                    'CHEM1-401' => 'Process Safety',
                                                    'CHEM1-402' => 'Environmental Engineering',
                                                    'CHEM1-403' => 'Plant Economics',
                                                    'CHEM2-404' => 'ChemE Capstone Project',
                                                    'CHEM2-405' => 'ChemE Internship',
                                                    'CHEM2-406' => 'Advanced Process Design',
                                                    // BSME Subjects
                                                    'ME1-101' => 'Introduction to Mechanical Engineering',
                                                    'ME1-102' => 'Engineering Mechanics',
                                                    'ME1-103' => 'Engineering Materials',
                                                    'ME2-104' => 'Engineering Drawing',
                                                    'ME2-105' => 'Thermodynamics 1',
                                                    'ME2-106' => 'Fluid Mechanics',
                                                    'ME1-201' => 'Fluid Mechanics',
                                                    'ME1-202' => 'Heat Transfer',
                                                    'ME1-203' => 'Machine Design',
                                                    'ME2-204' => 'Manufacturing Processes',
                                                    'ME2-205' => 'Thermodynamics 2',
                                                    'ME2-206' => 'Mechanical Vibrations',
                                                    'ME1-301' => 'Mechanical Vibrations',
                                                    'ME1-302' => 'Control Systems',
                                                    'ME1-303' => 'Power Plants',
                                                    'ME2-304' => 'Robotics',
                                                    'ME2-305' => 'Automotive Engineering',
                                                    'ME2-306' => 'Energy Systems',
                                                    'ME1-401' => 'Energy Systems',
                                                    'ME1-402' => 'HVAC Systems',
                                                    'ME1-403' => 'Renewable Energy',
                                                    'ME2-404' => 'ME Capstone Project',
                                                    'ME2-405' => 'ME Internship',
                                                    'ME2-406' => 'Advanced Manufacturing'
                                                ];
                                                
                                                echo htmlspecialchars($subject_names[$subject_code] ?? 'Subject Name Not Found');
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($subject['class_section']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($subject['teacher_lname'] . ', ' . $subject['teacher_fname']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold"><?php echo htmlspecialchars($subject['final_grade']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="text-4xl text-gray-400 mb-4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <p class="text-gray-500">No failed subjects</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Incomplete Subjects Dialog -->
            <div id="incompleteSubjectsDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">Incomplete Subjects</h3>
                        <button onclick="closeIncompleteSubjectsDialog()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <?php if (!empty($incomplete_subjects)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($incomplete_subjects as $subject): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($subject['subject']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php
                                                // Get subject name based on subject code
                                                $subject_code = $subject['subject'];
                                                $subject_name = '';
                                                
                                                // Define subject names
                                                $subject_names = [
                                                    // BSCS Subjects
                                                    'CS1-101' => 'Introduction to Computer Science',
                                                    'CS1-102' => 'Programming Fundamentals',
                                                    'CS1-103' => 'Discrete Mathematics',
                                                    'CS2-104' => 'Computer Architecture',
                                                    'CS2-105' => 'Data Structures',
                                                    'CS2-106' => 'Object-Oriented Programming',
                                                    'CS1-201' => 'Algorithms and Complexity',
                                                    'CS1-202' => 'Operating Systems',
                                                    'CS1-203' => 'Computer Networks',
                                                    'CS2-204' => 'Software Engineering',
                                                    'CS2-205' => 'Database Systems',
                                                    'CS2-206' => 'Web Development',
                                                    'CS1-301' => 'Artificial Intelligence',
                                                    'CS1-302' => 'Machine Learning',
                                                    'CS1-303' => 'Computer Graphics',
                                                    'CS2-304' => 'Compiler Design',
                                                    'CS2-305' => 'Computer Security',
                                                    'CS2-306' => 'Distributed Systems',
                                                    'CS1-401' => 'Advanced AI',
                                                    'CS1-402' => 'Distributed Systems',
                                                    'CS1-403' => 'Computer Vision',
                                                    'CS2-404' => 'CS Capstone Project',
                                                    'CS2-405' => 'CS Internship',
                                                    'CS2-406' => 'Advanced Topics in CS',
                                                    // BSIT Subjects
                                                    'IT1-101' => 'Introduction to Computing',
                                                    'IT1-102' => 'Computer Programming 1',
                                                    'IT1-103' => 'Computer Programming 2',
                                                    'IT2-104' => 'Data Structures and Algorithms',
                                                    'IT2-105' => 'Web Development',
                                                    'IT2-106' => 'Database Management Systems',
                                                    'IT1-201' => 'Database Management Systems',
                                                    'IT1-202' => 'Object-Oriented Programming',
                                                    'IT1-203' => 'Networking 1',
                                                    'IT2-204' => 'Systems Analysis and Design',
                                                    'IT2-205' => 'Mobile Application Development',
                                                    'IT2-206' => 'Web Technologies',
                                                    'IT1-301' => 'Advanced Database Systems',
                                                    'IT1-302' => 'Web Application Development',
                                                    'IT1-303' => 'Networking 2',
                                                    'IT2-304' => 'Software Engineering',
                                                    'IT2-305' => 'Information Security',
                                                    'IT2-306' => 'Cloud Computing',
                                                    'IT1-401' => 'IT Project Management',
                                                    'IT1-402' => 'Cloud Computing',
                                                    'IT1-403' => 'Artificial Intelligence',
                                                    'IT2-404' => 'IT Capstone Project',
                                                    'IT2-405' => 'IT Internship',
                                                    'IT2-406' => 'Emerging Technologies',
                                                    // BSCE Subjects
                                                    'CE1-101' => 'Introduction to Computer Engineering',
                                                    'CE1-102' => 'Digital Logic Design',
                                                    'CE1-103' => 'Computer Organization',
                                                    'CE2-104' => 'Programming for Engineers',
                                                    'CE2-105' => 'Circuit Analysis',
                                                    'CE2-106' => 'Microprocessors',
                                                    'CE1-201' => 'Microprocessors',
                                                    'CE1-202' => 'Computer Architecture',
                                                    'CE1-203' => 'Embedded Systems',
                                                    'CE2-204' => 'Digital Systems',
                                                    'CE2-205' => 'Computer Networks',
                                                    'CE2-206' => 'VLSI Design',
                                                    'CE1-301' => 'VLSI Design',
                                                    'CE1-302' => 'Computer Security',
                                                    'CE1-303' => 'Real-time Systems',
                                                    'CE2-304' => 'Robotics',
                                                    'CE2-305' => 'Computer Vision',
                                                    'CE2-306' => 'IoT Systems',
                                                    'CE1-401' => 'Advanced Computer Architecture',
                                                    'CE1-402' => 'IoT Systems',
                                                    'CE1-403' => 'Hardware Security',
                                                    'CE2-404' => 'CE Capstone Project',
                                                    'CE2-405' => 'CE Internship',
                                                    'CE2-406' => 'Advanced Digital Systems',
                                                    // BSEE Subjects
                                                    'EE1-101' => 'Introduction to Electrical Engineering',
                                                    'EE1-102' => 'Circuit Analysis 1',
                                                    'EE1-103' => 'Electronics 1',
                                                    'EE2-104' => 'Digital Electronics',
                                                    'EE2-105' => 'Engineering Mathematics',
                                                    'EE2-106' => 'Electromagnetics',
                                                    'EE1-201' => 'Circuit Analysis 2',
                                                    'EE1-202' => 'Electronics 2',
                                                    'EE1-203' => 'Electromagnetics',
                                                    'EE2-204' => 'Power Systems',
                                                    'EE2-205' => 'Control Systems',
                                                    'EE2-206' => 'Power Electronics',
                                                    'EE1-301' => 'Power Electronics',
                                                    'EE1-302' => 'Electric Machines',
                                                    'EE1-303' => 'Communication Systems',
                                                    'EE2-304' => 'Digital Signal Processing',
                                                    'EE2-305' => 'Power Distribution',
                                                    'EE2-306' => 'Renewable Energy',
                                                    'EE1-401' => 'Renewable Energy Systems',
                                                    'EE1-402' => 'Smart Grid Technology',
                                                    'EE1-403' => 'Power System Protection',
                                                    'EE2-404' => 'EE Capstone Project',
                                                    'EE2-405' => 'EE Internship',
                                                    'EE2-406' => 'Advanced Power Systems',
                                                    // BSChem Subjects
                                                    'CHEM1-101' => 'General Chemistry',
                                                    'CHEM1-102' => 'Organic Chemistry 1',
                                                    'CHEM1-103' => 'Physical Chemistry 1',
                                                    'CHEM2-104' => 'Analytical Chemistry',
                                                    'CHEM2-105' => 'Chemical Engineering Principles',
                                                    'CHEM2-106' => 'Chemical Thermodynamics',
                                                    'CHEM1-201' => 'Organic Chemistry 2',
                                                    'CHEM1-202' => 'Physical Chemistry 2',
                                                    'CHEM1-203' => 'Chemical Thermodynamics',
                                                    'CHEM2-204' => 'Chemical Kinetics',
                                                    'CHEM2-205' => 'Unit Operations',
                                                    'CHEM2-206' => 'Process Control',
                                                    'CHEM1-301' => 'Chemical Process Design',
                                                    'CHEM1-302' => 'Transport Phenomena',
                                                    'CHEM1-303' => 'Chemical Reaction Engineering',
                                                    'CHEM2-304' => 'Process Control',
                                                    'CHEM2-305' => 'Plant Design',
                                                    'CHEM2-306' => 'Environmental Engineering',
                                                    'CHEM1-401' => 'Process Safety',
                                                    'CHEM1-402' => 'Environmental Engineering',
                                                    'CHEM1-403' => 'Plant Economics',
                                                    'CHEM2-404' => 'ChemE Capstone Project',
                                                    'CHEM2-405' => 'ChemE Internship',
                                                    'CHEM2-406' => 'Advanced Process Design',
                                                    // BSME Subjects
                                                    'ME1-101' => 'Introduction to Mechanical Engineering',
                                                    'ME1-102' => 'Engineering Mechanics',
                                                    'ME1-103' => 'Engineering Materials',
                                                    'ME2-104' => 'Engineering Drawing',
                                                    'ME2-105' => 'Thermodynamics 1',
                                                    'ME2-106' => 'Fluid Mechanics',
                                                    'ME1-201' => 'Fluid Mechanics',
                                                    'ME1-202' => 'Heat Transfer',
                                                    'ME1-203' => 'Machine Design',
                                                    'ME2-204' => 'Manufacturing Processes',
                                                    'ME2-205' => 'Thermodynamics 2',
                                                    'ME2-206' => 'Mechanical Vibrations',
                                                    'ME1-301' => 'Mechanical Vibrations',
                                                    'ME1-302' => 'Control Systems',
                                                    'ME1-303' => 'Power Plants',
                                                    'ME2-304' => 'Robotics',
                                                    'ME2-305' => 'Automotive Engineering',
                                                    'ME2-306' => 'Energy Systems',
                                                    'ME1-401' => 'Energy Systems',
                                                    'ME1-402' => 'HVAC Systems',
                                                    'ME1-403' => 'Renewable Energy',
                                                    'ME2-404' => 'ME Capstone Project',
                                                    'ME2-405' => 'ME Internship',
                                                    'ME2-406' => 'Advanced Manufacturing'
                                                ];
                                                
                                                echo htmlspecialchars($subject_names[$subject_code] ?? 'Subject Name Not Found');
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($subject['class_section']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($subject['teacher_lname'] . ', ' . $subject['teacher_fname']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">
                                                <?php 
                                                $display_grade = $subject['final_grade'];
                                                if ($display_grade == '0.0' || $display_grade == '0') {
                                                    $display_grade = 'INC';
                                                }
                                                echo htmlspecialchars($display_grade); 
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="text-4xl text-gray-400 mb-4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <p class="text-gray-500">No incomplete subjects</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <script>
                function openFailedSubjectsDialog() {
                    document.getElementById('failedSubjectsDialog').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                function closeFailedSubjectsDialog() {
                    document.getElementById('failedSubjectsDialog').classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }

                function openIncompleteSubjectsDialog() {
                    document.getElementById('incompleteSubjectsDialog').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                function closeIncompleteSubjectsDialog() {
                    document.getElementById('incompleteSubjectsDialog').classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }

                // Close dialog when clicking outside
                document.getElementById('failedSubjectsDialog').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeFailedSubjectsDialog();
                    }
                });

                document.getElementById('incompleteSubjectsDialog').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeIncompleteSubjectsDialog();
                    }
                });
            </script>
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
                            <span class="font-medium"><?php echo htmlspecialchars($profile['student_id'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Program:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['program'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Section:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['section'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Year Level:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['year_level'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2">
                            <span class="text-gray-600">Semester:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($profile['semester'] ?? 'Not set'); ?></span>
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
                <div class="bg-white rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
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

