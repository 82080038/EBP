<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];

// Permission check
if (!hasPermission('webhook_manage')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit;
}

$page_title = 'Webhook Configuration';

// Get existing webhooks
$webhooks = query("SELECT w.*, COUNT(wd.id) as total_deliveries, 
                  SUM(CASE WHEN wd.status = 'sent' THEN 1 ELSE 0 END) as successful_deliveries
                  FROM webhooks w
                  LEFT JOIN webhook_deliveries wd ON w.id = wd.webhook_id
                  GROUP BY w.id
                  ORDER BY w.created_at DESC");

if (!is_array($webhooks)) {
    $webhooks = [];
}
?>
<?php include BASE_PATH . '/includes/header.php'; ?>

<div class="main-container">
    <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

    <main class="content-area">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Webhook Configuration</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#webhookModal">
                <i class="bi bi-plus-circle"></i> Add Webhook
            </button>
        </div>

        <!-- Webhooks List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Webhooks</h6>
            </div>
            <div class="card-body">
                <?php if (empty($webhooks)): ?>
                    <p class="text-muted">No webhooks configured yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Event Type</th>
                                    <th>Target URL</th>
                                    <th>Status</th>
                                    <th>Deliveries</th>
                                    <th>Last Triggered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($webhooks as $webhook): ?>
                                    <tr>
                                        <td>
                                            <code><?php echo htmlspecialchars($webhook['event_type']); ?></code>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars(substr($webhook['target_url'], 0, 50)) . '...'; ?></small>
                                        </td>
                                        <td>
                                            <?php if ($webhook['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $webhook['total_deliveries'] ?? 0; ?> 
                                            (<?php echo $webhook['successful_deliveries'] ?? 0; ?> successful)
                                        </td>
                                        <td>
                                            <?php echo $webhook['last_triggered_at'] ? formatDate($webhook['last_triggered_at'], 'd M Y H:i') : 'Never'; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="testWebhook(<?php echo $webhook['id']; ?>)">
                                                <i class="bi bi-play"></i> Test
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editWebhook(<?php echo $webhook['id']; ?>)">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteWebhook(<?php echo $webhook['id']; ?>)">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Webhook Logs -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Delivery Logs</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Webhook</th>
                                <th>Event</th>
                                <th>Status</th>
                                <th>Attempt</th>
                                <th>Triggered At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $logs = query("SELECT wl.*, w.event_type, w.target_url 
                                          FROM webhook_logs wl
                                          JOIN webhooks w ON wl.webhook_id = w.id
                                          ORDER BY wl.triggered_at DESC
                                          LIMIT 10");
                            if (!is_array($logs)) $logs = [];
                            ?>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No delivery logs yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars($log['event_type']); ?></small></td>
                                        <td><small><?php echo htmlspecialchars($log['event_type']); ?></small></td>
                                        <td>
                                            <?php if ($log['status'] == 'success'): ?>
                                                <span class="badge bg-success">Success</span>
                                            <?php elseif ($log['status'] == 'failed'): ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Retrying</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $log['attempt_number']; ?></td>
                                        <td><?php echo formatDate($log['triggered_at'], 'd M Y H:i'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Webhook Modal -->
<div class="modal fade" id="webhookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="webhookModalTitle">Add Webhook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="webhookForm">
                    <input type="hidden" id="webhookId" name="webhook_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Event Type</label>
                        <select class="form-select" id="eventType" name="event_type" required>
                            <option value="">Select Event</option>
                            <option value="pinjaman.approved">Pinjaman Approved</option>
                            <option value="pinjaman.rejected">Pinjaman Rejected</option>
                            <option value="pembayaran.received">Pembayaran Received</option>
                            <option value="nasabah.registered">Nasabah Registered</option>
                            <option value="nasabah.blacklisted">Nasabah Blacklisted</option>
                            <option value="angsuran.overdue">Angsuran Overdue</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target URL</label>
                        <input type="url" class="form-control" id="targetUrl" name="target_url" required placeholder="https://example.com/webhook">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Secret Key (Optional)</label>
                        <input type="text" class="form-control" id="secretKey" name="secret_key" placeholder="HMAC signature secret">
                        <small class="text-muted">Used for webhook signature verification</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Retry Count</label>
                        <input type="number" class="form-control" id="retryCount" name="retry_count" value="3" min="0" max="10">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Retry Interval (seconds)</label>
                        <input type="number" class="form-control" id="retryInterval" name="retry_interval" value="300" min="60">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveWebhook()">Save</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const webhookModal = new bootstrap.Modal(document.getElementById('webhookModal'));
    
    function saveWebhook() {
        const form = document.getElementById('webhookForm');
        const formData = new FormData(form);
        
        fetch('/kewer/api/webhooks.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                webhookModal.hide();
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to save webhook'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    
    function editWebhook(id) {
        fetch('/kewer/api/webhooks.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const webhook = data.data;
                document.getElementById('webhookId').value = webhook.id;
                document.getElementById('eventType').value = webhook.event_type;
                document.getElementById('targetUrl').value = webhook.target_url;
                document.getElementById('secretKey').value = webhook.secret_key || '';
                document.getElementById('retryCount').value = webhook.retry_count || 3;
                document.getElementById('retryInterval').value = webhook.retry_interval || 300;
                document.getElementById('isActive').checked = webhook.is_active == 1;
                
                document.getElementById('webhookModalTitle').textContent = 'Edit Webhook';
                webhookModal.show();
            }
        });
    }
    
    function deleteWebhook(id) {
        if (confirm('Are you sure you want to delete this webhook?')) {
            fetch('/kewer/api/webhooks.php?id=' + id, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete webhook'));
                }
            });
        }
    }
    
    function testWebhook(id) {
        fetch('/kewer/api/webhooks.php?action=test&id=' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Test webhook sent successfully!');
            } else {
                alert('Error: ' + (data.error || 'Failed to send test webhook'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    
    document.querySelector('[data-bs-target="#webhookModal"]').addEventListener('click', function() {
        document.getElementById('webhookForm').reset();
        document.getElementById('webhookId').value = '';
        document.getElementById('webhookModalTitle').textContent = 'Add Webhook';
    });
</script>
</body>
</html>
