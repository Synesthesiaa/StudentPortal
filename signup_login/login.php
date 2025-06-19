<?php
session_start();
require('../databaseConnector/connector.php');

if(isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = "SELECT * FROM signup_login WHERE email = '$email' AND role = '$role'";
    $result = mysqli_query($con, $query);

    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        if(md5($password) === $row['password']) {
            $_SESSION['email'] = $email;
            $_SESSION['fname'] = $row['fname'];
            $_SESSION['lname'] = $row['lname'];
            $_SESSION['role'] = $role;

            // Get the ID from the appropriate profile table
            if($role == 'student') {
                $profile_query = "SELECT student_id FROM student_profiles WHERE email = '$email'";
                $profile_result = mysqli_query($con, $profile_query);
                if($profile_row = mysqli_fetch_assoc($profile_result)) {
                    $_SESSION['student_id'] = $profile_row['student_id'];
                }
                header("Location: ../student/student_dashboard.php");
            } else {
                $profile_query = "SELECT teacher_id FROM teacher_profiles WHERE email = '$email'";
                $profile_result = mysqli_query($con, $profile_query);
                if($profile_row = mysqli_fetch_assoc($profile_result)) {
                    $_SESSION['teacher_id'] = $profile_row['teacher_id'];
                }
                header("Location: ../teacher/teacher_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid email or role";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Login - CvSU Naic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gradient-to-br from-green-50 to-white font-sans min-h-screen">

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-5xl">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="flex flex-col md:flex-row">
                    <!-- Left Column -->
                    <div class="w-full md:w-1/2 bg-green-600 p-8">
                        <div class="text-center">
                            <img src="../photos/logo.png" alt="CvSU Logo" class="w-48 h-48 mx-auto mb-6">
                            <h1 class="text-3xl text-white font-bold mb-4">Welcome to CvSU Naic <br> Student Portal</h1>
                
                            <p class="text-base text-green-100 mb-6">Sign in to access your account and manage your academic journey.</p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-1/2 p-8">
                        <!-- Error Message -->
                        <?php if(isset($error)) { ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline"><?php echo $error; ?></span>
                            </div>
                        <?php } ?>

                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Sign In</h2>
                            <form method="post" name="login" class="space-y-6">
                                <!-- Email Input -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                        </span>
                                        <input type="email" name="email" 
                                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                               placeholder="Enter your email" required autofocus/>
                                    </div>
                                </div>

                                <!-- Password Input -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </span>
                                        <input type="password" name="password" 
                                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                               placeholder="Enter your password" required/>
                                    </div>
                                </div>

                                <!-- Role Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </span>
                                        <select name="role" required 
                                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none">
                                            <option value="">Select Role</option>
                                            <option value="student">Student</option>
                                            <option value="teacher">Teacher</option>
                                        </select>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </span>
                                    </div>
                                </div>

                                <!-- Login Button -->
                                <button type="submit" name="submit" 
                                        class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                    Sign In
                                </button>

                                <!-- Registration Link -->
                                <div class="text-center mt-4">
                                    <p class="text-sm text-gray-600">
                                        Don't have an account? 
                                        <a href="registration.php" class="text-green-600 hover:text-green-700 font-medium">
                                            Register here
                                        </a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-sm text-gray-600">
                    © <?php echo date('Y'); ?> CvSU Naic. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>