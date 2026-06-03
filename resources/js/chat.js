(function () {
    const chatPage = document.querySelector('.chat-page');

    if (!chatPage) return;

    const authId = chatPage.dataset.authId;
    const currentChatUserId = chatPage.dataset.userId;
    const csrfToken = chatPage.dataset.csrfToken;

    const messagesBox = document.getElementById('messagesBox');
    const typingIndicator = document.getElementById('typingIndicator');

    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const imageInput = document.getElementById('imageInput');
    const sendButton = document.getElementById('sendButton');
    const cancelEditButton = document.getElementById('cancelEditButton');
    const voiceNoteButton = document.getElementById('voiceNoteButton');

    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const removeImagePreview = document.getElementById('removeImagePreview');

    let typingTimeout = null;
    let editingMessageId = null;

    function scrollToBottom() {
        if (messagesBox) {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }
    }

    function clearImagePreview() {
        if (imageInput) imageInput.value = '';
        if (imagePreview) imagePreview.src = '';
        if (imagePreviewContainer) imagePreviewContainer.classList.add('hidden');
    }

    function bindImagePreview() {
        if (!imageInput || !imagePreview || !imagePreviewContainer) return;

        imageInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                clearImagePreview();
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('Por favor seleciona apenas uma imagem.');
                clearImagePreview();
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.classList.remove('hidden');
            };

            reader.readAsDataURL(file);
        });

        if (removeImagePreview) {
            removeImagePreview.onclick = function () {
                clearImagePreview();
            };
        }
    }

    function safeTime(dateString) {
        if (!dateString) return '';

        if (/^\d{2}:\d{2}$/.test(dateString)) {
            return dateString;
        }

        const date = new Date(dateString);

        if (isNaN(date.getTime())) {
            return dateString;
        }

        return date.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function bindMessageButtons() {
        document.querySelectorAll('.edit-message-btn').forEach(button => {
            button.onclick = function () {
                editingMessageId = this.dataset.messageId;

                const row = document.getElementById(`message-${editingMessageId}`);
                const body = row.querySelector('.message-body');

                if (!body) return;

                clearImagePreview();

                messageInput.value = body.textContent.trim();
                sendButton.textContent = 'Guardar';
                cancelEditButton.classList.remove('hidden');
                messageInput.focus();
                imageInput.disabled = true;
            };
        });

        document.querySelectorAll('.delete-message-btn').forEach(button => {
            button.onclick = function () {
                const messageId = this.dataset.messageId;

                if (!confirm('Tens certeza que queres apagar esta mensagem?')) {
                    return;
                }

                deleteMessage(messageId);
            };
        });
    }

    function cancelEdit() {
        editingMessageId = null;
        messageInput.value = '';
        sendButton.textContent = 'Enviar';
        cancelEditButton.classList.add('hidden');
        imageInput.disabled = false;
        clearImagePreview();
    }

    function sendMessage() {
        const formData = new FormData(chatForm);

        fetch(chatForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            return data;
        })
        .then(data => {
            addMineMessage(data);
            chatForm.reset();
            clearImagePreview();
            scrollToBottom();
        })
        .catch(error => {
            alert(error.message || 'Erro ao enviar mensagem.');
        });
    }

    function sendVoiceNote(audioBlob) {
        const formData = new FormData();

        formData.append('audio', audioBlob, 'voice-note.webm');

        fetch(chatForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            return data;
        })
        .then(data => {
            addMineMessage(data);
            scrollToBottom();
        })
        .catch(error => {
            alert(error.message || 'Erro ao enviar áudio.');
        });
    }

    function bindVoiceRecorder() {
        if (!voiceNoteButton || !chatForm) return;

        let mediaRecorder = null;
        let audioChunks = [];
        let isRecording = false;
        let currentStream = null;

        voiceNoteButton.onclick = async function () {
            if (!isRecording) {
                try {
                    currentStream = await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });

                    mediaRecorder = new MediaRecorder(currentStream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = function (event) {
                        if (event.data && event.data.size > 0) {
                            audioChunks.push(event.data);
                        }
                    };

                    mediaRecorder.onstop = function () {
                        const audioBlob = new Blob(audioChunks, {
                            type: 'audio/webm'
                        });

                        sendVoiceNote(audioBlob);

                        if (currentStream) {
                            currentStream.getTracks().forEach(track => track.stop());
                            currentStream = null;
                        }
                    };

                    mediaRecorder.start();
                    isRecording = true;

                    voiceNoteButton.classList.add('recording');
                    voiceNoteButton.textContent = '⏹️';

                } catch (error) {
                    alert('Não foi possível aceder ao microfone.');
                }

                return;
            }

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }

            isRecording = false;

            voiceNoteButton.classList.remove('recording');
            voiceNoteButton.textContent = '🎤';
        };
    }

    function updateMessage(messageId, body) {
        fetch(`/messages/${messageId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                body: body
            })
        })
        .then(async response => {
            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            return data;
        })
        .then(data => {
            markMessageAsEdited(data);
            cancelEdit();
        })
        .catch(error => {
            alert(error.message || 'Erro ao editar mensagem.');
        });
    }

    function deleteMessage(messageId) {
        fetch(`/messages/${messageId}/delete-for-everyone`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(async response => {
            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            return data;
        })
        .then(data => {
            markMessageAsDeleted(data.id);

            if (editingMessageId == data.id) {
                cancelEdit();
            }
        })
        .catch(error => {
            alert(error.message || 'Erro ao apagar mensagem.');
        });
    }

    function addMineMessage(message) {
        if (document.getElementById(`message-${message.id}`)) {
            return;
        }

        const row = document.createElement('div');
        row.id = `message-${message.id}`;
        row.className = 'wa-message-row mine flex mb-2 justify-end';

        row.innerHTML = `
            <div class="wa-bubble mine bg-emerald-100 rounded-2xl rounded-tr-sm max-w-[75%] md:max-w-[48%] px-3 py-2 shadow-sm text-[14px] leading-snug">

                ${message.body ? `
                    <div class="wa-message-body message-body whitespace-pre-wrap break-words text-slate-800">
                        ${escapeHtml(message.body)}
                    </div>
                ` : ''}

                ${message.image ? `
                    <img src="/storage/${message.image}" class="rounded-xl mt-2 max-w-[220px]">
                ` : ''}

                ${message.audio ? `
                    <audio controls class="mt-2" style="max-width:250px;">
                        <source src="/storage/${message.audio}" type="audio/webm">
                        O teu navegador não suporta áudio.
                    </audio>
                ` : ''}

                <div class="wa-actions flex justify-end gap-2 mt-2">
                    ${message.body ? `
                        <button type="button"
                            class="edit-message-btn text-[11px] px-2.5 py-0.5 rounded-full bg-white/70 hover:bg-white text-slate-500"
                            data-message-id="${message.id}">
                            Editar
                        </button>
                    ` : ''}

                    <button type="button"
                        class="delete-message-btn text-[11px] px-2.5 py-0.5 rounded-full bg-white/70 hover:bg-white text-slate-500"
                        data-message-id="${message.id}">
                        Apagar
                    </button>
                </div>

                <div class="wa-message-meta text-[10px] text-slate-400 mt-1 text-right whitespace-nowrap">
                    ${safeTime(message.created_at)} ·
                    <span id="message-status-${message.id}">✓ Enviada</span>
                </div>
            </div>
        `;

        messagesBox.appendChild(row);
        bindMessageButtons();
    }

    function addMessage(message) {
        if (document.getElementById(`message-${message.id}`)) {
            return;
        }

        const row = document.createElement('div');
        row.id = `message-${message.id}`;
        row.className = 'wa-message-row other flex mb-2 justify-start';

        row.innerHTML = `
            <div class="wa-bubble other bg-white rounded-2xl rounded-tl-sm max-w-[75%] md:max-w-[48%] px-3 py-2 shadow-sm text-[14px] leading-snug">

                ${message.deleted_for_everyone
                    ? `<em class="text-slate-400">Mensagem apagada</em>`
                    : `
                        ${message.body
                            ? `<div class="wa-message-body message-body whitespace-pre-wrap break-words text-slate-800">${escapeHtml(message.body)}</div>`
                            : ''}

                        ${message.image
                            ? `<img src="/storage/${message.image}" class="rounded-xl mt-2 max-w-[220px]">`
                            : ''}

                        ${message.audio
                            ? `<audio controls class="mt-2" style="max-width:250px;">
                                    <source src="/storage/${message.audio}" type="audio/webm">
                                    O teu navegador não suporta áudio.
                               </audio>`
                            : ''}
                    `
                }

                <div class="wa-message-meta text-[10px] text-slate-400 mt-1 text-right whitespace-nowrap">
                    ${safeTime(message.created_at)}
                </div>

            </div>
        `;

        messagesBox.appendChild(row);
    }

    function markMessageAsDeleted(messageId) {
        const message = document.getElementById(`message-${messageId}`);

        if (!message) return;

        const bubble = message.querySelector('.wa-bubble');

        bubble.innerHTML = `
            <em class="text-slate-400">Mensagem apagada</em>
        `;
    }

    function markMessageAsEdited(message) {
        const messageRow = document.getElementById(`message-${message.id}`);

        if (!messageRow) return;

        const body = messageRow.querySelector('.message-body');
        const meta = messageRow.querySelector('.wa-message-meta');

        if (body) {
            body.textContent = message.body;
        }

        if (meta && !meta.textContent.includes('editada')) {
            meta.innerHTML += ' · editada';
        }
    }

    function updateMessageStatus(messageId, status) {
        const statusElement = document.getElementById(`message-status-${messageId}`);

        if (statusElement) {
            statusElement.textContent = status;
        }
    }

    function markDelivered(messageId) {
        fetch(`/messages/${messageId}/delivered`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    if (cancelEditButton) {
        cancelEditButton.onclick = function () {
            cancelEdit();
        };
    }

    if (chatForm) {
        chatForm.onsubmit = function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (editingMessageId) {
                updateMessage(editingMessageId, messageInput.value);
                return false;
            }

            sendMessage();
            return false;
        };
    }

    function startEcho() {
        if (!window.Echo) {
            setTimeout(startEcho, 500);
            return;
        }

        window.Echo.private(`chat.${authId}`)
            .listen('.message.sent', function (e) {
                if (e.message.sender_id == currentChatUserId) {
                    addMessage(e.message);
                    scrollToBottom();
                    markDelivered(e.message.id);
                }
            })
            .listen('.message.deleted', function (e) {
                markMessageAsDeleted(e.message.id);
            })
            .listen('.message.edited', function (e) {
                markMessageAsEdited(e.message);
            })
            .listen('.message.read', function (e) {
                updateMessageStatus(e.message.id, '✓✓ Lida');
            })
            .listen('.message.delivered', function (e) {
                updateMessageStatus(e.message.id, '✓✓ Entregue');
            })
            .listen('.user.typing', function (e) {
                if (e.sender_id == currentChatUserId) {
                    typingIndicator.classList.remove('hidden');

                    clearTimeout(typingTimeout);

                    typingTimeout = setTimeout(() => {
                        typingIndicator.classList.add('hidden');
                    }, 1500);
                }
            });
    }

    bindMessageButtons();
    bindVoiceRecorder();
    bindImagePreview();
    scrollToBottom();
    startEcho();
})();