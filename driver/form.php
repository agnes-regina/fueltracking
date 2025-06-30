
<?php
require_once '../config/db.php';
requireLogin();
requireRole('driver');

$pageTitle = 'Input Data Driver - Driver';

$id = $_GET['id'] ?? 0;
$form = $_GET['form'] ?? '1';
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
    
    // Check if Form 1 is already completed
    $form1_completed = !empty($log['dr_loading_start']) && !empty($log['dr_loading_end']) && !empty($log['dr_loading_location']);
    
    // Auto redirect to Form 2 if Form 1 is completed
    if ($form1_completed && $form == '1') {
        header('Location: form.php?id=' . $id . '&form=2');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    if ($form == '1' && !$form1_completed) {
        // Form 1 data
        $dr_loading_start = $_POST['dr_loading_start'] ?? '';
        $dr_loading_end = $_POST['dr_loading_end'] ?? '';
        $dr_loading_location = $_POST['dr_loading_location'] ?? '';
        $dr_waktu_keluar_pertamina = $_POST['dr_waktu_keluar_pertamina'] ?? '';
        
        // Handle file uploads for form 1
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
            
            // Update database for form 1
            $sql = "UPDATE fuel_logs SET 
                        dr_loading_start = ?, dr_loading_end = ?, dr_loading_location = ?,
                        dr_waktu_keluar_pertamina = ?, dr_created_by = ?, dr_created_at = NOW()";
            
            $params = [
                $dr_loading_start, $dr_loading_end, $dr_loading_location,
                $dr_waktu_keluar_pertamina, $_SESSION['user_id']
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
            
            $success = "FORM 1 berhasil disimpan. Silakan lanjut ke FORM 2 untuk input data unloading.";
            
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
        
    } elseif ($form == '2') {
        // Form 2 data  
        $dr_unload_start = $_POST['dr_unload_start'] ?? '';
        $dr_unload_end = $_POST['dr_unload_end'] ?? '';
        $dr_unload_location = $_POST['dr_unload_location'] ?? '';
        
        try {
            // Update database for form 2
            $sql = "UPDATE fuel_logs SET 
                        dr_unload_start = ?, dr_unload_end = ?, dr_unload_location = ?,
                        status_progress = 'waiting_depo' WHERE id = ?";
            
            $params = [$dr_unload_start, $dr_unload_end, $dr_unload_location, $id];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $success = "FORM 2 berhasil disimpan. Status berubah menjadi 'Menunggu Pengawas Depo'";
            
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-truck"></i> Input Data Driver
                    <span class="badge bg-light text-dark">#<?php echo $log['id']; ?></span>
                </h4>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <hr class="my-3">
                        <div class="d-grid gap-2">
                            <?php if ($form == '1'): ?>
                                <a href="form.php?id=<?php echo $id; ?>&form=2" class="btn btn-primary">
                                    <i class="bi bi-arrow-right"></i> Lanjut ke FORM 2
                                </a>
                            <?php endif; ?>
                            <a href="list.php" class="btn btn-outline-success">
                                <i class="bi bi-list"></i> Kembali ke List
                            </a>
                            <a href="../detail.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$error && !$success): ?>
                    <!-- Progress Indicator -->
                    <div class="progress-indicator">
                        <div class="progress-step <?php echo $form1_completed ? 'active' : ($form == '1' ? 'current' : ''); ?>">
                            <div class="progress-circle">1</div>
                            <small>Loading Data</small>
                        </div>
                        <div class="progress-step <?php echo $form == '2' ? 'current' : ''; ?>">
                            <div class="progress-circle">2</div>
                            <small>Unloading Data</small>
                        </div>
                    </div>
                    
                    <!-- Basic Info Display -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6><i class="bi bi-info-circle text-primary"></i> Informasi Pengiriman:</h6>
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="bg-light p-3 rounded">
                                                <strong><i class="bi bi-truck"></i> Unit:</strong><br>
                                                <span class="h5 text-primary"><?php echo htmlspecialchars($log['nomor_unit']); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="bg-light p-3 rounded">
                                                <strong><i class="bi bi-person"></i> Driver:</strong><br>
                                                <span class="h5 text-primary"><?php echo htmlspecialchars($log['driver_name']); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="bg-light p-3 rounded text-center">
                                                <strong><i class="bi bi-flag"></i> Status:</strong><br>
                                                <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                                                    <?php echo $statusLabels[$log['status_progress']]; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Navigation -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <?php if ($form1_completed): ?>
                                <div class="btn btn-success w-100 disabled">
                                    <i class="bi bi-check-circle"></i> FORM 1<br><small>✓ Sudah Terisi</small>
                                </div>
                            <?php else: ?>
                                <a href="form.php?id=<?php echo $id; ?>&form=1" 
                                   class="btn <?php echo $form == '1' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <i class="bi bi-upload"></i> FORM 1<br><small>Loading Data</small>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-6">
                            <?php if ($form1_completed): ?>
                                <a href="form.php?id=<?php echo $id; ?>&form=2" 
                                   class="btn <?php echo $form == '2' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <i class="bi bi-download"></i> FORM 2<br><small>Unloading Data</small>
                                </a>
                            <?php else: ?>
                                <div class="btn btn-outline-secondary w-100 disabled">
                                    <i class="bi bi-lock"></i> FORM 2<br><small>Terkunci</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($form == '1' && !$form1_completed): ?>
                        <!-- FORM 1: Loading Information -->
                        <form method="POST" enctype="multipart/form-data" id="driverForm1">
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5><i class="bi bi-clipboard-check"></i> FORM 1: Data Loading (Versi Driver)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="dr_loading_start" class="form-label">
                                                <i class="bi bi-play-circle text-success"></i> Waktu Mulai Loading *
                                            </label>
                                            <input type="datetime-local" class="form-control" id="dr_loading_start" 
                                                   name="dr_loading_start" required>
                                            <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                                    onclick="setCurrentTime('dr_loading_start')">
                                                <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="dr_loading_end" class="form-label">
                                                <i class="bi bi-stop-circle text-danger"></i> Waktu Selesai Loading *
                                            </label>
                                            <input type="datetime-local" class="form-control" id="dr_loading_end" 
                                                   name="dr_loading_end" required>
                                            <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                                    onclick="setCurrentTime('dr_loading_end')">
                                                <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="dr_loading_location" class="form-label">
                                                <i class="bi bi-geo-alt text-primary"></i> Lokasi Loading *
                                            </label>
                                            <input type="text" class="form-control" id="dr_loading_location" 
                                                   name="dr_loading_location" placeholder="Koordinat GPS" required>
                                            <button type="button" class="btn btn-outline-primary mt-2 w-100" 
                                                    onclick="autoFillLocation('dr_loading_location')">
                                                <i class="bi bi-crosshair"></i> Ambil Lokasi GPS Saya
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="dr_waktu_keluar_pertamina" class="form-label">
                                                <i class="bi bi-box-arrow-right text-warning"></i> Waktu Keluar Pertamina *
                                            </label>
                                            <input type="datetime-local" class="form-control" id="dr_waktu_keluar_pertamina" 
                                                   name="dr_waktu_keluar_pertamina" required>
                                            <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                                    onclick="setCurrentTime('dr_waktu_keluar_pertamina')">
                                                <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Segel Photos (Driver Version) -->
                                    <div class="mt-4">
                                        <h6 class="text-primary"><i class="bi bi-camera"></i> Foto Segel (Versi Driver)</h6>
                                        <div class="row g-3">
                                            <?php for($i = 1; $i <= 4; $i++): ?>
                                                <div class="col-sm-6">
                                                    <div class="card border-light">
                                                        <div class="card-body p-3">
                                                            <label for="dr_segel_photo_<?php echo $i; ?>" class="form-label">
                                                                <i class="bi bi-shield text-warning"></i> Foto Segel <?php echo $i; ?>
                                                            </label>
                                                            <input type="file" class="form-control mb-2" id="dr_segel_photo_<?php echo $i; ?>" 
                                                                   name="dr_segel_photo_<?php echo $i; ?>" accept="image/*"
                                                                   onchange="previewImage(this, 'preview_dr_segel_<?php echo $i; ?>')">
                                                            <button type="button" class="btn btn-outline-primary w-100 mb-2" 
                                                                    onclick="openCamera('dr_segel_photo_<?php echo $i; ?>', 'preview_dr_segel_<?php echo $i; ?>')">
                                                                <i class="bi bi-camera"></i> Buka Kamera
                                                            </button>
                                                            <img id="preview_dr_segel_<?php echo $i; ?>" class="photo-preview w-100" style="display: none;">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Documents (Driver Version) -->
                                    <div class="mt-4">
                                        <h6 class="text-primary"><i class="bi bi-file-earmark-text"></i> Dokumen (Versi Driver)</h6>
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <div class="card border-light">
                                                    <div class="card-body p-3">
                                                        <label for="dr_doc_do" class="form-label">
                                                            <i class="bi bi-file-text text-info"></i> Foto Delivery Order
                                                        </label>
                                                        <input type="file" class="form-control mb-2" id="dr_doc_do" 
                                                               name="dr_doc_do" accept="image/*,application/pdf"
                                                               onchange="previewImage(this, 'preview_dr_do')">
                                                        <button type="button" class="btn btn-outline-primary w-100 mb-2" 
                                                                onclick="openCamera('dr_doc_do', 'preview_dr_do')">
                                                            <i class="bi bi-camera"></i> Buka Kamera
                                                        </button>
                                                        <img id="preview_dr_do" class="photo-preview w-100" style="display: none;">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-sm-6">
                                                <div class="card border-light">
                                                    <div class="card-body p-3">
                                                        <label for="dr_doc_surat_pertamina" class="form-label">
                                                            <i class="bi bi-envelope text-success"></i> Foto Surat Pertamina
                                                        </label>
                                                        <input type="file" class="form-control mb-2" id="dr_doc_surat_pertamina" 
                                                               name="dr_doc_surat_pertamina" accept="image/*,application/pdf"
                                                               onchange="previewImage(this, 'preview_dr_surat')">
                                                        <button type="button" class="btn btn-outline-primary w-100 mb-2" 
                                                                onclick="openCamera('dr_doc_surat_pertamina', 'preview_dr_surat')">
                                                            <i class="bi bi-camera"></i> Buka Kamera
                                                        </button>
                                                        <img id="preview_dr_surat" class="photo-preview w-100" style="display: none;">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="card border-light">
                                                    <div class="card-body p-3">
                                                        <label for="dr_doc_sampel_bbm" class="form-label">
                                                            <i class="bi bi-droplet text-primary"></i> Foto Sampel BBM
                                                        </label>
                                                        <input type="file" class="form-control mb-2" id="dr_doc_sampel_bbm" 
                                                               name="dr_doc_sampel_bbm" accept="image/*,application/pdf"
                                                               onchange="previewImage(this, 'preview_dr_sampel')">
                                                        <button type="button" class="btn btn-outline-primary w-100 mb-2" 
                                                                onclick="openCamera('dr_doc_sampel_bbm', 'preview_dr_sampel')">
                                                            <i class="bi bi-camera"></i> Buka Kamera
                                                        </button>
                                                        <img id="preview_dr_sampel" class="photo-preview w-100" style="display: none;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn1">
                                    <i class="bi bi-save"></i> Simpan FORM 1
                                </button>
                                <a href="list.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke List
                                </a>
                            </div>
                        </form>
                    
                    <?php elseif ($form == '2' && $form1_completed): ?>
                        <!-- FORM 2: Unloading Information -->
                        <form method="POST" enctype="multipart/form-data" id="driverForm2">
                            <div class="card mb-4 border-success">
                                <div class="card-header bg-success text-white">
                                    <h5><i class="bi bi-download"></i> FORM 2: Data Unloading</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> 
                                        <strong>Petunjuk:</strong> Isi form ini setelah Anda sampai di lokasi tujuan dan siap melakukan unloading.
                                    </div>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="dr_unload_start" class="form-label">
                                                <i class="bi bi-play-circle text-success"></i> Waktu Mulai Unloading *
                                            </label>
                                            <input type="datetime-local" class="form-control" id="dr_unload_start" 
                                                   name="dr_unload_start" required>
                                            <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                                    onclick="setCurrentTime('dr_unload_start')">
                                                <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="dr_unload_end" class="form-label">
                                                <i class="bi bi-stop-circle text-danger"></i> Waktu Selesai Unloading *
                                            </label>
                                            <input type="datetime-local" class="form-control" id="dr_unload_end" 
                                                   name="dr_unload_end" required>
                                            <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                                    onclick="setCurrentTime('dr_unload_end')">
                                                <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                            </button>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="dr_unload_location" class="form-label">
                                                <i class="bi bi-geo-alt text-primary"></i> Lokasi Unloading *
                                            </label>
                                            <input type="text" class="form-control" id="dr_unload_location" 
                                                   name="dr_unload_location" placeholder="Koordinat GPS" required>
                                            <button type="button" class="btn btn-outline-primary mt-2 w-100" 
                                                    onclick="autoFillLocation('dr_unload_location')">
                                                <i class="bi bi-crosshair"></i> Ambil Lokasi GPS Saya
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn2">
                                    <i class="bi bi-check-circle"></i> Simpan & Lanjutkan ke Pengawas Depo
                                </button>
                                <a href="list.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-list"></i> Kembali ke List
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Camera Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ambil Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <video id="cameraVideo" autoplay playsinline style="width: 100%; max-width: 400px;"></video>
                <canvas id="cameraCanvas" style="display: none;"></canvas>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" id="captureBtn">
                        <i class="bi bi-camera"></i> Ambil Foto
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentInput = null;
let currentPreview = null;
let stream = null;

// Set current time function
function setCurrentTime(fieldId) {
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById(fieldId).value = localDateTime;
}

// Auto fill location function
function autoFillLocation(fieldId) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById(fieldId).value = lat + ',' + lng;
            alert('Lokasi berhasil diambil: ' + lat + ',' + lng);
        }, function(error) {
            alert('Gagal mengambil lokasi: ' + error.message);
        });
    } else {
        alert('Browser tidak mendukung geolocation');
    }
}

// Preview image function
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Open camera function
function openCamera(inputId, previewId) {
    currentInput = document.getElementById(inputId);
    currentPreview = document.getElementById(previewId);
    
    const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
    modal.show();
    
    // Start camera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(function(mediaStream) {
            stream = mediaStream;
            document.getElementById('cameraVideo').srcObject = stream;
        })
        .catch(function(err) {
            console.error('Error accessing camera:', err);
            alert('Tidak dapat mengakses kamera: ' + err.message);
        });
}

// Capture photo
document.getElementById('captureBtn').addEventListener('click', function() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    canvas.toBlob(function(blob) {
        const file = new File([blob], 'camera-photo.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        currentInput.files = dataTransfer.files;
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            currentPreview.src = e.target.result;
            currentPreview.style.display = 'block';
        }
        reader.readAsDataURL(file);
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();
    }, 'image/jpeg', 0.8);
});

// Stop camera when modal is closed
document.getElementById('cameraModal').addEventListener('hidden.bs.modal', function() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
});

// Form validation
document.getElementById('driverForm1')?.addEventListener('submit', function(e) {
    showLoading('submitBtn1');
});

document.getElementById('driverForm2')?.addEventListener('submit', function(e) {
    showLoading('submitBtn2');
});

function showLoading(btnId) {
    const btn = document.getElementById(btnId);
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    btn.disabled = true;
}
</script>

<?php require_once '../includes/footer.php'; ?>
