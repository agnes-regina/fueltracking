
<script>
function showLocationOnMap(coordinates, elementId) {
    if (!coordinates || coordinates.trim() === '') {
        return;
    }
    
    const coords = coordinates.split(',');
    if (coords.length !== 2) {
        return;
    }
    
    const lat = parseFloat(coords[0].trim());
    const lng = parseFloat(coords[1].trim());
    
    if (isNaN(lat) || isNaN(lng)) {
        return;
    }
    
    // Create modal for map
    const modalHtml = `
        <div class="modal fade" id="mapModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Lokasi GPS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="googleMap" style="height: 400px; width: 100%;"></div>
                        <div class="mt-2">
                            <strong>Koordinat:</strong> ${lat}, ${lng}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-geo-alt"></i> Buka di Google Maps
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('mapModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('mapModal'));
    modal.show();
    
    // Initialize Google Map
    if (typeof google !== 'undefined' && google.maps) {
        const map = new google.maps.Map(document.getElementById('googleMap'), {
            center: { lat: lat, lng: lng },
            zoom: 15
        });
        
        const marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            title: 'Lokasi GPS'
        });
        
        const infoWindow = new google.maps.InfoWindow({
            content: `<div><strong>Koordinat:</strong><br>${lat}, ${lng}</div>`
        });
        
        marker.addListener('click', () => {
            infoWindow.open(map, marker);
        });
    } else {
        // Fallback to static map
        document.getElementById('googleMap').innerHTML = `
            <iframe src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=${lat},${lng}&zoom=15" 
                    width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
            <div class="text-center mt-2">
                <small class="text-muted">Peta interaktif memerlukan Google Maps API</small>
            </div>
        `;
    }
}

// Function to add map button to coordinate fields
function addMapButtons() {
    const coordFields = document.querySelectorAll('[data-coordinates]');
    coordFields.forEach(field => {
        if (field.dataset.coordinates && field.dataset.coordinates.trim() !== '') {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm ms-2';
            btn.innerHTML = '<i class="bi bi-geo-alt"></i> Lihat Peta';
            btn.onclick = () => showLocationOnMap(field.dataset.coordinates);
            field.parentNode.appendChild(btn);
        }
    });
}

// Auto-add map buttons when page loads
document.addEventListener('DOMContentLoaded', addMapButtons);
</script>

<!-- Google Maps API (replace YOUR_API_KEY with actual key) -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>
