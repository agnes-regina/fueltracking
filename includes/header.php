<?php
$base_url = "/fueltracking/"; // Adjust this to your base URL
//$base_url = "/"; // Adjust this to your base URL
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Fuel Transport Tracking System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #f093fb;
            --success-color: #4ade80;
            --danger-color: #f87171;
            --warning-color: #fbbf24;
            --info-color: #60a5fa;
            --purple-color: #a855f7;
            --teal-color: #14b8a6;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            background: linear-gradient(135deg, var(--primary-color), var(--purple-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-nav .nav-link {
            color: var(--primary-color) !important;
            font-weight: 600;
            padding: 0.75rem 1rem !important;
            border-radius: 10px;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white !important;
            transform: translateY(-2px);
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--purple-color));
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 1.5rem;
            border: none;
        }
        
        .btn {
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            min-height: 50px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color), var(--teal-color));
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), #f59e0b);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #ef4444);
        }
        
        .form-control, .form-select {
            border-radius: 15px;
            border: 2px solid #e5e7eb;
            padding: 1rem;
            font-size: 1.1rem;
            min-height: 50px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }
        
        .status-badge {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1rem;
            display: inline-block;
            min-width: 150px;
            text-align: center;
        }
        
        .status-waiting_pengawas { 
            background: linear-gradient(135deg, var(--warning-color), #f59e0b); 
            color: white; 
        }
        .status-waiting_driver { 
            background: linear-gradient(135deg, var(--info-color), #3b82f6); 
            color: white; 
        }
        .status-driver_loading_done { 
            background: linear-gradient(135deg, var(--info-color),rgb(255, 0, 111)); 
            color: white; 
        }
        .status-waiting_depo { 
            background: linear-gradient(135deg, var(--purple-color), #9333ea); 
            color: white; 
        }
        .status-waiting_fuelman { 
            background: linear-gradient(135deg, #f97316, #ea580c); 
            color: white; 
        }
        .status-done { 
            background: linear-gradient(135deg, var(--success-color), var(--teal-color)); 
            color: white; 
        }
        
        .photo-preview {
            max-width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            margin: 0.75rem 0;
        }
        
        .main-container {
            padding: 2rem 1rem;
            min-height: calc(100vh - 80px);
        }
        
        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8));
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .stats-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--purple-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .alert {
            border-radius: 15px;
            padding: 1.5rem;
            border: none;
            font-size: 1.1rem;
        }
        
        .table {
            border-radius: 15px;
            overflow: hidden;
            background: white;
        }
        
        .table th {
            background: linear-gradient(135deg, var(--primary-color), var(--purple-color));
            color: white;
            font-weight: 600;
            padding: 1rem;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        @media (max-width: 768px) {
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .form-control, .form-select {
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .stats-number {
                font-size: 2rem;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .photo-preview {
                max-height: 150px;
            }
        }
        
        .progress-indicator {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
        }
        
        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .progress-step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 3px;
            background: #e5e7eb;
            z-index: 1;
        }
        
        .progress-step:last-child::before {
            display: none;
        }
        
        .progress-step.active::before {
            background: var(--success-color);
        }
        
        .progress-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            position: relative;
            z-index: 2;
            font-weight: 600;
        }
        
        .progress-step.active .progress-circle {
            background: var(--success-color);
            color: white;
        }
        
        .progress-step.current .progress-circle {
            background: var(--primary-color);
            color: white;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?= $base_url ?>">
                <i class="bi bi-fuel-pump"></i> Fuel Transport Tracking
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
<?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
<?php if (hasRole('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_url ?>admin/users.php">
                                    <i class="bi bi-people"></i> Kelola User
                                </a>
                            </li>
<?php endif; ?>
                        
<?php if (hasRole('pengawas_transportir')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $base_url ?>pengawas/create.php">
                                    <i class="bi bi-plus-circle"></i> Buat Pengiriman
                                </a>
                            </li>
<?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>logs.php">
                                <i class="bi bi-list-ul"></i> Data Pengiriman
                            </a>
                        </li>
<?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
<?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                        <a class="nav-link" href="<?= $base_url ?>profile.php">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?= $base_url ?>logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>

<?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_url ?>login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
<?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="main-container">
        <div class="container-fluid">
