<!-- Leaflet CDN (bisa taruh di <head>) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
function showLocationOnMap(coordinates, elementId) {
    if (!coordinates || coordinates.trim() === '') return;

    const coords = coordinates.split(',');
    if (coords.length !== 2) return;

    const lat = parseFloat(coords[0].trim());
    const lng = parseFloat(coords[1].trim());
    if (isNaN(lat) || isNaN(lng)) return;

    const modalHtml = `
        <div class="modal fade" id="mapModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Lokasi GPS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="leafletMap" style="height: 400px; width: 100%; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"></div>
                        <div class="mt-2 bg-light p-2 rounded">
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

    const existingModal = document.getElementById('mapModal');
    if (existingModal) existingModal.remove();

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('mapModal'));
    modal.show();

    // Inisialisasi Leaflet setelah modal muncul
    setTimeout(() => {
        const map = L.map('leafletMap').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup(`Koordinat: ${lat}, ${lng}`)
            .openPopup();
    }, 300);
}

function addMapButtons() {
    const coordFields = document.querySelectorAll('[data-coordinates]');
    coordFields.forEach(field => {
        if (field.dataset.coordinates && field.dataset.coordinates.trim() !== '') {
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

document.addEventListener('DOMContentLoaded', addMapButtons);
</script>
