<?php
require_once '../config/db.php';
requireLogin();
requireRole('pengawas_lapangan');

$pageTitle = 'Input Data Lapangan - Pengawas Lapangan';

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
    
    if ($log['status_progress'] !== 'waiting_pengawas') {
        $error = 'Data sudah diproses atau belum siap untuk diisi';
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $pl_loading_start = $_POST['pl_loading_start'] ?? '';
    $pl_loading_end = $_POST['pl_loading_end'] ?? '';
    $pl_loading_location = $_POST['pl_loading_location'] ?? '';
    $pl_waktu_keluar_pertamina = $_POST['pl_waktu_keluar_pertamina'] ?? '';
    
    $pl_segel_1 = $_POST['pl_segel_1'] ?? '';
    $pl_segel_2 = $_POST['pl_segel_2'] ?? '';
    $pl_segel_3 = $_POST['pl_segel_3'] ?? '';
    $pl_segel_4 = $_POST['pl_segel_4'] ?? '';
    
    // Handle file uploads
    $photoFields = ['pl_segel_photo_1', 'pl_segel_photo_2', 'pl_segel_photo_3', 'pl_segel_photo_4'];
    $docFields = ['pl_doc_sampel', 'pl_doc_do', 'pl_doc_suratjalan'];
    $uploadedFiles = [];
    
    try {
        // Upload photos
        foreach ($photoFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $uploadPath = uploadFile($_FILES[$field], ['jpg', 'jpeg', 'png']);
                if ($uploadPath) {
                    $uploadedFiles[$field] = $uploadPath;
                }
            }
        }
        
        // Upload documents
        foreach ($docFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $uploadPath = uploadFile($_FILES[$field], ['jpg', 'jpeg', 'png', 'pdf']);
                if ($uploadPath) {
                    $uploadedFiles[$field] = $uploadPath;
                }
            }
        }
        
        // Update database
        $sql = "UPDATE fuel_logs SET 
                    pl_loading_start = ?, pl_loading_end = ?, pl_loading_location = ?,
                    pl_segel_1 = ?, pl_segel_2 = ?, pl_segel_3 = ?, pl_segel_4 = ?,
                    pl_waktu_keluar_pertamina = ?, pl_created_by = ?, pl_created_at = NOW(),
                    status_progress = 'waiting_driver'";
        
        $params = [
            $pl_loading_start, $pl_loading_end, $pl_loading_location,
            $pl_segel_1, $pl_segel_2, $pl_segel_3, $pl_segel_4,
            $pl_waktu_keluar_pertamina, $_SESSION['user_id']
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
        
        $success = "Data berhasil disimpan. Status berubah menjadi 'Menunggu Driver'";
        
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
                    <i class="bi bi-clipboard-check"></i> Input Data Pengawas Lapangan
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
                    
                    <form method="POST" enctype="multipart/form-data" id="lapanganForm">
                        <!-- Loading Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-clock"></i> Informasi Loading</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pl_loading_start" class="form-label">
                                            <i class="bi bi-play-circle"></i> Waktu Mulai Loading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="pl_loading_start" 
                                               name="pl_loading_start" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('pl_loading_start')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="pl_loading_end" class="form-label">
                                            <i class="bi bi-stop-circle"></i> Waktu Selesai Loading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="pl_loading_end" 
                                               name="pl_loading_end" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('pl_loading_end')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pl_loading_location" class="form-label">
                                            <i class="bi bi-geo-alt"></i> Lokasi Loading *
                                        </label>
                                        <input type="text" class="form-control" id="pl_loading_location" 
                                               name="pl_loading_location" placeholder="Koordinat GPS" required>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" 
                                                onclick="autoFillLocation('pl_loading_location')">
                                            <i class="bi bi-crosshair"></i> Ambil Lokasi GPS
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="pl_waktu_keluar_pertamina" class="form-label">
                                            <i class="bi bi-box-arrow-right"></i> Waktu Keluar Pertamina *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="pl_waktu_keluar_pertamina" 
                                               name="pl_waktu_keluar_pertamina" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('pl_waktu_keluar_pertamina')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segel Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-shield-check"></i> Data Segel</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
<?php for($i = 1; $i <= 4; $i++): ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="pl_segel_<?php echo $i; ?>" class="form-label">
                                                <i class="bi bi-tag"></i> Nomor Segel <?php echo $i; ?>
                                            </label>
                                            <input type="text" class="form-control" id="pl_segel_<?php echo $i; ?>" 
                                                   name="pl_segel_<?php echo $i; ?>" placeholder="Nomor segel <?php echo $i; ?>">
                                        </div>
<?php endfor; ?>
                                </div>
                                
                                <h6 class="mt-4"><i class="bi bi-camera"></i> Foto Segel</h6>
                                <div class="row">
<?php for($i = 1; $i <= 4; $i++): ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="pl_segel_photo_<?php echo $i; ?>" class="form-label">
                                                Foto Segel <?php echo $i; ?>
                                            </label>
                                            <input type="file" class="form-control" id="pl_segel_photo_<?php echo $i; ?>" 
                                                   name="pl_segel_photo_<?php echo $i; ?>" accept="image/*"
                                                   onchange="previewImage(this, 'preview_segel_<?php echo $i; ?>')">
                                            <img id="preview_segel_<?php echo $i; ?>" class="photo-preview mt-2" style="display: none;">
                                        </div>
<?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Documents -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-file-earmark-text"></i> Dokumen Pengangkutan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="pl_doc_sampel" class="form-label">
                                            <i class="bi bi-droplet"></i> Foto Sampel BBM
                                        </label>
                                        <input type="file" class="form-control" id="pl_doc_sampel" 
                                               name="pl_doc_sampel" accept="image/*,application/pdf"
                                               onchange="previewImage(this, 'preview_sampel')">
                                        <img id="preview_sampel" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="pl_doc_do" class="form-label">
                                            <i class="bi bi-file-text"></i> Foto Delivery Order
                                        </label>
                                        <input type="file" class="form-control" id="pl_doc_do" 
                                               name="pl_doc_do" accept="image/*,application/pdf"
                                               onchange="previewImage(this, 'preview_do')">
                                        <img id="preview_do" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="pl_doc_suratjalan" class="form-label">
                                            <i class="bi bi-envelope"></i> Foto Surat Jalan
                                        </label>
                                        <input type="file" class="form-control" id="pl_doc_suratjalan" 
                                               name="pl_doc_suratjalan" accept="image/*,application/pdf"
                                               onchange="previewImage(this, 'preview_suratjalan')">
                                        <img id="preview_suratjalan" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save"></i> Simpan & Lanjutkan ke Driver
                            </button>
                        </div>
                    </form>
<?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('lapanganForm')?.addEventListener('submit', function(e) {
    if (!validateForm('lapanganForm')) {
        e.preventDefault();
        return false;
    }
    showLoading('submitBtn');
});
</script>

<?php require_once '../includes/footer.php'; ?>
