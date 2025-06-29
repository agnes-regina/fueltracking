
<?php
require_once '../config/db.php';
requireLogin();
requireRole('pengawas_depo');

$pageTitle = 'Input Data Depo - Pengawas Depo';

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
    
    if ($log['status_progress'] !== 'waiting_depo') {
        $error = 'Data sudah diproses atau belum siap untuk diisi';
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $pd_arrived_at = $_POST['pd_arrived_at'] ?? '';
    $pd_goto_msf = $_POST['pd_goto_msf'] ?? '';
    
    // Handle file uploads
    $photoFields = [
        'pd_foto_kondisi_1', 'pd_foto_kondisi_2', 'pd_foto_kondisi_3', 'pd_foto_kondisi_4',
        'pd_foto_sib', 'pd_foto_ftw', 'pd_foto_p2h'
    ];
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
        
        // Update database
        $sql = "UPDATE fuel_logs SET 
                    pd_arrived_at = ?, pd_goto_msf = ?, pd_created_by = ?, pd_created_at = NOW(),
                    status_progress = 'waiting_fuelman'";
        
        $params = [$pd_arrived_at, $pd_goto_msf, $_SESSION['user_id']];
        
        // Add uploaded files to query
        foreach ($photoFields as $field) {
            if (isset($uploadedFiles[$field])) {
                $sql .= ", $field = ?";
                $params[] = $uploadedFiles[$field];
            }
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $success = "Data berhasil disimpan. Status berubah menjadi 'Menunggu Fuelman'";
        
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
                    <i class="bi bi-building"></i> Input Data Pengawas Depo
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
                    
                    <form method="POST" enctype="multipart/form-data" id="depoForm">
                        <!-- Time Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-clock"></i> Informasi Waktu</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pd_arrived_at" class="form-label">
                                            <i class="bi bi-geo-alt"></i> Waktu Tiba Segel di Depo *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="pd_arrived_at" 
                                               name="pd_arrived_at" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('pd_arrived_at')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="pd_goto_msf" class="form-label">
                                            <i class="bi bi-arrow-right"></i> Waktu Berangkat ke Main Tank MSF *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="pd_goto_msf" 
                                               name="pd_goto_msf" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('pd_goto_msf')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Condition Photos -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-camera"></i> Foto Kondisi Segel</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="pd_foto_kondisi_<?php echo $i; ?>" class="form-label">
                                                <i class="bi bi-shield-check"></i> Foto Kondisi Segel <?php echo $i; ?>
                                            </label>
                                            <input type="file" class="form-control" id="pd_foto_kondisi_<?php echo $i; ?>" 
                                                   name="pd_foto_kondisi_<?php echo $i; ?>" accept="image/*"
                                                   onchange="previewImage(this, 'preview_kondisi_<?php echo $i; ?>')">
                                            <img id="preview_kondisi_<?php echo $i; ?>" class="photo-preview mt-2" style="display: none;">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Document Photos -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-file-earmark-text"></i> Foto Dokumen</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="pd_foto_sib" class="form-label">
                                            <i class="bi bi-file-text"></i> Foto SIB (Surat Izin Bongkar) *
                                        </label>
                                        <input type="file" class="form-control" id="pd_foto_sib" 
                                               name="pd_foto_sib" accept="image/*" required
                                               onchange="previewImage(this, 'preview_sib')">
                                        <img id="preview_sib" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="pd_foto_ftw" class="form-label">
                                            <i class="bi bi-file-spreadsheet"></i> Foto FTW (Fuel Transfer Worksheet) *
                                        </label>
                                        <input type="file" class="form-control" id="pd_foto_ftw" 
                                               name="pd_foto_ftw" accept="image/*" required
                                               onchange="previewImage(this, 'preview_ftw')">
                                        <img id="preview_ftw" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="pd_foto_p2h" class="form-label">
                                            <i class="bi bi-clipboard-check"></i> Foto P2H (Pemeriksaan 2 Harian) *
                                        </label>
                                        <input type="file" class="form-control" id="pd_foto_p2h" 
                                               name="pd_foto_p2h" accept="image/*" required
                                               onchange="previewImage(this, 'preview_p2h')">
                                        <img id="preview_p2h" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting:</h6>
                            <ul class="mb-0">
                                <li>Pastikan semua foto dokumen (SIB, FTW, P2H) sudah diupload</li>
                                <li>Foto kondisi segel opsional tapi disarankan untuk dokumentasi</li>
                                <li>Setelah submit, status akan berubah menjadi "Menunggu Fuelman"</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save"></i> Simpan & Lanjutkan ke Fuelman
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('depoForm')?.addEventListener('submit', function(e) {
    if (!validateForm('depoForm')) {
        e.preventDefault();
        return false;
    }
    showLoading('submitBtn');
});
</script>

<?php require_once '../includes/footer.php'; ?>
