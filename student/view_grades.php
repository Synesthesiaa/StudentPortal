<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');
require_once('functions_student.php');

// Get student's information
$email = $_SESSION['email'];
$student_id = $_SESSION['student_id'];

// Get selected semester from query parameter, default to current semester
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : '1st Semester';

// Fetch student's grades
$query = "SELECT sg.*, sl.fname as teacher_fname, sl.lname as teacher_lname 
          FROM student_grades sg 
          JOIN signup_login sl ON sg.teacher_email = sl.email 
          WHERE sg.student_id = ? AND sg.semester = ? AND sg.final_grade != 'INC'
          ORDER BY sg.subject, sg.class_section";
$stmt = $con->prepare($query);
$stmt->bind_param("ss", $student_id, $selected_semester);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);

// Group grades by year level based on subject codes
$grades_by_year = [
    '1st Year' => [],
    '2nd Year' => [],
    '3rd Year' => [],
    '4th Year' => []
];

foreach ($grades as $grade) {
    $year_level = getYearLevelFromSubjectCode($grade['subject']);
    if (isset($grades_by_year[$year_level])) {
        $grades_by_year[$year_level][] = $grade;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>View Grades - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gradient-to-br from-green-50 to-white font-sans min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="student_dashboard.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-800 mb-6">My Grades</h2>

            <!-- Semester Selection -->
            <div class="mb-6">
                <form method="GET" class="flex items-center space-x-4">
                    <label class="text-sm font-medium text-gray-700">Select Semester:</label>
                    <select name="semester" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="1st Semester" <?php echo $selected_semester === '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                        <option value="2nd Semester" <?php echo $selected_semester === '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                    </select>
                </form>
            </div>
            
            <?php if (empty($grades)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-graduation-cap text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No grades available for <?php echo htmlspecialchars($selected_semester); ?>.</p>
                </div>
            <?php else: ?>
                <!-- Grades by Year Level -->
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
                    <?php foreach ($grades_by_year as $year_level => $year_grades): ?>
                        <?php if (!empty($year_grades)): ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mr-2">
                                        <?php echo $year_level; ?>
                                    </span>
                                    <span class="text-gray-600">(<?php echo count($year_grades); ?> subjects)</span>
                                </h3>
                                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($year_grades as $grade): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                                        <div class="font-medium"><?php echo htmlspecialchars($grade['subject']); ?></div>
                                                        <div class="text-gray-500"><?php echo htmlspecialchars(getSubjectName($grade['subject'])); ?></div>
                                                        <div class="text-gray-400"><?php echo htmlspecialchars($grade['class_section']); ?></div>
                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                                        <div class="text-lg font-bold <?php echo ($grade['final_grade'] === 'INC' || $grade['final_grade'] === 'DRP' || $grade['final_grade'] == '0.0' || $grade['final_grade'] == '0') ? 'text-red-600' : 'text-gray-900'; ?>">
                                        <?php
                                                            $display_grade = $grade['final_grade'];
                                                            if ($display_grade == '0.0' || $display_grade == '0') {
                                                                $display_grade = 'DRP';
                                                            }
                                                            echo htmlspecialchars($display_grade); 
                                        ?>
                                                        </div>
                                                        <div class="text-gray-500"><?php echo htmlspecialchars($grade['teacher_lname'] . ', ' . $grade['teacher_fname']); ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Summary Section -->
                <div class="mt-8 bg-blue-50 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-blue-800 mb-3">Grade Average</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($grades_by_year as $year_level => $year_grades): ?>
                            <?php if (!empty($year_grades)): ?>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">
                                        <?php 
                                        $total_grade = 0;
                                        $count = 0;
                                        foreach ($year_grades as $grade) {
                                            if (!empty($grade['final_grade']) && $grade['final_grade'] !== 'INC' && $grade['final_grade'] !== 'DRP' && $grade['final_grade'] != '0.0' && $grade['final_grade'] != '0') {
                                                $total_grade += floatval($grade['final_grade']);
                                                $count++;
                                            }
                                        }
                                        echo $count > 0 ? number_format($total_grade / $count, 2) : 'N/A';
                                        ?>
                                    </div>
                                    <div class="text-sm text-blue-700"><?php echo $year_level; ?> Average</div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
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
</body>
</html> 