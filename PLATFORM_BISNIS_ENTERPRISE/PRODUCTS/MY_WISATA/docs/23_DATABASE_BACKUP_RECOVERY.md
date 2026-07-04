# MODUL 23 — DATABASE BACKUP & RECOVERY

> **Versi:** 1.1 · **Tanggal:** 2026-06-30 · **Last Updated:** 2026-06-30

---

## 1. RINGKASAN

Strategi backup dan recovery database MySQL untuk memastikan data aman dan
dapat dipulihkan saat terjadi kegagalan.

---

## 2. STRATEGI BACKUP

| Tipe | Frekuensi | Retensi | Metode |
|------|-----------|---------|--------|
| Full backup | Harian (02:00) | 7 hari | mysqldump |
| Weekly backup | Mingguan | 4 minggu | mysqldump compressed |
| Monthly backup | Bulanan | 12 bulan | mysqldump + upload cloud |

---

## 3. SCRIPT BACKUP OTOMATIS

```bash
#!/bin/bash
# /opt/scripts/backup_db.sh
# Cron: 0 2 * * * /opt/scripts/backup_db.sh

DB_NAME="tour_guide_app"
DB_USER="root"
DB_PASS=""
BACKUP_DIR="/opt/lampp/htdocs/wisata/database/backup"
DATE=$(date +%Y%m%d_%H%M%S)
FILE="${BACKUP_DIR}/${DB_NAME}_${DATE}.sql.gz"

# Create backup
mysqldump -u${DB_USER} -p${DB_PASS} ${DB_NAME} | gzip > ${FILE}

# Delete backups older than 7 days
find ${BACKUP_DIR} -name "*.sql.gz" -mtime +7 -delete

# Log
echo "$(date): Backup created ${FILE}" >> ${BACKUP_DIR}/backup.log
```

---

## 4. CRON JOB SETUP

```bash
# Edit crontab
crontab -e

# Tambahkan:
0 2 * * * /opt/scripts/backup_db.sh
0 3 * * 0 /opt/scripts/backup_db_weekly.sh
```

---

## 5. SCRIPT RECOVERY

```bash
#!/bin/bash
# /opt/scripts/restore_db.sh
# Usage: ./restore_db.sh backup_file.sql.gz

DB_NAME="tour_guide_app"
DB_USER="root"
FILE=$1

if [ -z "$FILE" ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    exit 1
fi

echo "Restoring from $FILE..."
gunzip -c $FILE | mysql -u${DB_USER} ${DB_NAME}
echo "Restore complete."
```

---

## 6. BACKUP VIA PHP (ADMIN PANEL)

```php
<?php
class BackupController extends Controller {

    public function __construct() {
        Middleware::requireRole('admin');
    }

    public function create() {
        $filename = 'tour_guide_app_' . date('Ymd_His') . '.sql';
        $path = 'database/backup/' . $filename;

        $command = "mysqldump -u" . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " > " . BASE_PATH . '/' . $path;
        system($command);

        Logger::audit('backup', 'database', "Manual backup: {$filename}");
        $this->json(['status' => 'success', 'message' => 'Backup dibuat', 'data' => ['file' => $filename]]);
    }

    public function listBackups() {
        $dir = BASE_PATH . '/database/backup/';
        $files = glob($dir . '*.sql');
        $backups = array_map(function($f) {
            return [
                'filename' => basename($f),
                'size' => filesize($f),
                'date' => filemtime($f)
            ];
        }, $files);
        usort($backups, fn($a, $b) => $b['date'] - $a['date']);
        $this->json(['status' => 'success', 'data' => $backups]);
    }

    public function download($filename) {
        $path = BASE_PATH . '/database/backup/' . $filename;
        if (!file_exists($path)) {
            http_response_code(404);
            die('File not found');
        }
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($path);
        exit;
    }

    public function restore() {
        $input = json_decode(file_get_contents('php://input'), true);
        $filename = $input['filename'];
        $path = BASE_PATH . '/database/backup/' . $filename;

        if (!file_exists($path)) {
            $this->json(['status' => 'error', 'message' => 'File tidak ditemukan'], 404);
        }

        $command = "mysql -u" . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " < " . escapeshellarg($path);
        system($command, $retval);

        if ($retval === 0) {
            Logger::audit('restore', 'database', "Restored from {$filename}");
            $this->json(['status' => 'success', 'message' => 'Database dipulihkan']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Restore gagal'], 500);
        }
    }
}
```

---

## 7. VIEW: Backup Panel

```php
<!-- app/views/admin/backup.php -->
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Backup Database</h5>
        <button class="btn btn-primary btn-sm" id="btn-backup">
            <i class="fas fa-download"></i> Backup Sekarang
        </button>
    </div>
    <div class="card-body">
        <table class="table" id="backup-table">
            <thead><tr><th>File</th><th>Size</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody id="backup-list"></tbody>
        </table>
    </div>
</div>

<script>
function loadBackups() {
    API.get('admin/backup/list', {}, function(res) {
        let html = '';
        res.data.forEach(function(b) {
            html += `<tr>
                <td>${b.filename}</td>
                <td>${(b.size/1024).toFixed(0)} KB</td>
                <td>${new Date(b.date*1000).toLocaleString()}</td>
                <td>
                    <a href="${BASE_URL}admin/backup/download/${b.filename}" class="btn btn-sm btn-info">Download</a>
                    <button class="btn btn-sm btn-warning btn-restore" data-file="${b.filename}">Restore</button>
                </td>
            </tr>`;
        });
        $('#backup-list').html(html);
    });
}

$('#btn-backup').click(function() {
    Swal.fire({title: 'Buat backup?', icon: 'question', showCancelButton: true})
    .then(r => { if (r.isConfirmed) {
        API.post('admin/backup/create', {}, function(res) {
            Swal.fire('Berhasil!', res.message, 'success').then(loadBackups);
        });
    }});
});

$(document).on('click', '.btn-restore', function() {
    let file = $(this).data('file');
    Swal.fire({title: 'Restore database?', text: 'Data saat ini akan ditimpa!', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Ya, Restore'})
    .then(r => { if (r.isConfirmed) {
        API.post('admin/backup/restore', {filename: file}, function(res) {
            Swal.fire('Berhasil!', res.message, 'success');
        });
    }});
});

loadBackups();
</script>
```

---

## 8. AUTOMATED BACKUP VERIFICATION

**Status:** Not Implemented — HIGH PRIORITY

Implementasi automated backup verification untuk memastikan backup dapat di-restore:

```bash
#!/bin/bash
# /opt/scripts/verify_backup.sh
# Cron: 0 3 * * * /opt/scripts/verify_backup.sh (1 hour after backup)

DB_NAME="tour_guide_app"
DB_USER="root"
DB_PASS=""
BACKUP_DIR="/opt/lampp/htdocs/wisata/database/backup"
TEST_DB="${DB_NAME}_test_restore"
DATE=$(date +%Y%m%d_%H%M%S)
LATEST_BACKUP=$(ls -t ${BACKUP_DIR}/${DB_NAME}_*.sql.gz | head -1)

# Create test database
mysql -u${DB_USER} -p${DB_PASS} -e "DROP DATABASE IF EXISTS ${TEST_DB}"
mysql -u${DB_USER} -p${DB_PASS} -e "CREATE DATABASE ${TEST_DB}"

# Restore backup to test database
gunzip < ${LATEST_BACKUP} | mysql -u${DB_USER} -p${DB_PASS} ${TEST_DB}

# Verify data integrity
TABLE_COUNT=$(mysql -u${DB_USER} -p${DB_PASS} ${TEST_DB} -e "SHOW TABLES" | wc -l)
USER_COUNT=$(mysql -u${DB_USER} -p${DB_PASS} ${TEST_DB} -e "SELECT COUNT(*) FROM users" | tail -1)
BOOKING_COUNT=$(mysql -u${DB_USER} -p${DB_PASS} ${TEST_DB} -e "SELECT COUNT(*) FROM bookings" | tail -1)

# Check thresholds
if [ $TABLE_COUNT -lt 10 ] || [ $USER_COUNT -lt 1 ] || [ $BOOKING_COUNT -lt 1 ]; then
    echo "$(date): BACKUP VERIFICATION FAILED - ${LATEST_BACKUP}" >> ${BACKUP_DIR}/verification.log
    # Send alert
    echo "Backup verification failed for ${LATEST_BACKUP}" | mail -s "Backup Alert" admin@example.com
else
    echo "$(date): Backup verification PASSED - ${LATEST_BACKUP}" >> ${BACKUP_DIR}/verification.log
fi

# Cleanup test database
mysql -u${DB_USER} -p${DB_PASS} -e "DROP DATABASE ${TEST_DB}"
```

### Point-in-Time Recovery (PITR)

```bash
#!/bin/bash
# /opt/scripts/pitr_restore.sh
# Restore to specific point in time using binary logs

DB_NAME="tour_guide_app"
DB_USER="root"
DB_PASS=""
BACKUP_DIR="/opt/lampp/htdocs/wisata/database/backup"
BINLOG_DIR="/var/lib/mysql"
TARGET_TIME="2026-06-30 14:30:00"

# 1. Restore full backup
FULL_BACKUP="${BACKUP_DIR}/${DB_NAME}_20260630_020001.sql.gz"
gunzip < ${FULL_BACKUP} | mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME}

# 2. Apply binary logs up to target time
mysqlbinlog --start-datetime="2026-06-30 02:00:01" \
            --stop-datetime="${TARGET_TIME}" \
            ${BINLOG_DIR}/binlog.000001 | mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME}

mysqlbinlog --start-datetime="2026-06-30 02:00:01" \
            --stop-datetime="${TARGET_TIME}" \
            ${BINLOG_DIR}/binlog.000002 | mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME}
```

### Offsite Backup to Cloud Storage

```bash
#!/bin/bash
# /opt/scripts/offsite_backup.sh
# Upload backup to AWS S3 or Google Cloud Storage

BACKUP_DIR="/opt/lampp/htdocs/wisata/database/backup"
LATEST_BACKUP=$(ls -t ${BACKUP_DIR}/${DB_NAME}_*.sql.gz | head -1)

# Upload to AWS S3
aws s3 cp ${LATEST_BACKUP} s3://tourguide-backups/database/

# Or upload to Google Cloud Storage
gsutil cp ${LATEST_BACKUP} gs://tourguide-backups/database/
```

---

## 9. CHECKLIST BACKUP

- [ ] Cron job harian aktif
- [ ] Retensi 7 hari auto-cleanup
- [ ] Backup tercompress (gzip)
- [ ] Log backup tercatat
- [ ] Test restore minimal 1x/bulan
- [ ] Backup disimpan di lokasi terpisah (opsional: cloud storage)

---

> **Modul Selanjutnya:** `24_TESTING_SYSTEM.md`
