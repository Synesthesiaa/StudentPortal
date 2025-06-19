<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Registration - CvSU Naic</title>
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
                            <img src="../photos/logo.png" alt="CvSU Logo" class="w-48 h-48 mx-auto my-20 mb-6">
                            <h1 class="text-3xl text-white font-bold mb-4">Create Your Account</h1>
                            <p class="text-base text-green-100 mb-6">Join our academic community and start your journey with CvSU Naic.</p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-1/2 p-8">
                        <?php
                        require('../databaseConnector/connector.php');

                        if (isset($_REQUEST['email'])) {
                            $email = stripslashes($_REQUEST['email']);
                            $email = mysqli_real_escape_string($con, $email);

                            // Check if email already exists
                            $check_query = "SELECT * FROM signup_login WHERE email = '$email'";
                            $check_result = mysqli_query($con, $check_query);
                            
                            if(mysqli_num_rows($check_result) > 0) {
                                echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                      <span class='block sm:inline'>Email already exists. Please use a different email address.</span>
                                      </div>
                                      <div class='text-center mt-4'>
                                      <a href='registration.php' class='text-green-600 hover:text-green-700 font-medium'>Try again</a>
                                      </div>";
                            } else {
                                $fname = stripslashes($_REQUEST['fname']);
                                $fname = mysqli_real_escape_string($con, $fname);

                                $lname = stripslashes($_REQUEST['lname']);
                                $lname = mysqli_real_escape_string($con, $lname);

                                $password = stripslashes($_REQUEST['password']);
                                $password = mysqli_real_escape_string($con, $password);

                                $gender = stripslashes($_REQUEST['gender']);
                                $gender = mysqli_real_escape_string($con, $gender);
                                
                                $role = stripslashes($_REQUEST['role']);
                                $role = mysqli_real_escape_string($con, $role);
                                
                                $create_datetime = date("Y-m-d H:i:s");
                                
                                // Start transaction
                                mysqli_autocommit($con, FALSE);
                                
                                $query = "INSERT into `signup_login` (fname, lname, email, password, role, gender, create_datetime)
                                         VALUES ('$fname', '$lname', '$email', '" . md5($password) . "', '$role', '$gender', '$create_datetime')";
                                $result = mysqli_query($con, $query);
                                
                                if ($result) {
                                    $year = date('Y');
                                    $profile_result = false;
                                    
                                    if ($role === 'student') {
                                        // Check if gender column exists in student_profiles
                                        $check_gender = "SHOW COLUMNS FROM `student_profiles` LIKE 'gender'";
                                        $result_gender = mysqli_query($con, $check_gender);
                                        
                                        if(mysqli_num_rows($result_gender) == 0) {
                                            // Add gender column if it doesn't exist
                                            $alter_gender = "ALTER TABLE `student_profiles` ADD `gender` ENUM('Male', 'Female') NOT NULL";
                                            mysqli_query($con, $alter_gender);
                                        }

                                        // Generate unique student ID using a loop to handle duplicates
                                        $attempts = 0;
                                        $max_attempts = 10;
                                        
                                        do {
                                            // Get the highest existing ID for this year
                                            $query = "SELECT MAX(CAST(SUBSTRING(student_id, 5) AS UNSIGNED)) as max_num 
                                                     FROM student_profiles 
                                                     WHERE student_id LIKE '$year%'";
                                            $result = mysqli_query($con, $query);
                                            $row = mysqli_fetch_assoc($result);
                                            $next_num = ($row['max_num'] ? $row['max_num'] : 0) + 1;
                                            $student_id = $year . str_pad($next_num, 4, '0', STR_PAD_LEFT);

                                            // Try to insert with this ID
                                            $profile_query = "INSERT INTO student_profiles (email, student_id, gender, semester) 
                                                            VALUES ('$email', '$student_id', '$gender', '1st Semester')";
                                            $profile_result = mysqli_query($con, $profile_query);
                                            
                                            $attempts++;
                                            
                                            // If insert failed due to duplicate key, try again
                                            if (!$profile_result && mysqli_errno($con) == 1062 && $attempts < $max_attempts) {
                                                // Duplicate entry error, wait a moment and try again
                                                usleep(100000); // Wait 0.1 seconds
                                                continue;
                                            }
                                            
                                            break;
                                            
                                        } while ($attempts < $max_attempts);
                                        
                                    } else if ($role === 'teacher') {
                                        // Check if gender column exists in teacher_profiles
                                        $check_gender = "SHOW COLUMNS FROM `teacher_profiles` LIKE 'gender'";
                                        $result_gender = mysqli_query($con, $check_gender);
                                        
                                        if(mysqli_num_rows($result_gender) == 0) {
                                            // Add gender column if it doesn't exist
                                            $alter_gender = "ALTER TABLE `teacher_profiles` ADD `gender` ENUM('Male', 'Female') NOT NULL";
                                            mysqli_query($con, $alter_gender);
                                        }

                                        // Generate unique teacher ID using a loop to handle duplicates
                                        $attempts = 0;
                                        $max_attempts = 10;
                                        
                                        do {
                                            // Get the highest existing ID for this year
                                            $query = "SELECT MAX(CAST(SUBSTRING(teacher_id, 5) AS UNSIGNED)) as max_num 
                                                     FROM teacher_profiles 
                                                     WHERE teacher_id LIKE '$year%'";
                                            $result = mysqli_query($con, $query);
                                            $row = mysqli_fetch_assoc($result);
                                            $next_num = ($row['max_num'] ? $row['max_num'] : 0) + 1;
                                            $teacher_id = $year . str_pad($next_num, 4, '0', STR_PAD_LEFT);

                                            // Try to insert with this ID
                                            $profile_query = "INSERT INTO teacher_profiles (email, teacher_id, gender) 
                                                            VALUES ('$email', '$teacher_id', '$gender')";
                                            $profile_result = mysqli_query($con, $profile_query);
                                            
                                            $attempts++;
                                            
                                            // If insert failed due to duplicate key, try again
                                            if (!$profile_result && mysqli_errno($con) == 1062 && $attempts < $max_attempts) {
                                                // Duplicate entry error, wait a moment and try again
                                                usleep(100000); // Wait 0.1 seconds
                                                continue;
                                            }
                                            
                                            break;
                                            
                                        } while ($attempts < $max_attempts);
                                    }

                                    if ($profile_result) {
                                        // Commit transaction
                                        mysqli_commit($con);
                                        echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                              <span class='block sm:inline'>You are registered successfully.</span>
                                              </div>
                                              <div class='text-center mt-4'>
                                              <a href='login.php' class='text-green-600 hover:text-green-700 font-medium'>Click here to Login</a>
                                              </div>";
                                    } else {
                                        // Rollback transaction
                                        mysqli_rollback($con);
                                        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                              <span class='block sm:inline'>Error creating profile. Please try again.</span>
                                              </div>
                                              <div class='text-center mt-4'>
                                              <a href='registration.php' class='text-green-600 hover:text-green-700 font-medium'>Try again</a>
                                              </div>";
                                    }
                                } else {
                                    // Rollback transaction
                                    mysqli_rollback($con);
                                    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                                          <span class='block sm:inline'>Required fields are missing.</span>
                                          </div>
                                          <div class='text-center mt-4'>
                                          <a href='registration.php' class='text-green-600 hover:text-green-700 font-medium'>Try again</a>
                                          </div>";
                                }
                                
                                // Restore autocommit
                                mysqli_autocommit($con, TRUE);
                            }
                        } else {
                        ?>
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Registration Form</h2>
                        <form action="" method="post" class="space-y-6">
                            <!-- First Name Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </span>
                                    <input type="text" name="fname" 
                                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                           placeholder="Enter your first name" required />
                                </div>
                            </div>

                            <!-- Last Name Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </span>
                                    <input type="text" name="lname" 
                                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                           placeholder="Enter your last name" required />
                                </div>
                            </div>

                            <!-- Email Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </span>
                                    <input type="email" name="email" 
                                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                           placeholder="Enter your email" required />
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
                                           placeholder="Enter your password" required />
                                </div>
                            </div>

                            <!-- Gender Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-venus-mars text-gray-400"></i>
                                    </span>
                                    <select name="gender" required 
                                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Role Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fas fa-user-tag text-gray-400"></i>
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

                            <!-- Register Button -->
                            <button type="submit" name="submit" 
                                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                Register
                            </button>

                            <!-- Login Link -->
                            <div class="text-center mt-4">
                                <p class="text-sm text-gray-600">
                                    Already have an account? 
                                    <a href="login.php" class="text-green-600 hover:text-green-700 font-medium">
                                        Click to Login
                                    </a>
                                </p>
                            </div>
                        </form>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>