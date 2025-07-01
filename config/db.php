<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fuel_tracking');
define('DB_USER', 'root');
define('DB_PASS', '123456');
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'fueb3648_fuel_tracking');
// define('DB_USER', 'fueb3648_root');
// define('DB_PASS', 'Gajah890!');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Authentication helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

// function hasRole($requiredRole) {
//     return isLoggedIn() && $_SESSION['role'] === $requiredRole;
// }

// function requireRole($requiredRole) {
//     if (!hasRole($requiredRole)) {
//         die("Access denied. Required role: " . $requiredRole);
//     }
// }

function hasRole($requiredRole) {
    if (!isLoggedIn()) return false;

    $userRole = $_SESSION['role'];

    // Kalau multiple role
    if (is_array($requiredRole)) {
        return in_array($userRole, $requiredRole);
    }

    return $userRole === $requiredRole;
}

function requireRole($requiredRole) {
    if (!hasRole($requiredRole)) {
        $roleDisplay = is_array($requiredRole) ? implode(', ', $requiredRole) : $requiredRole;
        die("Access denied. Required role: $roleDisplay");
    }
}


// Upload helper function
function uploadFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedTypes)) {
        return false;
    }
    
    $fileName = uniqid() . '_' . time() . '.' . $fileExt;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return 'uploads/' . $fileName;
    }
    
    return false;
}

// Status mapping
$statusLabels = [
    'waiting_pengawas' => 'Menunggu Pengawas Lapangan',
    'waiting_driver' => 'Menunggu Driver',
    'driver_loading_done' => 'Driver Selesai Loading',
    'waiting_depo' => 'Menunggu Pengawas Depo',
    'waiting_fuelman' => 'Menunggu Fuelman',
    'done' => 'Selesai'
];

$roleLabels = [
    'admin' => 'Administrator',
    'pengawas_transportir' => 'Pengawas Transportir',
    'pengawas_lapangan' => 'Pengawas Lapangan',
    'driver' => 'Driver',
    'pengawas_depo' => 'Pengawas Depo',
    'fuelman' => 'Fuelman',
    'gl_pama' => 'GL PAMA'
];
?>
