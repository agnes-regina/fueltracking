
<?php
require_once '../config/db.php';
requireLogin();
requireRole('driver');

$pageTitle = 'Input Data Driver - Driver';

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

if (!$id) {
    header('Location: list.php');
    exit();
}

// Get log data
try {
    $stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id = ?");
    $stmt->execute([$id]);
    $log = $stmt->fetch();
    
    if (!$log) {
        header('Location: list.php');
        exit();
    }
    
    if ($log['status_progress'] !== 'waiting_driver') {
        $error = 'Data sudah diproses atau belum siap untuk diisi';
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    // Form 1 data
    $dr_loading_start = $_POST['dr_loading_start'] ?? '';
    $dr_loading_end = $_POST['dr_loading_end'] ?? '';
    $dr_loading_location = $_POST['dr_loading_location'] ?? '';
    $dr_waktu_keluar_pertamina = $_POST['dr_waktu_keluar_pertamina'] ?? '';
    
    // Form 2 data  
    $dr_unload_start = $_POST['dr_unload_start'] ?? '';
    $dr_unload_end = $_POST['dr_unload_end'] ?? '';
    $dr_unload_location = $_POST['dr_unload_location'] ?? '';
    
    // Handle file uploads
    $photoFields = ['dr_segel_photo_1', 'dr_segel_photo_2', 'dr_segel_photo_3', 'dr_segel_photo_4'];
    $docFields = ['dr_doc_do', 'dr_doc_surat_pertamina', 'dr_doc_sampel_bbm'];
    $uploadedFiles = [];
    
    try {
        // Upload photos and documents
        foreach (array_merge($photoFields, $docFields) as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = in_array($field, $docFields) ? ['jpg', 'jpeg', 'png', 'pdf'] : ['jpg', 'jpeg', 'png'];
                $uploadPath = uploadFile($_FILES[$field], $allowedTypes);
                if ($uploadPath) {
                    $uploadedFiles[$field] = $uploadPath;
                }
            }
        }
        
        // Update database
        $sql = "UPDATE fuel_logs SET 
                    dr_loading_start = ?, dr_loading_end = ?, dr_loading_location = ?,
                    dr_waktu_keluar_pertamina = ?, dr_unload_start = ?, dr_unload_end = ?, 
                    dr_unload_location = ?, dr_created_by = ?, dr_created_at = NOW(),
                    status_progress = 'waiting_depo'";
        
        $params = [
            $dr_loading_start, $dr_loading_end, $dr_loading_location,
            $dr_waktu_keluar_pertamina, $dr_unload_start, $dr_unload_end,
            $dr_unload_location, $_SESSION['user_id']
        ];
        
        // Add uploaded files to query
        foreach (array_merge($photoFields, $docFields) as $field) {
            if (isset($uploadedFiles[$field])) {
                $sql .= ", $field = ?";
                $params[] = $uploadedFiles[$field];
            }
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $success = "Data berhasil disimpan. Status berubah menjadi 'Menunggu Pengawas Depo'";
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-truck"></i> Input Data Driver
                    <span class="badge bg-primary">#<?php echo $log['id']; ?></span>
                </h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <hr>
                        <a href="list.php" class="btn btn-outline-success">Kembali ke List</a>
                        <a href="../detail.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">Lihat Detail</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$error && !$success): ?>
                    <!-- Basic Info Display -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Informasi Pengiriman:</h6>
                                    <p><strong>Unit:</strong> <?php echo htmlspecialchars($log['nomor_unit']); ?></p>
                                    <p><strong>Driver:</strong> <?php echo htmlspecialchars($log['driver_name']); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                                            <?php echo $statusLabels[$log['status_progress']]; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" id="driverForm">
                        <!-- FORM 1: Loading Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-clipboard-check"></i> FORM 1: Data Loading (Versi Driver)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="dr_loading_start" class="form-label">
                                            <i class="bi bi-play-circle"></i> Waktu Mulai Loading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="dr_loading_start" 
                                               name="dr_loading_start" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('dr_loading_start')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="dr_loading_end" class="form-label">
                                            <i class="bi bi-stop-circle"></i> Waktu Selesai Loading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="dr_loading_end" 
                                               name="dr_loading_end" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('dr_loading_end')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="dr_loading_location" class="form-label">
                                            <i class="bi bi-geo-alt"></i> Lokasi Loading *
                                        </label>
                                        <input type="text" class="form-control" id="dr_loading_location" 
                                               name="dr_loading_location" placeholder="Koordinat GPS" required>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" 
                                                onclick="autoFillLocation('dr_loading_location')">
                                            <i class="bi bi-crosshair"></i> Ambil Lokasi GPS
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="dr_waktu_keluar_pertamina" class="form-label">
                                            <i class="bi bi-box-arrow-right"></i> Waktu Keluar Pertamina *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="dr_waktu_keluar_pertamina" 
                                               name="dr_waktu_keluar_pertamina" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('dr_waktu_keluar_pertamina')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Segel Photos (Driver Version) -->
                                <h6 class="mt-4"><i class="bi bi-camera"></i> Foto Segel (Versi Driver)</h6>
                                <div class="row">
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="dr_segel_photo_<?php echo $i; ?>" class="form-label">
                                                Foto Segel <?php echo $i; ?>
                                            </label>
                                            <input type="file" class="form-control" id="dr_segel_photo_<?php echo $i; ?>" 
                                                   name="dr_segel_photo_<?php echo $i; ?>" accept="image/*"
                                                   onchange="previewImage(this, 'preview_dr_segel_<?php echo $i; ?>')">
                                            <img id="preview_dr_segel_<?php echo $i; ?>" class="photo-preview mt-2" style="display: none;">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <!-- Documents (Driver Version) -->
                                <h6 class="mt-4"><i class="bi bi-file-earmark-text"></i> Dokumen (Versi Driver)</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="dr_doc_do" class="form-label">
                                            <i class="bi bi-file-text"></i> Foto Delivery Order
                                        </label>
                                        <input type="file" class="form-control" id="dr_doc_do" 
                                               name="dr_doc_do" accept="image/*,application/pdf"
                                               onchange="previewImage(this, 'preview_dr_do')">
                                        <img id="preview_dr_do" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="dr_doc_surat_pertamina" class="form-label">
                                            <i class="bi bi-envelope"></i> Foto Surat Pertamina
                                        </label>
                                        <input type="file" class="form-control" id="dr_doc_surat_pertamina" 
                                               name="dr_doc_surat_pertamina" accept="image/*,application/pdf"
                                               onchange="previewImage(this, 'preview_dr_surat')">
                                        <img id="preview_dr_surat" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="dr_doc_sampel_bbm" class="form-label">
                                            <i class="bi bi-droplet"></i> Foto Sampel BBM
                                        </label>
                                        <input type="file" class="form-control" id="dr_doc_sampel_bbm" 
                                               name="dr_doc_sampel_bbm" accept="image/*,application/pdf"
                                               onchange="previewImage(this, 'preview_dr_sampel')">
                                        <img id="preview_dr_sampel" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- FORM 2: Unloading Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-download"></i> FORM 2: Data Unloading</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="dr_unload_start" class="form-label">
                                            <i class="bi bi-play-circle"></i> Waktu Mulai Unloading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="dr_unload_start" 
                                               name="dr_unload_start" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('dr_unload_start')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="dr_unload_end" class="form-label">
                                            <i class="bi bi-stop-circle"></i> Waktu Selesai Unloading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="dr_unload_end" 
                                               name="dr_unload_end" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('dr_unload_end')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="dr_unload_location" class="form-label">
                                            <i class="bi bi-geo-alt"></i> Lokasi Unloading *
                                        </label>
                                        <input type="text" class="form-control" id="dr_unload_location" 
                                               name="dr_unload_location" placeholder="Koordinat GPS" required>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" 
                                                onclick="autoFillLocation('dr_unload_location')">
                                            <i class="bi bi-crosshair"></i> Ambil Lokasi GPS
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save"></i> Simpan & Lanjutkan ke Pengawas Depo
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('driverForm')?.addEventListener('submit', function(e) {
    if (!validateForm('driverForm')) {
        e.preventDefault();
        return false;
    }
    showLoading('submitBtn');
});
</script>

<?php require_once '../includes/footer.php'; ?>
