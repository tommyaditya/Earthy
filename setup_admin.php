<?php
/**
 * Setup Admin User
 * Script untuk membuat admin user default
 * Akses: http://localhost/Maps/setup_admin.php
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Setup Admin User</h2>";
    echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
    
    // Check if admin already exists
    $checkQuery = "SELECT * FROM admin_users WHERE username = 'admin'";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo "<p class='info'>Admin user sudah ada. Updating password...</p>";
        
        // Update password
        $password = 'admin123';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $updateQuery = "UPDATE admin_users SET password_hash = :password_hash WHERE username = 'admin'";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':password_hash', $password_hash);
        $updateStmt->execute();
        
        echo "<p class='success'>✅ Password admin berhasil di-update!</p>";
    } else {
        echo "<p class='info'>Admin user tidak ada. Membuat user baru...</p>";
        
        // Create new admin
        $username = 'admin';
        $email = 'admin@tourismmap.com';
        $password = 'admin123';
        $full_name = 'Administrator';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $insertQuery = "INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) 
                        VALUES (:username, :email, :password_hash, :full_name, 'admin', 1)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':email', $email);
        $insertStmt->bindParam(':password_hash', $password_hash);
        $insertStmt->bindParam(':full_name', $full_name);
        $insertStmt->execute();
        
        echo "<p class='success'>✅ Admin user berhasil dibuat!</p>";
    }
    
    // Display admin info
    echo "<hr>";
    echo "<h3>Admin Login Credentials:</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Password Hash:</strong> " . password_hash('admin123', PASSWORD_DEFAULT) . "</p>";
    
    echo "<hr>";
    echo "<h3>Test Password Verification:</h3>";
    $testHash = password_hash('admin123', PASSWORD_DEFAULT);
    $testVerify = password_verify('admin123', $testHash);
    echo "<p>Password verify test: " . ($testVerify ? "✅ PASS" : "❌ FAIL") . "</p>";
    
    // Get current admin data
    echo "<hr>";
    echo "<h3>Current Admin Data:</h3>";
    $query = "SELECT id, username, email, full_name, role, is_active, created_at FROM admin_users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<pre>";
        print_r($admin);
        echo "</pre>";
    }
    
    echo "<hr>";
    echo "<p><a href='admin/login.php'>➡️ Go to Login Page</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Pastikan:</p>";
    echo "<ul>";
    echo "<li>MySQL sudah running di XAMPP</li>";
    echo "<li>Database 'tourism_map_db' sudah dibuat</li>";
    echo "<li>File database/schema.sql sudah di-import</li>";
    echo "</ul>";
    echo "<p><a href='check_database.php'>➡️ Check Database Status</a></p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
