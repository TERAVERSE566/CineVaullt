<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineVault – Register</title>
    <link rel="stylesheet" href="cinevault.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-body-overlay"></div>
    <div class="auth-box">
        <h2><span>Join</span> CineVault</h2>
        <div class="auth-error" id="errorMsg"></div>
        <div class="auth-success" id="successMsg"></div>
        <form id="registerForm">
            <div class="input-grp">
                <label>Username</label>
                <input type="text" id="username" required placeholder="Choose a username">
            </div>
            <div class="input-grp">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="Enter your email">
            </div>
            <div class="input-grp">
                <label>Password</label>
                <input type="password" id="password" required minlength="6" placeholder="Create a password">
            </div>
            <button type="submit" class="auth-btn">Sign Up</button>
        </form>
        <div class="auth-links">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
    <script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const email    = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errorMsg   = document.getElementById('errorMsg');
        const successMsg = document.getElementById('successMsg');
        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
        try {
            const formData = new FormData();
            formData.append('username', username);
            formData.append('email', email);
            formData.append('password', password);
            const response = await fetch('api/register.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.status === 'success') {
                successMsg.textContent = "Registration successful! Redirecting to login...";
                successMsg.style.display = 'block';
                setTimeout(() => { window.location.href = 'login.php'; }, 2000);
            } else {
                errorMsg.textContent = data.message;
                errorMsg.style.display = 'block';
            }
        } catch (err) {
            errorMsg.textContent = "Connection error. Please try again.";
            errorMsg.style.display = 'block';
        }
    });
    </script>
</body>
</html>
