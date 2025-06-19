<?php
require('../databaseConnector/connector.php');

if(isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    
    // Check if email exists in the database
    $query = "SELECT * FROM signup_login WHERE email = ? AND role = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store the token in the database
        $update_query = "UPDATE signup_login SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param("sss", $token, $expiry, $email);
        
        if($update_stmt->execute()) {
            // Send reset email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/new/signup_login/reset_password.php?token=" . $token;
            $to = $email;
            $subject = "Password Reset Request";
            $message = "Hello,\n\n";
            $message .= "You have requested to reset your password. Click the link below to reset your password:\n\n";
            $message .= $reset_link . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you did not request this, please ignore this email.\n\n";
            $message .= "Best regards,\nYour School System";
            
            $headers = "From: noreply@schoolsystem.com";
            
            if(mail($to, $subject, $message, $headers)) {
                $success = "Password reset instructions have been sent to your email.";
            } else {
                $error = "Failed to send reset email. Please try again.";
            }
        } else {
            $error = "Error processing your request. Please try again.";
        }
    } else {
        $error = "Email not found for the selected role.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gradient-to-br from-green-50 to-white font-sans min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Forgot Password</h1>
                <p class="text-gray-600 mt-2">Enter your email to reset your password</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="Enter your email">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>

                <button type="submit" name="submit" 
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Reset Password
                </button>

                <div class="text-center">
                    <a href="login.php" class="text-green-600 hover:text-green-700">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 