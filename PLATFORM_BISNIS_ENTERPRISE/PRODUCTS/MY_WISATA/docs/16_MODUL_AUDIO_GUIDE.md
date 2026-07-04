# MODUL 16 — MODUL AUDIO GUIDE

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul untuk upload, manage, dan playback audio guide multibahasa per destinasi wisata.

---

## 2. FITUR

- Admin upload audio per destinasi & bahasa
- Wisatawan pilih bahasa → putar audio
- Player HTML5 (play/pause/seek/volume)
- Transkrip teks sinkron
- Statistik play count

---

## 3. CONTROLLER

```php
<?php
class AudioGuideController extends Controller {

    // Admin: upload audio
    public function upload() {
        Middleware::requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->validateInput($_POST);

            $allowed = ['mp3', 'ogg', 'wav'];
            $ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), $allowed)) {
                $this->json(['status' => 'error', 'message' => 'Format tidak didukung'], 400);
            }

            $path = Helper::uploadFile($_FILES['audio'], 'uploads/audio/');

            $model = $this->model('AudioGuide');
            $id = $model->insert([
                'destination_id' => $input['destination_id'],
                'language' => $input['language'],
                'title' => $input['title'],
                'description' => $input['description'] ?? null,
                'file_path' => $path,
                'transcript' => $input['transcript'] ?? null,
                'is_active' => 1
            ]);

            $this->json(['status' => 'success', 'message' => 'Audio guide diupload']);
        }
    }

    // Wisatawan: get audio per destinasi
    public function getByDestination($destinationId) {
        $lang = $_GET['lang'] ?? 'id';
        $model = $this->model('AudioGuide');
        $audio = $model->getByDestinationAndLang($destinationId, $lang);
        if ($audio) {
            // Increment play count
            $model->incrementPlay($audio['id']);
        }
        $this->json(['status' => 'success', 'data' => $audio]);
    }
}
```

---

## 4. VIEW: Audio Player

```php
<!-- app/views/wisatawan/audio_guide.php -->
<div class="card">
    <div class="card-header">
        <h5>Audio Guide — <?= $destination['name'] ?></h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label>Pilih Bahasa:</label>
            <select id="audio-lang" class="form-select" style="max-width: 200px;">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
                <option value="jp">日本語</option>
            </select>
        </div>

        <audio id="audio-player" controls class="w-100">
            <source src="" type="audio/mpeg">
        </audio>

        <div class="mt-3">
            <h6>Transkrip</h6>
            <div id="transcript" class="border p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                <p class="text-muted">Pilih bahasa untuk melihat transkrip</p>
            </div>
        </div>
    </div>
</div>

<script>
$('#audio-lang').change(function() {
    let lang = $(this).val();
    API.get('audio-guide/<?= $destination["id"] ?>?lang=' + lang, function(res) {
        if (res.data) {
            $('#audio-player source').attr('src', BASE_URL + 'public/' + res.data.file_path);
            $('#audio-player')[0].load();
            $('#transcript').html(res.data.transcript || '<p class="text-muted">Tidak ada transkrip</p>');
        } else {
            Swal.fire('Tidak tersedia', 'Audio bahasa ini belum tersedia', 'info');
        }
    });
});
</script>
```

---

## 5. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| POST | `api/admin/audio/upload` | Upload audio |
| GET | `api/audio-guide/{dest_id}?lang=id` | Get audio by destinasi & bahasa |
| GET | `api/admin/audio/list` | List semua audio (admin) |
| POST | `api/admin/audio/delete/{id}` | Hapus audio |

---

## 6. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **Performance:** Implementasi CDN untuk audio streaming
- [ ] **Performance:** Audio compression untuk mobile
- [ ] **Security:** Validasi file type dan size saat upload
- [ ] **Analytics:** Track audio play count dan completion rate
- [ ] **Accessibility:** Tambahkan captions/subtitles untuk aksesibilitas
- [ ] **Backup:** Backup audio files ke cloud storage
- [ ] **Caching:** Implementasi browser caching untuk audio files
- [ ] **Bandwidth:** Implementasi adaptive bitrate streaming

---

> **Modul Selanjutnya:** `17_MODUL_AI_TOUR_GUIDE.md`
