<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

// Fetch teacher profile data including avatar
$query = "SELECT * FROM teacher_profiles WHERE email = '{$_SESSION['email']}'";
$result = mysqli_query($con, $query);
$profile = mysqli_fetch_assoc($result);

// Get teacher's assigned classes
$assigned_classes = $profile['class_assigned'] ?? '';

// If no profile exists, create one
if (!$profile) {
    $insert_query = "INSERT INTO teacher_profiles (email) VALUES (?)";
    $stmt = $con->prepare($insert_query);
    $stmt->bind_param("s", $_SESSION['email']);
    
    if ($stmt->execute()) {
        // Fetch the newly created profile
        $result = mysqli_query($con, $query);
        $profile = mysqli_fetch_assoc($result);
        $assigned_classes = $profile['class_assigned'] ?? '';
    }
}

if(isset($_POST['submit'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $query = "SELECT password FROM signup_login WHERE email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if(md5($current_password) !== $row['password']) {
        $error = "Current password is incorrect.";
    } elseif($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        // Update password
        $hashed_password = md5($new_password);
        $update_query = "UPDATE signup_login SET password = ? WHERE email = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param("ss", $hashed_password, $_SESSION['email']);
        
        if($update_stmt->execute()) {
            $success = "Password has been changed successfully.";
        } else {
            $error = "Error changing password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <a href="#" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition active:bg-white active:text-green-600 text-sm">
                    <i class="fas fa-home flex-shrink-0 text-xl"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my_classes.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-book flex-shrink-0 text-xl"></i>
                    <span>My Classes</span>
                </a>
                <a href="https://drive.google.com/drive/folders/1kaYz88_Eur4S1MlFs-D0OyG-E1zwryZj" target="_blank" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-calendar-alt flex-shrink-0 text-xl"></i>
                    <span>Schedule</span>
                </a>
                <a href="upload_grades.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
                    <i class="fas fa-chart-bar flex-shrink-0 text-xl"></i>
                    <span>Upload Grades</span>
                </a>
                <a href="../signup_login/change_password.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-sm">
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
                <a href="" class="flex items-center space-x-3 px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-green-600 transition text-base">
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
                    <div class="text-sm lg:text-base">Keep your account secure by changing your password regularly</div>
                </div>
                <img src="../photos/cvsu-naic.jpg" class="w-full max-w-xs lg:w-80 lg:max-w-none rounded-2xl object-cover" alt="CvSU Naic Campus">
            </div>

         <!-- Change Password -->
         <div class="flex justify-center items-center">
                <div class="max-w-2xl w-full">
                    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">


                        <?php if(isset($error)): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <span class="block sm:inline"><?php echo $error; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($success)): ?>
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span class="block sm:inline"><?php echo $success; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Current Password</label>
                                <div class="relative">
                                    <input type="password" name="current_password" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter your current password">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">New Password</label>
                                <div class="relative">
                                    <input type="password" name="new_password" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter your new password">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i class="fas fa-key text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Confirm your new password">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i class="fas fa-check text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-shield-alt mr-2 text-green-500"></i>
                                    <span>Your password will be securely updated</span>
                                </div>
                                <button type="submit" name="submit" 
                                        class=" md:w-50  md:h-15 bg-green-600 text-white py-3 px-8 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 font-semibold  shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <i class="fas fa-save mr-2"></i>
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
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

            <style>
                /* Custom Scrollbar Styles */
                .custom-scrollbar {
                    scrollbar-width: thin;
                    scrollbar-color: #4CAF50 #f3f4f6;
                }

                .custom-scrollbar::-webkit-scrollbar {
                    width: 8px;
                }

                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f3f4f6;
                    border-radius: 4px;
                }

                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background-color: #4CAF50;
                    border-radius: 4px;
                    border: 2px solid #f3f4f6;
                }

                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background-color: #388E3C;
                }

                /* Mobile-specific styles */
                @media (max-width: 768px) {
                    .custom-scrollbar {
                        -webkit-overflow-scrolling: touch;
                        scrollbar-width: none; /* Firefox */
                    }
                    
                    .custom-scrollbar::-webkit-scrollbar {
                        width: 4px; /* Thinner scrollbar for mobile */
                    }
                }
            </style>
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