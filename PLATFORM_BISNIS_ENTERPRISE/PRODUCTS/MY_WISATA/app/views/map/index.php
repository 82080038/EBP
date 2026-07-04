<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Peta Destinasi Wisata</h1>
            
            <div class="card">
                <div class="card-body">
                    <div id="map" style="height: 600px; background-color: #e9ecef;"></div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h3>Destinasi Terdekat</h3>
                    <div id="nearbyDestinations" class="row">
                        <p class="text-muted">Klik tombol "Lokasi Saya" untuk melihat destinasi terdekat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
var map;
var markers = [];

// Initialize map
function initMap() {
    map = L.map('map').setView([-6.2088, 106.8456], 10); // Jakarta coordinates
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Load destinations
    loadDestinations();
}

// Load destinations from API
function loadDestinations() {
    fetch(window.APP_URL + 'map/getDestinations')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                data.markers.forEach(function(marker) {
                    addMarker(marker);
                });
            }
        });
}

// Add marker to map
function addMarker(data) {
    var icon = L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    
    var marker = L.marker([data.latitude, data.longitude], {icon: icon})
        .addTo(map)
        .bindPopup(`
            <div style="min-width: 200px;">
                <h6>${data.name}</h6>
                <p class="mb-1"><small>${data.city}</small></p>
                <p class="mb-1"><span class="text-warning">★</span> ${data.rating}</p>
                <a href="${window.APP_URL}destinations/detail?id=${data.id}" class="btn btn-sm btn-primary">Lihat Detail</a>
            </div>
        `);
    
    markers.push(marker);
}

// Get user location
function getUserLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            
            // Add user marker
            var userIcon = L.divIcon({
                className: 'user-marker',
                html: '<div style="background: #007bff; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            
            L.marker([lat, lng], {icon: userIcon})
                .addTo(map)
                .bindPopup('Lokasi Anda')
                .openPopup();
            
            // Center map on user
            map.setView([lat, lng], 12);
            
            // Load nearby destinations
            loadNearbyDestinations(lat, lng);
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Lokasi Anda berhasil didapatkan',
                timer: 1500,
                showConfirmButton: false
            });
        }, function(error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal mendapatkan lokasi: ' + error.message,
                confirmButtonColor: '#0d6efd'
            });
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Browser tidak mendukung geolocation',
            confirmButtonColor: '#0d6efd'
        });
    }
}

// Load nearby destinations
function loadNearbyDestinations(lat, lng) {
    fetch(window.APP_URL + 'map/getNearby?lat=' + lat + '&lng=' + lng + '&radius=10')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                var container = document.getElementById('nearbyDestinations');
                container.innerHTML = '';
                
                if (data.markers.length === 0) {
                    container.innerHTML = '<p class="text-muted">Tidak ada destinasi terdekat ditemukan.</p>';
                    return;
                }
                
                data.markers.forEach(function(marker) {
                    var card = `
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">${marker.name}</h6>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i>${marker.city}
                                    </p>
                                    <p class="card-text small">
                                        <i class="fas fa-road me-1"></i>${marker.distance.toFixed(2)} km
                                    </p>
                                    <a href="${window.APP_URL}destinations/detail?id=${marker.id}" class="btn btn-sm btn-primary">Lihat</a>
                                </div>
                            </div>
                        </div>
                    `;
                    container.innerHTML += card;
                });
            }
        });
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    // Add location button
    var locationBtn = L.control({position: 'topright'});
    locationBtn.onAdd = function(map) {
        var div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
        div.innerHTML = '<button onclick="getUserLocation()" style="padding: 8px 12px; background: white; border: 2px solid #ccc; cursor: pointer;"><i class="fas fa-crosshairs"></i> Lokasi Saya</button>';
        return div;
    };
    locationBtn.addTo(map);
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
