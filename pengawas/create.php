<?php
require_once '../config/db.php';
requireLogin();
requireRole('pengawas_transportir');

$pageTitle = 'Buat Pengiriman Baru - Pengawas Transportir';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pt_unit_number = trim($_POST['pt_unit_number'] ?? '');
    $pt_driver_id = trim($_POST['pt_driver_id'] ?? '');

    if (empty($pt_unit_number) || empty($pt_driver_id)) {
        $error = 'Nomor unit dan driver harus diisi';
    } else {
        // Ambil nama driver dari id
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ? AND role = 'driver' LIMIT 1");
        $stmt->execute([$pt_driver_id]);
        $driver = $stmt->fetch();
        $pt_driver_name = $driver ? $driver['full_name'] : '';

        if (!$pt_driver_name) {
            $error = 'Driver tidak ditemukan';
        } else {
            try {
                // $stmt = $pdo->prepare("
                //     INSERT INTO fuel_logs (
                //         nomor_unit, driver_name, status_progress,
                //         pt_unit_number, pt_driver_name, pt_created_by, pt_created_at
                //     ) VALUES (?, ?, 'waiting_pengawas', ?, ?, ?, NOW())
                // ");
                $stmt = $pdo->prepare("
                    INSERT INTO fuel_logs (
                        nomor_unit, driver_name, status_progress,
                        pt_unit_number, pt_driver_name, pt_driver_id, pt_created_by, pt_created_at
                    ) VALUES (?, ?, 'waiting_pengawas', ?, ?, ?, ?, NOW())
                ");

                $stmt->execute([
                    $pt_unit_number,      // nomor_unit
                    $pt_driver_name,      // driver_name
                    $pt_unit_number,      // pt_unit_number
                    $pt_driver_name,      // pt_driver_name
                    $pt_driver_id,        // pt_driver_id
                    $_SESSION['user_id']  // pt_created_by
                ]);

                $logId = $pdo->lastInsertId();
                $success = "Pengiriman berhasil dibuat dengan ID #$logId";
                $_POST = [];
            } catch(PDOException $e) {
                $error = "Error creating log: " . $e->getMessage();
            }
        }
    }
}

// Ambil daftar driver dari tabel user
try {
    $stmtDrivers = $pdo->prepare("SELECT id, full_name FROM users WHERE role = 'driver' AND is_active = 1 ORDER BY full_name ASC");
    $stmtDrivers->execute();
    $drivers = $stmtDrivers->fetchAll();
} catch(PDOException $e) {
    $drivers = [];
}

require_once '../includes/header.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Buat Pengiriman BBM Baru
                </h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <hr>
                        <a href="../logs.php" class="btn btn-outline-success">Lihat Semua Data</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="createForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pt_unit_number" class="form-label">
                                <i class="bi bi-truck"></i> Nomor Unit Kendaraan *
                            </label>
                            <input type="text" class="form-control" id="pt_unit_number" name="pt_unit_number" 
                                   value="<?php echo htmlspecialchars($_POST['pt_unit_number'] ?? ''); ?>" 
                                   placeholder="Contoh: B1234XYZ" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pt_driver_id" class="form-label">
                                <i class="bi bi-person"></i> Nama Driver *
                            </label>
                            <select class="form-control" id="pt_driver_id" name="pt_driver_id" required>
                                <option value="">-- Pilih Driver --</option>
                                <?php foreach ($drivers as $driver): ?>
                                    <option value="<?= $driver['id'] ?>"
                                        <?= (($_POST['pt_driver_id'] ?? '') == $driver['id']) ? 'selected' : '' ?>>
                                        <?= $driver['id'] . ' - ' . htmlspecialchars($driver['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Informasi Penting:</h6>
                        <ul class="mb-0">
                            <li>Setelah pengiriman dibuat, status akan menjadi "Menunggu Pengawas Lapangan"</li>
                            <li>Pengawas Lapangan akan melakukan input loading log dan dokumentasi</li>
                            <li>Pastikan data unit dan driver sudah benar sebelum submit</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="../index.php" class="btn btn-outline-secondary me-md-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-save"></i> Buat Pengiriman
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Recent Submissions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pengiriman Terbaru Anda</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $stmt = $pdo->prepare("
                        SELECT * FROM fuel_logs 
                        WHERE pt_created_by = ? 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $recentLogs = $stmt->fetchAll();
                    
                    if (empty($recentLogs)): ?>
                        <p class="text-muted text-center">Belum ada pengiriman yang dibuat</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Unit</th>
                                        <th>Driver</th>
                                        <th>Status</th>
                                        <th>Dibuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentLogs as $log): ?>
                                        <tr>
                                            <td>#<?php echo $log['id']; ?></td>
                                            <td><?php echo htmlspecialchars($log['nomor_unit']); ?></td>
                                            <td><?php echo htmlspecialchars($log['driver_name']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                                                    <?php echo $statusLabels[$log['status_progress']]; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif;
                } catch(PDOException $e) {
                    echo '<p class="text-danger">Error loading recent data</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#pt_driver_id').select2({
        placeholder: "-- Pilih Driver --",
        allowClear: true,
        width: '100%'
    });
});
</script>

<script>
document.getElementById('createForm').addEventListener('submit', function(e) {
    if (!validateForm('createForm')) {
        e.preventDefault();
        return false;
    }
    showLoading('submitBtn');
});
</script>

<?php require_once '../includes/footer.php'; ?>
