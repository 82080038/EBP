---
description: How to create a new module (controller, model, views) following project conventions
---

# Create a New Module

## Overview
Each module consists of: Controller, Model(s), View(s), and optionally API endpoints.

## Steps

### 1. Create Model
File: `app/models/ExampleName.php`
```php
<?php
class ExampleName extends Model {
    protected $table = 'example_names';

    public function getAll() {
        return $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1")->fetchAll();
    }

    public function findById($id) {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        )->fetch();
    }
}
```

### 2. Create Controller
File: `app/controllers/ExampleNameController.php`
```php
<?php
class ExampleNameController extends Controller {

    public function __construct() {
        Middleware::requireRole('admin');  // or 'wisatawan', 'tour_guide'
    }

    public function index() {
        $data = $this->model('ExampleName')->getAll();
        $this->view('admin/examples', [
            'title' => 'Examples',
            'data' => $data
        ]);
    }

    public function apiList() {
        $data = $this->model('ExampleName')->getAll();
        $this->json(['status' => 'success', 'data' => $data]);
    }
}
```

### 3. Create View
File: `app/views/admin/examples.php`
```php
<?php include 'app/views/layouts/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include 'app/views/layouts/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 mt-3"><?= Helper::e($title) ?></h1>
            <!-- Content here -->
        </main>
    </div>
</div>
<?php include 'app/views/layouts/footer.php'; ?>
```

### 4. Add API Endpoint (if needed)
Add to `app/controllers/ApiController.php` or create dedicated API method in controller:
```php
public function apiCreate() {
    $input = json_decode(file_get_contents('php://input'), true);
    $v = new Validator($input);
    $v->required(['name'])->max('name', 100);
    if ($v->fails()) {
        $this->json(['status' => 'error', 'message' => 'Validasi gagal', 'errors' => $v->errors()], 422);
    }
    $id = $this->model('ExampleName')->insert($input);
    Logger::audit('create', 'examples', "Created #{$id}");
    $this->json(['status' => 'success', 'data' => ['id' => $id]], 201);
}
```

### 5. Add Route (if using routes.php)
The router auto-maps `URL → Controller/method`. No manual route needed unless custom mapping required.

### 6. Add to Sidebar
Edit `app/views/layouts/sidebar.php` to add menu item for the new module.

## Checklist
- [ ] Model created with PDO prepared statements
- [ ] Controller created with middleware role check
- [ ] View created with escaped output (`Helper::e()`)
- [ ] CSRF token in all POST forms
- [ ] API endpoint returns standard JSON format
- [ ] Audit log for create/update/delete actions
- [ ] Input validation on all endpoints
- [ ] Responsive layout (mobile + desktop)

## Reference
- Architecture: `docs/03_DESAIN_ARSITEKTUR_APLIKASI.md`
- Coding standards: `docs/28_STANDAR_KODE_KONTRIBUSI.md`
- API design: `docs/21_API_DESIGN_AJAX_JSON.md`
