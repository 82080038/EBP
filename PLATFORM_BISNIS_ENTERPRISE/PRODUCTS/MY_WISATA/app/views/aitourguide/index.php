<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">AI Tour Guide</h1>
            
            <div class="card">
                <div class="card-body">
                    <div id="chatContainer" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #f8f9fa;">
                        <div class="chat-message ai-message mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary text-white rounded p-3" style="max-width: 70%;">
                                    <p class="mb-0">Halo! Saya adalah AI Tour Guide. Bagaimana saya bisa membantu Anda merencanakan perjalanan wisata Anda?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <input type="text" class="form-control" id="messageInput" placeholder="Ketik pesan Anda..." onkeypress="if(event.key === 'Enter') sendMessage()">
                        <button class="btn btn-primary" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendMessage() {
    var messageInput = document.getElementById('messageInput');
    var message = messageInput.value.trim();
    
    if (!message) return;
    
    // Add user message to chat
    addMessage(message, 'user');
    
    // Clear input
    messageInput.value = '';
    
    // Send to server
    var formData = new FormData();
    formData.append('message', message);
    formData.append('csrf_token', '<?= Middleware::csrfToken() ?>');
    
    fetch(window.APP_URL + 'aitourguide/chat', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            addMessage(data.response, 'ai');
        } else {
            addMessage('Maaf, terjadi kesalahan. Silakan coba lagi.', 'ai');
        }
    })
    .catch(error => {
        addMessage('Maaf, terjadi kesalahan koneksi.', 'ai');
    });
}

function addMessage(message, sender) {
    var chatContainer = document.getElementById('chatContainer');
    var messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message ' + sender + '-message mb-3';
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="d-flex justify-content-end">
                <div class="bg-success text-white rounded p-3" style="max-width: 70%;">
                    <p class="mb-0">${message}</p>
                </div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="d-flex align-items-start">
                <div class="bg-primary text-white rounded p-3" style="max-width: 70%;">
                    <p class="mb-0">${message}</p>
                </div>
            </div>
        `;
    }
    
    chatContainer.appendChild(messageDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
