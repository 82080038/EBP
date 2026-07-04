    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>MyWisata</h5>
                    <p class="text-muted">Platform marketplace untuk layanan pariwisata di Indonesia.</p>
                </div>
                <div class="col-md-4">
                    <h5>Tautan</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= View::url() ?>" class="text-white text-decoration-none">Beranda</a></li>
                        <li><a href="<?= View::url('home/about') ?>" class="text-white text-decoration-none">Tentang</a></li>
                        <li><a href="<?= View::url('home/contact') ?>" class="text-white text-decoration-none">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <p class="text-muted mb-1"><i class="fas fa-envelope me-2"></i>admin@mywisata.com</p>
                    <p class="text-muted mb-1"><i class="fas fa-phone me-2"></i>+62 812 3456 7890</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; <?= date('Y') ?> MyWisata Application. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= View::asset('js/main.js') ?>"></script>
</body>
</html>
