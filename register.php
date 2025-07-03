<?php
session_start();
include("config.php");
$error = "";
$msg = "";

$formData = [
    'firstName' => '',
    'phone' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => ''
];
$errors = [];
$isLoading = false;

// Add the missing validation functions
function validatePhone($phone) {
    // Check if phone starts with +251 and has 9 more digits
    return preg_match('/^\+251[0-9]{9}$/', $phone);
}

function validatePassword($password) {
    // Check for 8+ chars with uppercase, lowercase, number, and special character
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password) && 
           preg_match('/[^A-Za-z0-9]/', $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneInput = trim($_POST['phone'] ?? '');
    $phoneWithCode = '+251' . $phoneInput;
    
    $formData = [
        'firstName' => trim($_POST['firstName'] ?? ''),
        'phone' => $phoneWithCode, // Store the full number with code
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirmPassword' => $_POST['confirmPassword'] ?? '',
        'utype' => $_POST['utype'] ?? '',
    ];
    
    // Fix syntax errors in file upload handling
    $uimage = '';
    $temp_name1 = '';

    if (isset($_FILES['uimage']) && $_FILES['uimage']['error'] != UPLOAD_ERR_NO_FILE) {
        $uimage = $_FILES['uimage']['name'];
        $temp_name1 = $_FILES['uimage']['tmp_name'];
        
        // Optional: Add file type validation if a file is uploaded
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($uimage, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors['uimage'] = 'Only JPG, JPEG, PNG, and GIF files are allowed';
            $uimage = '';
            $temp_name1 = '';
        }
    }

    // Validate first name
    if (empty($formData['firstName'])) {
        $errors['firstName'] = 'First name is required';
    }

    // Validate phone
    if (empty($formData['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!validatePhone($formData['phone'])) {
        $errors['phone'] = 'Invalid phone number. Must be in format +251XXXXXXXXX';
    }

    // Validate email
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email is invalid';
    }

    // Validate password
    if (empty($formData['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($formData['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    // Validate confirm password
    if ($formData['password'] !== $formData['confirmPassword']) {
        $errors['confirmPassword'] = 'Passwords do not match';
    }
    // Fix the logical error in password validation
    elseif (!validatePassword($formData['password'])) {
        $errors['confirmPassword'] = 'Password must be 8+ chars with uppercase, lowercase, number, and special character';
    }

    // Fix the SQL query to use proper parameter
    $query = "SELECT * FROM user WHERE uemail='" . mysqli_real_escape_string($con, $formData['email']) . "'";
    $res = mysqli_query($con, $query);
    $num = mysqli_num_rows($res);

    if ($num == 1) {
        $errors['email'] = 'Email Id already exists';
    } 
    
    // Process form if no errors
    if (empty($errors)) {
        $isLoading = true;
        
        // Simulate API delay
        sleep(1);
        $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO user (uname, uemail, uphone, upass, utype, uimage) VALUES (
            '" . mysqli_real_escape_string($con, $formData['firstName']) . "', 
            '" . mysqli_real_escape_string($con, $formData['email']) . "', 
            '" . mysqli_real_escape_string($con, $formData['phone']) . "', 
            '" . mysqli_real_escape_string($con, $hashedPassword) . "', 
            '" . mysqli_real_escape_string($con, $formData['utype']) . "', 
            '" . mysqli_real_escape_string($con, $uimage) . "'
        )";
        
        $result = mysqli_query($con, $sql);
        
        // Fix file upload path
        if (!empty($temp_name1) && !empty($uimage)) {
            $upload_dir = "admin/user/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            move_uploaded_file($temp_name1, $upload_dir . $uimage);
        } else {
            // Set a default image if no image is uploaded
            $uimage = 'default-user.jpg';
        }
        
        if ($result) {
            $msg = "<p class='alert alert-success'>Registered successfully!</p>";
            header("location:login1.php?msg=$msg");
            exit;
        } else {
            $errors['signup'] = 'Registration failed: ' . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REMS - Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/lucide-icons@0.344.0/font/lucide.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>
    <div class="min-h-screen bg-gradient-to-br from-blue-900/90 to-blue-950/90 flex flex-col items-center justify-center p-4 md:p-6 relative overflow-hidden">
        <!-- Background image with overlay -->
        <div 
            class="absolute inset-0 bg-cover bg-center z-0 opacity-30" 
            style="background-image: url('images/rms.jpg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'); background-blend-mode: overlay;"
        ></div>
        
        <!-- Content container -->
        <div class="w-full max-w-md z-10">
            <!-- Logo and title -->
            <div class="text-center mb-8 transform transition duration-500 hover:scale-105">
                <div class="w-20 h-20 mx-auto rounded-full bg-white flex items-center justify-center shadow-lg relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-emerald-500/10"></div>
                    <i class="lucide-building-2 text-emerald-600 text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold mt-4 text-white">REMS</h1>
                
            </div>
            
            <!-- Sign up form card -->
            <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-xl shadow-2xl p-6 md:p-8 transition-all duration-300 hover:shadow-blue-900/20">
            <marquee><h2 class="text-2xl font-semibold text-blue-950 mb-6">Create Account</h2></marquee>
                
                <?php if (isset($errors['signup'])): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($errors['signup']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($msg): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6" enctype="multipart/form-data">
                    <!-- Name Fields -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input
                                type="text"
                                id="firstName"
                                name="firstName"
                                value="<?php echo htmlspecialchars($formData['firstName']); ?>"
                                placeholder="lemi"
                                class="w-full rounded-lg border <?php echo isset($errors['firstName']) ? 'border-red-500' : 'border-gray-300'; ?> bg-white px-3 py-2 text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
                            >
                            <?php if (isset($errors['firstName'])): ?>
                                <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($errors['firstName']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="space-y-1">
    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
    <div class="flex rounded-lg border <?php echo isset($errors['phone']) ? 'border-red-500' : 'border-gray-300'; ?>">
        <span class="inline-flex items-center px-3 rounded-l-md border-r border-gray-300 bg-gray-50 text-gray-500 text-sm">
            +251
        </span>
        <input
            type="text"
            id="phone"
            name="phone"
            value="<?php echo htmlspecialchars(substr($formData['phone'], 4) ?? ''); ?>"
            placeholder="9XXXXXXXX"
            maxlength="9"
            pattern="[0-9]{9}"
            class="w-full rounded-r-lg border-0 bg-white px-3 py-2 text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
        >
    </div>
    <?php if (isset($errors['phone'])): ?>
        <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($errors['phone']); ?></p>
    <?php endif; ?>
    <p class="text-gray-500 text-xs mt-1">Enter 9 digits after +251</p>
</div>


                    <!-- Email Input -->
                    <div class="space-y-1">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($formData['email']); ?>"
                            placeholder="your@email.com"
                            class="w-full rounded-lg border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-300'; ?> bg-white px-3 py-2 text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
                        >
                        <?php if (isset($errors['email'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($errors['email']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Password Input -->
                    <div class="space-y-1 relative">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            class="w-full rounded-lg border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-300'; ?> bg-white px-3 py-2 text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
                        >
                        <button
                            type="button"
                            onclick="togglePassword('password')"
                            class="absolute right-3 top-[38px] text-gray-500 hover:text-blue-800 transition-colors"
                        >
                            <i class="lucide-eye text-lg"></i>
                        </button>
                        <?php if (isset($errors['password'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($errors['password']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="space-y-1 relative">
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirmPassword"
                            placeholder="••••••••"
                            class="w-full rounded-lg border <?php echo isset($errors['confirmPassword']) ? 'border-red-500' : 'border-gray-300'; ?> bg-white px-3 py-2 text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
                        >
                        <button
                            type="button"
                            onclick="togglePassword('confirmPassword')"
                            class="absolute right-3 top-[38px] text-gray-500 hover:text-blue-800 transition-colors"
                        >
                            <i class="lucide-eye text-lg"></i>
                        </button>
                        <?php if (isset($errors['confirmPassword'])): ?>
                            <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($errors['confirmPassword']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- User Type -->
                    <div class="space-y-1">
                        <label for="utype" class="block text-sm font-medium text-gray-700">User Type</label>
                        <div class="mt-1">
                            <label class="inline-flex items-center">
                                <input type="radio" name="utype" value="user" checked class="text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700">Customer</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- User Image -->
                    <div class="space-y-1">
                        <label for="uimage" class="block text-sm font-medium text-gray-700">Profile Image (Optional)</label>
                        <input 
                            type="file" 
                            id="uimage" 
                            name="uimage" 
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
                        >
                        <p class="text-gray-500 text-xs mt-1">Upload a profile picture or leave empty to use default image</p>
                    </div>
                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full bg-blue-800 text-white py-2 px-4 rounded-lg hover:bg-blue-900 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 font-medium"
                    >
                        Create account
                    </button>

                    <!-- Social Login Divider 
                    <div class="relative flex items-center justify-center mt-6">
                        <div class="border-t border-gray-300 absolute w-full"></div>
                        <div class="bg-white px-4 relative z-10 text-sm text-gray-500">or sign up with</div>
                    </div>-->
                    
                    <!-- Social Login Buttons -->
                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" class="flex justify-center items-center py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <img src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png" alt="Google" class="w-5 h-5">
                        </button>
                        <button type="button" class="flex justify-center items-center py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="lucide-twitter text-blue-500"></i>
                        </button>
                        <button type="button" class="flex justify-center items-center py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="lucide-github"></i>
                        </button>
                    </div>

                    <!-- Sign In Link -->
                    <div class="text-center mt-6">
                        <p class="text-gray-600">
                            Already have an account?
                            <a href="login1.php" class="text-blue-800 hover:text-blue-950 font-medium transition-colors ml-1">
                                Sign in
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-blue-200 text-sm">
                <p>© 2025 EstateManager. All rights reserved.</p>
                <div class="mt-2 space-x-4">
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Support</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = passwordInput.nextElementSibling.querySelector('.lucide-eye, .lucide-eye-off');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('lucide-eye', 'lucide-eye-off');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('lucide-eye-off', 'lucide-eye');
            }
        }
    </script>
</body>
</html>
