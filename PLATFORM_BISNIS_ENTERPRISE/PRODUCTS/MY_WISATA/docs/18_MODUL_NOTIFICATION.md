# MODUL 18 — MODUL NOTIFICATION

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Modul notifikasi untuk mengirim pesan ke pengguna via in-app dan email.

---

## 2. JENIS NOTIFIKASI

| Type | Trigger | Penerima |
|------|---------|----------|
| booking | Booking dibuat/dikonfirmasi/ditolak | Wisatawan & Guide |
| payment | Pembayaran terverifikasi/gagal | Wisatawan |
| event | Pendaftaran event, pengingat H-1 | Wisatawan |
| reminder | Pengingat tour besok | Wisatawan & Guide |
| system | Update sistem, maintenance | Semua user |
| broadcast | Admin broadcast | Target role |

---

## 3. MODEL

```php
<?php
class Notification extends Model {
    protected $table = 'notifications';

    public function send($userId, $type, $title, $message, $link = null) {
        $id = $this->insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0,
            'is_email_sent' => 0
        ]);

        // Send email (optional)
        if ($this->shouldSendEmail($type)) {
            $userModel = new User();
            $user = $userModel->find($userId);
            if ($user && $user['email_verified']) {
                Helper::sendEmail($user['email'], $title, $message);
                $this->update($id, ['is_email_sent' => 1]);
            }
        }
        return $id;
    }

    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table}
                WHERE user_id = :uid AND is_read = 0";
        return $this->db->query($sql, ['uid' => $userId])->fetch()['cnt'];
    }

    public function getRecent($userId, $limit = 10) {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :uid ORDER BY created_at DESC LIMIT {$limit}";
        return $this->db->query($sql, ['uid' => $userId])->fetchAll();
    }

    public function markAsRead($id) {
        return $this->update($id, ['is_read' => 1]);
    }

    public function markAllRead($userId) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = :uid AND is_read = 0";
        return $this->db->query($sql, ['uid' => $userId])->rowCount();
    }

    private function shouldSendEmail($type) {
        return in_array($type, ['booking', 'payment', 'event']);
    }
}
```

---

## 4. EMAIL HELPER

```php
<?php
// app/core/Helper.php
public static function sendEmail($to, $subject, $body) {
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: Tour Guide App <noreply@tourguide.app>'
    ];
    $html = "<html><body style='font-family: Arial; padding: 20px;'>
        <h2>{$subject}</h2>
        <p>{$body}</p>
        <hr><small>© Tour Guide Application</small>
    </body></html>";
    return mail($to, $subject, $html, implode("\r\n", $headers));
}
```

---

## 5. CONTROLLER

```php
<?php
class NotificationController extends Controller {

    public function getUnread() {
        Middleware::requireAuth();
        $model = $this->model('Notification');
        $count = $model->getUnreadCount($_SESSION['user_id']);
        $this->json(['status' => 'success', 'data' => ['count' => $count]]);
    }

    public function getList() {
        Middleware::requireAuth();
        $model = $this->model('Notification');
        $notifs = $model->getRecent($_SESSION['user_id'], 20);
        $this->json(['status' => 'success', 'data' => $notifs]);
    }

    public function markRead($id) {
        Middleware::requireAuth();
        $model = $this->model('Notification');
        $model->markAsRead($id);
        $this->json(['status' => 'success']);
    }

    public function markAllRead() {
        Middleware::requireAuth();
        $model = $this->model('Notification');
        $model->markAllRead($_SESSION['user_id']);
        $this->json(['status' => 'success']);
    }

    // Admin: broadcast
    public function broadcast() {
        Middleware::requireRole('admin');
        $input = json_decode(file_get_contents('php://input'), true);
        $userModel = $this->model('User');
        $notifModel = $this->model('Notification');

        $target = $input['target'] ?? 'all';
        $users = $target === 'all'
            ? $userModel->all(['status' => 'active'])
            : $userModel->all(['role' => $target, 'status' => 'active']);

        $count = 0;
        foreach ($users as $user) {
            $notifModel->insert([
                'user_id' => $user['id'],
                'type' => 'broadcast',
                'title' => $input['title'],
                'message' => $input['message'],
                'link' => $input['link'] ?? null
            ]);
            $count++;
        }

        $this->json(['status' => 'success', 'message' => "Notifikasi terkirim ke {$count} pengguna"]);
    }
}
```

---

## 6. VIEW: Badge Notifikasi (Navbar)

```javascript
// Polling notifikasi setiap 30 detik
function updateNotifBadge() {
    API.get('notification/unread', function(res) {
        let count = res.data.count;
        if (count > 0) {
            $('#notif-badge').text(count > 99 ? '99+' : count).show();
        } else {
            $('#notif-badge').hide();
        }
    });
}
setInterval(updateNotifBadge, 30000);
updateNotifBadge();
```

---

## 7. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `api/notification/unread` | Jumlah belum dibaca |
| GET | `api/notification/list` | List notifikasi |
| POST | `api/notification/read/{id}` | Tandai dibaca |
| POST | `api/notification/read-all` | Tandai semua dibaca |
| POST | `api/admin/notification/broadcast` | Admin broadcast |

---

## 8. INTEGRATION REMINDERS

**Pastikan integrasi berikut sebelum production:**

- [ ] **Performance:** Implementasi queue system untuk email sending
- [ ] **Security:** Rate limiting untuk notification sending
- [ ] **Delivery:** Implementasi delivery tracking untuk email
- [ ] **Fallback:** Fallback ke SMS jika email gagal
- [ ] **Analytics:** Track notification open rate dan click rate
- [ ] **Privacy:** Opsi user untuk unsubscribe dari certain notification types
- [ ] **Template:** Email template dengan responsive design
- [ ] **Spam:** Implementasi spam protection untuk broadcast

---

> **Modul Selanjutnya:** `19_MODUL_REPORT_ANALYTIC.md`
