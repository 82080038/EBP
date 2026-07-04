<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EBP Finance Platform - Saham</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">📈 EBP Finance Platform - Saham</h3>
                    </div>
                    <div class="card-body">
                        <h5>Cara Menjalankan Aplikasi</h5>
                        <p>Aplikasi ini menggunakan Python Streamlit. Ikuti langkah berikut:</p>
                        
                        <ol>
                            <li>Buka terminal/command prompt</li>
                            <li>Navigasi ke folder saham:
                                <pre class="bg-light p-2 mt-2">cd C:\xampp\htdocs\saham</pre>
                            </li>
                            <li>Aktifkan virtual environment:
                                <pre class="bg-light p-2 mt-2">.venv\Scripts\activate</pre>
                            </li>
                            <li>Jalankan aplikasi:
                                <pre class="bg-light p-2 mt-2">streamlit run src/app.py --server.port 8501</pre>
                            </li>
                            <li>Buka browser: <a href="http://localhost:8501" target="_blank">http://localhost:8501</a></li>
                        </ol>

                        <div class="alert alert-info mt-3">
                            <strong>Catatan:</strong> Pastikan Python 3.12+ dan dependencies sudah terinstall. 
                            Lihat <code>README.md</code> untuk informasi lengkap.
                        </div>

                        <div class="mt-4">
                            <a href="README.md" class="btn btn-secondary">📄 Baca README</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
