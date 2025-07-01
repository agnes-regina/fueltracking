
<?php
require_once 'config/db.php';
requireLogin();

$pageTitle = 'Detail Pengiriman - Fuel Transport Tracking';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: logs.php');
    exit();
}

// Get log data with user information
try {
    $stmt = $pdo->prepare("
        SELECT fl.*, 
               pt.full_name as pt_creator_name,
               pl.full_name as pl_creator_name,
               dr.full_name as dr_creator_name,
               pd.full_name as pd_creator_name,
               fm.full_name as fm_creator_name
        FROM fuel_logs fl
        LEFT JOIN users pt ON fl.pt_created_by = pt.id
        LEFT JOIN users pl ON fl.pl_created_by = pl.id  
        LEFT JOIN users dr ON fl.dr_created_by = dr.id
        LEFT JOIN users pd ON fl.pd_created_by = pd.id
        LEFT JOIN users fm ON fl.fm_created_by = fm.id
        WHERE fl.id = ?
    ");
    $stmt->execute([$id]);
    $log = $stmt->fetch();
    
    if (!$log) {
        header('Location: logs.php');
        exit();
    }
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<style>
.hero-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-gradient-blue {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-gradient-green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.card-gradient-orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-gradient-purple {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.card-gradient-red {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.info-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.stat-card {
    background: linear-gradient(145deg, #ffffff 0%, #f1f5f9 100%);
    border-radius: 25px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.progress-timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline-line {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e2e8f0 0%, #cbd5e1 100%);
    border-radius: 2px;
    z-index: 1;
}

.timeline-step {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    z-index: 2;
}

.timeline-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    color: white;
    background: #cbd5e1;
    border: 4px solid white;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.timeline-step.active .timeline-circle {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.3);
    transform: scale(1.1);
}

.timeline-step.active .timeline-label {
    color: #059669;
    font-weight: 600;
}

.timeline-label {
    font-size: 0.9rem;
    color: #64748b;
    max-width: 100px;
}

.data-section {
    background: white;
    border-radius: 25px;
    padding: 2rem;
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    margin-bottom: 2rem;
    transition: all 0.3s ease;
}

.data-section:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 45px rgba(0,0,0,0.12);
}

.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
}

.section-icon {
    width: 50px;
    height: 50px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-right: 1rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.data-item {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 15px;
    border-left: 4px solid #3b82f6;
}

.data-label {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.data-value {
    font-size: 1rem;
    color: #1e293b;
    font-weight: 600;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.photo-item {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.photo-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}

.photo-preview {
    width: 100%;
    height: 120px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.photo-preview:hover {
    transform: scale(1.05);
}

.photo-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
}

.btn-modern {
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.alert-warning-custom {
    background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
    border: 1px solid #f59e0b;
    border-radius: 15px;
    padding: 1rem;
    color: #92400e;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .stat-card {
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        font-size: 1.8rem;
    }
    
    .timeline-circle {
        width: 50px;
        height: 50px;
        font-size: 1rem;
    }
    
    .data-section {
        padding: 1.5rem;
    }
    
    .section-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    
    .photo-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .photo-preview {
        height: 100px;
    }
}

@media print {
    .btn, .action-buttons { 
        display: none !important;
    }
    
    .data-section {
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }
    
    .photo-preview {
        max-height: 100px !important;
    }
}
</style>

<div class="container-fluid px-3">
    <!-- Hero Header -->
    <div class="hero-gradient rounded-4 text-white p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">
                    <i class="bi bi-truck fs-1 me-2"></i>
                    Detail Pengiriman #<?php echo $log['id']; ?>
                </h2>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-calendar3 me-2"></i>
                    Dibuat: <?php echo date('d F Y, H:i', strtotime($log['created_at'])); ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-light text-dark fs-6 px-3 py-2 rounded-pill">
                    <?php echo $statusLabels[$log['status_progress']]; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h5 class="mb-1"><?php echo htmlspecialchars($log['nomor_unit']); ?></h5>
                <small class="text-muted">Nomor Unit</small>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h5 class="mb-1"><?php echo htmlspecialchars($log['driver_name']); ?></h5>
                <small class="text-muted">Nama Driver</small>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);">
                    <i class="bi bi-calendar3"></i>
                </div>
                <h5 class="mb-1"><?php echo date('d/m/Y', strtotime($log['created_at'])); ?></h5>
                <small class="text-muted">Tanggal Pengiriman</small>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="logs.php" class="btn btn-outline-secondary btn-modern">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
        <?php if (hasRole('admin')): ?>
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning btn-modern">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
        <?php endif; ?>
        <?php if (hasRole('admin') || hasRole('gl_pama')): ?>
            <button onclick="window.print()" class="btn btn-info btn-modern">
                <i class="bi bi-printer me-2"></i>Print
            </button>
        <?php endif; ?>
    </div>

    <!-- Progress Timeline -->
    <div class="info-card mb-4">
        <h5 class="mb-4 text-center">
            <i class="bi bi-diagram-3 me-2"></i>Progress Timeline
        </h5>
        <div class="progress-timeline">
            <div class="timeline-line"></div>
            <div class="row">
                <div class="col timeline-step <?php echo !empty($log['pt_created_at']) ? 'active' : ''; ?>">
                    <div class="timeline-circle">1</div>
                    <div class="timeline-label">Pengawas Transportir</div>
                </div>
                <div class="col timeline-step <?php echo !empty($log['pl_created_at']) ? 'active' : ''; ?>">
                    <div class="timeline-circle">2</div>
                    <div class="timeline-label">Pengawas Lapangan</div>
                </div>
                <div class="col timeline-step <?php echo !empty($log['dr_created_at']) ? 'active' : ''; ?>">
                    <div class="timeline-circle">3</div>
                    <div class="timeline-label">Driver</div>
                </div>
                <div class="col timeline-step <?php echo !empty($log['pd_created_at']) ? 'active' : ''; ?>">
                    <div class="timeline-circle">4</div>
                    <div class="timeline-label">Pengawas Depo</div>
                </div>
                <div class="col timeline-step <?php echo !empty($log['fm_created_at']) ? 'active' : ''; ?>">
                    <div class="timeline-circle">5</div>
                    <div class="timeline-label">Fuelman</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Sections -->
    <div class="row">
        <!-- Pengawas Lapangan -->
        <?php if (!empty($log['pl_created_at'])): ?>
            <div class="col-lg-6 mb-4">
                <div class="data-section">
                    <div class="section-header">
                        <div class="section-icon card-gradient-blue">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Data Pengawas Lapangan</h6>
                            <small class="text-muted">Diisi oleh: <?php echo htmlspecialchars($log['pl_creator_name'] ?? 'N/A'); ?></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">Mulai Loading</div>
                                <div class="data-value"><?php echo $log['pl_loading_start'] ? date('d/m/Y H:i', strtotime($log['pl_loading_start'])) : 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">Selesai Loading</div>
                                <div class="data-value"><?php echo $log['pl_loading_end'] ? date('d/m/Y H:i', strtotime($log['pl_loading_end'])) : 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-item">
                                <div class="data-label">Lokasi Loading</div>
                                <div class="data-value"><?php echo htmlspecialchars($log['pl_loading_location'] ?? 'N/A'); ?></div>
                                <?php if (!empty($log['pl_loading_location'])): ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" 
                                            onclick="showLocationOnMap('<?php echo htmlspecialchars($log['pl_loading_location']); ?>')">
                                        <i class="bi bi-geo-alt"></i> Lihat Peta
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Foto Segel -->
                    <h6 class="mt-4 mb-3">Foto Segel</h6>
                    <div class="photo-grid">
                        <?php for($i = 1; $i <= 4; $i++): ?>
                            <?php if (!empty($log["pl_segel_photo_$i"])): ?>
                                <div class="photo-item">
                                    <img src="<?php echo htmlspecialchars($log["pl_segel_photo_$i"]); ?>" 
                                         class="photo-preview" 
                                         onclick="showImageModal(this.src)">
                                    <div class="photo-label">
                                        Segel <?php echo $i; ?>: <?php echo htmlspecialchars($log["pl_segel_$i"] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>

                    <!-- Dokumen -->
                    <h6 class="mt-4 mb-3">Dokumen</h6>
                    <div class="photo-grid">
                        <?php if (!empty($log['pl_doc_sampel'])): ?>
                            <div class="photo-item">
                                <img src="<?php echo htmlspecialchars($log['pl_doc_sampel']); ?>" 
                                     class="photo-preview" 
                                     onclick="showImageModal(this.src)">
                                <div class="photo-label">Sampel BBM</div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($log['pl_doc_do'])): ?>
                            <div class="photo-item">
                                <img src="<?php echo htmlspecialchars($log['pl_doc_do']); ?>" 
                                     class="photo-preview" 
                                     onclick="showImageModal(this.src)">
                                <div class="photo-label">Delivery Order</div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($log['pl_doc_suratjalan'])): ?>
                            <div class="photo-item">
                                <img src="<?php echo htmlspecialchars($log['pl_doc_suratjalan']); ?>" 
                                     class="photo-preview" 
                                     onclick="showImageModal(this.src)">
                                <div class="photo-label">Surat Jalan</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Driver Data -->
        <?php if (!empty($log['dr_created_at'])): ?>
            <div class="col-lg-6 mb-4">
                <div class="data-section">
                    <div class="section-header">
                        <div class="section-icon card-gradient-green">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Data Driver</h6>
                            <small class="text-muted">Diisi oleh: <?php echo htmlspecialchars($log['dr_creator_name'] ?? 'N/A'); ?></small>
                        </div>
                    </div>

                    <h6 class="mb-3">Data Loading</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">Mulai Loading</div>
                                <div class="data-value"><?php echo $log['dr_loading_start'] ? date('d/m/Y H:i', strtotime($log['dr_loading_start'])) : 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">Selesai Loading</div>
                                <div class="data-value"><?php echo $log['dr_loading_end'] ? date('d/m/Y H:i', strtotime($log['dr_loading_end'])) : 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-item">
                                <div class="data-label">Waktu Keluar Pertamina</div>
                                <div class="data-value"><?php echo $log['dr_waktu_keluar_pertamina'] ? date('d/m/Y H:i', strtotime($log['dr_waktu_keluar_pertamina'])) : 'N/A'; ?></div>
                            </div>
                        </div>
                            <div class="col-12">
                                <div class="data-item">
                                    <div class="data-label">Lokasi Unloading</div>
                                    <div class="data-value"><?php echo htmlspecialchars($log['dr_unload_location'] ?? 'N/A'); ?></div>
                                    <?php if (!empty($log['dr_unload_location'])): ?>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" 
                                                onclick="showLocationOnMap('<?php echo htmlspecialchars($log['dr_unload_location']); ?>')">
                                            <i class="bi bi-geo-alt"></i> Lihat Peta
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                    </div>

                    <!-- Unloading Data -->
                    <?php if (!empty($log['dr_unload_start'])): ?>
                        <h6 class="mt-4 mb-3">Data Unloading</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="data-item">
                                    <div class="data-label">Mulai Unloading</div>
                                    <div class="data-value"><?php echo date('d/m/Y H:i', strtotime($log['dr_unload_start'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="data-item">
                                    <div class="data-label">Selesai Unloading</div>
                                    <div class="data-value"><?php echo $log['dr_unload_end'] ? date('d/m/Y H:i', strtotime($log['dr_unload_end'])) : 'N/A'; ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="data-item">
                                    <div class="data-label">Lokasi Unloading</div>
                                    <div class="data-value"><?php echo htmlspecialchars($log['dr_unload_location'] ?? 'N/A'); ?></div>
                                    <?php if (!empty($log['dr_unload_location'])): ?>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" 
                                                onclick="showLocationOnMap('<?php echo htmlspecialchars($log['dr_unload_location']); ?>')">
                                            <i class="bi bi-geo-alt"></i> Lihat Peta
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pengawas Depo -->
        <?php if (!empty($log['pd_created_at'])): ?>
            <div class="col-lg-6 mb-4">
                <div class="data-section">
                    <div class="section-header">
                        <div class="section-icon card-gradient-orange">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Data Pengawas Depo</h6>
                            <small class="text-muted">Diisi oleh: <?php echo htmlspecialchars($log['pd_creator_name'] ?? 'N/A'); ?></small>
                        </div>
                    </div>

                    <div class="data-item">
                        <div class="data-label">Waktu Tiba</div>
                        <div class="data-value"><?php echo $log['pd_arrived_at'] ? date('d/m/Y H:i', strtotime($log['pd_arrived_at'])) : 'N/A'; ?></div>
                    </div>

                    <!-- Travel Duration Check -->
                    <?php if (!empty($log['dr_waktu_keluar_pertamina']) && !empty($log['pd_arrived_at'])): ?>
                        <?php 
                        $keluar = new DateTime($log['dr_waktu_keluar_pertamina']);
                        $tiba = new DateTime($log['pd_arrived_at']);
                        $diff = $keluar->diff($tiba);
                        $hours = $diff->h + ($diff->days * 24);
                        ?>
                        <div class="data-item">
                            <div class="data-label">Durasi Perjalanan</div>
                            <div class="data-value <?php echo $hours > 7 ? 'text-warning' : ''; ?>">
                                <?php echo $hours; ?> jam <?php echo $diff->i; ?> menit
                                <?php if ($hours > 7): ?>
                                    <i class="bi bi-exclamation-triangle text-warning ms-2"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($hours > 7 && !empty($log['pd_alasan_lebih_7jam'])): ?>
                            <div class="alert-warning-custom">
                                <strong>Alasan Lebih dari 7 Jam:</strong><br>
                                <?php echo nl2br(htmlspecialchars($log['pd_alasan_lebih_7jam'])); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Depo Photos Segel-->
                    <h6 class="mt-4 mb-3">Foto Kondisi Segel</h6>
                    <div class="photo-grid">
                        <?php for($i = 1; $i <= 4; $i++): ?>
                            <?php if (!empty($log["pd_foto_kondisi_$i"])): ?>
                                <div class="photo-item">
                                    <img src="<?php echo htmlspecialchars($log["pd_foto_kondisi_$i"]); ?>" 
                                         class="photo-preview" 
                                         onclick="showImageModal(this.src)">
                                    <div class="photo-label">Kondisi Segel <?php echo $i; ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Depo Photos Kondisi -->
                    <h6 class="mt-4 mb-3">Foto Dokumen </h6>
                    <div class="photo-grid">

                        <div class="photo-item">
                            <img src="<?php echo htmlspecialchars($log["pd_foto_sib"]); ?>" 
                                    class="photo-preview" 
                                    onclick="showImageModal(this.src)">
                            <div class="photo-label">Foto SIB</div>
                        </div>
                        <div class="photo-item">
                            <img src="<?php echo htmlspecialchars($log["pd_foto_ftw"]); ?>" 
                                    class="photo-preview" 
                                    onclick="showImageModal(this.src)">
                            <div class="photo-label">Foto FTW</div>
                        </div>
                        <div class="photo-item">
                            <img src="<?php echo htmlspecialchars($log["pd_foto_p2h"]); ?>" 
                                    class="photo-preview" 
                                    onclick="showImageModal(this.src)">
                            <div class="photo-label">Foto P2H</div>
                        </div>
                    </div>
                    <br>

                    <div class="data-item">
                        <div class="data-label">Lanjut ke MSF</div>
                        <div class="data-value"><?php echo $log['pd_goto_msf'] ? date('d/m/Y H:i', strtotime($log['pd_arrived_at'])) : 'N/A'; ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Fuelman Data -->
        <?php if (!empty($log['fm_created_at'])): ?>
            <div class="col-lg-6 mb-4">
                <div class="data-section">
                    <div class="section-header">
                        <div class="section-icon card-gradient-red">
                            <i class="bi bi-droplet"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Data Fuelman</h6>
                            <small class="text-muted">Diisi oleh: <?php echo htmlspecialchars($log['fm_creator_name'] ?? 'N/A'); ?></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">Mulai Unloading</div>
                                <div class="data-value"><?php echo $log['fm_unload_start'] ? date('d/m/Y H:i', strtotime($log['fm_unload_start'])) : 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">Selesai Unloading</div>
                                <div class="data-value"><?php echo $log['fm_unload_end'] ? date('d/m/Y H:i', strtotime($log['fm_unload_end'])) : 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-item">
                                <div class="data-label">Flowmeter</div>
                                <div class="data-value"><?php echo htmlspecialchars($log['fm_flowmeter'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">FM Awal</div>
                                <div class="data-value"><?php echo $log['fm_awal'] ?? 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-item">
                                <div class="data-label">FM Akhir</div>
                                <div class="data-value"><?php echo $log['fm_akhir'] ?? 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="data-item">
                                <div class="data-label">Density</div>
                                <div class="data-value"><?php echo $log['fm_fuel_density'] ?? 'N/A'; ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="data-item">
                                <div class="data-label">Temperature</div>
                                <div class="data-value"><?php echo $log['fm_fuel_temp'] ?? 'N/A'; ?>°C</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="data-item">
                                <div class="data-label">FAME</div>
                                <div class="data-value"><?php echo $log['fm_fuel_fame'] ?? 'N/A'; ?>%</div>
                            </div>
                        </div>
                        
                        <?php if (!empty($log['fm_location'])): ?>
                            <div class="col-12">
                                <div class="data-item">
                                    <div class="data-label">Lokasi Unload</div>
                                    <div class="data-value"><?php echo htmlspecialchars($log['fm_location']); ?></div>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" 
                                            onclick="showLocationOnMap('<?php echo htmlspecialchars($log['fm_location']); ?>')">
                                        <i class="bi bi-geo-alt"></i> Lihat Peta
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Foto Segel Awal -->
                    <h6 class="mt-4 mb-3">Foto Segel Awal</h6>
                    <div class="photo-grid">
                        <?php for($i = 1; $i <= 4; $i++): ?>
                            <?php if (!empty($log["fm_segel_photo_awal_$i"])): ?>
                                <div class="photo-item">
                                    <img src="<?php echo htmlspecialchars($log["fm_segel_photo_awal_$i"]); ?>" 
                                         class="photo-preview" 
                                         onclick="showImageModal(this.src)">
                                    <div class="photo-label">Segel Awal <?php echo $i; ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>

                    <!-- Foto Tanki Kosong -->
                    <h6 class="mt-4 mb-3">Foto Tanki Kosong</h6>
                    <div class="photo-grid">
                        <?php for($i = 1; $i <= 4; $i++): ?>
                            <?php if (!empty($log["fm_photo_akhir$i"])): ?>
                                <div class="photo-item">
                                    <img src="<?php echo htmlspecialchars($log["fm_photo_akhir$i"]); ?>" 
                                         class="photo-preview" 
                                         onclick="showImageModal(this.src)">
                                    <div class="photo-label">Tanki Kosong <?php echo $i; ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>

                    <!-- Foto Kejernihan -->
                    <?php if (!empty($log['fm_photo_kejernihan'])): ?>
                        <h6 class="mt-4 mb-3">Foto Kejernihan BBM</h6>
                        <div class="photo-grid">
                            <div class="photo-item">
                                <img src="<?php echo htmlspecialchars($log['fm_photo_kejernihan']); ?>" 
                                     class="photo-preview" 
                                     onclick="showImageModal(this.src)">
                                <div class="photo-label">Kejernihan BBM</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Print styles
window.addEventListener('beforeprint', function() {
    document.body.classList.add('printing');
});

window.addEventListener('afterprint', function() {
    document.body.classList.remove('printing');
});
</script>

<?php 
require_once 'includes/maps.php';
require_once 'includes/footer.php'; 
?>
