<?php include APP_ROOT . '/app/views/layouts/tourguide_header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Bahasa & Spesialisasi</h2>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bahasa</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="language_id" class="form-label">Tambah Bahasa</label>
                        <select class="form-select" id="language_id">
                            <option value="">Pilih Bahasa</option>
                            <?php foreach ($languages as $lang): ?>
                                <?php $hasLanguage = false; ?>
                                <?php foreach ($guide_languages as $gl): ?>
                                    <?php if ($gl['language_id'] == $lang['id']): ?>
                                        <?php $hasLanguage = true; break; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!$hasLanguage): ?>
                                    <option value="<?= $lang['id'] ?>"><?= View::e($lang['name']) ?> (<?= View::e($lang['native_name']) ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="proficiency" class="form-label">Tingkat Kemampuan</label>
                        <select class="form-select" id="proficiency">
                            <option value="basic">Basic</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="fluent">Fluent</option>
                            <option value="native">Native</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="addLanguage()">
                        <i class="fas fa-plus me-2"></i>Tambah Bahasa
                    </button>
                    
                    <hr>
                    
                    <h6>Bahasa Saya:</h6>
                    <?php if (empty($guide_languages)): ?>
                        <p class="text-muted">Belum ada bahasa yang ditambahkan.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($guide_languages as $gl): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= View::e($gl['language_name']) ?></strong>
                                    <span class="badge bg-secondary ms-2"><?= View::e($gl['proficiency']) ?></span>
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="removeLanguage(<?= $gl['language_id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Spesialisasi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="specialization_id" class="form-label">Tambah Spesialisasi</label>
                        <select class="form-select" id="specialization_id">
                            <option value="">Pilih Spesialisasi</option>
                            <?php foreach ($specializations as $spec): ?>
                                <?php $hasSpec = false; ?>
                                <?php foreach ($guide_specializations as $gs): ?>
                                    <?php if ($gs['specialization_id'] == $spec['id']): ?>
                                        <?php $hasSpec = true; break; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!$hasSpec): ?>
                                    <option value="<?= $spec['id'] ?>"><?= View::e($spec['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="addSpecialization()">
                        <i class="fas fa-plus me-2"></i>Tambah Spesialisasi
                    </button>
                    
                    <hr>
                    
                    <h6>Spesialisasi Saya:</h6>
                    <?php if (empty($guide_specializations)): ?>
                        <p class="text-muted">Belum ada spesialisasi yang ditambahkan.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($guide_specializations as $gs): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><?= View::e($gs['specialization_name']) ?></strong>
                                <button class="btn btn-sm btn-danger" onclick="removeSpecialization(<?= $gs['specialization_id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addLanguage() {
    var languageId = document.getElementById('language_id').value;
    var proficiency = document.getElementById('proficiency').value;
    
    if (!languageId) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silakan pilih bahasa terlebih dahulu',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    ajax({
        url: window.APP_URL + 'tourguide/addLanguage',
        method: 'POST',
        data: { language_id: languageId, proficiency: proficiency },
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonColor: '#0d6efd'
                });
            }
        }
    });
}

function removeLanguage(languageId) {
    Swal.fire({
        title: 'Hapus Bahasa?',
        text: 'Apakah Anda yakin ingin menghapus bahasa ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            ajax({
                url: window.APP_URL + 'tourguide/removeLanguage',
                method: 'POST',
                data: { language_id: languageId },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                }
            });
        }
    });
}

function addSpecialization() {
    var specializationId = document.getElementById('specialization_id').value;
    
    if (!specializationId) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silakan pilih spesialisasi terlebih dahulu',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    ajax({
        url: window.APP_URL + 'tourguide/addSpecialization',
        method: 'POST',
        data: { specialization_id: specializationId },
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonColor: '#0d6efd'
                });
            }
        }
    });
}

function removeSpecialization(specializationId) {
    Swal.fire({
        title: 'Hapus Spesialisasi?',
        text: 'Apakah Anda yakin ingin menghapus spesialisasi ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            ajax({
                url: window.APP_URL + 'tourguide/removeSpecialization',
                method: 'POST',
                data: { specialization_id: specializationId },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                }
            });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/tourguide_footer.php'; ?>
