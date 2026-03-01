<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// Path to users data file
$usersFile = __DIR__ . '/users.json';

// Function to load users from JSON file
function loadUsers() {
    global $usersFile;
    if (file_exists($usersFile)) {
        $data = file_get_contents($usersFile);
        return json_decode($data, true) ?? [];
    }
    return [];
}

// Function to save users to JSON file
function saveUsers($users) {
    global $usersFile;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'register') {
        handleRegistration();
    } elseif ($action === 'login') {
        handleLogin();
    }
}

// Handle registration
function handleRegistration() {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['user'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['pass'] ?? '';
    
    // Validate input
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = "Full name is required.";
    }
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if (!empty($errors)) {
        showError($errors, 'register');
        return;
    }
    
    // Load existing users
    $users = loadUsers();
    
    // Check if username already exists
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            showError(["Username already exists. Please choose another."], 'register');
            return;
        }
    }
    
    // Check if email already exists
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            showError(["Email already registered. Please use another email."], 'register');
            return;
        }
    }
    
    // Create new user
    $newUser = [
        'id' => count($users) + 1,
        'fullname' => $fullname,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT), // Hash password for security
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Add user to array
    $users[] = $newUser;
    
    // Save users
    saveUsers($users);
    
    // Set session and show success
    $_SESSION['user_id'] = $newUser['id'];
    $_SESSION['username'] = $newUser['username'];
    $_SESSION['fullname'] = $newUser['fullname'];
    
    showSuccess("Registration successful! Welcome, " . htmlspecialchars($fullname) . "!", 'Welcome');
}

// Handle login
function handleLogin() {
    $username = trim($_POST['user'] ?? '');
    $password = $_POST['pass'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        showError(["Username and password are required."], 'login');
        return;
    }
    
    // Load users
    $users = loadUsers();
    
    // Find user
    $foundUser = null;
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $foundUser = $user;
            break;
        }
    }
    
    // Verify credentials
    if ($foundUser && password_verify($password, $foundUser['password'])) {
        // Set session
        $_SESSION['user_id'] = $foundUser['id'];
        $_SESSION['username'] = $foundUser['username'];
        $_SESSION['fullname'] = $foundUser['fullname'];
        $_SESSION['email'] = $foundUser['email'];
        
        showSuccess("Login successful! Welcome back, " . htmlspecialchars($foundUser['fullname']) . "!", 'Dashboard');
    } else {
        showError(["Invalid username or password."], 'login');
    }
}

// Show error message
function showError($errors, $redirectTo = null) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Error | University of Mindanao CCE</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="css/util.css">
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <style>
            .error-container { max-width: 600px; margin: 50px auto; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; }
            .error-title { color: #721c24; font-size: 24px; margin-bottom: 15px; }
            .error-message { color: #721c24; margin: 10px 0; }
            .error-link { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #721c24; color: white; text-decoration: none; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h2 class="error-title">Error</h2>
            <?php foreach ($errors as $error): ?>
                <p class="error-message">• <?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
            <?php if ($redirectTo): ?>
                <a href="<?php echo htmlspecialchars($redirectTo); ?>.php" class="error-link">Go back to <?php echo ucfirst($redirectTo); ?></a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Show success message
function showSuccess($message, $dashboardName = 'Dashboard') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Success | University of Mindanao CCE</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="css/util.css">
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <style>
            .success-container { max-width: 600px; margin: 50px auto; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; }
            .success-title { color: #155724; font-size: 24px; margin-bottom: 15px; }
            .success-message { color: #155724; margin: 10px 0; font-size: 16px; }
            .success-link { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #155724; color: white; text-decoration: none; border-radius: 3px; }
            .session-info { margin-top: 20px; padding: 15px; background: #c3e6cb; border-radius: 3px; }
            .session-info h3 { color: #155724; margin-top: 0; }
            .session-info p { color: #155724; margin: 5px 0; }
        </style>
    </head>
    <body>
        <div class="success-container">
            <h2 class="success-title">Success!</h2>
            <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
            
            <div class="session-info">
                <h3>Your Information:</h3>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
                <?php if (isset($_SESSION['email'])): ?>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                <?php endif; ?>
            </div>
            
            <a href="login.php" class="success-link">Go to Login</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
