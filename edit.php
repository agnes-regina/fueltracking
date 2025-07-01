<?php
require_once 'config/db.php';
requireLogin();
requireRole('admin');

$pageTitle = 'Edit Pengiriman - Admin';

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

if (!$id) {
    header('Location: logs.php');
    exit();
}

// Get log data
try {
    $stmt = $pdo->prepare("
        SELECT fl.*, u.full_name as creator_name 
        FROM fuel_logs fl 
        LEFT JOIN users u ON fl.pt_created_by = u.id 
        WHERE fl.id = ?
    ");
    $stmt->execute([$id]);
    $log = $stmt->fetch();
    
    if (!$log) {
        header('Location: logs.php');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $nomor_unit = $_POST['nomor_unit'] ?? '';
    $driver_name = $_POST['driver_name'] ?? '';
    $status_progress = $_POST['status_progress'] ?? '';
    
    try {
        $sql = "UPDATE fuel_logs SET 
                    nomor_unit = ?, driver_name = ?, status_progress = ?,
                    updated_at = NOW() WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nomor_unit, $driver_name, $status_progress, $id]);
        
        $success = "Data berhasil diperbarui!";
        
        // Refresh data
        $stmt = $pdo->prepare("
            SELECT fl.*, u.full_name as creator_name 
            FROM fuel_logs fl 
            LEFT JOIN users u ON fl.pt_created_by = u.id 
            WHERE fl.id = ?
        ");
        $stmt->execute([$id]);
        $log = $stmt->fetch();
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Edit Pengiriman
                    <span class="badge bg-light text-dark">#<?php echo $log['id']; ?></span>
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
                            <label for="nomor_unit" class="form-label">
                                <i class="bi bi-truck"></i> Nomor Unit *
                            </label>
                            <input type="text" class="form-control" id="nomor_unit" 
                                   name="nomor_unit" value="<?php echo htmlspecialchars($log['nomor_unit']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="driver_name" class="form-label">
                                <i class="bi bi-person"></i> Nama Driver *
                            </label>
                            <input type="text" class="form-control" id="driver_name" 
                                   name="driver_name" value="<?php echo htmlspecialchars($log['driver_name']); ?>" required>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="status_progress" class="form-label">
                                <i class="bi bi-arrow-repeat"></i> Status Progress *
                            </label>
                            <select class="form-select" id="status_progress" name="status_progress" required>
                                <option value="waiting_pengawas" <?php echo $log['status_progress'] == 'waiting_pengawas' ? 'selected' : ''; ?>>
                                    <?php echo $statusLabels['waiting_pengawas']; ?>
                                </option>
                                <option value="waiting_driver" <?php echo $log['status_progress'] == 'waiting_driver' ? 'selected' : ''; ?>>
                                    <?php echo $statusLabels['waiting_driver']; ?>
                                </option>
                                <option value="waiting_depo" <?php echo $log['status_progress'] == 'waiting_depo' ? 'selected' : ''; ?>>
                                    <?php echo $statusLabels['waiting_depo']; ?>
                                </option>
                                <option value="waiting_fuelman" <?php echo $log['status_progress'] == 'waiting_fuelman' ? 'selected' : ''; ?>>
                                    <?php echo $statusLabels['waiting_fuelman']; ?>
                                </option>
                                <option value="done" <?php echo $log['status_progress'] == 'done' ? 'selected' : ''; ?>>
                                    <?php echo $statusLabels['done']; ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="bi bi-info-circle"></i> Informasi Tambahan:</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Dibuat:</strong><br><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Diperbarui:</strong><br><?php echo date('d/m/Y H:i', strtotime($log['updated_at'])); ?></p>
                                        </div>
                                    </div>
                                    <?php if ($log['creator_name']): ?>
                                        <p><strong>Dibuat oleh:</strong><br><?php echo htmlspecialchars($log['creator_name']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="detail.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        <a href="logs.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
