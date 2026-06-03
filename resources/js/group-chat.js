(function () {
    const chatPage = document.querySelector('.wa-chat-page');
    if (!chatPage) return;

    const authId = chatPage.dataset.authId;
    const currentGroupId = chatPage.dataset.groupId;

    const groupChatForm = document.getElementById('groupChatForm');
    const groupMessageInput = document.getElementById('groupMessageInput');
    const groupImageInput = document.getElementById('groupImageInput');
    const groupSendButton = document.getElementById('groupSendButton');
    const groupMessagesBox = document.getElementById('groupMessagesBox');

    const groupImagePreviewContainer = document.getElementById('groupImagePreviewContainer');
    const groupImagePreview = document.getElementById('groupImagePreview');
    const removeGroupImagePreview = document.getElementById('removeGroupImagePreview');

    let editingGroupMessageId = null;

    function initGroupChatPage() {
        bindVoiceRecorder();
        bindGroupMessageButtons();
        bindGroupImagePreview();
        scrollGroupToBottom();
        startGroupEcho();
        bindGroupDetailsModal();
        bindEmojiPicker();
    }

    function clearGroupImagePreview() {
        if (groupImageInput) {
            groupImageInput.value = '';
        }

        if (groupImagePreview) {
            groupImagePreview.src = '';
        }

        if (groupImagePreviewContainer) {
            groupImagePreviewContainer.classList.add('hidden');
        }
    }

    function bindGroupImagePreview() {
        if (!groupImageInput || !groupImagePreview || !groupImagePreviewContainer) {
            return;
        }

        groupImageInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                clearGroupImagePreview();
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('Por favor seleciona apenas uma imagem.');
                clearGroupImagePreview();
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                groupImagePreview.src = e.target.result;
                groupImagePreviewContainer.classList.remove('hidden');
            };

            reader.readAsDataURL(file);
        });

        if (removeGroupImagePreview) {
            removeGroupImagePreview.onclick = function () {
                clearGroupImagePreview();
            };
        }
    }

    function sendVoiceNote(audioBlob) {
        const formData = new FormData();

        formData.append('audio', audioBlob, 'voice-note.webm');

        fetch(groupChatForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': chatPage.dataset.csrfToken,
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
                addGroupMessage(data);
                scrollGroupToBottom();
            })
            .catch(error => {
                alert(error.message || 'Erro ao enviar áudio.');
            });
    }

    function bindVoiceRecorder() {
        const button = document.getElementById('voiceNoteButton');

        if (!button || !groupChatForm) return;

        let mediaRecorder = null;
        let audioChunks = [];
        let isRecording = false;
        let currentStream = null;

        button.onclick = async function () {
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

                    button.classList.add('recording');
                    button.textContent = '⏹️';

                } catch (error) {
                    alert('Não foi possível aceder ao microfone.');
                }

                return;
            }

            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }

            isRecording = false;

            button.classList.remove('recording');
            button.textContent = '🎤';
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGroupChatPage);
    } else {
        initGroupChatPage();
    }

    function scrollGroupToBottom() {
        if (groupMessagesBox) {
            groupMessagesBox.scrollTop = groupMessagesBox.scrollHeight;
        }
    }

    function bindGroupMessageButtons() {
        document.querySelectorAll('.edit-group-message-btn').forEach(button => {
            button.onclick = function () {
                editingGroupMessageId = this.dataset.messageId;

                const row = document.getElementById(`group-message-${editingGroupMessageId}`);
                const body = row.querySelector('.group-message-body');

                if (!body) return;

                clearGroupImagePreview();

                groupMessageInput.value = body.textContent.trim();
                groupMessageInput.focus();

                groupSendButton.textContent = 'Guardar';

                if (groupImageInput) {
                    groupImageInput.disabled = true;
                }
            };
        });

        document.querySelectorAll('.delete-group-message-btn').forEach(button => {
            button.onclick = function () {
                const messageId = this.dataset.messageId;

                if (!confirm('Tens certeza que queres apagar esta mensagem?')) {
                    return;
                }

                deleteGroupMessage(messageId);
            };
        });
    }

    if (groupChatForm) {
        groupChatForm.onsubmit = function (e) {
            e.preventDefault();

            if (editingGroupMessageId) {
                updateGroupMessage(editingGroupMessageId, groupMessageInput.value);
                return false;
            }

            sendGroupMessage();
            return false;
        };
    }

    function sendGroupMessage() {
        const formData = new FormData(groupChatForm);

        fetch(groupChatForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': chatPage.dataset.csrfToken,
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
                addGroupMessage(data);
                groupChatForm.reset();
                clearGroupImagePreview();
                scrollGroupToBottom();
            })
            .catch(error => {
                alert(error.message || 'Erro ao enviar mensagem.');
            });
    }

    function cancelGroupEdit() {
        editingGroupMessageId = null;
        groupMessageInput.value = '';
        groupSendButton.textContent = 'Enviar';

        if (groupImageInput) {
            groupImageInput.disabled = false;
        }

        clearGroupImagePreview();
    }

    function updateGroupMessage(messageId, body) {
        fetch(`/group-messages/${messageId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': chatPage.dataset.csrfToken,
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
                markGroupMessageAsEdited(data);
                cancelGroupEdit();
            })
            .catch(error => {
                alert(error.message || 'Erro ao editar mensagem.');
            });
    }

    function deleteGroupMessage(messageId) {
        fetch(`/group-messages/${messageId}/delete-for-everyone`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': chatPage.dataset.csrfToken,
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
                markGroupMessageAsDeleted(data.id);

                if (editingGroupMessageId == data.id) {
                    cancelGroupEdit();
                }
            })
            .catch(error => {
                alert(error.message || 'Erro ao apagar mensagem.');
            });
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

    function addGroupMessage(message) {
        if (document.getElementById(`group-message-${message.id}`)) {
            return;
        }

        const mine = message.sender_id == authId;

        const row = document.createElement('div');
        row.id = `group-message-${message.id}`;
        row.className = `wa-message-row ${mine ? 'mine' : 'other'}`;

        row.innerHTML = `
            <div class="wa-bubble ${mine ? 'mine' : 'other'}">

                ${!mine ? `
                    <div class="wa-sender-name">
                        ${escapeHtml(message.sender_name || '')}
                    </div>
                ` : ''}

                ${message.deleted_for_everyone ? `
                    <em class="text-muted">Mensagem apagada</em>
                ` : `
                    ${message.body ? `
                        <div class="wa-message-body group-message-body">
                            ${escapeHtml(message.body)}
                        </div>
                    ` : ''}

                    ${message.image ? `
                        <img src="/storage/${message.image}" class="group-message-image">
                    ` : ''}

                    ${message.audio ? `
                        <audio controls class="mt-2" style="max-width:250px;">
                            <source src="/storage/${message.audio}" type="audio/webm">
                            O teu navegador não suporta áudio.
                        </audio>
                    ` : ''}

                    ${mine ? `
                        <div class="wa-actions">
                            ${message.body ? `
                                <button type="button"
                                        class="edit-group-message-btn"
                                        data-message-id="${message.id}">
                                    Editar
                                </button>
                            ` : ''}

                            <button type="button"
                                    class="delete-group-message-btn"
                                    data-message-id="${message.id}">
                                Apagar
                            </button>
                        </div>
                    ` : ''}
                `}

                <div class="wa-message-meta">
                    ${safeTime(message.created_at)}
                </div>

            </div>
        `;

        groupMessagesBox.appendChild(row);
        bindGroupMessageButtons();
    }

    function markGroupMessageAsEdited(message) {
        const row = document.getElementById(`group-message-${message.id}`);

        if (!row) return;

        const body = row.querySelector('.group-message-body');
        const meta = row.querySelector('.wa-message-meta');

        if (body) {
            body.textContent = message.body;
        }

        if (meta && !meta.textContent.includes('editada')) {
            meta.innerHTML += ' · editada';
        }
    }

    function markGroupMessageAsDeleted(messageId) {
        const row = document.getElementById(`group-message-${messageId}`);

        if (!row) return;

        const bubble = row.querySelector('.wa-bubble');

        bubble.innerHTML = `
            <em class="text-muted">Mensagem apagada</em>
        `;

        if (editingGroupMessageId == messageId) {
            cancelGroupEdit();
        }
    }

    function bindGroupDetailsModal() {
        const modal = document.getElementById('groupDetailsModal');
        const overlay = document.getElementById('groupDetailsModalOverlay');
        const closeBtn = document.getElementById('closeGroupDetailsModal');
        const cancelBtn = document.getElementById('cancelGroupDetailsModal');
        const searchInput = document.getElementById('groupMembersSearch');

        function openModal() {
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModal() {
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        document.onclick = function (e) {
            if (e.target.closest('#openGroupDetailsModal')) {
                e.preventDefault();
                openModal();
            }
        };

        if (overlay) {
            overlay.onclick = closeModal;
        }

        if (closeBtn) {
            closeBtn.onclick = closeModal;
        }

        if (cancelBtn) {
            cancelBtn.onclick = closeModal;
        }

        if (searchInput) {
            searchInput.oninput = function () {
                const search = this.value.toLowerCase();

                document.querySelectorAll('.group-member-option').forEach(item => {
                    const name = item.dataset.name || '';
                    const email = item.dataset.email || '';

                    item.style.display =
                        name.includes(search) || email.includes(search)
                            ? 'flex'
                            : 'none';
                });
            };
        }
    }

    function bindEmojiPicker() {
        const button = document.getElementById('emojiButton');
        const picker = document.getElementById('emojiPicker');
        const input = document.getElementById('groupMessageInput');

        if (!button || !picker || !input) {
            return;
        }

        const emojis = [
            '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣',
            '😊', '😍', '😎', '🤗', '🤔', '😢', '😭', '😡',
            '👍', '👎', '👏', '🙌', '❤️', '💙', '💚', '💛',
            '🧡', '💜', '🖤', '🤍', '💯', '🔥', '🎉', '🎊',
            '🚀', '⭐', '🌟', '💪', '🤝', '🙏', '😇', '🥳',
            '😘', '😋', '🤤', '😴', '🤯', '😱', '😜', '🤪',
            '😏', '🤭', '🙄', '😬', '😌', '😔', '😤', '😮',
            '🤩', '😇', '👌', '✌️', '👀', '💔', '✅', '❌'
        ];

        picker.innerHTML = '';

        emojis.forEach(emoji => {
            const span = document.createElement('span');
            span.textContent = emoji;

            span.onclick = function () {
                input.value += emoji;
                input.focus();
            };

            picker.appendChild(span);
        });

        button.onclick = function (e) {
            e.preventDefault();
            e.stopPropagation();
            picker.classList.toggle('hidden');
        };

        picker.onclick = function (e) {
            e.stopPropagation();
        };

        document.addEventListener('click', function (e) {
            if (!picker.contains(e.target) && !button.contains(e.target)) {
                picker.classList.add('hidden');
            }
        });
    }

    function startGroupEcho() {
        if (!window.Echo) {
            setTimeout(startGroupEcho, 500);
            return;
        }

        window.Echo.private(`group.${currentGroupId}`)
            .listen('.group.message.sent', function (e) {
                addGroupMessage(e.message);
                scrollGroupToBottom();
            })
            .listen('.group.message.deleted', function (e) {
                markGroupMessageAsDeleted(e.message.id);
            })
            .listen('.group.message.edited', function (e) {
                markGroupMessageAsEdited(e.message);
            });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
})();