<?php
require_once '../config/db.php';
requireLogin();
requireRole('pengawas_depo');

$pageTitle = 'Input Data Depo - Pengawas Depo';

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';
$duration_hours = 0;
$requires_reason = false;

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
    
    // Calculate duration if dr_waktu_keluar_pertamina exists
    if ($log['dr_waktu_keluar_pertamina']) {
        $keluar_time = new DateTime($log['dr_waktu_keluar_pertamina']);
        $now = new DateTime();
        $interval = $keluar_time->diff($now);
        $duration_hours = $interval->h + ($interval->days * 24);
        
        if ($duration_hours > 7) {
            $requires_reason = true;
        }
    }
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $pd_arrived_at = $_POST['pd_arrived_at'] ?? '';
    $pd_goto_msf = $_POST['pd_goto_msf'] ?? '';
    $pd_alasan_lebih_7jam = $_POST['pd_alasan_lebih_7jam'] ?? '';
    
    // Validate 7-hour rule
    if ($log['dr_waktu_keluar_pertamina'] && $pd_arrived_at) {
        $keluar_time = new DateTime($log['dr_waktu_keluar_pertamina']);
        $tiba_time = new DateTime($pd_arrived_at);
        $interval = $keluar_time->diff($tiba_time);
        $actual_duration = $interval->h + ($interval->days * 24) + ($interval->i / 60);
        
        if ($actual_duration > 7 && empty($pd_alasan_lebih_7jam)) {
            $error = 'Durasi perjalanan melebihi 7 jam (' . number_format($actual_duration, 1) . ' jam). Alasan wajib diisi!';
        }
    }
    
    if (!$error) {
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
            
            // Add reason if provided
            if (!empty($pd_alasan_lebih_7jam)) {
                $sql .= ", pd_alasan_lebih_7jam = ?";
                $params[] = $pd_alasan_lebih_7jam;
            }
            
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
}

require_once '../includes/header.php';
?>

<style>
/* Copy CSS dari lapangan/form.php, bisa disesuaikan jika perlu */
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
.photo-upload-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 2px dashed #667eea;
    transition: all 0.3s ease;
}
.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}
.photo-item {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
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
    margin-top: 0.5rem;
}
.camera-btn:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    color: white;
}
.photo-preview {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 10px;
    margin-top: 1rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-building"></i> Input Data Pengawas Depo
                    <span class="badge bg-light text-dark">#<?php echo $log['id']; ?></span>
                </h4>
            </div>
            <div class="card-body">
<?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <hr class="my-3">
                        <div class="d-grid gap-2">
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
                    <!-- Basic Info Display -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="bi bi-info-circle"></i> Informasi Pengiriman:</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Unit:</strong><br><?php echo htmlspecialchars($log['nomor_unit']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Driver:</strong><br><?php echo htmlspecialchars($log['driver_name']); ?></p>
                                        </div>
                                    </div>
                                    <p><strong>Status:</strong><br>
                                        <span class="status-badge status-<?php echo $log['status_progress']; ?>">
<?php echo $statusLabels[$log['status_progress']]; ?>
                                        </span>
                                    </p>
                                    
<?php if ($log['dr_waktu_keluar_pertamina']): ?>
                                        <div class="alert <?php echo $duration_hours > 7 ? 'alert-warning' : 'alert-info'; ?> mt-3">
                                            <i class="bi bi-clock"></i> 
                                            <strong>Waktu Keluar Pertamina:</strong> <?php echo date('d/m/Y H:i', strtotime($log['dr_waktu_keluar_pertamina'])); ?><br>
                                            <strong>Durasi Perjalanan:</strong> ~<?php echo $duration_hours; ?> jam
<?php if ($duration_hours > 7): ?>
                                                <br><span class="text-danger"><strong>⚠️ Melebihi batas 7 jam!</strong></span>
<?php endif; ?>
                                        </div>
<?php endif; ?>
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
                                <div class="mb-3">
                                    <label for="pd_arrived_at" class="form-label">
                                        <i class="bi bi-geo-alt"></i> Waktu Tiba Segel di Depo *
                                    </label>
                                    <input type="datetime-local" class="form-control" id="pd_arrived_at" 
                                           name="pd_arrived_at" required>
                                    <!-- <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                            onclick="setCurrentTime('pd_arrived_at')">
                                        <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                    </button> -->
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pd_goto_msf" class="form-label">
                                        <i class="bi bi-arrow-right"></i> Waktu Berangkat ke Main Tank MSF *
                                    </label>
                                    <input type="datetime-local" class="form-control" id="pd_goto_msf" 
                                           name="pd_goto_msf" required>
                                    <!-- <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                            onclick="setCurrentTime('pd_goto_msf')">
                                        <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                    </button> -->
                                </div>
                                
                                <!-- 7 Hour Validation Field -->
                                <div id="reason_field" class="mb-3" style="display: <?php echo $requires_reason ? 'block' : 'none'; ?>">
                                    <label for="pd_alasan_lebih_7jam" class="form-label">
                                        <i class="bi bi-exclamation-triangle text-warning"></i> Alasan Perjalanan Lebih dari 7 Jam *
                                    </label>
                                    <textarea class="form-control" id="pd_alasan_lebih_7jam" name="pd_alasan_lebih_7jam" 
                                              rows="4" placeholder="Jelaskan alasan mengapa perjalanan memakan waktu lebih dari 7 jam..."
<?php echo $requires_reason ? 'required' : ''; ?>></textarea>
                                    <small class="form-text text-muted">
                                        Field ini wajib diisi jika durasi perjalanan melebihi 7 jam
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Condition Photos -->
                        <div class="photo-upload-section">
                            <h3 class="section-title">
                                <i class="bi bi-camera me-2"></i>Foto Kondisi Segel
                            </h3>
                            <div class="photo-grid">
<?php for($i = 1; $i <= 4; $i++): ?>
                                <div class="photo-item">
                                    <label for="pd_foto_kondisi_<?php echo $i; ?>" class="form-label fw-bold">
                                        <i class="bi bi-shield-check"></i> Foto Kondisi Segel <?php echo $i; ?>
                                    </label>
                                    <input type="file" class="form-control" id="pd_foto_kondisi_<?php echo $i; ?>" 
                                           name="pd_foto_kondisi_<?php echo $i; ?>" accept="image/*"
                                           onchange="previewImage(this, 'preview_kondisi_<?php echo $i; ?>')">
                                    <button type="button" class="btn camera-btn w-100"
                                        onclick="openCameraModal('pd_foto_kondisi_<?php echo $i; ?>', 'preview_kondisi_<?php echo $i; ?>')">
                                        <i class="bi bi-camera me-2"></i>Buka Kamera
                                    </button>
                                    <img id="preview_kondisi_<?php echo $i; ?>" class="photo-preview" style="display: none;">
                                </div>
<?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Document Photos -->
                        <div class="photo-upload-section">
                            <h3 class="section-title">
                                <i class="bi bi-file-earmark-text me-2"></i>Foto Dokumen Wajib
                            </h3>
                            <div class="photo-grid">
                                <div class="photo-item">
                                    <label for="pd_foto_sib" class="form-label fw-bold">
                                        <i class="bi bi-file-text"></i> Foto SIB (Surat Izin Bongkar) *
                                    </label>
                                    <input type="file" class="form-control" id="pd_foto_sib" 
                                           name="pd_foto_sib" accept="image/*" required
                                           onchange="previewImage(this, 'preview_sib')">
                                    <button type="button" class="btn camera-btn w-100"
                                        onclick="openCameraModal('pd_foto_sib', 'preview_sib')">
                                        <i class="bi bi-camera me-2"></i>Buka Kamera
                                    </button>
                                    <img id="preview_sib" class="photo-preview" style="display: none;">
                                </div>
                                <div class="photo-item">
                                    <label for="pd_foto_ftw" class="form-label fw-bold">
                                        <i class="bi bi-file-spreadsheet"></i> Foto FTW (Fuel Transfer Worksheet) *
                                    </label>
                                    <input type="file" class="form-control" id="pd_foto_ftw" 
                                           name="pd_foto_ftw" accept="image/*" required
                                           onchange="previewImage(this, 'preview_ftw')">
                                    <button type="button" class="btn camera-btn w-100"
                                        onclick="openCameraModal('pd_foto_ftw', 'preview_ftw')">
                                        <i class="bi bi-camera me-2"></i>Buka Kamera
                                    </button>
                                    <img id="preview_ftw" class="photo-preview" style="display: none;">
                                </div>
                                <div class="photo-item">
                                    <label for="pd_foto_p2h" class="form-label fw-bold">
                                        <i class="bi bi-clipboard-check"></i> Foto P2H (Pemeriksaan 2 Harian) *
                                    </label>
                                    <input type="file" class="form-control" id="pd_foto_p2h" 
                                           name="pd_foto_p2h" accept="image/*" required
                                           onchange="previewImage(this, 'preview_p2h')">
                                    <button type="button" class="btn camera-btn w-100"
                                        onclick="openCameraModal('pd_foto_p2h', 'preview_p2h')">
                                        <i class="bi bi-camera me-2"></i>Buka Kamera
                                    </button>
                                    <img id="preview_p2h" class="photo-preview" style="display: none;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting:</h6>
                            <ul class="mb-0">
                                <li>Semua foto dokumen (SIB, FTW, P2H) wajib diupload</li>
                                <li>Jika perjalanan > 7 jam, wajib isi alasan</li>
                                <li>Setelah submit, status akan berubah menjadi "Menunggu Fuelman"</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save"></i> Simpan & Lanjutkan ke Fuelman
                            </button>
                            <a href="list.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke List
                            </a>
                        </div>
                    </form>
<?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Set current time function
function setCurrentTime(fieldId) {
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById(fieldId).value = localDateTime;
    
    // Check duration when arrival time is set
    if (fieldId === 'pd_arrived_at') {
        checkDuration();
    }
}

// Check duration function
function checkDuration() {
    const keluarPertamina = '<?php echo $log['dr_waktu_keluar_pertamina']; ?>';
    const arrivedAt = document.getElementById('pd_arrived_at').value;
    
    if (keluarPertamina && arrivedAt) {
        const keluar = new Date(keluarPertamina);
        const tiba = new Date(arrivedAt);
        const diffHours = Math.abs(tiba - keluar) / 36e5; // Convert to hours
        
        const reasonField = document.getElementById('reason_field');
        const reasonTextarea = document.getElementById('pd_alasan_lebih_7jam');
        
        if (diffHours > 7) {
            reasonField.style.display = 'block';
            reasonTextarea.required = true;
            
            // Show alert
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning mt-2';
            alert.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Durasi perjalanan: ' + diffHours.toFixed(1) + ' jam (melebihi 7 jam). Alasan wajib diisi!';
            
            // Remove existing alerts
            const existingAlert = document.querySelector('.duration-alert');
            if (existingAlert) existingAlert.remove();
            
            alert.classList.add('duration-alert');
            document.getElementById('pd_arrived_at').parentNode.appendChild(alert);
        } else {
            reasonField.style.display = 'none';
            reasonTextarea.required = false;
            
            // Remove alert
            const existingAlert = document.querySelector('.duration-alert');
            if (existingAlert) existingAlert.remove();
        }
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

// Compress image before upload (max 300kb)
function compressAndUpload(input, previewId) {
    const file = input.files[0];
    if (!file) return;

    const maxKB = 300;
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            let width = img.width;
            let height = img.height;
            const maxDim = 1280;
            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height *= maxDim / width;
                    width = maxDim;
                } else {
                    width *= maxDim / height;
                    height = maxDim;
                }
            }
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            let quality = 0.85;
            let dataUrl = canvas.toDataURL('image/jpeg', quality);

            // Reduce quality until under 300kb
            while (dataUrl.length / 1024 > maxKB && quality > 0.4) {
                quality -= 0.05;
                dataUrl = canvas.toDataURL('image/jpeg', quality);
            }

            // Set preview
            if (previewId) {
                const preview = document.getElementById(previewId);
                preview.src = dataUrl;
                preview.style.display = 'block';
            }

            // Convert dataUrl to Blob and replace file in input
            fetch(dataUrl)
                .then(res => res.blob())
                .then(blob => {
                    const compressedFile = new File([blob], file.name, {type: 'image/jpeg'});
                    const dt = new DataTransfer();
                    dt.items.add(compressedFile);
                    input.files = dt.files;
                });
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Set compress for all photo input fields
document.addEventListener('DOMContentLoaded', function() {
    // Foto kondisi segel
    for (let i = 1; i <= 4; i++) {
        const input = document.getElementById('pd_foto_kondisi_' + i);
        if (input) {
            input.onchange = function() {
                compressAndUpload(this, 'preview_kondisi_' + i);
            };
        }
    }
    // Dokumen wajib
    const docInputs = [
        {id: 'pd_foto_sib', preview: 'preview_sib'},
        {id: 'pd_foto_ftw', preview: 'preview_ftw'},
        {id: 'pd_foto_p2h', preview: 'preview_p2h'}
    ];
    docInputs.forEach(function(item) {
        const el = document.getElementById(item.id);
        if (el) {
            el.onchange = function() {
                compressAndUpload(this, item.preview);
            };
        }
    });
});

// Form validation
document.getElementById('depoForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    btn.disabled = true;
});

// Check duration on page load if arrival time exists
document.addEventListener('DOMContentLoaded', function() {
    const arrivedAt = document.getElementById('pd_arrived_at');
    arrivedAt.addEventListener('change', checkDuration);
});
</script>

<?php
require_once '../includes/footer.php'; 
require_once '../includes/camera.php';
?>
