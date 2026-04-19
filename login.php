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
    <title>CineVault – Sign In</title>
    <link rel="stylesheet" href="cinevault.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-body-overlay"></div>
    <div class="auth-box">
        <h2><span>Cine</span>Vault</h2>
        <div class="auth-error" id="errorMsg"></div>
        <form id="loginForm">
            <div class="input-grp">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="Enter your email">
            </div>
            <div class="input-grp">
                <label>Password</label>
                <input type="password" id="password" required placeholder="Enter your password">
            </div>
            <button type="submit" class="auth-btn">Sign In</button>
        </form>
        <div class="auth-links">
            New to CineVault? <a href="register.php">Sign up now</a>
        </div>
    </div>
    <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email    = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errorMsg = document.getElementById('errorMsg');
        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            const response = await fetch('api/login.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.status === 'success') {
                window.location.href = 'home.php';
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
