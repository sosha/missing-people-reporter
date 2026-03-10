document.addEventListener('DOMContentLoaded', function () {
    const mapContainer = document.getElementById('mpr-single-map');

    if (!mapContainer || typeof L === 'undefined' || typeof mpr_map_data === 'undefined') return;

    const lat = parseFloat(mpr_map_data.lat);
    const lng = parseFloat(mpr_map_data.lng);
    const name = mpr_map_data.name;

    if (isNaN(lat) || isNaN(lng)) return;

    const map = L.map('mpr-single-map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(`<strong>Last Seen: ${name}</strong>`).openPopup();
});
