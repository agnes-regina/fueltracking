
<?php
require_once 'config/db.php';
requireLogin();

$pageTitle = 'Data Pengiriman - Fuel Transport Tracking System';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filters
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$unitFilter = $_GET['unit'] ?? '';

// Build query
$whereConditions = [];
$params = [];

if ($statusFilter && $statusFilter !== 'all') {
    $whereConditions[] = "status_progress = ?";
    $params[] = $statusFilter;
}

if ($dateFilter) {
    $whereConditions[] = "DATE(created_at) = ?";
    $params[] = $dateFilter;
}

if ($unitFilter) {
    $whereConditions[] = "nomor_unit LIKE ?";
    $params[] = "%$unitFilter%";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

try {
    // Get total count
    $countSql = "SELECT COUNT(*) FROM fuel_logs $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
    
    // Get data
    $sql = "
        SELECT fl.*, 
               u1.full_name as transportir_name,
               u2.full_name as lapangan_name,
               u3.full_name as driver_user_name,
               u4.full_name as depo_name,
               u5.full_name as fuelman_name
        FROM fuel_logs fl
        LEFT JOIN users u1 ON fl.pt_created_by = u1.id
        LEFT JOIN users u2 ON fl.pl_created_by = u2.id  
        LEFT JOIN users u3 ON fl.dr_created_by = u3.id
        LEFT JOIN users u4 ON fl.pd_created_by = u4.id
        LEFT JOIN users u5 ON fl.fm_created_by = u5.id
        $whereClause
        ORDER BY fl.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-list-ul"></i> Data Pengiriman BBM
                </h4>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <?php foreach ($statusLabels as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($dateFilter); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor Unit</label>
                        <input type="text" name="unit" class="form-control" placeholder="Cari unit..." 
                               value="<?php echo htmlspecialchars($unitFilter); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label><br>
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="logs.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </form>
                
                <!-- Statistics Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Menampilkan <?php echo count($logs); ?> dari <?php echo $totalRecords; ?> total pengiriman
                        </div>
                    </div>
                </div>
                
                <!-- Data Table -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif (empty($logs)): ?>
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle"></i> Tidak ada data pengiriman ditemukan
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
                                    <th>Progress</th>
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
                                            <div class="progress" style="height: 20px;">
                                                <?php
                                                $progressPercentage = 0;
                                                switch($log['status_progress']) {
                                                    case 'waiting_pengawas': $progressPercentage = 20; break;
                                                    case 'waiting_driver': $progressPercentage = 40; break;
                                                    case 'waiting_depo': $progressPercentage = 60; break;
                                                    case 'waiting_fuelman': $progressPercentage = 80; break;
                                                    case 'done': $progressPercentage = 100; break;
                                                }
                                                ?>
                                                <div class="progress-bar bg-<?php echo $progressPercentage == 100 ? 'success' : 'primary'; ?>" 
                                                     style="width: <?php echo $progressPercentage; ?>%">
                                                    <?php echo $progressPercentage; ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y', strtotime($log['created_at'])); ?><br>
                                                <?php echo date('H:i', strtotime($log['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="detail.php?id=<?php echo $log['id']; ?>" 
                                                   class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (hasRole('admin')): ?>
                                                    <a href="edit.php?id=<?php echo $log['id']; ?>" 
                                                       class="btn btn-outline-warning" data-bs-toggle="tooltip" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&unit=<?php echo $unitFilter; ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&unit=<?php echo $unitFilter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&unit=<?php echo $unitFilter; ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
