<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet" />
    <link href="./css/main.css" rel="stylesheet" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | University of Mindanao CCE</title>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h1>Login</h1>
            <div class="uname-password">
                <form action="process_login.php" method="POST">
                    <input type="text" name="username" placeholder="Username" required />
                    <input type="password" name="password" placeholder="Password" required />
                    <button type="submit">Login</button>
                    <a href="register.php">Don't have an account? Register here.</a>
                </form>
            </div>
        </div>

    </div>
    
</body>
</html>