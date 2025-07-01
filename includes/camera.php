<script>
let currentCameraInput = null;
let currentCameraPreview = null;
let cameraStream = null;

// Enhanced camera functionality
function openCameraModal(inputId, previewId) {
    currentCameraInput = document.getElementById(inputId);
    currentCameraPreview = document.getElementById(previewId);
    
    const modal = new bootstrap.Modal(document.getElementById('cameraModal'));
    modal.show();
    
    // Start camera with enhanced options
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment',
            // width: { ideal: 1920, max: 1920 },
            // height: { ideal: 1080, max: 1080 },
            width: { ideal: 640 },
            height: { ideal: 480 },
            aspectRatio: { ideal: 16/9 }
        } 
    })
    .then(function(stream) {
        cameraStream = stream;
        const video = document.getElementById('cameraVideo');
        video.srcObject = stream;
        video.play();
        
        // Auto-focus if supported
        const track = stream.getVideoTracks()[0];
        if (track.getCapabilities && track.getCapabilities().focusMode) {
            track.applyConstraints({
                advanced: [{ focusMode: 'continuous' }]
            });
        }
    })
    .catch(function(err) {
        console.error('Error accessing camera:', err);
        alert('Tidak dapat mengakses kamera: ' + err.message);
    });
}

// Enhanced capture photo function
function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    
    if (video.videoWidth === 0 || video.videoHeight === 0) {
        alert('Kamera belum siap. Silakan tunggu sebentar.');
        return;
    }
    
    // // Set canvas size to video size
    // canvas.width = video.videoWidth;
    // canvas.height = video.videoHeight;
    
    // // Draw video frame to canvas
    // context.drawImage(video, 0, 0);
    

    canvas.width = 640;
    canvas.height = 480;
    context.drawImage(video, 0, 0, 640, 480);
    
    // Convert to blob with high quality
    canvas.toBlob(function(blob) {
        if (!blob) {
            alert('Gagal mengambil foto. Silakan coba lagi.');
            return;
        }
        
        const file = new File([blob], 'camera-photo-' + Date.now() + '.jpg', { 
            type: 'image/jpeg' 
        });
        
        // Create FileList and assign to input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        currentCameraInput.files = dataTransfer.files;
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            currentCameraPreview.src = e.target.result;
            currentCameraPreview.style.display = 'block';
            currentCameraPreview.classList.add('img-thumbnail');
        }
        reader.readAsDataURL(file);
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();
        
        // Trigger change event
        currentCameraInput.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Show success feedback
        showToast('Foto berhasil diambil!', 'success');
        
    }, 'image/jpeg', 0.9);
}

// Stop camera when modal is closed
document.getElementById('cameraModal')?.addEventListener('hidden.bs.modal', function() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => {
            track.stop();
        });
        cameraStream = null;
    }
    
    // Clear video source
    const video = document.getElementById('cameraVideo');
    if (video) {
        video.srcObject = null;
    }
});

// Enhanced preview function
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('File harus berupa gambar!');
            input.value = '';
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 5MB.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            preview.classList.add('img-thumbnail');
            
            // Add animation
            preview.style.opacity = '0';
            preview.style.transform = 'scale(0.8)';
            setTimeout(() => {
                preview.style.transition = 'all 0.3s ease';
                preview.style.opacity = '1';
                preview.style.transform = 'scale(1)';
            }, 10);
        }
        reader.readAsDataURL(file);
    }
}

// Add camera buttons to existing file inputs
function addCameraButtons() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    fileInputs.forEach(input => {
        if (!input.dataset.cameraAdded && !input.parentNode.querySelector('.camera-btn')) {
            const previewId = input.id.replace(/input|file/, 'preview') || input.id + '_preview';
            
            // Create camera button
            const cameraBtn = document.createElement('button');
            cameraBtn.type = 'button';
            cameraBtn.className = 'btn btn-outline-primary w-100 mt-2 camera-btn';
            cameraBtn.innerHTML = '<i class="bi bi-camera me-2"></i>Buka Kamera';
            cameraBtn.onclick = () => openCameraModal(input.id, previewId);
            
            // Insert after input
            input.parentNode.insertBefore(cameraBtn, input.nextSibling);
            input.dataset.cameraAdded = 'true';
        }
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'info'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1060';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Auto-add camera buttons when page loads
document.addEventListener('DOMContentLoaded', addCameraButtons);

// Switch camera (front/back) if available
function switchCamera() {
    if (cameraStream) {
        const videoTrack = cameraStream.getVideoTracks()[0];
        const currentFacingMode = videoTrack.getSettings().facingMode;
        const newFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        
        // Stop current stream
        cameraStream.getTracks().forEach(track => track.stop());
        
        // Start new stream with different camera
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: newFacingMode,
                width: { ideal: 1920, max: 1920 },
                height: { ideal: 1080, max: 1080 }
            } 
        })
        .then(function(stream) {
            cameraStream = stream;
            const video = document.getElementById('cameraVideo');
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.error('Error switching camera:', err);
            // Fallback to original camera
            openCameraModal(currentCameraInput.id, currentCameraPreview.id);
        });
    }
}
</script>

<!-- Enhanced Camera Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <h5 class="modal-title">
                    <i class="bi bi-camera fs-4 me-2"></i>Ambil Foto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4" style="background: linear-gradient(145deg, #f8fafc 0%, #ffffff 100%);">
                <div class="position-relative mb-4">
                    <video id="cameraVideo" autoplay playsinline 
                           style="width: 100%; max-width: 500px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);"></video>
                    <div class="position-absolute top-50 start-50 translate-middle" 
                         style="pointer-events: none; border: 3px solid rgba(255,255,255,0.8); width: 200px; height: 200px; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.3);"></div>
                    
                    <!-- Camera switch button -->
                    <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 rounded-circle" 
                            onclick="switchCamera()" style="width: 45px; height: 45px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
                
                <canvas id="cameraCanvas" style="display: none;"></canvas>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <button type="button" class="btn btn-lg px-4 py-3" onclick="capturePhoto()"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 50px; color: white; font-weight: 600; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3); transition: all 0.3s ease;">
                        <i class="bi bi-camera fs-5 me-2"></i>Ambil Foto
                    </button>
                    <button type="button" class="btn btn-lg px-4 py-3" data-bs-dismiss="modal"
                            style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); border: 2px solid #cbd5e1; border-radius: 50px; color: #64748b; font-weight: 600;">
                        <i class="bi bi-x fs-5 me-2"></i>Batal
                    </button>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Pastikan objek berada dalam frame putih untuk hasil terbaik
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.camera-btn:hover {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
}

#cameraVideo {
    transform: scaleX(-1); /* Mirror effect for better UX */
}

.toast-container .toast {
    border-radius: 15px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
}
</style>
