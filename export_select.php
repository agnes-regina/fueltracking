<?php
require_once 'config/db.php';
requireRole(['admin', 'gl_pama']);

$pageTitle = 'Pilih Data Export - Fuel Transport Tracking';

// Pagination & Filter (mirip logs.php)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$unitFilter = $_GET['unit'] ?? '';

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

// Get total count
$countSql = "SELECT COUNT(*) FROM fuel_logs $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Get data
$sql = "SELECT id, nomor_unit, driver_name, status_progress, created_at FROM fuel_logs $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Status label
$statusLabels = [
    'waiting_pengawas' => 'Menunggu Pengawas',
    'waiting_driver' => 'Menunggu Driver',
    'driver_loading_done' => 'Driver Loading Done',
    'waiting_depo' => 'Menunggu Depo',
    'waiting_fuelman' => 'Menunggu Fuelman',
    'done' => 'Selesai'
];

require_once 'includes/header.php';
?>

<style>
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
.text-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>

<div class="container py-4">
<div class="d-flex justify-content-center align-items-center mb-4">
    <h2 class="fw-bold m-0 px-4 py-3"
        style="font-size:2rem; letter-spacing:1px; border: 3px solid #fff; border-radius: 2rem; box-shadow:0 4px 24px rgba(102,126,234,0.08); background:rgba(102,126,234,0.85); color:#fff;">
        <i class="bi bi-file-earmark-excel me-2"></i>
        Pilih Data Pengiriman untuk Export Excel
    </h2>
</div>
    <div class="card table-modern">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="bi bi-file-earmark-excel"></i> Pilih Data Export
        </div>
        <div class="card-body">
            <!-- Filter -->
            <form method="GET" class="row g-3 mb-4" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel me-2"></i>Status
                    </label>
                    <select name="status" class="form-select" id="statusFilter">
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
                    <input type="date" name="date" class="form-control"
                           value="<?php echo htmlspecialchars($dateFilter); ?>" id="dateFilter">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-truck me-2"></i>Nomor Unit
                    </label>
                    <input type="text" name="unit" class="form-control"
                           placeholder="Cari unit..." value="<?php echo htmlspecialchars($unitFilter); ?>" id="unitFilter">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="export_select.php" class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle me-2"></i>Reset
                    </a>
                </div>
            </form>

            <!-- Table -->
            <form action="export_by_id.php" method="post" target="_blank">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>ID</th>
                                <th>Nomor Unit</th>
                                <th>Nama Driver</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-exclamation-circle fs-2 mb-2 d-block"></i>
                                        <div class="fw-bold">Tidak ada data sesuai filter yang diterapkan.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="ids[]" value="<?= $log['id'] ?>">
                                        </td>
                                        <td><strong class="text-primary">#<?= $log['id'] ?></strong></td>
                                        <td>
                                            <span class="badge bg-secondary fs-6 px-3 py-2 rounded-pill">
                                                <?= htmlspecialchars($log['nomor_unit']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log['driver_name']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $log['status_progress'] ?>">
                                                <?= $statusLabels[$log['status_progress']] ?? $log['status_progress'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y', strtotime($log['created_at'])) ?><br>
                                                <strong><?= date('H:i', strtotime($log['created_at'])) ?></strong>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success mt-3">
                    <i class="bi bi-file-earmark-excel"></i> Export Pilihan
                </button>
            </form>

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
        </div>
    </div>
</div>

<script>
document.getElementById('checkAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    for (const cb of checkboxes) {
        cb.checked = this.checked;
    }
});

// Auto filter with delay
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
});
</script>

<?php require_once 'includes/footer.php'; ?>