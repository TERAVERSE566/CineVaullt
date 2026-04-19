<?php
require_once 'api/db_connect.php';

$username = 'admin';
$email = 'admin@cinevault.local';
$password = 'admin123'; // The plaintext password

try {
    // Generate a secure hash for the password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    
    if ($stmt->rowCount() > 0) {
        // Update existing user to admin role just in case
        $pdo->exec("UPDATE users SET role = 'admin', password_hash = '$hash' WHERE email = '$email'");
        echo "Admin account already exists. Password reset to 'admin123' and role confirmed as 'admin'.\n";
    } else {
        // Insert new admin user
        $insert = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
        $insert->execute([$username, $email, $hash]);
        echo "Administrator account successfully created!\n";
    }
    
    echo "--------------------------\n";
    echo "Login Credentials for you:\n";
    echo "Username: admin \n";
    echo "Email: admin@cinevault.local \n";
    echo "Password: admin123 \n";
    echo "--------------------------\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
