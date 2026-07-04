# Setup Cron Job untuk Daily Tasks (Windows Task Scheduler)

## File Scheduled Task
`C:\xampp\htdocs\kewer\cron_daily_tasks.php`

## Langkah Setup Windows Task Scheduler

### 1. Buka Task Scheduler
- Tekan `Win + R`
- Ketik `taskschd.msc`
- Tekan Enter

### 2. Create Basic Task
- Klik kanan pada "Task Scheduler Library"
- Pilih "Create Basic Task"
- Beri nama: `Kewer Daily Tasks`
- Klik Next

### 3. Trigger
- Pilih "Daily"
- Klik Next
- Set start date dan time (rekomendasi: 00:00 / 12:00 AM)
- Klik Next

### 4. Action
- Pilih "Start a program"
- Klik Next

### 5. Program/Script
- **Program/script:** `C:\xampp\php\php.exe`
- **Add arguments:** `C:\xampp\htdocs\kewer\cron_daily_tasks.php`
- **Start in:** `C:\xampp\htdocs\kewer`

Klik Next, lalu Finish.

### 6. Verify Task
- Cari task "Kewer Daily Tasks" di Task Scheduler Library
- Klik kanan > Run untuk test manual
- Cek log output di: `C:\xampp\htdocs\kewer\logs\cron_daily_tasks.log`

## Fungsi Daily Tasks
Script `cron_daily_tasks.php` menjalankan:
1. Auto-create penagihan untuk angsuran jatuh tempo
2. Hitung denda harian
3. Update kolektibilitas nasabah
4. Tag pinjaman macet
5. Kirim notifikasi (jika dikonfigurasi)

## Log Output
Logs disimpan di `logs/cron_daily_tasks.log` dengan format:
```
[YYYY-MM-DD HH:MM:SS] - Task name: Status
```

## Troubleshooting
Jika task tidak berjalan:
1. Pastikan XAMPP Apache & MySQL berjalan
2. Cek Task Scheduler History untuk error
3. Jalankan manual: `C:\xampp\php\php.exe C:\xampp\htdocs\kewer\cron_daily_tasks.php`
4. Cek file log untuk error message
