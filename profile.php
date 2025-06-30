<?php
require_once __DIR__ . '/config/db.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
$user = null;

if ($username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = "Profil Saya";
include __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow rounded-4 border-0">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i> Profil Pengguna
                    </h5>
                </div>
                <div class="card-body">
<?php if ($user): ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-person-fill fs-3"></i>
                            </div>
                            <div class="ms-3">
                                <h4 class="mb-0"><?= htmlspecialchars($user['full_name']) ?></h4>
                                <small class="text-muted"><?= htmlspecialchars($user['role']) ?> | <?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?></small>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>👤 Username:</strong> <?= htmlspecialchars($user['username']) ?>
                            </li>
                            <li class="list-group-item">
                                <strong>📧 Email:</strong> <?= htmlspecialchars($user['email'] ?? '-') ?>
                            </li>
                            <li class="list-group-item">
                                <strong>📞 Telepon:</strong> <?= htmlspecialchars($user['phone'] ?? '-') ?>
                            </li>
                            <li class="list-group-item">
                                <strong>🗓️ Dibuat:</strong> <?= htmlspecialchars($user['created_at']) ?>
                            </li>
                            <li class="list-group-item">
                                <strong>🔄 Update Terakhir:</strong> <?= htmlspecialchars($user['updated_at']) ?>
                            </li>
                        </ul>
<?php else: ?>
                        <div class="alert alert-danger mt-3">Data pengguna tidak ditemukan.</div>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include __DIR__ . '/includes/footer.php'; ?>
