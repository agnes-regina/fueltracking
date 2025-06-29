
<?php
require_once 'config/db.php';
requireLogin();

$pageTitle = 'Detail Pengiriman - Fuel Transport Tracking System';

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: logs.php');
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT fl.*, 
               u1.full_name as transportir_name,
               u2.full_name as lapangan_name,
               u3.full_name as driver_user_name,
               u4.full_name as depo_name,
               u5.full_name as fuelman_name
        FROM fuel_logs fl
        LEFT JOIN users u1 ON fl.pt_created_by = u1.id
        LEFT JOIN users u2 ON fl.pl_created_by = u2.id  
        LEFT JOIN users u3 ON fl.dr_created_by = u3.id
        LEFT JOIN users u4 ON fl.pd_created_by = u4.id
        LEFT JOIN users u5 ON fl.fm_created_by = u5.id
        WHERE fl.id = ?
    ");
    $stmt->execute([$id]);
    $log = $stmt->fetch();
    
    if (!$log) {
        header('Location: logs.php');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-info-circle"></i> Detail Pengiriman #<?php echo $log['id']; ?>
                </h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php else: ?>
                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="bi bi-truck"></i> Informasi Dasar</h5>
                            <table class="table">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>#<?php echo $log['id']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nomor Unit:</strong></td>
                                    <td><?php echo htmlspecialchars($log['nomor_unit']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Driver:</strong></td>
                                    <td><?php echo htmlspecialchars($log['driver_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $log['status_progress']; ?>">
                                            <?php echo $statusLabels[$log['status_progress']]; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Dibuat:</strong></td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="bi bi-bar-chart"></i> Progress Timeline</h5>
                            <div class="timeline">
                                <?php if ($log['pt_created_at']): ?>
                                    <div class="timeline-item completed">
                                        <i class="bi bi-check-circle"></i>
                                        <div>
                                            <strong>Pengawas Transportir</strong><br>
                                            <small><?php echo date('d/m/Y H:i', strtotime($log['pt_created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($log['pl_created_at']): ?>
                                    <div class="timeline-item completed">
                                        <i class="bi bi-check-circle"></i>
                                        <div>
                                            <strong>Pengawas Lapangan</strong><br>
                                            <small><?php echo date('d/m/Y H:i', strtotime($log['pl_created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($log['dr_created_at']): ?>
                                    <div class="timeline-item completed">
                                        <i class="bi bi-check-circle"></i>
                                        <div>
                                            <strong>Driver</strong><br>
                                            <small><?php echo date('d/m/Y H:i', strtotime($log['dr_created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($log['pd_created_at']): ?>
                                    <div class="timeline-item completed">
                                        <i class="bi bi-check-circle"></i>
                                        <div>
                                            <strong>Pengawas Depo</strong><br>
                                            <small><?php echo date('d/m/Y H:i', strtotime($log['pd_created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($log['fm_created_at']): ?>
                                    <div class="timeline-item completed">
                                        <i class="bi bi-check-circle"></i>
                                        <div>
                                            <strong>Fuelman</strong><br>
                                            <small><?php echo date('d/m/Y H:i', strtotime($log['fm_created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pengawas Lapangan Data -->
                    <?php if ($log['pl_created_at']): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5><i class="bi bi-clipboard-check"></i> Data Pengawas Lapangan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Loading Start:</strong></td>
                                                <td><?php echo $log['pl_loading_start'] ? date('d/m/Y H:i', strtotime($log['pl_loading_start'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Loading End:</strong></td>
                                                <td><?php echo $log['pl_loading_end'] ? date('d/m/Y H:i', strtotime($log['pl_loading_end'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lokasi:</strong></td>
                                                <td><?php echo htmlspecialchars($log['pl_loading_location'] ?? '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Waktu Keluar:</strong></td>
                                                <td><?php echo $log['pl_waktu_keluar_pertamina'] ? date('d/m/Y H:i', strtotime($log['pl_waktu_keluar_pertamina'])) : '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Nomor Segel:</h6>
                                        <ul>
                                            <?php for($i = 1; $i <= 4; $i++): ?>
                                                <?php if ($log["pl_segel_$i"]): ?>
                                                    <li>Segel <?php echo $i; ?>: <?php echo htmlspecialchars($log["pl_segel_$i"]); ?></li>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Photos -->
                                <?php 
                                $photos = ['pl_segel_photo_1', 'pl_segel_photo_2', 'pl_segel_photo_3', 'pl_segel_photo_4'];
                                $hasPhotos = false;
                                foreach($photos as $photo) {
                                    if ($log[$photo]) {
                                        $hasPhotos = true;
                                        break;
                                    }
                                }
                                ?>
                                
                                <?php if ($hasPhotos): ?>
                                    <h6>Foto Segel:</h6>
                                    <div class="row">
                                        <?php foreach($photos as $photo): ?>
                                            <?php if ($log[$photo]): ?>
                                                <div class="col-md-3 mb-2">
                                                    <img src="<?php echo htmlspecialchars($log[$photo]); ?>" 
                                                         class="photo-preview img-fluid" alt="Segel Photo">
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Documents -->
                                <?php 
                                $docs = [
                                    'pl_doc_sampel' => 'Sampel BBM',
                                    'pl_doc_do' => 'Delivery Order', 
                                    'pl_doc_suratjalan' => 'Surat Jalan'
                                ];
                                ?>
                                
                                <h6>Dokumen:</h6>
                                <div class="row">
                                    <?php foreach($docs as $docField => $docName): ?>
                                        <?php if ($log[$docField]): ?>
                                            <div class="col-md-4 mb-2">
                                                <strong><?php echo $docName; ?>:</strong><br>
                                                <img src="<?php echo htmlspecialchars($log[$docField]); ?>" 
                                                     class="photo-preview img-fluid" alt="<?php echo $docName; ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Driver Data -->
                    <?php if ($log['dr_created_at']): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5><i class="bi bi-person-badge"></i> Data Driver</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Loading Info:</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Loading Start:</strong></td>
                                                <td><?php echo $log['dr_loading_start'] ? date('d/m/Y H:i', strtotime($log['dr_loading_start'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Loading End:</strong></td>
                                                <td><?php echo $log['dr_loading_end'] ? date('d/m/Y H:i', strtotime($log['dr_loading_end'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lokasi Loading:</strong></td>
                                                <td><?php echo htmlspecialchars($log['dr_loading_location'] ?? '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Waktu Keluar:</strong></td>
                                                <td><?php echo $log['dr_waktu_keluar_pertamina'] ? date('d/m/Y H:i', strtotime($log['dr_waktu_keluar_pertamina'])) : '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Unloading Info:</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Unload Start:</strong></td>
                                                <td><?php echo $log['dr_unload_start'] ? date('d/m/Y H:i', strtotime($log['dr_unload_start'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Unload End:</strong></td>
                                                <td><?php echo $log['dr_unload_end'] ? date('d/m/Y H:i', strtotime($log['dr_unload_end'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lokasi Unload:</strong></td>
                                                <td><?php echo htmlspecialchars($log['dr_unload_location'] ?? '-'); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Driver Photos -->
                                <?php 
                                $drPhotos = ['dr_segel_photo_1', 'dr_segel_photo_2', 'dr_segel_photo_3', 'dr_segel_photo_4'];
                                $drHasPhotos = false;
                                foreach($drPhotos as $photo) {
                                    if ($log[$photo]) {
                                        $drHasPhotos = true;
                                        break;
                                    }
                                }
                                ?>
                                
                                <?php if ($drHasPhotos): ?>
                                    <h6>Foto Segel (Driver):</h6>
                                    <div class="row">
                                        <?php foreach($drPhotos as $photo): ?>
                                            <?php if ($log[$photo]): ?>
                                                <div class="col-md-3 mb-2">
                                                    <img src="<?php echo htmlspecialchars($log[$photo]); ?>" 
                                                         class="photo-preview img-fluid" alt="Driver Segel Photo">
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Driver Documents -->
                                <?php 
                                $drDocs = [
                                    'dr_doc_do' => 'Delivery Order',
                                    'dr_doc_surat_pertamina' => 'Surat Pertamina',
                                    'dr_doc_sampel_bbm' => 'Sampel BBM'
                                ];
                                ?>
                                
                                <h6>Dokumen Driver:</h6>
                                <div class="row">
                                    <?php foreach($drDocs as $docField => $docName): ?>
                                        <?php if ($log[$docField]): ?>
                                            <div class="col-md-4 mb-2">
                                                <strong><?php echo $docName; ?>:</strong><br>
                                                <img src="<?php echo htmlspecialchars($log[$docField]); ?>" 
                                                     class="photo-preview img-fluid" alt="<?php echo $docName; ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Pengawas Depo Data -->
                    <?php if ($log['pd_created_at']): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5><i class="bi bi-building"></i> Data Pengawas Depo</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Waktu Tiba:</strong></td>
                                                <td><?php echo $log['pd_arrived_at'] ? date('d/m/Y H:i', strtotime($log['pd_arrived_at'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Berangkat ke MSF:</strong></td>
                                                <td><?php echo $log['pd_goto_msf'] ? date('d/m/Y H:i', strtotime($log['pd_goto_msf'])) : '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Depo Photos -->
                                <?php 
                                $pdPhotos = [
                                    'pd_foto_kondisi_1' => 'Kondisi Segel 1',
                                    'pd_foto_kondisi_2' => 'Kondisi Segel 2', 
                                    'pd_foto_kondisi_3' => 'Kondisi Segel 3',
                                    'pd_foto_kondisi_4' => 'Kondisi Segel 4',
                                    'pd_foto_sib' => 'SIB',
                                    'pd_foto_ftw' => 'FTW',
                                    'pd_foto_p2h' => 'P2H'
                                ];
                                ?>
                                
                                <h6>Dokumentasi:</h6>
                                <div class="row">
                                    <?php foreach($pdPhotos as $photoField => $photoName): ?>
                                        <?php if ($log[$photoField]): ?>
                                            <div class="col-md-3 mb-3">
                                                <strong><?php echo $photoName; ?>:</strong><br>
                                                <img src="<?php echo htmlspecialchars($log[$photoField]); ?>" 
                                                     class="photo-preview img-fluid" alt="<?php echo $photoName; ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Fuelman Data -->
                    <?php if ($log['fm_created_at']): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5><i class="bi bi-droplet"></i> Data Fuelman</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Unloading Info:</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Unload Start:</strong></td>
                                                <td><?php echo $log['fm_unload_start'] ? date('d/m/Y H:i', strtotime($log['fm_unload_start'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Unload End:</strong></td>
                                                <td><?php echo $log['fm_unload_end'] ? date('d/m/Y H:i', strtotime($log['fm_unload_end'])) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lokasi:</strong></td>
                                                <td><?php echo htmlspecialchars($log['fm_location'] ?? '-'); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Flowmeter Info:</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Flowmeter:</strong></td>
                                                <td><?php echo htmlspecialchars($log['fm_flowmeter'] ?? '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Serial:</strong></td>
                                                <td><?php echo htmlspecialchars($log['fm_serial'] ?? '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>FM Awal:</strong></td>
                                                <td><?php echo $log['fm_awal'] ?? '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>FM Akhir:</strong></td>
                                                <td><?php echo $log['fm_akhir'] ?? '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Data Bahan Bakar:</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td><strong>Density:</strong></td>
                                                <td><?php echo $log['fm_fuel_density'] ?? '-'; ?></td>
                                                <td><strong>Temperature:</strong></td>
                                                <td><?php echo $log['fm_fuel_temp'] ?? '-'; ?></td>
                                                <td><strong>FAME:</strong></td>
                                                <td><?php echo $log['fm_fuel_fame'] ?? '-'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Fuelman Photos -->
                                <h6>Foto Segel Awal:</h6>
                                <div class="row">
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <?php if ($log["fm_segel_photo_awal_$i"]): ?>
                                            <div class="col-md-3 mb-2">
                                                <strong>Segel Awal <?php echo $i; ?>:</strong><br>
                                                <img src="<?php echo htmlspecialchars($log["fm_segel_photo_awal_$i"]); ?>" 
                                                     class="photo-preview img-fluid" alt="Segel Awal <?php echo $i; ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                
                                <h6>Foto Akhir & Kejernihan:</h6>
                                <div class="row">
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <?php if ($log["fm_photo_akhir_$i"]): ?>
                                            <div class="col-md-3 mb-2">
                                                <strong>Tanki Kosong <?php echo $i; ?>:</strong><br>
                                                <img src="<?php echo htmlspecialchars($log["fm_photo_akhir_$i"]); ?>" 
                                                     class="photo-preview img-fluid" alt="Tanki Kosong <?php echo $i; ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <?php if ($log['fm_photo_kejernihan']): ?>
                                        <div class="col-md-3 mb-2">
                                            <strong>Kejernihan BBM:</strong><br>
                                            <img src="<?php echo htmlspecialchars($log['fm_photo_kejernihan']); ?>" 
                                                 class="photo-preview img-fluid" alt="Kejernihan BBM">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="logs.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke List
                                </a>
                                
                                <?php if (hasRole('admin')): ?>
                                    <a href="edit.php?id=<?php echo $log['id']; ?>" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Role-specific action buttons -->
                                <?php if (hasRole('pengawas_lapangan') && $log['status_progress'] == 'waiting_pengawas'): ?>
                                    <a href="lapangan/form.php?id=<?php echo $log['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-clipboard-check"></i> Input Data Lapangan
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (hasRole('driver') && $log['status_progress'] == 'waiting_driver'): ?>
                                    <a href="driver/form.php?id=<?php echo $log['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-truck"></i> Input Data Driver
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (hasRole('pengawas_depo') && $log['status_progress'] == 'waiting_depo'): ?>
                                    <a href="depo/form.php?id=<?php echo $log['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-building"></i> Input Data Depo
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (hasRole('fuelman') && $log['status_progress'] == 'waiting_fuelman'): ?>
                                    <a href="fuelman/form.php?id=<?php echo $log['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-droplet"></i> Input Data Fuelman
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    padding-left: 0;
}

.timeline-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.timeline-item.completed {
    background: #d4edda;
    color: #155724;
}

.timeline-item i {
    margin-right: 1rem;
    font-size: 1.2rem;
}

.photo-preview {
    max-height: 150px;
    cursor: pointer;
    transition: transform 0.2s;
}

.photo-preview:hover {
    transform: scale(1.05);
}
</style>

<script>
// Photo modal view
document.addEventListener('DOMContentLoaded', function() {
    const photos = document.querySelectorAll('.photo-preview');
    photos.forEach(photo => {
        photo.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Preview Foto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${this.src}" class="img-fluid" alt="Preview">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
