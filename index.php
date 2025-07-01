<?php
$base_url = "/fueltracking/"; // Adjust this to your base URL
//$base_url = "/"; // Adjust this to your base URL
date_default_timezone_set('Asia/Jakarta');
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
            $myTasks = $pdo->query("
                SELECT COUNT(*) 
                FROM fuel_logs 
                WHERE status_progress IN ('waiting_driver', 'driver_loading_done') 
                AND pt_driver_id = " . $pdo->quote($_SESSION['user_id'])
            )->fetchColumn();
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

<style>
/* Mobile-first Beautiful Dashboard Styles */
.dashboard-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-welcome {
    background: linear-gradient(135deg,rgb(89, 0, 255) 0%,rgb(255, 0, 174) 50%,rgb(0, 255, 255) 100%);
    border-radius: 25px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 40px rgba(255, 154, 158, 0.3);
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounceIn {
    0%, 20%, 40%, 60%, 80%, 100% {
        transition-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
    }
    0% {
        opacity: 0;
        transform: scale3d(.3, .3, .3);
    }
    20% {
        transform: scale3d(1.1, 1.1, 1.1);
    }
    40% {
        transform: scale3d(.9, .9, .9);
    }
    60% {
        opacity: 1;
        transform: scale3d(1.03, 1.03, 1.03);
    }
    80% {
        transform: scale3d(.97, .97, .97);
    }
    100% {
        opacity: 1;
        transform: scale3d(1, 1, 1);
    }
}

.welcome-text {
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.beautiful-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    border: 1px solid rgba(255,255,255,0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.beautiful-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ff9a9e, #fecfef, #667eea, #764ba2);
    background-size: 300% 100%;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.beautiful-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.stat-card-modern {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 25px;
    padding: 2rem 1.5rem;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    animation: bounceIn 0.8s ease-out;
}

.stat-card-modern:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 30px 60px rgba(0,0,0,0.15);
}

.stat-icon-modern {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.stat-icon-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: inherit;
    border-radius: inherit;
    filter: blur(10px);
    opacity: 0.7;
    z-index: -1;
}

.gradient-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.gradient-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.gradient-orange { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }
.gradient-purple { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }

.stat-number-modern {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.action-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
}

.action-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea, #764ba2, #ff9a9e, #fecfef);
    z-index: -1;
    transition: opacity 0.3s ease;
    opacity: 0;
}

.action-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border-color: rgba(102, 126, 234, 0.3);
}

.action-card:hover::before {
    opacity: 0.1;
}

.action-btn-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 15px;
    padding: 1rem 1.5rem;
    color: white;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.action-btn-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.action-btn-modern:hover::before {
    left: 100%;
}

.action-btn-modern:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.action-btn-modern i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.badge-modern {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 5px 15px rgba(255, 154, 158, 0.3);
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
}

.table-modern tbody tr:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    transform: scale(1.01);
}

.floating-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(17, 153, 142, 0.3);
    z-index: 1000;
    animation: slideInRight 0.5s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .hero-welcome {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card-modern {
        padding: 1.5rem 1rem;
        margin-bottom: 1rem;
    }
    
    .stat-icon-modern {
        width: 60px;
        height: 60px;
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }
    
    .stat-number-modern {
        font-size: 2rem;
    }
    
    .action-btn-modern {
        min-height: 100px;
        padding: 0.8rem 1rem;
    }
    
    .action-btn-modern i {
        font-size: 2rem;
    }
    
    .beautiful-card {
        padding: 1rem;
        border-radius: 15px;
    }
    
    .floating-notification {
        top: 10px;
        right: 10px;
        left: 10px;
        width: auto;
    }
}

@media (max-width: 480px) {
    .hero-welcome {
        padding: 1rem;
        text-align: center;
    }
    
    .stat-card-modern {
        padding: 1rem;
    }
    
    .action-btn-modern {
        min-height: 80px;
        font-size: 0.9rem;
    }
    
    .action-btn-modern i {
        font-size: 1.5rem;
    }
}
</style>

<div class="container-fluid px-3">
    <!-- Beautiful Hero Welcome Section -->
    <div class="hero-welcome">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="welcome-text">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="bi bi-speedometer2 me-3"></i>
                        Welcome Back! 👋
                    </h1>
                    <p class="lead mb-2">Dashboard - <?php echo $roleLabels[$_SESSION['role']]; ?></p>
                    <p class="mb-0 opacity-90">
                        <i class="bi bi-calendar3 me-2"></i>
                        <?php echo date('l, d F Y'); ?>
                    </p>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex flex-column align-items-lg-end">
                    <div class="badge-modern mb-2">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                    <small class="text-white opacity-75">Last login: <?php echo date('H:i'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Beautiful Stats Cards -->
    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div class="stat-card-modern">
                <div class="stat-icon-modern gradient-blue">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="stat-number-modern"><?php echo $totalLogs; ?></div>
                <div class="text-muted fw-600">Total Pengiriman</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="stat-card-modern">
                <div class="stat-icon-modern gradient-orange">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-number-modern text-warning"><?php echo $pendingLogs; ?></div>
                <div class="text-muted fw-600">Dalam Proses</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="stat-card-modern">
                <div class="stat-icon-modern gradient-green">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-number-modern text-success"><?php echo $completedLogs; ?></div>
                <div class="text-muted fw-600">Selesai</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="stat-card-modern">
                <div class="stat-icon-modern gradient-purple">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stat-number-modern text-primary"><?php echo $myTasks; ?></div>
                <div class="text-muted fw-600">Tugas Saya</div>
                <?php if ($myTasks > 0): ?>
                    <div class="position-absolute top-0 end-0 p-2">
                        <span class="badge bg-danger rounded-pill"><?php echo $myTasks; ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Beautiful Quick Actions -->
    <div class="beautiful-card">
        <h3 class="section-title">
            <i class="bi bi-lightning-charge me-2"></i>Quick Actions
        </h3>
        <div class="row g-3">
            <?php if (hasRole('pengawas_transportir')): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="action-card">
                        <a href="<?= $base_url ?>pengawas/create.php" class="action-btn-modern w-100">
                            <i class="bi bi-plus-circle"></i>
                            <span>Buat Pengiriman</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (hasRole('pengawas_lapangan')): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="action-card position-relative">
                        <a href="<?= $base_url ?>lapangan/list.php" class="action-btn-modern w-100" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Loading Log</span>
                        </a>
                        <?php if ($myTasks > 0): ?>
                            <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">
                                <?php echo $myTasks; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (hasRole('driver')): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="action-card position-relative">
                        <a href="<?= $base_url ?>driver/list.php" class="action-btn-modern w-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="bi bi-truck"></i>
                            <span>Update Driver</span>
                        </a>
                        <?php if ($myTasks > 0): ?>
                            <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">
                                <?php echo $myTasks; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (hasRole('pengawas_depo')): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="action-card position-relative">
                        <a href="<?= $base_url ?>depo/list.php" class="action-btn-modern w-100" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                            <i class="bi bi-building"></i>
                            <span>Data Depo</span>
                        </a>
                        <?php if ($myTasks > 0): ?>
                            <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">
                                <?php echo $myTasks; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (hasRole('fuelman')): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="action-card position-relative">
                        <a href="<?= $base_url ?>fuelman/list.php" class="action-btn-modern w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-droplet"></i>
                            <span>Unloading</span>
                        </a>
                        <?php if ($myTasks > 0): ?>
                            <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">
                                <?php echo $myTasks; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (hasRole('admin') || hasRole('gl_pama')): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="action-card">
                        <a href="<?= $base_url ?>logs.php" class="action-btn-modern w-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="bi bi-graph-up"></i>
                            <span>Reports</span>
                        </a>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="action-card">
                        <a href="<?= $base_url ?>export_all.php" class="action-btn-modern w-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="bi bi-file-earmark-excel"></i>
                            <span>Export Excel</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="col-6 col-md-4 col-lg-3">
                <div class="action-card">
                    <a href="<?= $base_url ?>logs.php" class="action-btn-modern w-100" style="background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);">
                        <i class="bi bi-list-ul"></i>
                        <span>Semua Data</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Status -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="beautiful-card">
                <h3 class="section-title">
                    <i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru
                </h3>
                <?php if (empty($recentLogs)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted mb-3"></i>
                        <p class="text-muted">Belum ada data pengiriman</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?php echo htmlspecialchars($log['nomor_unit']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['driver_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $log['status_progress'] == 'done' ? 'success' : 'warning'; ?> rounded-pill">
                                                <?php echo $statusLabels[$log['status_progress']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                        <td>
                                            <a href="<?= $base_url ?>detail.php?id=<?php echo $log['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary rounded-pill">
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
        
        <div class="col-lg-4">
            <div class="beautiful-card mb-4">
                <h3 class="section-title">
                    <i class="bi bi-pie-chart me-2"></i>Status Overview
                </h3>
                <?php if (empty($statusStats)): ?>
                    <p class="text-muted text-center">Tidak ada data</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($statusStats as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3" 
                                 style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);">
                                <div>
                                    <span class="badge bg-<?php echo $stat['status_progress'] == 'done' ? 'success' : 'warning'; ?> rounded-pill">
                                        <?php echo $statusLabels[$stat['status_progress']]; ?>
                                    </span>
                                </div>
                                <strong class="text-primary"><?php echo $stat['count']; ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="beautiful-card">
                <h3 class="section-title">
                    <i class="bi bi-info-circle me-2"></i>System Info
                </h3>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Role Anda</span>
                        <strong><?php echo $roleLabels[$_SESSION['role']]; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Login</span>
                        <strong><?php echo date('d/m/Y H:i'); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Version</span>
                        <strong>2.0.0</strong>
                    </div>
                </div>
                <hr class="my-3">
                <div class="text-center">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Fuel Guardian System © 2024
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($myTasks > 0): ?>
    <div class="floating-notification" id="taskNotification">
        <i class="bi bi-bell me-2"></i>
        You have <?php echo $myTasks; ?> pending task<?php echo $myTasks > 1 ? 's' : ''; ?>!
    </div>
    
    <script>
        setTimeout(function() {
            const notification = document.getElementById('taskNotification');
            if (notification) {
                notification.style.animation = 'slideOutRight 0.5s ease-out forwards';
                setTimeout(() => notification.remove(), 500);
            }
        }, 5000);
    </script>
<?php endif; ?>

<?php 
require_once 'includes/camera.php';
require_once 'includes/maps.php';
require_once 'includes/footer.php'; 
?>