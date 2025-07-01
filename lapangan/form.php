<?php
require_once '../config/db.php';
requireLogin();
requireRole('pengawas_lapangan');

$pageTitle = 'Form Pengawas Lapangan - Loading Log';

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

if (!$id) {
    header('Location: list.php');
    exit();
}

// Get log data
try {
    $stmt = $pdo->prepare("SELECT * FROM fuel_logs WHERE id = ? AND status_progress = 'waiting_pengawas'");
    $stmt->execute([$id]);
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
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $data = [
        'pl_loading_start' => $_POST['pl_loading_start'] ?? '',
        'pl_loading_end' => $_POST['pl_loading_end'] ?? '',
        'pl_loading_location' => $_POST['pl_loading_location'] ?? '',
        'pl_segel_1' => $_POST['pl_segel_1'] ?? '',
        'pl_segel_2' => $_POST['pl_segel_2'] ?? '',
        'pl_segel_3' => $_POST['pl_segel_3'] ?? '',
        'pl_segel_4' => $_POST['pl_segel_4'] ?? '',
    ];
    
    // Handle file uploads
    $photoFields = ['pl_segel_photo_1', 'pl_segel_photo_2', 'pl_segel_photo_3', 'pl_segel_photo_4', 'pl_doc_sampel', 'pl_doc_do', 'pl_doc_suratjalan'];
    
    foreach ($photoFields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '_' . uniqid() . '_' . basename($_FILES[$field]['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
                $data[$field] = 'uploads/' . $fileName;
            }
        }
    }
    
    try {
        $setParts = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($value !== '') {
                $setParts[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        $setParts[] = "pl_created_by = ?";
        $setParts[] = "pl_created_at = NOW()";
        $setParts[] = "status_progress = 'waiting_driver'";
        
        $params[] = $_SESSION['user_id'];
        $params[] = $id;
        
        $sql = "UPDATE fuel_logs SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $success = "Data berhasil disimpan! Menunggu driver untuk update selanjutnya.";
        
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

.input-group-modern {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.segel-input-group {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: end;
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

.datetime-local-modern {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e2e8f0;
    border-radius: 15px;
    padding: 1rem 1.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.datetime-local-modern:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1), 0 8px 25px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
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
    
    .segel-input-group {
        grid-template-columns: 1fr;
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
                <i class="bi bi-clipboard-check fs-1 me-3"></i>
                Loading Log Form
            </h1>
            <p class="lead mb-2">Pengawas Lapangan - Data Loading</p>
            <p class="mb-0 opacity-90">
                Unit: <strong class="badge bg-light text-dark fs-6 px-3 py-2 rounded-pill"><?php echo htmlspecialchars($log['nomor_unit']); ?></strong>
            </p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success-modern text-center" id="successAlert">
            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
            <strong>Berhasil!</strong> <?php echo $success; ?>
            <div class="mt-4">
                <a href="list.php" class="btn btn-secondary-modern">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
        <script>
        // Sembunyikan form setelah submit sukses
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('loadingForm');
            if (form) form.style.display = 'none';
        });
        </script>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger-modern">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
            <strong>Error!</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="loadingForm">
        <!-- Loading Information -->
        <div class="form-card">
            <h3 class="section-title">
                <i class="bi bi-clock-history me-2"></i>Informasi Loading
            </h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating-modern">
                        <input type="datetime-local" class="form-control datetime-local-modern" 
                               id="pl_loading_start" name="pl_loading_start" 
                               placeholder=" " required>
                        <label for="pl_loading_start">
                            <i class="bi bi-play-circle me-2"></i>Mulai Loading
                        </label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-floating-modern">
                        <input type="datetime-local" class="form-control datetime-local-modern" 
                               id="pl_loading_end" name="pl_loading_end" 
                               placeholder=" " required>
                        <label for="pl_loading_end">
                            <i class="bi bi-stop-circle me-2"></i>Selesai Loading
                        </label>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="form-floating-modern">
                        <input type="text" class="form-control" 
                               id="pl_loading_location" name="pl_loading_location" 
                               placeholder=" " required>
                        <label for="pl_loading_location">
                            <i class="bi bi-geo-alt me-2"></i>Lokasi Loading (GPS Coordinates)
                        </label>
                    </div>
                    <button type="button" class="btn camera-btn w-100 mt-2" onclick="getLocation()">
                        <i class="bi bi-geo-alt-fill me-2"></i>Ambil Lokasi GPS Otomatis
                    </button>
                </div>
            </div>
        </div>

        <!-- Foto Segel Section -->
        <div class="photo-upload-section">
            <h3 class="section-title">
                <i class="bi bi-camera me-2"></i>Foto & Data Segel
            </h3>
            
            <div class="photo-grid">
                <?php for($i = 1; $i <= 4; $i++): ?>
                    <div class="photo-item">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-shield-check me-2"></i>Segel <?php echo $i; ?>
                        </h6>
                        
                        <div class="segel-input-group">
                            <div class="form-floating-modern">
                                <input type="text" class="form-control" 
                                       id="pl_segel_<?php echo $i; ?>" name="pl_segel_<?php echo $i; ?>" 
                                       placeholder=" ">
                                <label for="pl_segel_<?php echo $i; ?>">Nomor Segel <?php echo $i; ?></label>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-camera me-2"></i>Foto Segel <?php echo $i; ?>
                            </label>
                            <input type="file" class="form-control" 
                                   id="pl_segel_photo_<?php echo $i; ?>" name="pl_segel_photo_<?php echo $i; ?>" 
                                   accept="image/*" onchange="previewImage(this, 'preview_segel_<?php echo $i; ?>')">
                            <button type="button" class="btn camera-btn w-100 mt-2" 
                                    onclick="openCameraModal('pl_segel_photo_<?php echo $i; ?>', 'preview_segel_<?php echo $i; ?>')">
                                <i class="bi bi-camera me-2"></i>Buka Kamera
                            </button>
                            <img id="preview_segel_<?php echo $i; ?>" class="photo-preview" style="display: none;">
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Dokumen Section -->
        <div class="photo-upload-section">
            <h3 class="section-title">
                <i class="bi bi-file-earmark-text me-2"></i>Dokumen
            </h3>
            
            <div class="photo-grid">
                <div class="photo-item">
                    <h6 class="fw-bold text-success mb-3">
                        <i class="bi bi-droplet me-2"></i>Sampel BBM
                    </h6>
                    <input type="file" class="form-control" 
                           id="pl_doc_sampel" name="pl_doc_sampel" 
                           accept="image/*" onchange="previewImage(this, 'preview_sampel')">
                    <button type="button" class="btn camera-btn w-100 mt-2" 
                            onclick="openCameraModal('pl_doc_sampel', 'preview_sampel')">
                        <i class="bi bi-camera me-2"></i>Buka Kamera
                    </button>
                    <img id="preview_sampel" class="photo-preview" style="display: none;">
                </div>
                
                <div class="photo-item">
                    <h6 class="fw-bold text-warning mb-3">
                        <i class="bi bi-file-text me-2"></i>Delivery Order
                    </h6>
                    <input type="file" class="form-control" 
                           id="pl_doc_do" name="pl_doc_do" 
                           accept="image/*" onchange="previewImage(this, 'preview_do')">
                    <button type="button" class="btn camera-btn w-100 mt-2" 
                            onclick="openCameraModal('pl_doc_do', 'preview_do')">
                        <i class="bi bi-camera me-2"></i>Buka Kamera
                    </button>
                    <img id="preview_do" class="photo-preview" style="display: none;">
                </div>
                
                <div class="photo-item">
                    <h6 class="fw-bold text-info mb-3">
                        <i class="bi bi-envelope me-2"></i>Surat Jalan
                    </h6>
                    <input type="file" class="form-control" 
                           id="pl_doc_suratjalan" name="pl_doc_suratjalan" 
                           accept="image/*" onchange="previewImage(this, 'preview_suratjalan')">
                    <button type="button" class="btn camera-btn w-100 mt-2" 
                            onclick="openCameraModal('pl_doc_suratjalan', 'preview_suratjalan')">
                        <i class="bi bi-camera me-2"></i>Buka Kamera
                    </button>
                    <img id="preview_suratjalan" class="photo-preview" style="display: none;">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mb-4">
            <button type="submit" class="btn btn-modern">
                <i class="bi bi-save me-2"></i>Simpan Data Loading
            </button>
            <a href="list.php" class="btn btn-secondary-modern ms-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<script>
// GPS Location function
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('pl_loading_location').value = lat + ',' + lng;
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
    
    document.getElementById('pl_loading_start').value = dateString;
});
</script>

<?php 
require_once '../includes/camera.php';
require_once '../includes/footer.php'; 
?>
