<?php
date_default_timezone_set('Asia/Jakarta');
require_once '../config/db.php';
requireLogin();
requireRole('admin');

$pageTitle = 'Edit User - Admin';

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

if (!$id) {
    header('Location: users.php');
    exit();
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: users.php');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        if (!empty($password)) {
            // Update with new password
            $sql = "UPDATE users SET full_name = ?, username = ?, role = ?, password = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $username, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            // Update without changing password
            $sql = "UPDATE users SET full_name = ?, username = ?, role = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$full_name, $username, $role, $id]);
        }
        
        $success = "User berhasil diperbarui!";
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-person-gear"></i> Edit User
                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($user['username']); ?></span>
                </h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person"></i> Nama Lengkap *
                            </label>
                            <input type="text" class="form-control" id="full_name" 
                                   name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-at"></i> Username *
                            </label>
                            <input type="text" class="form-control" id="username" 
                                   name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="role" class="form-label">
                                <i class="bi bi-shield"></i> Role *
                            </label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="pengawas_transportir" <?php echo $user['role'] == 'pengawas_transportir' ? 'selected' : ''; ?>>Pengawas Transportir</option>
                                <option value="pengawas_lapangan" <?php echo $user['role'] == 'pengawas_lapangan' ? 'selected' : ''; ?>>Pengawas Lapangan</option>
                                <option value="driver" <?php echo $user['role'] == 'driver' ? 'selected' : ''; ?>>Driver</option>
                                <option value="pengawas_depo" <?php echo $user['role'] == 'pengawas_depo' ? 'selected' : ''; ?>>Pengawas Depo</option>
                                <option value="fuelman" <?php echo $user['role'] == 'fuelman' ? 'selected' : ''; ?>>Fuelman</option>
                                <option value="gl_pama" <?php echo $user['role'] == 'gl_pama' ? 'selected' : ''; ?>>GL PAMA</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-key"></i> Password Baru
                            </label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="bi bi-info-circle"></i> Informasi User:</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Dibuat:</strong><br><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Login Terakhir:</strong><br><?php echo date('d/m/Y H:i'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
