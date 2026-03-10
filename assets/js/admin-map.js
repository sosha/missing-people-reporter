document.addEventListener('DOMContentLoaded', function () {
    const latField = document.getElementById('mpr_latitude');
    const lngField = document.getElementById('mpr_longitude');
    const addressField = document.getElementById('mpr_last_seen_location');
    const geocodeBtn = document.getElementById('mpr_geocode_button');
    const mapContainer = document.getElementById('mpr-admin-map');

    if (!mapContainer || typeof L === 'undefined') return;

    // Default to Kenya coordinates if nothing is set
    let initialLat = mpr_admin_map_vars.lat || -1.2921;
    let initialLng = mpr_admin_map_vars.lng || 36.8219;
    let zoom = mpr_admin_map_vars.lat ? 15 : 6;

    const map = L.map('mpr-admin-map').setView([initialLat, initialLng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker;

    if (mpr_admin_map_vars.lat && mpr_admin_map_vars.lng) {
        marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    }

    // Function to update fields when marker moves or map is clicked
    function updateCoordinates(lat, lng) {
        latField.value = lat.toFixed(6);
        lngField.value = lng.toFixed(6);

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });
        }
    }

    // Map Click
    map.on('click', function (e) {
        updateCoordinates(e.latlng.lat, e.latlng.lng);
    });

    // Geocoding Logic
    geocodeBtn.addEventListener('click', function () {
        const address = addressField.value;
        if (!address) {
            alert('Please enter a location name first.');
            return;
        }

        geocodeBtn.disabled = true;
        geocodeBtn.textContent = 'Searching...';

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`, {
            headers: {
                'User-Agent': 'MissingPeopleReporterPlugin/1.0'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lon = parseFloat(result.lon);

                    map.setView([lat, lon], 15);
                    updateCoordinates(lat, lon);
                } else {
                    alert('Location not found. Please try pinning it manually on the map.');
                }
            })
            .catch(error => {
                console.error('Geocoding error:', error);
                alert('Error searching for location. Please try pinning it manually.');
            })
            .finally(() => {
                geocodeBtn.disabled = false;
                geocodeBtn.textContent = 'Find on Map';
            });
    });

    // Handle marker drag
    if (marker) {
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
    }
});
