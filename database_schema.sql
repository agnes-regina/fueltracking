
-- Database Schema for Fuel Transport Tracking System
CREATE DATABASE fuel_tracking;
USE fuel_tracking;

-- Users table for authentication and role management
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pengawas_transportir', 'pengawas_lapangan', 'driver', 'pengawas_depo', 'fuelman', 'gl_pama') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Main fuel logs table with all role-specific columns
CREATE TABLE fuel_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_unit VARCHAR(50) NOT NULL,
    driver_name VARCHAR(100) NOT NULL,
    status_progress ENUM('waiting_pengawas', 'waiting_driver', 'waiting_depo', 'waiting_fuelman', 'done') DEFAULT 'waiting_pengawas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Pengawas Transportir columns (prefix pt_)
    pt_driver_name VARCHAR(100),
    pt_unit_number VARCHAR(50),
    pt_created_by INT,
    pt_created_at TIMESTAMP NULL,
    
    -- Pengawas Lapangan columns (prefix pl_)
    pl_loading_start DATETIME,
    pl_loading_end DATETIME,
    pl_loading_location TEXT,
    pl_segel_photo_1 TEXT,
    pl_segel_photo_2 TEXT,
    pl_segel_photo_3 TEXT,
    pl_segel_photo_4 TEXT,
    pl_segel_1 VARCHAR(50),
    pl_segel_2 VARCHAR(50),
    pl_segel_3 VARCHAR(50),
    pl_segel_4 VARCHAR(50),
    pl_doc_sampel TEXT,
    pl_doc_do TEXT,
    pl_doc_suratjalan TEXT,
    pl_waktu_keluar_pertamina DATETIME,
    pl_created_by INT,
    pl_created_at TIMESTAMP NULL,
    
    -- Driver columns (prefix dr_)
    dr_loading_start DATETIME,
    dr_loading_end DATETIME,
    dr_loading_location TEXT,
    dr_segel_photo_1 TEXT,
    dr_segel_photo_2 TEXT,
    dr_segel_photo_3 TEXT,
    dr_segel_photo_4 TEXT,
    dr_doc_do TEXT,
    dr_doc_surat_pertamina TEXT,
    dr_doc_sampel_bbm TEXT,
    dr_waktu_keluar_pertamina DATETIME,
    dr_unload_start DATETIME,
    dr_unload_end DATETIME,
    dr_unload_location TEXT,
    dr_created_by INT,
    dr_created_at TIMESTAMP NULL,
    
    -- Pengawas Depo columns (prefix pd_)
    pd_arrived_at DATETIME,
    pd_foto_kondisi_1 TEXT,
    pd_foto_kondisi_2 TEXT,
    pd_foto_kondisi_3 TEXT,
    pd_foto_kondisi_4 TEXT,
    pd_foto_sib TEXT,
    pd_foto_ftw TEXT,
    pd_foto_p2h TEXT,
    pd_goto_msf DATETIME,
    pd_created_by INT,
    pd_created_at TIMESTAMP NULL,
    
    -- Fuelman columns (prefix fm_)
    fm_unload_start DATETIME,
    fm_unload_end DATETIME,
    fm_location TEXT,
    fm_segel_photo_awal_1 TEXT,
    fm_segel_photo_awal_2 TEXT,
    fm_segel_photo_awal_3 TEXT,
    fm_segel_photo_awal_4 TEXT,
    fm_photo_akhir_1 TEXT,
    fm_photo_akhir_2 TEXT,
    fm_photo_akhir_3 TEXT,
    fm_photo_akhir_4 TEXT,
    fm_photo_kejernihan TEXT,
    fm_flowmeter VARCHAR(100),
    fm_serial VARCHAR(100),
    fm_awal FLOAT,
    fm_akhir FLOAT,
    fm_fuel_density FLOAT,
    fm_fuel_temp FLOAT,
    fm_fuel_fame FLOAT,
    fm_created_by INT,
    fm_created_at TIMESTAMP NULL,
    
    -- Foreign key constraints
    FOREIGN KEY (pt_created_by) REFERENCES users(id),
    FOREIGN KEY (pl_created_by) REFERENCES users(id),
    FOREIGN KEY (dr_created_by) REFERENCES users(id),
    FOREIGN KEY (pd_created_by) REFERENCES users(id),
    FOREIGN KEY (fm_created_by) REFERENCES users(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role, full_name, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', 'admin@fueltrack.com');

-- Insert sample users for testing
INSERT INTO users (username, password, role, full_name, email) VALUES 
('pengawas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHeWG/igi', 'pengawas_transportir', 'Pengawas Transportir 1', 'pengawas1@fueltrack.com'),
('lapangan1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHeWG/igi', 'pengawas_lapangan', 'Pengawas Lapangan 1', 'lapangan1@fueltrack.com'),
('driver1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'Driver Utama', 'driver1@fueltrack.com'),
('depo1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pengawas_depo', 'Pengawas Depo 1', 'depo1@fueltrack.com'),
('fuelman1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fuelman', 'Fuelman 1', 'fuelman1@fueltrack.com'),
('glpama1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gl_pama', 'GL PAMA 1', 'glpama1@fueltrack.com');

-- Create indexes for better performance
CREATE INDEX idx_status_progress ON fuel_logs(status_progress);
CREATE INDEX idx_created_at ON fuel_logs(created_at);
CREATE INDEX idx_nomor_unit ON fuel_logs(nomor_unit);
CREATE INDEX idx_user_role ON users(role);
