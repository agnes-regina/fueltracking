
<?php
$base_url = "/fueltracking/"; // Adjust this to your base URL
//$base_url = "/"; // Adjust this to your base URL
require_once 'config/db.php';

$pageTitle = 'Dashboard - Fuel Transport Tracking System';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get statistics
try {
    $totalLogs = $pdo->query("SELECT COUNT(*) FROM fuel_logs")->fetchColumn();
    $pendingLogs = $pdo->query("SELECT COUNT(*) FROM fuel_logs WHERE status_progress != 'done'")->fetchColumn();
    $completedLogs = $pdo->query("SELECT COUNT(*) FROM fuel_logs WHERE status_progress = 'done'")->fetchColumn();
    $todayLogs = $pdo->query("SELECT COUNT(*) FROM fuel_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    
    // Get recent logs based on user role
    if ($_SESSION['role'] === 'driver') {
        $stmt = $pdo->prepare("
            SELECT fl.*, u.full_name as creator_name 
            FROM fuel_logs fl 
            LEFT JOIN users u ON fl.pt_created_by = u.id 
            WHERE fl.pt_driver_id = ? 
            ORDER BY fl.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT fl.*, u.full_name as creator_name 
            FROM fuel_logs fl 
            LEFT JOIN users u ON fl.pt_created_by = u.id 
            ORDER BY fl.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute();
    }
    $recentLogs = $stmt->fetchAll();

    // Get role-specific pending tasks
    $myTasks = 0;
    switch($_SESSION['role']) {
        case 'pengawas_lapangan':
            $myTasks = $pdo->query("SELECT COUNT(*) FROM fuel_logs WHERE status_progress = 'waiting_pengawas'")->fetchColumn();
            break;
        case 'driver':
            $myTasks = $pdo->query("SELECT COUNT(*) FROM fuel_logs WHERE status_progress = 'waiting_driver' AND pt_driver_id = " . $_SESSION['user_id'])->fetchColumn();
            break;
        case 'pengawas_depo':
            $myTasks = $pdo->query("SELECT COUNT(*) FROM fuel_logs WHERE status_progress = 'waiting_depo'")->fetchColumn();
            break;
        case 'fuelman':
            $myTasks = $pdo->query("SELECT COUNT(*) FROM fuel_logs WHERE status_progress = 'waiting_fuelman'")->fetchColumn();
            break;
    }
    
    // Status distribution
    $statusStats = $pdo->query("
        SELECT status_progress, COUNT(*) as count 
        FROM fuel_logs 
        GROUP BY status_progress
    ")->fetchAll();
    
} catch(PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-speedometer2"></i> Dashboard - <?php echo $roleLabels[$_SESSION['role']]; ?>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <p class="stats-number"><?php echo $totalLogs; ?></p>
                            <p class="text-muted mb-0">Total Pengiriman</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <p class="stats-number text-warning"><?php echo $pendingLogs; ?></p>
                            <p class="text-muted mb-0">Dalam Proses</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card">
                            <p class="stats-number text-success"><?php echo $completedLogs; ?></p>
                            <p class="text-muted mb-0">Selesai</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stats-card bg-gradient-primary text-white">
                            <p class="stats-number"><?php echo $myTasks; ?></p>
                            <p class="mb-0">Tugas Saya</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (hasRole('pengawas_transportir')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $base_url ?>pengawas/create.php" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-plus-circle"></i><br>
                                Buat Pengiriman Baru
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (hasRole('pengawas_lapangan')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $base_url ?>lapangan/list.php" class="btn btn-warning w-100 py-2 position-relative">
                                <i class="bi bi-clipboard-check"></i><br>
                                Input Loading Log
                                <?php if ($myTasks > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $myTasks; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (hasRole('driver')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $base_url ?>driver/list.php" class="btn btn-info w-100 py-2 position-relative">
                                <i class="bi bi-truck"></i><br>
                                Update Status Driver
                                <?php if ($myTasks > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $myTasks; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (hasRole('pengawas_depo')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $base_url ?>depo/list.php" class="btn btn-warning w-100 py-2 position-relative">
                                <i class="bi bi-building"></i><br>
                                Input Data Depo
                                <?php if ($myTasks > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $myTasks; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (hasRole('fuelman')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $base_url ?>fuelman/list.php" class="btn btn-success w-100 py-2 position-relative">
                                <i class="bi bi-droplet"></i><br>
                                Proses Unloading
                                <?php if ($myTasks > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $myTasks; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (hasRole('admin') || hasRole('gl_pama')): ?>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $base_url ?>logs.php" class="btn btn-secondary w-100 py-2">
                                <i class="bi bi-graph-up"></i><br>
                                Monitoring & Reports
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= $base_url ?>export_all.php" class="btn btn-success w-100 py-2">
                                <i class="bi bi-file-earmark-excel"></i><br>
                                Export Excel + Images
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-md-4 mb-3">
                        <a href="<?= $base_url ?>logs.php" class="btn btn-outline-primary w-100 py-2">
                            <i class="bi bi-list-ul"></i><br>
                            Lihat Semua Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aktivitas Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentLogs)): ?>
                    <p class="text-muted text-center py-3">Belum ada data pengiriman</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($log['nomor_unit']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($log['driver_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                                                <?php echo $statusLabels[$log['status_progress']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                        <td>
                                            <a href="<?= $base_url ?>detail.php?id=<?php echo $log['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Status Distribution</h5>
            </div>
            <div class="card-body">
                <?php if (empty($statusStats)): ?>
                    <p class="text-muted text-center">Tidak ada data</p>
                <?php else: ?>
                    <?php foreach ($statusStats as $stat): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="status-badge status-<?php echo $stat['status_progress']; ?>">
                                <?php echo $statusLabels[$stat['status_progress']]; ?>
                            </span>
                            <strong><?php echo $stat['count']; ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Sistem Info</h5>
            </div>
            <div class="card-body">
                <p><strong>Role Anda:</strong> <?php echo $roleLabels[$_SESSION['role']]; ?></p>
                <p><strong>Last Login:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                <p><strong>Version:</strong> 1.0.0</p>
                <hr>
                <small class="text-muted">
                    Sistem Pelacakan Transport BBM<br>
                    © 2024 Fuel Guardian System
                </small>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'includes/camera.php';
require_once 'includes/maps.php';
require_once 'includes/footer.php'; 
?>
