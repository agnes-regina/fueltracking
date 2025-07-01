
<?php
require_once '../config/db.php';
requireLogin();
requireRole('driver');

$pageTitle = 'Form Driver - Update Pengiriman';

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

if (!$id) {
    header('Location: list.php');
    exit();
}

// Get log data
try {
    $stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id = ? AND status_progress IN ('waiting_driver', 'driver_loading_done') AND pt_driver_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $log = $stmt->fetch();
    
    if (!$log) {
        header('Location: list.php');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Determine current phase
$isLoadingPhase = empty($log['dr_loading_start']);
$isUnloadingPhase = !empty($log['dr_loading_start']) && empty($log['dr_unload_start']);
$isCompleted = !empty($log['dr_unload_start']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $phase = $_POST['phase'] ?? '';
    
    if ($phase === 'loading' && $isLoadingPhase) {
        // Loading phase
        $data = [
            'dr_loading_start' => $_POST['dr_loading_start'] ?? '',
            'dr_loading_end' => $_POST['dr_loading_end'] ?? '',
            'dr_waktu_keluar_pertamina' => $_POST['dr_waktu_keluar_pertamina'] ?? '',
            'dr_loading_location' => $_POST['dr_loading_location'] ?? '',
        ];
        
        try {
            $setParts = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($value !== '') {
                    $setParts[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            $setParts[] = "dr_created_by = ?";
            $setParts[] = "dr_created_at = NOW()";
            $setParts[] = "status_progress = 'driver_loading_done'";
            
            $params[] = $_SESSION['user_id'];
            $params[] = $id;
            
            $sql = "UPDATE fuel_logs SET " . implode(', ', $setParts) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $success = "Data loading berhasil disimpan! Silakan isi data unloading ketika sudah tiba di tujuan.";
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id = ?");
            $stmt->execute([$id]);
            $log = $stmt->fetch();
            $isLoadingPhase = false;
            $isUnloadingPhase = true;
            
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
        
    } elseif ($phase === 'unloading' && $isUnloadingPhase) {
        // Unloading phase
        $data = [
            'dr_unload_start' => $_POST['dr_unload_start'] ?? '',
            'dr_unload_end' => $_POST['dr_unload_end'] ?? '',
            'dr_unload_location' => $_POST['dr_unload_location'] ?? '',
        ];
        
        try {
            $setParts = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($value !== '') {
                    $setParts[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            $setParts[] = "status_progress = 'waiting_depo'";
            $params[] = $id;
            
            $sql = "UPDATE fuel_logs SET " . implode(', ', $setParts) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $success = "Data unloading berhasil disimpan! Menunggu pengawas depo untuk konfirmasi.";
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id = ?");
            $stmt->execute([$id]);
            $log = $stmt->fetch();
            $isUnloadingPhase = false;
            $isCompleted = true;
            
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<style>
/* Beautiful Mobile-First Form Styles */
.form-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-section {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
    border-radius: 25px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 20px 40px rgba(255, 154, 158, 0.3);
    animation: slideInUp 0.8s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 25px;
    padding: 2rem;
    box-shadow: 0 25px 50px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out;
}

.form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea, #764ba2, #ff9a9e, #fecfef);
    background-size: 300% 100%;
    animation: gradientMove 3s ease infinite;
}

@keyframes gradientMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
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

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    text-align: center;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.form-floating-modern {
    position: relative;
    margin-bottom: 2rem;
}

.form-floating-modern .form-control {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e2e8f0;
    border-radius: 15px;
    padding: 1rem 1.5rem;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.form-floating-modern .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1), 0 8px 25px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.form-floating-modern label {
    position: absolute;
    top: 1rem;
    left: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 600;
    transition: all 0.3s ease;
    pointer-events: none;
}

.form-floating-modern .form-control:focus + label,
.form-floating-modern .form-control:not(:placeholder-shown) + label {
    top: -0.5rem;
    left: 1rem;
    font-size: 0.85rem;
    background: white;
    padding: 0 0.5rem;
    color: #667eea !important;
    -webkit-text-fill-color: #667eea !important;
}

.btn-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50px;
    padding: 1rem 2rem;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-modern:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-modern:hover::before {
    left: 100%;
}

.btn-secondary-modern {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-secondary-modern:hover {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    color: #475569;
    border-color: #cbd5e1;
}

.camera-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.camera-btn:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    color: white;
}

.alert-modern {
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.alert-success-modern {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-left: 5px solid #10b981;
}

.alert-danger-modern {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border-left: 5px solid #ef4444;
}

.readonly-card {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border: 2px solid #cbd5e1;
    opacity: 0.8;
}

.readonly-card .form-control {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #64748b;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-card {
        padding: 1.5rem;
        border-radius: 20px;
    }
    
    .btn-modern {
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .hero-section {
        padding: 1rem;
        text-align: center;
    }
    
    .form-card {
        padding: 1rem;
        border-radius: 15px;
    }
    
    .btn-modern {
        width: 100%;
        padding: 1rem;
    }
}
</style>

<div class="container-fluid px-3">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="text-center text-white">
            <h1 class="display-5 fw-bold mb-3">
                <i class="bi bi-truck fs-1 me-3"></i>
                Driver Form
            </h1>
            <p class="lead mb-2">Update Data Pengiriman</p>
            <p class="mb-0 opacity-90">
                Unit: <strong class="badge bg-light text-dark fs-6 px-3 py-2 rounded-pill"><?php echo htmlspecialchars($log['nomor_unit']); ?></strong>
            </p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success-modern">
            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
            <strong>Berhasil!</strong> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger-modern">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
            <strong>Error!</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Loading Phase -->
    <?php if ($isLoadingPhase): ?>
        <div class="form-card">
            <h3 class="section-title">
                <i class="bi bi-play-circle me-2"></i>Phase 1: Data Loading
            </h3>
            
            <form method="POST">
                <input type="hidden" name="phase" value="loading">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating-modern">
                            <input type="datetime-local" class="form-control" 
                                   id="dr_loading_start" name="dr_loading_start" 
                                   placeholder=" " required>
                            <label for="dr_loading_start">
                                <i class="bi bi-play-circle me-2"></i>Mulai Loading
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating-modern">
                            <input type="datetime-local" class="form-control" 
                                   id="dr_loading_end" name="dr_loading_end" 
                                   placeholder=" " required>
                            <label for="dr_loading_end">
                                <i class="bi bi-stop-circle me-2"></i>Selesai Loading
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-floating-modern">
                            <input type="datetime-local" class="form-control" 
                                   id="dr_waktu_keluar_pertamina" name="dr_waktu_keluar_pertamina" 
                                   placeholder=" " required>
                            <label for="dr_waktu_keluar_pertamina">
                                <i class="bi bi-box-arrow-right me-2"></i>Waktu Keluar Pertamina
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating-modern">
                            <input type="text" class="form-control" 
                                   id="dr_loading_location" name="dr_loading_location" 
                                   placeholder=" ">
                            <label for="dr_loading_location">
                                <i class="bi bi-geo-alt me-2"></i>Lokasi Loading (GPS)
                            </label>
                        </div>
                        <button type="button" class="btn camera-btn w-100 mt-2" onclick="getLocation0()">
                            <i class="bi bi-geo-alt me-2"></i>Ambil Lokasi GPS
                        </button>
                    </div>
                </div>
                <br>
                <div class="text-center">
                    <button type="submit" class="btn btn-modern">
                        <i class="bi bi-save me-2"></i>Simpan Data Loading
                    </button>
                    <a href="list.php" class="btn btn-secondary-modern ms-3">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Show completed loading data -->
    <?php if (!$isLoadingPhase): ?>
        <div class="form-card readonly-card">
            <h3 class="section-title">
                <i class="bi bi-check-circle me-2"></i>Data Loading (Completed)
            </h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating-modern">
                        <input type="datetime-local" class="form-control" 
                               value="<?php echo $log['dr_loading_start']; ?>" readonly>
                        <label>Mulai Loading</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating-modern">
                        <input type="datetime-local" class="form-control" 
                               value="<?php echo $log['dr_loading_end']; ?>" readonly>
                        <label>Selesai Loading</label>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating-modern">
                        <input type="datetime-local" class="form-control" 
                               value="<?php echo $log['dr_waktu_keluar_pertamina']; ?>" readonly>
                        <label>Waktu Keluar Pertamina</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-floating-modern">
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($log['dr_loading_location']); ?>" readonly>
                        <label>Lokasi Loading</label>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Unloading Phase -->
    <?php if ($isUnloadingPhase): ?>
        <div class="form-card">
            <h3 class="section-title">
                <i class="bi bi-arrow-down-circle me-2"></i>Phase 2: Data Unloading
            </h3>
            
            <form method="POST">
                <input type="hidden" name="phase" value="unloading">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating-modern">
                            <input type="datetime-local" class="form-control" 
                                   id="dr_unload_start" name="dr_unload_start" 
                                   placeholder=" " required>
                            <label for="dr_unload_start">
                                <i class="bi bi-play-circle me-2"></i>Mulai Unloading
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating-modern">
                            <input type="datetime-local" class="form-control" 
                                   id="dr_unload_end" name="dr_unload_end" 
                                   placeholder=" ">
                            <label for="dr_unload_end">
                                <i class="bi bi-stop-circle me-2"></i>Selesai Unloading
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-floating-modern">
                            <input type="text" class="form-control" 
                                   id="dr_unload_location" name="dr_unload_location" 
                                   placeholder=" ">
                            <label for="dr_unload_location">
                                <i class="bi bi-geo-alt me-2"></i>Lokasi Unloading (GPS)
                            </label>
                        </div>
                        <button type="button" class="btn camera-btn w-100 mt-2" onclick="getLocation()">
                            <i class="bi bi-geo-alt me-2"></i>Ambil Lokasi GPS
                        </button>
                    </div>
                </div>
                <br>
                <div class="text-center">
                    <button type="submit" class="btn btn-modern">
                        <i class="bi bi-save me-2"></i>Simpan Data Unloading
                    </button>
                    <a href="list.php" class="btn btn-secondary-modern ms-3">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Show completed unloading data -->
    <?php if ($isCompleted): ?>
        <div class="form-card readonly-card">
            <h3 class="section-title">
                <i class="bi bi-check-circle me-2"></i>Data Unloading (Completed)
            </h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating-modern">
                        <input type="datetime-local" class="form-control" 
                               value="<?php echo $log['dr_unload_start']; ?>" readonly>
                        <label>Mulai Unloading</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating-modern">
                        <input type="datetime-local" class="form-control" 
                               value="<?php echo $log['dr_unload_end']; ?>" readonly>
                        <label>Selesai Unloading</label>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating-modern">
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($log['dr_unload_location']); ?>" readonly>
                        <label>Lokasi Unloading</label>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <div class="alert alert-success-modern">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Proses Selesai!</strong> Menunggu konfirmasi dari pengawas depo.
                </div>
                <a href="list.php" class="btn btn-secondary-modern">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke List
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function getLocation0() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('dr_loading_location').value = lat + ',' + lng;
        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('dr_unload_location').value = lat + ',' + lng;
        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Set current time for datetime inputs
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const dateString = now.getFullYear() + '-' + 
                      String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(now.getDate()).padStart(2, '0') + 'T' + 
                      String(now.getHours()).padStart(2, '0') + ':' + 
                      String(now.getMinutes()).padStart(2, '0');
    
    // Set default time for current active input
    <?php if ($isLoadingPhase): ?>
        document.getElementById('dr_loading_start').value = dateString;
    <?php elseif ($isUnloadingPhase): ?>
        document.getElementById('dr_unload_start').value = dateString;
    <?php endif; ?>
});
</script>

<?php require_once '../includes/footer.php'; ?>
