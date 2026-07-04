# MODUL 17 — MODUL AI TOUR GUIDE

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul AI Tour Guide menggunakan pendekatan **rule-based + keyword matching**
(sederhana, tanpa LLM eksternal berbayar) untuk rekomendasi destinasi,
generate itinerary, dan FAQ.

---

## 2. FITUR

- Chatbot rekomendasi destinasi
- Generate itinerary berdasarkan preferensi
- FAQ destinasi
- Rekomendasi berdasarkan lokasi GPS
- Rekomendasi berdasarkan riwayat booking

---

## 3. ARSITEKTUR AI (RULE-BASED)

```
User message → Preprocess (lowercase, tokenize)
→ Keyword matching → Intent detection
→ Query database → Format response
→ Return to chat
```

### 3.1 Intent Detection

| Keyword | Intent | Action |
|---------|--------|--------|
| "rekomendasi", "saran", "mau wisata" | rekomendasi | Query destinasi terlaris/featured |
| "pantai", "gunung", "alam", "budaya" | kategori | Query by category |
| "itinerary", "rencana", "jadwal wisata" | itinerary | Generate itinerary |
| "berapa harga", "tiket", "biaya" | harga | Query ticket prices |
| "lokasi", "alamat", "dimana" | lokasi | Query destination address |
| "guide", "pemandu", "bahasa inggris" | guide | Query tour guides |
| "hotel", "penginapan", "homestay" | hotel | Query hotels |
| "makan", "kuliner", "restoran" | kuliner | Query restaurants |
| "event", "festival", "acara" | event | Query upcoming events |
| "halo", "hai", "hi" | greeting | Return greeting |

---

## 4. CONTROLLER

```php
<?php
class AIGuideController extends Controller {

    public function chat() {
        Middleware::requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $message = strtolower(trim($input['message']));

        // Get or create session
        $sessionModel = $this->model('ChatSession');
        $session = $sessionModel->getOrCreateByUser($_SESSION['user_id']);

        // Save user message
        $msgModel = $this->model('ChatMessage');
        $msgModel->insert([
            'session_id' => $session['id'],
            'role' => 'user',
            'message' => $input['message']
        ]);

        // Process response
        $response = $this->processMessage($message);

        // Save AI response
        $msgModel->insert([
            'session_id' => $session['id'],
            'role' => 'assistant',
            'message' => $response['message'],
            'metadata' => json_encode($response['data'] ?? null)
        ]);

        $this->json(['status' => 'success', 'data' => $response]);
    }

    private function processMessage($message) {
        // Greeting
        if (preg_match('/\b(halo|hai|hi|hello|assalam)\b/', $message)) {
            return [
                'message' => "Halo! Saya AI Tour Guide. Saya bisa membantu Anda mencari destinasi wisata, tour guide, hotel, restoran, dan event. Apa yang Anda cari?",
                'quick_replies' => ['Rekomendasi destinasi', 'Cari tour guide', 'Buat itinerary']
            ];
        }

        // Rekomendasi
        if (preg_match('/\b(rekomendasi|saran|mau wisata|destinasi terbaik)\b/', $message)) {
            $destModel = $this->model('Destination');
            $dests = $destModel->getFeatured(5);
            $list = array_map(function($d) {
                return "• {$d['name']} ({$d['city']}) — Rating: {$d['rating_avg']}★";
            }, $dests);
            return [
                'message' => "Berikut rekomendasi destinasi terbaik:\n" . implode("\n", $list),
                'data' => $dests
            ];
        }

        // Kategori
        $categories = ['pantai' => 'Pantai', 'gunung' => 'Gunung', 'alam' => 'Alam',
                       'budaya' => 'Budaya', 'sejarah' => 'Sejarah', 'museum' => 'Museum',
                       'kuliner' => 'Kuliner', 'taman nasional' => 'Taman Nasional'];
        foreach ($categories as $keyword => $catName) {
            if (strpos($message, $keyword) !== false) {
                $catModel = $this->model('DestinationCategory');
                $cat = $catModel->findBySlug(Helper::slugify($catName));
                if ($cat) {
                    $destModel = $this->model('Destination');
                    $dests = $destModel->all(['category_id' => $cat['id'], 'is_active' => 1]);
                    $list = array_map(function($d) {
                        return "• {$d['name']} — {$d['city']}";
                    }, array_slice($dests, 0, 5));
                    return [
                        'message' => "Destinasi kategori {$catName}:\n" . implode("\n", $list),
                        'data' => $dests
                    ];
                }
            }
        }

        // Itinerary
        if (preg_match('/\b(itinerary|rencana|jadwal wisata|rute)\b/', $message)) {
            return $this->generateItinerary($message);
        }

        // Guide
        if (preg_match('/\b(guide|pemandu|tour guide)\b/', $message)) {
            $guideModel = $this->model('TourGuide');
            $guides = $guideModel->getTopRated(5);
            $list = array_map(function($g) {
                return "• {$g['name']} — Rating: {$g['rating_avg']}★ — Rp {$g['hourly_rate']}/jam";
            }, $guides);
            return [
                'message' => "Tour guide terbaik:\n" . implode("\n", $list),
                'data' => $guides
            ];
        }

        // Hotel
        if (preg_match('/\b(hotel|penginapan|homestay|villa)\b/', $message)) {
            $hotelModel = $this->model('Hotel');
            $hotels = $hotelModel->getTopRated(5);
            $list = array_map(function($h) {
                return "• {$h['name']} ({$h['type']}) — {$h['city']}";
            }, $hotels);
            return [
                'message' => "Rekomendasi akomodasi:\n" . implode("\n", $list),
                'data' => $hotels
            ];
        }

        // Kuliner
        if (preg_match('/\b(makan|kuliner|restoran|warung|cafe)\b/', $message)) {
            $restModel = $this->model('Restaurant');
            $rests = $restModel->getTopRated(5);
            $list = array_map(function($r) {
                return "• {$r['name']} — {$r['cuisine_type']} — {$r['city']}";
            }, $rests);
            return [
                'message' => "Rekomendasi kuliner:\n" . implode("\n", $list),
                'data' => $rests
            ];
        }

        // Event
        if (preg_match('/\b(event|festival|acara)\b/', $message)) {
            $eventModel = $this->model('Event');
            $events = $eventModel->getUpcoming(5);
            $list = array_map(function($e) {
                return "• {$e['title']} — " . date('d M Y', strtotime($e['start_date']));
            }, $events);
            return [
                'message' => "Event mendatang:\n" . implode("\n", $list),
                'data' => $events
            ];
        }

        // Fallback
        return [
            'message' => "Maaf, saya belum mengerti. Coba tanya tentang: rekomendasi destinasi, tour guide, hotel, restoran, event, atau itinerary.",
            'quick_replies' => ['Rekomendasi destinasi', 'Cari tour guide', 'Buat itinerary', 'Event mendatang']
        ];
    }

    private function generateItinerary($message) {
        // Simple 3-day itinerary
        $destModel = $this->model('Destination');
        $dests = $destModel->getFeatured(9);
        if (count($dests) < 3) {
            return ['message' => 'Maaf, belum cukup destinasi untuk membuat itinerary.'];
        }

        $itinerary = "Berikut rekomendasi itinerary 3 hari:\n\n";
        $days = ['Hari 1', 'Hari 2', 'Hari 3'];
        for ($i = 0; $i < 3; $i++) {
            $itinerary .= "**{$days[$i]}:**\n";
            $itinerary .= "Pagi: {$dests[$i]['name']} ({$dests[$i]['city']})\n";
            $itinerary .= "Siang: Makan siang di restoran lokal\n";
            if (isset($dests[$i + 3])) {
                $itinerary .= "Sore: {$dests[$i + 3]['name']}\n";
            }
            $itinerary .= "\n";
        }
        $itinerary .= "Mau saya bantu booking tour guide untuk itinerary ini?";

        return ['message' => $itinerary, 'data' => $dests];
    }
}
```

---

## 5. VIEW: Chat Interface

```php
<!-- app/views/wisatawan/ai_chat.php -->
<?php include 'app/views/layouts/header.php'; ?>

<div class="container mt-4" style="max-width: 700px;">
    <h3>AI Tour Guide</h3>
    <div class="card" style="height: 500px;">
        <div class="card-body overflow-auto" id="chat-messages">
            <div class="text-center text-muted mt-5">
                <i class="fas fa-robot fa-3x"></i>
                <p class="mt-3">Halo! Tanyakan apa saja tentang wisata.</p>
            </div>
        </div>
        <div class="card-footer">
            <div id="quick-replies" class="mb-2"></div>
            <div class="input-group">
                <input type="text" id="chat-input" class="form-control" placeholder="Ketik pesan...">
                <button class="btn btn-primary" id="btn-send">Kirim</button>
            </div>
        </div>
    </div>
</div>

<script>
function sendMessage(text) {
    if (!text.trim()) return;
    $('#chat-messages').append(`
        <div class="d-flex justify-content-end mb-2">
            <div class="bg-primary text-white p-2 rounded" style="max-width: 70%;">${text}</div>
        </div>`);
    $('#chat-input').val('');
    scrollChat();

    API.post('ai/chat', { message: text }, function(res) {
        let data = res.data;
        $('#chat-messages').append(`
            <div class="d-flex justify-content-start mb-2">
                <div class="bg-light p-2 rounded" style="max-width: 70%;">${data.message.replace(/\n/g, '<br>')}</div>
            </div>`);
        if (data.quick_replies) {
            let html = '';
            data.quick_replies.forEach(function(q) {
                html += `<button class="btn btn-sm btn-outline-primary me-1 btn-quick">${q}</button>`;
            });
            $('#quick-replies').html(html);
        } else {
            $('#quick-replies').empty();
        }
        scrollChat();
    });
}

function scrollChat() {
    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
}

$('#btn-send').click(() => sendMessage($('#chat-input').val()));
$('#chat-input').keypress(e => { if (e.which === 13) sendMessage($(e.target).val()); });
$(document).on('click', '.btn-quick', function() { sendMessage($(this).text()); });
</script>

<?php include 'app/views/layouts/footer.php'; ?>
```

---

## 6. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| POST | `api/ai/chat` | Kirim pesan & dapatkan response |
| GET | `api/ai/history` | Riwayat chat |
| POST | `api/ai/session/reset` | Reset sesi chat |

---

## 7. KNOWLEDGE BASE (Admin)

Admin dapat mengelola FAQ tambahan via tabel `settings` atau custom table:

```
FAQ Entry:
- keyword: "jam buka"
- response: "Jam buka destinasi bervariasi, umumnya 08:00-17:00. Cek detail destinasi untuk info akurat."
```

---

## 8. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **ML Integration:** Pertimbangkan integrasi dengan ML API untuk NLP yang lebih advanced
- [ ] **Performance:** Cache AI responses untuk pertanyaan yang sering muncul
- [ ] **Analytics:** Track AI chat interactions dan user satisfaction
- [ ] **Privacy:** Anonimisasi data chat untuk training model
- [ ] **Fallback:** Fallback ke human support jika AI tidak bisa menjawab
- [ ] **Testing:** A/B test AI responses untuk improvement
- [ ] **Knowledge Base:** Regular update knowledge base dengan FAQ baru
- [ ] **Rate Limiting:** Rate limiting untuk API endpoint AI chat

---

> **Modul Selanjutnya:** `18_MODUL_NOTIFICATION.md`
