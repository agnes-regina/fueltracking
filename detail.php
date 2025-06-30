
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

<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--purple-color));">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0">
                            <i class="bi bi-file-text"></i> Detail Pengiriman #<?php echo $log['id']; ?>
                        </h4>
                        <small class="opacity-75">Dibuat: <?php echo date('d F Y, H:i', strtotime($log['created_at'])); ?></small>
                    </div>
                    <div class="col-auto">
                        <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                            <?php echo $statusLabels[$log['status_progress']]; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                            <i class="bi bi-truck display-4 text-primary"></i>
                            <h5 class="mt-2 mb-1"><?php echo htmlspecialchars($log['nomor_unit']); ?></h5>
                            <small class="text-muted">Nomor Unit</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                            <i class="bi bi-person-circle display-4 text-success"></i>
                            <h5 class="mt-2 mb-1"><?php echo htmlspecialchars($log['driver_name']); ?></h5>
                            <small class="text-muted">Nama Driver</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                            <i class="bi bi-calendar3 display-4 text-info"></i>
                            <h5 class="mt-2 mb-1"><?php echo date('d/m/Y', strtotime($log['created_at'])); ?></h5>
                            <small class="text-muted">Tanggal Pengiriman</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="logs.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke List
                    </a>
                    <?php if (hasRole('admin')): ?>
                        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    <?php endif; ?>
                    <?php if (hasRole('admin') || hasRole('gl_pama')): ?>
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Timeline Progress -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Progress Timeline</h5>
            </div>
            <div class="card-body">
                <div class="progress-indicator">
                    <div class="progress-step <?php echo !empty($log['pt_created_at']) ? 'active' : ''; ?>">
                        <div class="progress-circle">1</div>
                        <small>Pengawas Transportir</small>
                    </div>
                    <div class="progress-step <?php echo !empty($log['pl_created_at']) ? 'active' : ''; ?>">
                        <div class="progress-circle">2</div>
                        <small>Pengawas Lapangan</small>
                    </div>
                    <div class="progress-step <?php echo !empty($log['dr_created_at']) ? 'active' : ''; ?>">
                        <div class="progress-circle">3</div>
                        <small>Driver</small>
                    </div>
                    <div class="progress-step <?php echo !empty($log['pd_created_at']) ? 'active' : ''; ?>">
                        <div class="progress-circle">4</div>
                        <small>Pengawas Depo</small>
                    </div>
                    <div class="progress-step <?php echo !empty($log['fm_created_at']) ? 'active' : ''; ?>">
                        <div class="progress-circle">5</div>
                        <small>Fuelman</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Sections -->
        <div class="row">
            <!-- Pengawas Transportir Data -->
            <?php if (!empty($log['pt_created_at'])): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="bi bi-person-badge"></i> Data Pengawas Transportir</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Diisi oleh:</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($log['pt_creator_name'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Waktu Input:</small>
                                <div><?php echo date('d/m/Y H:i', strtotime($log['pt_created_at'])); ?></div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Driver ID:</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($log['pt_driver_id'] ?? 'N/A'); ?></div>
                            </div>
                            <div>
                                <small class="text-muted">Unit Number:</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($log['pt_unit_number'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pengawas Lapangan Data -->
            <?php if (!empty($log['pl_created_at'])): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Data Pengawas Lapangan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted">Diisi oleh:</small>
                                    <div class="fw-bold"><?php echo htmlspecialchars($log['pl_creator_name'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Mulai Loading:</small>
                                    <div><?php echo $log['pl_loading_start'] ? date('d/m/Y H:i', strtotime($log['pl_loading_start'])) : 'N/A'; ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Selesai Loading:</small>
                                    <div><?php echo $log['pl_loading_end'] ? date('d/m/Y H:i', strtotime($log['pl_loading_end'])) : 'N/A'; ?></div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Lokasi:</small>
                                    <div><?php echo htmlspecialchars($log['pl_loading_location'] ?? 'N/A'); ?></div>
                                </div>
                                
                                <!-- Foto Segel -->
                                <?php for($i = 1; $i <= 4; $i++): ?>
                                    <?php if (!empty($log["pl_segel_photo_$i"])): ?>
                                        <div class="col-6">
                                            <small class="text-muted">Foto Segel <?php echo $i; ?>:</small>
                                            <div>
                                                <img src="<?php echo htmlspecialchars($log["pl_segel_photo_$i"]); ?>" 
                                                     class="photo-preview img-fluid rounded" 
                                                     onclick="showImageModal(this.src)">
                                            </div>
                                            <small class="text-success">Nomor: <?php echo htmlspecialchars($log["pl_segel_$i"] ?? 'N/A'); ?></small>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <!-- Dokumen -->
                                <?php if (!empty($log['pl_doc_sampel'])): ?>
                                    <div class="col-4">
                                        <small class="text-muted">Sampel BBM:</small>
                                        <div>
                                            <img src="<?php echo htmlspecialchars($log['pl_doc_sampel']); ?>" 
                                                 class="photo-preview img-fluid rounded" 
                                                 onclick="showImageModal(this.src)">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($log['pl_doc_do'])): ?>
                                    <div class="col-4">
                                        <small class="text-muted">Delivery Order:</small>
                                        <div>
                                            <img src="<?php echo htmlspecialchars($log['pl_doc_do']); ?>" 
                                                 class="photo-preview img-fluid rounded" 
                                                 onclick="showImageModal(this.src)">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($log['pl_doc_suratjalan'])): ?>
                                    <div class="col-4">
                                        <small class="text-muted">Surat Jalan:</small>
                                        <div>
                                            <img src="<?php echo htmlspecialchars($log['pl_doc_suratjalan']); ?>" 
                                                 class="photo-preview img-fluid rounded" 
                                                 onclick="showImageModal(this.src)">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Driver Data -->
            <?php if (!empty($log['dr_created_at'])): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-truck"></i> Data Driver</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted">Diisi oleh:</small>
                                    <div class="fw-bold"><?php echo htmlspecialchars($log['dr_creator_name'] ?? 'N/A'); ?></div>
                                </div>
                                
                                <!-- Loading Data -->
                                <div class="col-12"><hr><h6 class="text-primary">Data Loading:</h6></div>
                                <div class="col-6">
                                    <small class="text-muted">Mulai Loading:</small>
                                    <div><?php echo $log['dr_loading_start'] ? date('d/m/Y H:i', strtotime($log['dr_loading_start'])) : 'N/A'; ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Selesai Loading:</small>
                                    <div><?php echo $log['dr_loading_end'] ? date('d/m/Y H:i', strtotime($log['dr_loading_end'])) : 'N/A'; ?></div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Waktu Keluar Pertamina:</small>
                                    <div><?php echo $log['dr_waktu_keluar_pertamina'] ? date('d/m/Y H:i', strtotime($log['dr_waktu_keluar_pertamina'])) : 'N/A'; ?></div>
                                </div>
                                
                                <!-- Unloading Data -->
                                <?php if (!empty($log['dr_unload_start'])): ?>
                                    <div class="col-12"><hr><h6 class="text-primary">Data Unloading:</h6></div>
                                    <div class="col-6">
                                        <small class="text-muted">Mulai Unloading:</small>
                                        <div><?php echo date('d/m/Y H:i', strtotime($log['dr_unload_start'])); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Selesai Unloading:</small>
                                        <div><?php echo $log['dr_unload_end'] ? date('d/m/Y H:i', strtotime($log['dr_unload_end'])) : 'N/A'; ?></div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">Lokasi Unloading:</small>
                                        <div><?php echo htmlspecialchars($log['dr_unload_location'] ?? 'N/A'); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Driver Photos -->
                                <div class="col-12"><hr><h6 class="text-primary">Foto Segel (Versi Driver):</h6></div>
                                <?php for($i = 1; $i <= 4; $i++): ?>
                                    <?php if (!empty($log["dr_segel_photo_$i"])): ?>
                                        <div class="col-6">
                                            <small class="text-muted">Foto Segel <?php echo $i; ?>:</small>
                                            <div>
                                                <img src="<?php echo htmlspecialchars($log["dr_segel_photo_$i"]); ?>" 
                                                     class="photo-preview img-fluid rounded" 
                                                     onclick="showImageModal(this.src)">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Continue with other roles... -->
            <?php if (!empty($log['pd_created_at'])): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-building"></i> Data Pengawas Depo</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted">Diisi oleh:</small>
                                    <div class="fw-bold"><?php echo htmlspecialchars($log['pd_creator_name'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Waktu Tiba:</small>
                                    <div><?php echo $log['pd_arrived_at'] ? date('d/m/Y H:i', strtotime($log['pd_arrived_at'])) : 'N/A'; ?></div>
                                </div>
                                
                                <!-- Travel Duration Check -->
                                <?php if (!empty($log['dr_waktu_keluar_pertamina']) && !empty($log['pd_arrived_at'])): ?>
                                    <?php 
                                    $keluar = new DateTime($log['dr_waktu_keluar_pertamina']);
                                    $tiba = new DateTime($log['pd_arrived_at']);
                                    $diff = $keluar->diff($tiba);
                                    $hours = $diff->h + ($diff->days * 24);
                                    ?>
                                    <div class="col-12">
                                        <small class="text-muted">Durasi Perjalanan:</small>
                                        <div class="<?php echo $hours > 7 ? 'text-warning fw-bold' : ''; ?>">
                                            <?php echo $hours; ?> jam <?php echo $diff->i; ?> menit
                                            <?php if ($hours > 7): ?>
                                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($hours > 7 && !empty($log['pd_alasan_lebih_7jam'])): ?>
                                        <div class="col-12">
                                            <small class="text-muted">Alasan Lebih dari 7 Jam:</small>
                                            <div class="alert alert-warning p-2">
                                                <?php echo nl2br(htmlspecialchars($log['pd_alasan_lebih_7jam'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- Depo Photos -->
                                <?php for($i = 1; $i <= 4; $i++): ?>
                                    <?php if (!empty($log["pd_foto_kondisi_$i"])): ?>
                                        <div class="col-6">
                                            <small class="text-muted">Kondisi Segel <?php echo $i; ?>:</small>
                                            <div>
                                                <img src="<?php echo htmlspecialchars($log["pd_foto_kondisi_$i"]); ?>" 
                                                     class="photo-preview img-fluid rounded" 
                                                     onclick="showImageModal(this.src)">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Fuelman Data -->
            <?php if (!empty($log['fm_created_at'])): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="bi bi-droplet"></i> Data Fuelman</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted">Diisi oleh:</small>
                                    <div class="fw-bold"><?php echo htmlspecialchars($log['fm_creator_name'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Mulai Unloading:</small>
                                    <div><?php echo $log['fm_unload_start'] ? date('d/m/Y H:i', strtotime($log['fm_unload_start'])) : 'N/A'; ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Selesai Unloading:</small>
                                    <div><?php echo $log['fm_unload_end'] ? date('d/m/Y H:i', strtotime($log['fm_unload_end'])) : 'N/A'; ?></div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Flowmeter:</small>
                                    <div><?php echo htmlspecialchars($log['fm_flowmeter'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">FM Awal:</small>
                                    <div><?php echo $log['fm_awal'] ?? 'N/A'; ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">FM Akhir:</small>
                                    <div><?php echo $log['fm_akhir'] ?? 'N/A'; ?></div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Density:</small>
                                    <div><?php echo $log['fm_fuel_density'] ?? 'N/A'; ?></div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Temperature:</small>
                                    <div><?php echo $log['fm_fuel_temp'] ?? 'N/A'; ?>°C</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">FAME:</small>
                                    <div><?php echo $log['fm_fuel_fame'] ?? 'N/A'; ?>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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

<style>
@media print {
    .btn, .card-header { 
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    .printing .d-flex.gap-2 {
        display: none !important;
    }
    
    .photo-preview {
        max-height: 150px !important;
    }
}

.photo-preview {
    cursor: pointer;
    transition: transform 0.2s;
}

.photo-preview:hover {
    transform: scale(1.05);
}
</style>

<?php require_once 'includes/footer.php'; ?>
