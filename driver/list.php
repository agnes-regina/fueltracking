
<?php
require_once '../config/db.php';
requireLogin();
requireRole('driver');

$pageTitle = 'Data Pengiriman - Driver';

try {
    $stmt = $pdo->prepare("
        SELECT fl.*, u.full_name as creator_name 
        FROM fuel_logs fl 
        LEFT JOIN users u ON fl.pt_created_by = u.id 
        WHERE fl.status_progress IN ('waiting_driver', 'waiting_depo', 'waiting_fuelman', 'done')
        ORDER BY fl.created_at DESC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-truck"></i> Data Pengiriman - Driver
                </h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif (empty($logs)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> Belum ada data pengiriman
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Unit</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><strong>#<?php echo $log['id']; ?></strong></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($log['nomor_unit']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['driver_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                                                <?php echo $statusLabels[$log['status_progress']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y', strtotime($log['created_at'])); ?><br>
                                                <?php echo date('H:i', strtotime($log['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../detail.php?id=<?php echo $log['id']; ?>" 
                                                   class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                <?php if ($log['status_progress'] == 'waiting_driver'): ?>
                                                    <a href="form.php?id=<?php echo $log['id']; ?>" 
                                                       class="btn btn-primary" data-bs-toggle="tooltip" title="Input Data">
                                                        <i class="bi bi-truck"></i>
                                                    </a>
                                                <?php elseif ($log['dr_created_at']): ?>
                                                    <span class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Sudah Diisi">
                                                        <i class="bi bi-check-circle"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
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
</div>

<?php require_once '../includes/footer.php'; ?>
