<?php
/**
 * Check Database Status
 * Script untuk mengecek status database dan tabel
 * Akses: http://localhost/Maps/check_database.php
 */

echo "<h2>Database Status Check</h2>";
echo "<style>
    body{font-family:Arial;padding:20px;} 
    .success{color:green;} 
    .error{color:red;} 
    .warning{color:orange;} 
    .info{color:blue;}
    table{border-collapse:collapse;width:100%;margin:20px 0;}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;}
    th{background:#f2f2f2;}
</style>";

// Test 1: Check PHP Version
echo "<h3>1. PHP Version</h3>";
$phpVersion = phpversion();
echo "<p class='info'>PHP Version: <strong>$phpVersion</strong></p>";
if (version_compare($phpVersion, '5.5.0', '>=')) {
    echo "<p class='success'>✅ PHP version OK (password_hash supported)</p>";
} else {
    echo "<p class='error'>❌ PHP version terlalu lama. Update ke PHP 5.5+</p>";
}

// Test 2: Check PDO Extension
echo "<hr><h3>2. PDO Extension</h3>";
if (extension_loaded('pdo_mysql')) {
    echo "<p class='success'>✅ PDO MySQL extension loaded</p>";
} else {
    echo "<p class='error'>❌ PDO MySQL extension not found</p>";
}

// Test 3: Database Connection
echo "<hr><h3>3. Database Connection</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "<p class='success'>✅ Database connection successful</p>";
    echo "<p class='info'>Database: tourism_map_db</p>";
    
    // Test 4: Check Tables
    echo "<hr><h3>4. Database Tables</h3>";
    $tables = ['destinations', 'destination_images', 'tags', 'destination_tags', 'reviews', 'admin_users'];
    
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Status</th><th>Row Count</th></tr>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            $count = $result['count'];
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td class='success'>✅ Exists</td>";
            echo "<td>$count rows</td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td class='error'>❌ Not Found</td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Test 5: Check Admin User
    echo "<hr><h3>5. Admin User Status</h3>";
    try {
        $stmt = $db->query("SELECT * FROM admin_users WHERE username = 'admin'");
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<p class='success'>✅ Admin user found</p>";
            echo "<table>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            echo "<tr><td>ID</td><td>{$admin['id']}</td></tr>";
            echo "<tr><td>Username</td><td>{$admin['username']}</td></tr>";
            echo "<tr><td>Email</td><td>{$admin['email']}</td></tr>";
            echo "<tr><td>Full Name</td><td>{$admin['full_name']}</td></tr>";
            echo "<tr><td>Role</td><td>{$admin['role']}</td></tr>";
            echo "<tr><td>Is Active</td><td>" . ($admin['is_active'] ? 'Yes' : 'No') . "</td></tr>";
            echo "<tr><td>Password Hash</td><td>" . substr($admin['password_hash'], 0, 30) . "...</td></tr>";
            echo "</table>";
            
            // Test password verification
            echo "<h4>Password Verification Test:</h4>";
            $testPassword = 'admin123';
            $isValid = password_verify($testPassword, $admin['password_hash']);
            
            if ($isValid) {
                echo "<p class='success'>✅ Password 'admin123' is VALID</p>";
            } else {
                echo "<p class='error'>❌ Password 'admin123' is INVALID</p>";
                echo "<p class='warning'>⚠️ Password hash perlu di-reset!</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Admin user NOT found</p>";
            echo "<p class='warning'>⚠️ Admin user perlu dibuat!</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error checking admin_users table: " . $e->getMessage() . "</p>";
    }
    
    // Test 6: Session Check
    echo "<hr><h3>6. Session Support</h3>";
    if (session_status() === PHP_SESSION_DISABLED) {
        echo "<p class='error'>❌ Sessions are disabled</p>";
    } else {
        echo "<p class='success'>✅ Sessions are enabled</p>";
        if (!isset($_SESSION)) {
            session_start();
        }
        echo "<p class='info'>Session ID: " . session_id() . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>Actions:</h3>";
    echo "<p><a href='setup_admin.php' style='padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;border-radius:4px;'>🔧 Setup/Reset Admin User</a></p>";
    echo "<p><a href='admin/login.php' style='padding:10px 20px;background:#2196F3;color:white;text-decoration:none;border-radius:4px;'>🔐 Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Cannot connect to database</p>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    
    echo "<hr><h3>Possible Solutions:</h3>";
    echo "<ol>";
    echo "<li>Pastikan XAMPP MySQL sudah running (Start di XAMPP Control Panel)</li>";
    echo "<li>Buka phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Import file: <code>database/schema.sql</code></li>";
    echo "<li>Atau buat database manual:";
    echo "<pre>CREATE DATABASE tourism_map_db;</pre>";
    echo "</li>";
    echo "<li>Check config/database.php - pastikan credentials benar</li>";
    echo "</ol>";
}
