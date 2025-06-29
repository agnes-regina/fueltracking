
<?php
require_once '../config/db.php';
requireLogin();
requireRole('fuelman');

$pageTitle = 'Input Data Fuelman - Fuelman';

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
    
    if ($log['status_progress'] !== 'waiting_fuelman') {
        $error = 'Data sudah diproses atau belum siap untuk diisi';
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $fm_unload_start = $_POST['fm_unload_start'] ?? '';
    $fm_unload_end = $_POST['fm_unload_end'] ?? '';
    $fm_location = $_POST['fm_location'] ?? '';
    $fm_flowmeter = $_POST['fm_flowmeter'] ?? '';
    $fm_serial = $_POST['fm_serial'] ?? '';
    $fm_awal = $_POST['fm_awal'] ?? '';
    $fm_akhir = $_POST['fm_akhir'] ?? '';
    $fm_fuel_density = $_POST['fm_fuel_density'] ?? '';
    $fm_fuel_temp = $_POST['fm_fuel_temp'] ?? '';
    $fm_fuel_fame = $_POST['fm_fuel_fame'] ?? '';
    
    // Handle file uploads
    $photoFields = [
        'fm_segel_photo_awal_1', 'fm_segel_photo_awal_2', 'fm_segel_photo_awal_3', 'fm_segel_photo_awal_4',
        'fm_photo_akhir_1', 'fm_photo_akhir_2', 'fm_photo_akhir_3', 'fm_photo_akhir_4',
        'fm_photo_kejernihan'
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
                    fm_unload_start = ?, fm_unload_end = ?, fm_location = ?,
                    fm_flowmeter = ?, fm_serial = ?, fm_awal = ?, fm_akhir = ?,
                    fm_fuel_density = ?, fm_fuel_temp = ?, fm_fuel_fame = ?,
                    fm_created_by = ?, fm_created_at = NOW(), status_progress = 'done'";
        
        $params = [
            $fm_unload_start, $fm_unload_end, $fm_location,
            $fm_flowmeter, $fm_serial, $fm_awal, $fm_akhir,
            $fm_fuel_density, $fm_fuel_temp, $fm_fuel_fame,
            $_SESSION['user_id']
        ];
        
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
        
        $success = "Data berhasil disimpan. Proses pengiriman SELESAI!";
        
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
                    <i class="bi bi-droplet"></i> Input Data Fuelman - Proses Unloading
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
                    
                    <form method="POST" enctype="multipart/form-data" id="fuelmanForm">
                        <!-- Unloading Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-clock"></i> Informasi Unloading</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="fm_unload_start" class="form-label">
                                            <i class="bi bi-play-circle"></i> Waktu Mulai Unloading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="fm_unload_start" 
                                               name="fm_unload_start" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('fm_unload_start')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="fm_unload_end" class="form-label">
                                            <i class="bi bi-stop-circle"></i> Waktu Selesai Unloading *
                                        </label>
                                        <input type="datetime-local" class="form-control" id="fm_unload_end" 
                                               name="fm_unload_end" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1" 
                                                onclick="setCurrentTime('fm_unload_end')">
                                            <i class="bi bi-clock"></i> Waktu Sekarang
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="fm_location" class="form-label">
                                            <i class="bi bi-geo-alt"></i> Lokasi Unloading *
                                        </label>
                                        <input type="text" class="form-control" id="fm_location" 
                                               name="fm_location" placeholder="Koordinat GPS" required>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" 
                                                onclick="autoFillLocation('fm_location')">
                                            <i class="bi bi-crosshair"></i> Ambil Lokasi GPS
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Flowmeter Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-speedometer2"></i> Data Flowmeter</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fm_flowmeter" class="form-label">
                                            <i class="bi bi-gear"></i> Nama Alat Flowmeter *
                                        </label>
                                        <select class="form-select" id="fm_flowmeter" name="fm_flowmeter" required>
                                            <option value="">Pilih Flowmeter</option>
                                            <option value="Flowmeter A">Flowmeter A</option>
                                            <option value="Flowmeter B">Flowmeter B</option>
                                            <option value="Flowmeter C">Flowmeter C</option>
                                            <option value="Flowmeter D">Flowmeter D</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="fm_serial" class="form-label">
                                            <i class="bi bi-upc"></i> Serial Number *
                                        </label>
                                        <input type="text" class="form-control" id="fm_serial" 
                                               name="fm_serial" placeholder="Serial number flowmeter" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fm_awal" class="form-label">
                                            <i class="bi bi-arrow-down"></i> Angka Awal Flowmeter *
                                        </label>
                                        <input type="number" step="0.01" class="form-control" id="fm_awal" 
                                               name="fm_awal" placeholder="0.00" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="fm_akhir" class="form-label">
                                            <i class="bi bi-arrow-up"></i> Angka Akhir Flowmeter *
                                        </label>
                                        <input type="number" step="0.01" class="form-control" id="fm_akhir" 
                                               name="fm_akhir" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fuel Data -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-droplet-fill"></i> Data Bahan Bakar</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="fm_fuel_density" class="form-label">
                                            <i class="bi bi-thermometer"></i> Density Bahan Bakar *
                                        </label>
                                        <input type="number" step="0.001" class="form-control" id="fm_fuel_density" 
                                               name="fm_fuel_density" placeholder="0.000" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="fm_fuel_temp" class="form-label">
                                            <i class="bi bi-thermometer-half"></i> Temperatur (°C) *
                                        </label>
                                        <input type="number" step="0.1" class="form-control" id="fm_fuel_temp" 
                                               name="fm_fuel_temp" placeholder="0.0" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="fm_fuel_fame" class="form-label">
                                            <i class="bi bi-percent"></i> FAME (Kandungan Nabati) *
                                        </label>
                                        <input type="number" step="0.1" class="form-control" id="fm_fuel_fame" 
                                               name="fm_fuel_fame" placeholder="0.0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segel Photos (Before) -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-camera"></i> Foto Segel Sebelum Unloading</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="fm_segel_photo_awal_<?php echo $i; ?>" class="form-label">
                                                Foto Segel Awal <?php echo $i; ?>
                                            </label>
                                            <input type="file" class="form-control" id="fm_segel_photo_awal_<?php echo $i; ?>" 
                                                   name="fm_segel_photo_awal_<?php echo $i; ?>" accept="image/*"
                                                   onchange="previewImage(this, 'preview_awal_<?php echo $i; ?>')">
                                            <img id="preview_awal_<?php echo $i; ?>" class="photo-preview mt-2" style="display: none;">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Final Photos (After) -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-camera-fill"></i> Foto Sesudah Unloading</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="fm_photo_akhir_<?php echo $i; ?>" class="form-label">
                                                Foto Tanki Kosong <?php echo $i; ?>
                                            </label>
                                            <input type="file" class="form-control" id="fm_photo_akhir_<?php echo $i; ?>" 
                                                   name="fm_photo_akhir_<?php echo $i; ?>" accept="image/*"
                                                   onchange="previewImage(this, 'preview_akhir_<?php echo $i; ?>')">
                                            <img id="preview_akhir_<?php echo $i; ?>" class="photo-preview mt-2" style="display: none;">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fm_photo_kejernihan" class="form-label">
                                            <i class="bi bi-eye"></i> Foto Kejernihan Bahan Bakar *
                                        </label>
                                        <input type="file" class="form-control" id="fm_photo_kejernihan" 
                                               name="fm_photo_kejernihan" accept="image/*" required
                                               onchange="previewImage(this, 'preview_kejernihan')">
                                        <img id="preview_kejernihan" class="photo-preview mt-2" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Perhatian:</h6>
                            <ul class="mb-0">
                                <li>Pastikan semua data flowmeter sudah benar</li>
                                <li>Foto kejernihan bahan bakar wajib diupload</li>
                                <li>Setelah submit, status pengiriman akan menjadi "SELESAI"</li>
                                <li>Data tidak dapat diubah setelah submit</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Selesaikan Proses Unloading
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('fuelmanForm')?.addEventListener('submit', function(e) {
    if (!validateForm('fuelmanForm')) {
        e.preventDefault();
        return false;
    }
    
    if (!confirm('Apakah Anda yakin ingin menyelesaikan proses unloading? Data tidak dapat diubah setelah ini.')) {
        e.preventDefault();
        return false;
    }
    
    showLoading('submitBtn');
});
</script>

<?php require_once '../includes/footer.php'; ?>
