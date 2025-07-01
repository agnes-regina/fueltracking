
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
    
    // Start camera
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment',
            width: { ideal: 1280 },
            height: { ideal: 720 }
        } 
    })
    .then(function(stream) {
        cameraStream = stream;
        const video = document.getElementById('cameraVideo');
        video.srcObject = stream;
        video.play();
    })
    .catch(function(err) {
        console.error('Error accessing camera:', err);
        alert('Tidak dapat mengakses kamera: ' + err.message);
    });
}

// Capture photo function
function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    
    // Set canvas size to video size
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    context.drawImage(video, 0, 0);
    
    // Convert to blob and create file
    canvas.toBlob(function(blob) {
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
        }
        reader.readAsDataURL(file);
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();
        
        // Trigger change event
        currentCameraInput.dispatchEvent(new Event('change'));
        
    }, 'image/jpeg', 0.8);
}

// Stop camera when modal is closed
document.getElementById('cameraModal')?.addEventListener('hidden.bs.modal', function() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
});

// Enhanced preview function
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = 'block';
            preview.classList.add('img-thumbnail');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Add camera button to existing file inputs
function addCameraButtons() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    fileInputs.forEach(input => {
        if (!input.dataset.cameraAdded) {
            const previewId = input.id.replace(/input|file/, 'preview') || input.id + '_preview';
            
            // Create camera button
            const cameraBtn = document.createElement('button');
            cameraBtn.type = 'button';
            cameraBtn.className = 'btn btn-outline-primary w-100 mt-2';
            cameraBtn.innerHTML = '<i class="bi bi-camera"></i> Buka Kamera';
            cameraBtn.onclick = () => openCameraModal(input.id, previewId);
            
            // Insert after input
            input.parentNode.insertBefore(cameraBtn, input.nextSibling);
            input.dataset.cameraAdded = 'true';
        }
    });
}

// Auto-add camera buttons when page loads
document.addEventListener('DOMContentLoaded', addCameraButtons);
</script>

<!-- Enhanced Camera Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera"></i> Ambil Foto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="position-relative">
                    <video id="cameraVideo" autoplay playsinline 
                           style="width: 100%; max-width: 500px; border-radius: 10px;"></video>
                    <div class="position-absolute top-50 start-50 translate-middle" 
                         style="pointer-events: none; border: 2px solid rgba(255,255,255,0.8); width: 200px; height: 200px; border-radius: 10px;"></div>
                </div>
                <canvas id="cameraCanvas" style="display: none;"></canvas>
                <div class="mt-3 d-grid gap-2 d-md-flex justify-content-md-center">
                    <button type="button" class="btn btn-primary btn-lg" onclick="capturePhoto()">
                        <i class="bi bi-camera"></i> Ambil Foto
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
