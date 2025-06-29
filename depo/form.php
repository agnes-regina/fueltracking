
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
                                    <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                            onclick="setCurrentTime('pd_arrived_at')">
                                        <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pd_goto_msf" class="form-label">
                                        <i class="bi bi-arrow-right"></i> Waktu Berangkat ke Main Tank MSF *
                                    </label>
                                    <input type="datetime-local" class="form-control" id="pd_goto_msf" 
                                           name="pd_goto_msf" required>
                                    <button type="button" class="btn btn-outline-secondary mt-2 w-100" 
                                            onclick="setCurrentTime('pd_goto_msf')">
                                        <i class="bi bi-clock"></i> Gunakan Waktu Sekarang
                                    </button>
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
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-camera"></i> Foto Kondisi Segel</h5>
                            </div>
                            <div class="card-body">
                                <?php for($i = 1; $i <= 4; $i++): ?>
                                    <div class="mb-3">
                                        <label for="pd_foto_kondisi_<?php echo $i; ?>" class="form-label">
                                            <i class="bi bi-shield-check"></i> Foto Kondisi Segel <?php echo $i; ?>
                                        </label>
                                        <input type="file" class="form-control" id="pd_foto_kondisi_<?php echo $i; ?>" 
                                               name="pd_foto_kondisi_<?php echo $i; ?>" accept="image/*"
                                               onchange="previewImage(this, 'preview_kondisi_<?php echo $i; ?>')">
                                        <img id="preview_kondisi_<?php echo $i; ?>" class="photo-preview" style="display: none;">
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Document Photos -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-file-earmark-text"></i> Foto Dokumen Wajib</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="pd_foto_sib" class="form-label">
                                        <i class="bi bi-file-text"></i> Foto SIB (Surat Izin Bongkar) *
                                    </label>
                                    <input type="file" class="form-control" id="pd_foto_sib" 
                                           name="pd_foto_sib" accept="image/*" required
                                           onchange="previewImage(this, 'preview_sib')">
                                    <img id="preview_sib" class="photo-preview" style="display: none;">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pd_foto_ftw" class="form-label">
                                        <i class="bi bi-file-spreadsheet"></i> Foto FTW (Fuel Transfer Worksheet) *
                                    </label>
                                    <input type="file" class="form-control" id="pd_foto_ftw" 
                                           name="pd_foto_ftw" accept="image/*" required
                                           onchange="previewImage(this, 'preview_ftw')">
                                    <img id="preview_ftw" class="photo-preview" style="display: none;">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="pd_foto_p2h" class="form-label">
                                        <i class="bi bi-clipboard-check"></i> Foto P2H (Pemeriksaan 2 Harian) *
                                    </label>
                                    <input type="file" class="form-control" id="pd_foto_p2h" 
                                           name="pd_foto_p2h" accept="image/*" required
                                           onchange="previewImage(this, 'preview_p2h')">
                                    <img id="preview_p2h" class="photo-preview" style="display: none;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informasi Penting:</h6>
                            <ul class="mb-0">
                                <li>Semua foto dokumen (SIB, FTW, P2H) wajib diupload</li>
                                <li>Foto kondisi segel opsional tapi disarankan untuk dokumentasi</li>
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

<?php require_once '../includes/footer.php'; ?>
