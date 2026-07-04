# MODUL 10 — MODUL MAP & GPS (OpenStreetMap)

> **Aplikasi:** Tour Guide Application  
> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul peta interaktif berbasis OpenStreetMap + Leaflet.js untuk menampilkan
destinasi, rute, dan lokasi GPS pengguna tanpa biaya API.

---

## 2. FUNGSI UTAMA

| Fungsi | Deskripsi |
|--------|-----------|
| Tampilkan peta | Leaflet + tile OSM |
| Marker destinasi | Plot semua destinasi dari DB |
| Popup info | Nama, deskripsi, link detail |
| Cari destinasi | Filter nama/kategori di peta |
| Lokasi user | Geolocation API browser |
| Rute | Routing dari user → destinasi |
| Cluster | Marker clustering saat zoom out |
| Itinerary | Multiple destinations route |

---

## 3. INISIALISASI PETA

```javascript
// assets/js/map.js
let map = L.map('map').setView([-2.5, 118], 5);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Marker cluster group
let markers = L.markerClusterGroup();
map.addLayer(markers);
```

---

## 4. LOAD MARKER VIA AJAX

```javascript
function loadMarkers(category) {
    markers.clearLayers();
    let url = 'map/markers';
    if (category) url += '?category=' + category;

    API.get(url, function(res) {
        res.data.forEach(function(d) {
            let marker = L.marker([d.latitude, d.longitude]);
            marker.bindPopup(`
                <strong>${d.name}</strong><br>
                ${d.short_desc || ''}<br>
                <small>${d.city || ''}</small><br>
                <a href="${BASE_URL}wisatawan/destination-detail/${d.id}">Lihat Detail</a>
            `);
            markers.addLayer(marker);
        });
    });
}
loadMarkers();
```

---

## 5. GEOLOCATION USER

```javascript
function locateUser() {
    if (!navigator.geolocation) {
        Swal.fire('Error', 'Browser tidak mendukung geolocation', 'error');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            let lat = pos.coords.latitude;
            let lng = pos.coords.longitude;
            L.circleMarker([lat, lng], {radius: 8, color: 'blue'})
                .addTo(map).bindPopup('Lokasi Anda').openPopup();
            map.setView([lat, lng], 13);
        },
        function(err) {
            Swal.fire('Error', 'Tidak bisa mendapatkan lokasi', 'error');
        }
    );
}
```

---

## 6. ROUTING SEDERHANA

Menggunakan Leaflet Routing Machine (opsional) atau OSRM:

```javascript
function showRoute(destLat, destLng) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        L.Routing.control({
            waypoints: [
                L.latLng(pos.coords.latitude, pos.coords.longitude),
                L.latLng(destLat, destLng)
            ],
            routeWhileDragging: true
        }).addTo(map);
    });
}
```

---

## 7. ITINERARY ROUTE

```javascript
function showItinerary(destinations) {
    let waypoints = destinations.map(d => L.latLng(d.latitude, d.longitude));
    L.Routing.control({
        waypoints: waypoints,
        show: false
    }).addTo(map);
}
```

---

## 8. API ENDPOINTS

| Method | URL | Response |
|--------|-----|----------|
| GET | `api/map/markers` | Semua marker destinasi |
| GET | `api/map/markers?category=alam` | Filter kategori |
| GET | `api/map/nearby?lat={lat}&lng={lng}&radius=10` | Destinasi terdekat (km) |
| GET | `api/map/destination/{id}` | Detail destinasi untuk popup |

---

## 9. CONTROLLER

```php
<?php
class MapController extends Controller {

    public function markers() {
        $model = $this->model('Destination');
        $category = $_GET['category'] ?? null;
        $data = $model->getMarkers($category);
        $this->json(['status' => 'success', 'data' => $data]);
    }

    public function nearby() {
        $lat = (float)$_GET['lat'];
        $lng = (float)$_GET['lng'];
        $radius = (float)($_GET['radius'] ?? 10);
        $model = $this->model('Destination');
        $data = $model->getNearby($lat, $lng, $radius);
        $this->json(['status' => 'success', 'data' => $data]);
    }
}
```

---

## 10. MODEL: getNearby (Haversine)

```php
public function getNearby($lat, $lng, $radiusKm) {
    $sql = "SELECT *, (
        6371 * acos(cos(radians(:lat)) * cos(radians(latitude))
        * cos(radians(longitude) - radians(:lng))
        + sin(radians(:lat)) * sin(radians(latitude)))
    ) AS distance FROM {$this->table}
    WHERE is_active = 1
    HAVING distance <= :radius
    ORDER BY distance LIMIT 20";
    return $this->db->query($sql, [
        'lat' => $lat, 'lng' => $lng, 'radius' => $radiusKm
    ])->fetchAll();
}
```

---

## 11. VIEW: Halaman Peta

```php
<!-- app/views/wisatawan/map.php -->
<?php include 'app/views/layouts/header.php'; ?>

<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="col-md-3 p-3 bg-light" style="height: 100vh; overflow-y: auto;">
            <h5>Cari Destinasi</h5>
            <select id="filter-category" class="form-select mb-2">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button id="btn-locate" class="btn btn-outline-primary w-100 mb-2">
                <i class="fas fa-location-arrow"></i> Lokasi Saya
            </button>
            <hr>
            <div id="destination-list"></div>
        </div>
        <div class="col-md-9">
            <div id="map" style="height: 100vh;"></div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/footer.php'; ?>
```

---

## 12. LIBRARY YANG DIBUTUHKAN

| Library | CDN | Fungsi |
|---------|-----|--------|
| Leaflet | unpkg.com/leaflet@1.9.4 | Peta utama |
| Leaflet.markercluster | cdn.jsdelivr.net/npm/leaflet.markercluster | Cluster marker |
| Leaflet Routing Machine | cdn.jsdelivr.net/npm/leaflet-routing-machine | Routing |

---

## 13. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **Security:** Rate limiting untuk API endpoint map
- [ ] **Performance:** Implementasi offline maps untuk area yang sering dikunjungi
- [ ] **Privacy:** Request izin lokasi user dengan jelas
- [ ] **Analytics:** Track map interactions (marker clicks, route requests)
- [ ] **Caching:** Cache tile images dengan browser caching
- [ ] **Fallback:** Fallback ke Google Maps jika OSM down
- [ ] **Geofencing:** Implementasi geofencing untuk area wisata
- [ ] **Accessibility:** Pastikan peta accessible untuk screen reader

---

> **Modul Selanjutnya:** `11_MODUL_BOOKING_DAN_TRANSAKSI.md`
