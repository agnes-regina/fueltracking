
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

if ($_SESSION['role'] === 'driver') {
    $whereConditions[] = "pt_driver_id = ?";
    $params[] = $_SESSION['user_id'];
}

if ($statusFilter && $statusFilter !== 'all') {
    $whereConditions[] = "status_progress = ?";
    $params[] = $statusFilter;
}

if ($dateFilter) {
    $whereConditions[] = "DATE(fl.created_at) = ?";
    $params[] = $dateFilter;
}

if ($unitFilter) {
    $whereConditions[] = "nomor_unit LIKE ?";
    $params[] = "%$unitFilter%";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Initialize variables
$totalRecords = 0;
$totalPages = 0;
$logs = [];
$error = '';

try {
    // Get total count
    // $countSql = "SELECT COUNT(*) FROM fuel_logs $whereClause";
    $countSql = "SELECT COUNT(*) FROM fuel_logs AS fl $whereClause";

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

<style>
.filter-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.form-control-modern {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control-modern:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.btn-filter {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-reset {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border: 2px solid #e2e8f0;
    color: #64748b;
}

.btn-reset:hover {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    border-color: #cbd5e1;
    color: #475569;
}

.table-modern {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
}

.table-modern thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-modern tbody tr {
    transition: all 0.3s ease;
    border: none;
}

.table-modern tbody tr:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    transform: scale(1.01);
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-waiting_pengawas { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; }
.status-waiting_driver { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; }
.status-waiting_depo { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; }
.status-waiting_fuelman { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; }
.status-done { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }

.progress-modern {
    height: 8px;
    border-radius: 10px;
    background: #e2e8f0;
    overflow: hidden;
}

.progress-bar-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    transition: width 0.6s ease;
}

.btn-action {
    padding: 0.5rem;
    border-radius: 8px;
    border: none;
    transition: all 0.3s ease;
    margin: 0 2px;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.pagination-modern .page-link {
    border: none;
    border-radius: 12px;
    margin: 0 4px;
    padding: 0.75rem 1rem;
    color: #667eea;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.pagination-modern .page-link:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.pagination-modern .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

@media (max-width: 768px) {
    .filter-card {
        padding: 1rem;
    }
    
    .table-responsive {
        border-radius: 15px;
    }
    
    .btn-filter, .btn-reset {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<div class="container-fluid px-3">
    <div class="row">
        <div class="col-12">
            <div class="card table-modern">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h4 class="mb-0">
                        <i class="bi bi-list-ul"></i> Data Pengiriman BBM
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="filter-card">
                        <form method="GET" class="row g-3" id="filterForm">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-funnel me-2"></i>Status
                                </label>
                                <select name="status" class="form-select form-control-modern" id="statusFilter">
                                    <option value="">Semua Status</option>
                                    <?php foreach ($statusLabels as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-calendar3 me-2"></i>Tanggal
                                </label>
                                <input type="date" name="date" class="form-control form-control-modern" 
                                       value="<?php echo htmlspecialchars($dateFilter); ?>" id="dateFilter">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-truck me-2"></i>Nomor Unit
                                </label>
                                <input type="text" name="unit" class="form-control form-control-modern" 
                                       placeholder="Cari unit..." 
                                       value="<?php echo htmlspecialchars($unitFilter); ?>" id="unitFilter">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label><br>
                                <!-- <button type="submit" class="btn btn-filter me-2">
                                    <i class="bi bi-search me-2"></i>Filter
                                </button> -->
                                <a href="logs.php" class="btn btn-reset">
                                    <i class="bi bi-x-circle me-2"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Statistics Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info" style="border-radius: 15px; border: none; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af;">
                                <i class="bi bi-info-circle fs-5 me-2"></i> 
                                <strong>Menampilkan <?php echo count($logs); ?> dari <?php echo $totalRecords; ?> total pengiriman</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Table -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="border-radius: 15px;">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php elseif (empty($logs)): ?>
                        <div class="alert alert-warning text-center" style="border-radius: 15px; background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%); border: none; color: #92400e;">
                            <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
                            <h5>Tidak ada data pengiriman ditemukan</h5>
                            <p class="mb-0">Coba ubah filter pencarian Anda</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="text-white">
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
                                            <td><strong class="text-primary">#<?php echo $log['id']; ?></strong></td>
                                            <td>
                                                <span class="badge bg-secondary fs-6 px-3 py-2 rounded-pill">
                                                    <?php echo htmlspecialchars($log['nomor_unit']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['driver_name']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                                                    <?php echo $statusLabels[$log['status_progress']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress progress-modern" style="height: 20px;">
                                                    <?php
                                                    $progressPercentage = 0;
                                                    switch($log['status_progress']) {
                                                        case 'waiting_pengawas': $progressPercentage = 20; break;
                                                        case 'waiting_driver': $progressPercentage = 35; break;
                                                        case 'driver_loading_done': $progressPercentage = 45; break;
                                                        case 'waiting_depo': $progressPercentage = 60; break;
                                                        case 'waiting_fuelman': $progressPercentage = 80; break;
                                                        case 'done': $progressPercentage = 100; break;
                                                    }
                                                    ?>
                                                    <div class="progress-bar progress-bar-modern <?php echo $progressPercentage == 100 ? 'bg-success' : ''; ?>" 
                                                         style="width: <?php echo $progressPercentage; ?>%">
                                                        <small class="text-white fw-bold"><?php echo $progressPercentage; ?>%</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($log['created_at'])); ?><br>
                                                    <strong><?php echo date('H:i', strtotime($log['created_at'])); ?></strong>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="detail.php?id=<?php echo $log['id']; ?>" 
                                                       class="btn btn-outline-primary btn-action" data-bs-toggle="tooltip" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if (hasRole('admin')): ?>
                                                        <a href="edit.php?id=<?php echo $log['id']; ?>" 
                                                           class="btn btn-outline-warning btn-action" data-bs-toggle="tooltip" title="Edit">
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
                                <ul class="pagination justify-content-center pagination-modern">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&date=<?php echo $dateFilter; ?>&unit=<?php echo $unitFilter; ?>">
                                                <i class="bi bi-chevron-left"></i> Prev
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
                                                Next <i class="bi bi-chevron-right"></i>
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
</div>

<script>
// Auto filter with 2 second delay
let filterTimeout;

document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const unitFilter = document.getElementById('unitFilter');
    
    function autoSubmit() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 500);
    }
    
    statusFilter.addEventListener('change', autoSubmit);
    dateFilter.addEventListener('change', autoSubmit);
    unitFilter.addEventListener('input', autoSubmit);
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
