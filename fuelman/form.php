
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

// Check if already completed
$isCompleted = !empty($log['fm_created_at']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && !$isCompleted) {
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
        $isCompleted = true;
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id = ?");
        $stmt->execute([$id]);
        $log = $stmt->fetch();
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
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

.photo-upload-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 2px dashed #667eea;
    transition: all 0.3s ease;
}

.photo-upload-section:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.15);
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

.photo-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.photo-preview {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 10px;
    margin-top: 1rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
    
    .photo-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
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
    
    .photo-item {
        padding: 1rem;
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
                <i class="bi bi-droplet fs-1 me-3"></i>
                Fuelman Form
            </h1>
            <p class="lead mb-2">Input Data Fuelman - Proses Unloading</p>
            <p class="mb-0 opacity-90">
                Unit: <strong class="badge bg-light text-dark fs-6 px-3 py-2 rounded-pill"><?php echo htmlspecialchars($log['nomor_unit']); ?></strong>
            </p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success-modern">
            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
            <strong>Berhasil!</strong> <?php echo $success; ?>
            <hr>
            <a href="list.php" class="btn btn-outline-success">Kembali ke List</a>
            <a href="../detail.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">Lihat Detail</a>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger-modern">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
            <strong>Error!</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (!$error && !$isCompleted): ?>
        <form method="POST" enctype="multipart/form-data">
            <!-- Unloading Information -->
            <div class="form-card">
                <h3 class="section-title">
                    <i class="bi bi-clock-history me-2"></i>Informasi Unloading
                </h3>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="datetime-local" class="form-control" 
                                   id="fm_unload_start" name="fm_unload_start" 
                                   placeholder=" " required>
                            <label for="fm_unload_start">
                                <i class="bi bi-play-circle me-2"></i>Waktu Mulai Unloading
                            </label>
                        </div>
                        <button type="button" class="btn camera-btn w-100 mt-2" 
                                onclick="setCurrentTime('fm_unload_start')">
                            <i class="bi bi-clock me-2"></i>Waktu Sekarang
                        </button>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="datetime-local" class="form-control" 
                                   id="fm_unload_end" name="fm_unload_end" 
                                   placeholder=" " required>
                            <label for="fm_unload_end">
                                <i class="bi bi-stop-circle me-2"></i>Waktu Selesai Unloading
                            </label>
                        </div>
                        <button type="button" class="btn camera-btn w-100 mt-2" 
                                onclick="setCurrentTime('fm_unload_end')">
                            <i class="bi bi-clock me-2"></i>Waktu Sekarang
                        </button>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="text" class="form-control" 
                                   id="fm_location" name="fm_location" 
                                   placeholder=" " required>
                            <label for="fm_location">
                                <i class="bi bi-geo-alt me-2"></i>Lokasi Unloading
                            </label>
                        </div>
                        <button type="button" class="btn camera-btn w-100 mt-2" onclick="getLocation()">
                            <i class="bi bi-geo-alt-fill me-2"></i>Ambil Lokasi GPS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Flowmeter Information -->
            <div class="form-card">
                <h3 class="section-title">
                    <i class="bi bi-speedometer2 me-2"></i>Data Flowmeter
                </h3>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="text" class="form-control" 
                                   id="fm_flowmeter" name="fm_flowmeter" 
                                   placeholder=" " required>
                            <label for="fm_flowmeter">Flowmeter</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="text" class="form-control" 
                                   id="fm_serial" name="fm_serial" 
                                   placeholder=" ">
                            <label for="fm_serial">Serial Number</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="number" class="form-control" 
                                   id="fm_awal" name="fm_awal" 
                                   placeholder=" " step="0.01" required>
                            <label for="fm_awal">FM Awal</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="number" class="form-control" 
                                   id="fm_akhir" name="fm_akhir" 
                                   placeholder=" " step="0.01" required>
                            <label for="fm_akhir">FM Akhir</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="number" class="form-control" 
                                   id="fm_fuel_density" name="fm_fuel_density" 
                                   placeholder=" " step="0.001">
                            <label for="fm_fuel_density">Density</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating-modern">
                            <input type="number" class="form-control" 
                                   id="fm_fuel_temp" name="fm_fuel_temp" 
                                   placeholder=" " step="0.1">
                            <label for="fm_fuel_temp">Temperature (°C)</label>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-floating-modern">
                            <input type="number" class="form-control" 
                                   id="fm_fuel_fame" name="fm_fuel_fame" 
                                   placeholder=" " step="0.01">
                            <label for="fm_fuel_fame">FAME (%)</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photo Sections -->
            
            <!-- Foto Segel Awal -->
            <div class="photo-upload-section">
                <h3 class="section-title">
                    <i class="bi bi-camera me-2"></i>Foto Segel Awal
                </h3>
                
                <div class="photo-grid">
                    <?php for($i = 1; $i <= 4; $i++): ?>
                        <div class="photo-item">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-shield-check me-2"></i>Segel Awal <?php echo $i; ?>
                            </h6>
                            
                            <div class="mt-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-camera me-2"></i>Foto Segel Awal <?php echo $i; ?>
                                </label>
                                <input type="file" class="form-control" 
                                       id="fm_segel_photo_awal_<?php echo $i; ?>" name="fm_segel_photo_awal_<?php echo $i; ?>" 
                                       accept="image/*" onchange="previewImage(this, 'preview_segel_awal_<?php echo $i; ?>')">
                                <button type="button" class="btn camera-btn w-100 mt-2" 
                                        onclick="openCameraModal('fm_segel_photo_awal_<?php echo $i; ?>', 'preview_segel_awal_<?php echo $i; ?>')">
                                    <i class="bi bi-camera me-2"></i>Buka Kamera
                                </button>
                                <img id="preview_segel_awal_<?php echo $i; ?>" class="photo-preview" style="display: none;">
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Foto Segel Akhir -->
            <!-- <div class="photo-upload-section">
                <h3 class="section-title">
                    <i class="bi bi-camera me-2"></i>Foto Segel Akhir
                </h3>
                
                <div class="photo-grid">
                    <?php for($i = 1; $i <= 4; $i++): ?>
                        <div class="photo-item">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-shield-check me-2"></i>Segel Akhir <?php echo $i; ?>
                            </h6>
                            
                            <div class="mt-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-camera me-2"></i>Foto Segel Akhir <?php echo $i; ?>
                                </label>
                                <input type="file" class="form-control" 
                                       id="fm_photo_akhir_<?php echo $i; ?>" name="fm_photo_akhir_<?php echo $i; ?>" 
                                       accept="image/*" onchange="previewImage(this, 'preview_akhir_<?php echo $i; ?>')">
                                <button type="button" class="btn camera-btn w-100 mt-2" 
                                        onclick="openCameraModal('fm_photo_akhir_<?php echo $i; ?>', 'preview_akhir_<?php echo $i; ?>')">
                                    <i class="bi bi-camera me-2"></i>Buka Kamera
                                </button>
                                <img id="preview_akhir_<?php echo $i; ?>" class="photo-preview" style="display: none;">
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div> -->

            <!-- Foto Tanki Kosong -->
            <div class="photo-upload-section">
                <h3 class="section-title">
                    <i class="bi bi-camera me-2"></i>Foto Tanki Kosong
                </h3>
                
                <div class="photo-grid">
                    <?php for($i = 1; $i <= 4; $i++): ?>
                        <div class="photo-item">
                            <h6 class="fw-bold text-warning mb-3">
                                <i class="bi bi-fuel-pump me-2"></i>Tanki Kosong <?php echo $i; ?>
                            </h6>
                            
                            <div class="mt-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-camera me-2"></i>Foto Tanki Kosong <?php echo $i; ?>
                                </label>
                                <input type="file" class="form-control" 
                                       id="fm_photo_akhir_<?php echo $i; ?>" name="fm_photo_akhir_<?php echo $i; ?>" 
                                       accept="image/*" onchange="previewImage(this, 'preview_tanki_kosong_<?php echo $i; ?>')">
                                <button type="button" class="btn camera-btn w-100 mt-2" 
                                        onclick="openCameraModal('fm_photo_akhir_<?php echo $i; ?>', 'preview_tanki_kosong_<?php echo $i; ?>')">
                                    <i class="bi bi-camera me-2"></i>Buka Kamera
                                </button>
                                <img id="preview_tanki_kosong_<?php echo $i; ?>" class="photo-preview" style="display: none;">
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Foto Kejernihan -->
            <div class="photo-upload-section">
                <h3 class="section-title">
                    <i class="bi bi-camera me-2"></i>Foto Kejernihan BBM
                </h3>
                
                <div class="photo-grid">
                    <div class="photo-item">
                        <h6 class="fw-bold text-info mb-3">
                            <i class="bi bi-droplet me-2"></i>Kejernihan BBM
                        </h6>
                        
                        <div class="mt-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-camera me-2"></i>Foto Kejernihan
                            </label>
                            <input type="file" class="form-control" 
                                   id="fm_photo_kejernihan" name="fm_photo_kejernihan" 
                                   accept="image/*" onchange="previewImage(this, 'preview_kejernihan')">
                            <button type="button" class="btn camera-btn w-100 mt-2" 
                                    onclick="openCameraModal('fm_photo_kejernihan', 'preview_kejernihan')">
                                <i class="bi bi-camera me-2"></i>Buka Kamera
                            </button>
                            <img id="preview_kejernihan" class="photo-preview" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center mb-4">
                <button type="submit" class="btn btn-modern">
                    <i class="bi bi-save me-2"></i>Simpan Data Fuelman
                </button>
                <a href="list.php" class="btn btn-secondary-modern ms-3">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </form>
    <?php elseif ($isCompleted): ?>
        <!-- Show completed data -->
        <div class="form-card readonly-card">
            <h3 class="section-title">
                <i class="bi bi-check-circle me-2"></i>Data Fuelman (Completed)
            </h3>
            
            <div class="alert alert-success-modern">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Proses Selesai!</strong> Data fuelman sudah tersimpan.
            </div>
            
            <div class="text-center">
                <a href="list.php" class="btn btn-secondary-modern me-3">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke List
                </a>
                <a href="../detail.php?id=<?php echo $id; ?>" class="btn btn-modern">
                    <i class="bi bi-eye me-2"></i>Lihat Detail
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// GPS Location function
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('fm_location').value = lat + ',' + lng;
        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Set current time
function setCurrentTime(fieldId) {
    const now = new Date();
    const dateString = now.getFullYear() + '-' + 
                      String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(now.getDate()).padStart(2, '0') + 'T' + 
                      String(now.getHours()).padStart(2, '0') + ':' + 
                      String(now.getMinutes()).padStart(2, '0');
    
    document.getElementById(fieldId).value = dateString;
}

// Set current time for datetime inputs on page load
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const dateString = now.getFullYear() + '-' + 
                      String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(now.getDate()).padStart(2, '0') + 'T' + 
                      String(now.getHours()).padStart(2, '0') + ':' + 
                      String(now.getMinutes()).padStart(2, '0');
    
    const startField = document.getElementById('fm_unload_start');
    if (startField) {
        startField.value = dateString;
    }
});
</script>

<?php 
require_once '../includes/camera.php';
require_once '../includes/footer.php'; 
?>
