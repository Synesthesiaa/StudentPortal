<?php
require('../databaseConnector/connector.php');

if(!isset($_GET['token'])) {
    header("Location: login.php");
    exit();
}

$token = mysqli_real_escape_string($con, $_GET['token']);

// Check if token exists and is not expired
$query = "SELECT * FROM signup_login WHERE reset_token = ? AND reset_token_expiry > NOW()";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    $error = "Invalid or expired reset token. Please request a new password reset.";
} else {
    if(isset($_POST['submit'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Update password and clear reset token
            $hashed_password = md5($password);
            $update_query = "UPDATE signup_login SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
            $update_stmt = $con->prepare($update_query);
            $update_stmt->bind_param("ss", $hashed_password, $token);
            
            if($update_stmt->execute()) {
                $success = "Password has been reset successfully. You can now login with your new password.";
            } else {
                $error = "Error resetting password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gradient-to-br from-green-50 to-white font-sans min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
                <p class="text-gray-600 mt-2">Enter your new password</p>
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

            <?php if(!isset($error) || $error !== "Invalid or expired reset token. Please request a new password reset."): ?>
                <form method="post" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="Enter new password">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="Confirm new password">
                    </div>

                    <button type="submit" name="submit" 
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" class="text-green-600 hover:text-green-700">
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html> 