
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
    
    // Initialize Google Map after modal is shown
    modal._element.addEventListener('shown.bs.modal', function () {
        initializeMap(lat, lng);
    });
}

// Initialize Google Map function
function initializeMap(lat, lng) {
    if (typeof google !== 'undefined' && google.maps) {
        const mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        
        const map = new google.maps.Map(document.getElementById('googleMap'), mapOptions);
        
        // Use AdvancedMarkerElement if available, fallback to Marker
        if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
            const marker = new google.maps.marker.AdvancedMarkerElement({
                position: { lat: lat, lng: lng },
                map: map,
                title: 'Lokasi GPS'
            });
        } else {
            const marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                title: 'Lokasi GPS'
            });
        }
        
        const infoWindow = new google.maps.InfoWindow({
            content: `<div><strong>Koordinat:</strong><br>${lat}, ${lng}</div>`
        });
        
        // Add click listener for info window
        google.maps.event.addListener(marker, 'click', function() {
            infoWindow.open(map, marker);
        });
    } else {
        // Fallback to embedded map
        document.getElementById('googleMap').innerHTML = `
            <iframe src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=${lat},${lng}&zoom=15" 
                    width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
            <div class="text-center mt-2">
                <small class="text-muted">Peta interaktif memerlukan Google Maps API yang valid</small>
            </div>
        `;
    }
}

// Global callback function for Google Maps API
function initMap() {
    // This function is required by Google Maps API but we initialize maps on demand
    console.log('Google Maps API loaded successfully');
}

// Function to add map buttons to coordinate fields
function addMapButtons() {
    const coordFields = document.querySelectorAll('[data-coordinates]');
    coordFields.forEach(field => {
        if (field.dataset.coordinates && field.dataset.coordinates.trim() !== '') {
            // Check if button already exists
            if (!field.parentNode.querySelector('.map-view-btn')) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-primary btn-sm ms-2 map-view-btn';
                btn.innerHTML = '<i class="bi bi-geo-alt"></i> Lihat Peta';
                btn.onclick = () => showLocationOnMap(field.dataset.coordinates);
                field.parentNode.appendChild(btn);
            }
        }
    });
}

// Auto-add map buttons when page loads
document.addEventListener('DOMContentLoaded', addMapButtons);

// Load Google Maps API with proper async loading
function loadGoogleMapsAPI() {
    if (typeof google === 'undefined') {
        const script = document.createElement('script');
        script.async = true;
        script.defer = true;
        script.src = 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap&libraries=marker';
        script.onerror = function() {
            console.warn('Failed to load Google Maps API. Map functionality will be limited.');
        };
        document.head.appendChild(script);
    }
}

// Load the API when the page loads
document.addEventListener('DOMContentLoaded', loadGoogleMapsAPI);
</script>

<style>
.map-view-btn {
    margin-left: 0.5rem;
}

#googleMap {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.modal-body .mt-2 {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    font-size: 0.9rem;
}
</style>
