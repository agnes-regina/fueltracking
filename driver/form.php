
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
    $stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id = ? AND status_progress = 'waiting_driver' AND pt_driver_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $log = $stmt->fetch();
    
    if (!$log) {
        header('Location: list.php');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $data = [
        'dr_loading_start' => $_POST['dr_loading_start'] ?? '',
        'dr_loading_end' => $_POST['dr_loading_end'] ?? '',
        'dr_waktu_keluar_pertamina' => $_POST['dr_waktu_keluar_pertamina'] ?? '',
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
        
        $setParts[] = "dr_created_by = ?";
        $setParts[] = "dr_created_at = NOW()";
        $setParts[] = "status_progress = 'waiting_depo'";
        
        $params[] = $_SESSION['user_id'];
        $params[] = $id;
        
        $sql = "UPDATE fuel_logs SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $success = "Data berhasil disimpan! Menunggu pengawas depo untuk konfirmasi.";
        
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
                    <i class="bi bi-truck"></i> Update Data Driver - Unit <?php echo htmlspecialchars($log['nomor_unit']); ?>
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
                        <div class="col-12 mb-4">
                            <h5><i class="bi bi-clock-history"></i> Data Loading</h5>
                            <hr>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="dr_loading_start" class="form-label">
                                <i class="bi bi-play-circle"></i> Mulai Loading *
                            </label>
                            <input type="datetime-local" class="form-control" 
                                   id="dr_loading_start" name="dr_loading_start" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="dr_loading_end" class="form-label">
                                <i class="bi bi-stop-circle"></i> Selesai Loading *
                            </label>
                            <input type="datetime-local" class="form-control" 
                                   id="dr_loading_end" name="dr_loading_end" required>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="dr_waktu_keluar_pertamina" class="form-label">
                                <i class="bi bi-box-arrow-right"></i> Waktu Keluar Pertamina *
                            </label>
                            <input type="datetime-local" class="form-control" 
                                   id="dr_waktu_keluar_pertamina" name="dr_waktu_keluar_pertamina" required>
                        </div>
                        
                        <div class="col-12 mb-4">
                            <h5><i class="bi bi-arrow-down-circle"></i> Data Unloading (Opsional)</h5>
                            <hr>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="dr_unload_start" class="form-label">
                                <i class="bi bi-play-circle"></i> Mulai Unloading
                            </label>
                            <input type="datetime-local" class="form-control" 
                                   id="dr_unload_start" name="dr_unload_start">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="dr_unload_end" class="form-label">
                                <i class="bi bi-stop-circle"></i> Selesai Unloading
                            </label>
                            <input type="datetime-local" class="form-control" 
                                   id="dr_unload_end" name="dr_unload_end">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="dr_unload_location" class="form-label">
                                <i class="bi bi-geo-alt"></i> Lokasi Unloading (GPS)
                            </label>
                            <input type="text" class="form-control" 
                                   id="dr_unload_location" name="dr_unload_location" 
                                   placeholder="Contoh: -6.200000,106.816666">
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="getLocation()">
                                <i class="bi bi-geo-alt"></i> Ambil Lokasi GPS
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Data Driver
                        </button>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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
    
    document.getElementById('dr_loading_start').value = dateString;
});
</script>

<?php require_once '../includes/footer.php'; ?>
