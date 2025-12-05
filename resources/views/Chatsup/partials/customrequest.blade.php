<script>
    const chatForm = document.getElementById('chat-form');
    const user = @json(Auth::user());
    const submitBtn = chatForm.querySelector('#submitButton'); //Makes sure it is re-disabled.
    const origText = submitBtn.innerHTML;
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatName(name, support) {
        if (!support) return name.replace(/^\d+_/, '');
        else return name.replace(/^[A-Za-z]{2}-\d{8}-\d+_\d+_[1-4]_/, '');
    }

    function renderAttachments(attachments, isOwnMessage, temp = false) {
        if (!attachments || attachments.length === 0) return '';

        //console.log(attachments);

        let html = '';
        let ext = '';

        
        const routes = {
            client: "{{ url('/files') }}",
            support: "{{ url('/files/sup') }}"
        };

        const imgRoutes = {
            client: "{{ url('/images') }}",
            support: "{{ url('/images/sup') }}"
        };

        const endpoint = !isOwnMessage ? routes.client : routes.support;
        const imgpoint = !isOwnMessage ? imgRoutes.client : imgRoutes.support;

        const getEnd = (f, i) => {
            return i ? `${imgpoint}/${f}` : `${endpoint}/${f}`;
        } 

        const getSrc = (att) => {
            if (att.preview) {
                return att.preview;
            }

            const isImg = ['png', 'jpg', 'jpeg', 'webp'].includes(att.extension);

            return att.name ?
            getEnd(att.name, isImg) : 
            '';
        };

        attachments.forEach(attachment => {

            //Null checker + Extension Getter
            if (!attachment) return;
            if (attachment.extension) {
                ext = attachment.extension.toLowerCase();
            } else if (attachment.name) {
                const parts = attachment.name.split('.');
                if (parts.length > 1) {
                    ext = parts.pop().toLowerCase();
                }
            }
            

            const allowedImages = ['png', 'jpg', 'jpeg', 'webp'];
            const isImage = allowedImages.includes(ext);
            
            if (isImage) {
                if (temp)
                    html += `
                        <div class="mt-2 ${isOwnMessage ? 'text-end' : ''}">
                            <img src="${getSrc(attachment)}" class="img-thumbnail rounded shadow-sm chat-image-clickable" alt="${formatName(attachment.name, isOwnMessage)}" style="max-width: 250px; max-height: 200px; object-fit: cover;">
                        </div>
                    `;
                else {
                    const maxW = 250;
                    const maxH = 200;
                    const width = attachment.resolution[0];
                    const height= attachment.resolution[1];
                    const ratio = Math.min(maxW/width, maxH/height, 1);
                    const w = Math.floor(width * ratio);
                    const h = Math.floor(height * ratio);
                    html += `
                        <div class="mt-2 ${isOwnMessage ? 'text-end' : ''}">
                            <img src="${getSrc(attachment)}" class="img-thumbnail rounded shadow-sm chat-image-clickable" alt="${formatName(attachment.name, isOwnMessage)}" style="width: ${w}px; height: ${h}px; object-fit: cover;">
                        </div>
                    `;
                }
            } else {
                const fileIcon = getFileIcon(ext);
                const fileSize = attachment.size ? formatFileSize(attachment.size) : 'Size N/A'
                html += `
                    <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block">
                        <i class="${fileIcon}"></i>
                        <div class="d-flex flex-column">
                            <a href="${getSrc(attachment)}" download="${formatName(attachment.name, isOwnMessage)}" class="fw-semibold text-primary text-decoration-none">${formatName(attachment.name, isOwnMessage)}</a>
                            ${fileSize ? `<small class="text-muted">${fileSize}</small>` : ''}
                        </div>
                    </div>
                `;
            }
        });
        return html;
    }  

    function formatTime(timestamp){
        if (!timestamp) return '';
        const date = new Date(timestamp);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        if (diffInHours < 24) {
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
        } else {
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
        }
    }

    function renderChat(chat, temp = false) {
        const chatBody = document.getElementById('chatBody');
        if (!chatBody) return;

        const utcDate = new Date(chat.created_at);
        let date = '';

        if (temp) date = utcDate;
        else date = new Date(utcDate.getTime() + (utcDate.getTimezoneOffset() * 60000 * -1));

        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-divider';

        messageDiv.setAttribute('data-chat-id', chat.id);

        const isOwnMessage = chat.type === 'support';
        let messageClass = 'client';

        if (isOwnMessage) {
            messageDiv.className = 'chat-divider text-end';
            messageClass = 'support'
        }

        const timeText = formatTime(date);

        const isRead = (chat.status === 'read');
        const sentClass = isRead || temp ? 'd-none' : 'd-inline-block';
        const readClass = isRead ? 'd-inline-block' : 'd-none';

        let messageHTML = '';

        const pfp = user.profile_picture ? user.profile_picture : "{{ asset('assetsadmin/img/avatars/default.png') }}";
        const userPfp = "{{ 'https://ui-avatars.com/api/?name=' . urlencode($selectedTicket->client_name) }}";

        if (isOwnMessage) {
            const timeFormat = `
              <div class="text-small text-muted mt-1 text-end">
                  <span class="send-indicator me-1 ${sentClass}">Sent</span>
                  <span class="read-indicator me-1 ${readClass}">Read</span>
                  ${timeText}
              </div>
            `;
            messageHTML = `
            <div class="d-flex justify-content-end align-items-start">
                <div>
                    ${chat.response ? `<div class="message ${messageClass}" style="min-width: 180px; max-width: 300px;">${escapeHTML(chat.response)}</div>` : ''}
                    ${renderAttachments(chat.attachments, isOwnMessage, temp)}
                    ${timeFormat}
                </div>
                <img src="${pfp}" class="rounded-circle ms-2 mt-1" width="32" height="32" style="object-fit: cover;" />
            </div>`;
        } else {
            const timeFormat = !temp ?  `<div class="text-small text-muted mt-1">${formatTime(date)}</div>` : '';
            messageHTML = `
                    <div class="d-flex">
                        <img src="${userPfp}" class="rounded-circle me-2 mt-1" width="32" height="32" style="object-fit: cover;" />
                        <div>
                            ${chat.response ? `<div class="message ${messageClass} mb-2" style="min-width: 180px; max-width: 300px;">${escapeHTML(chat.response)}</div>` : ''}
                            ${renderAttachments(chat.attachments, isOwnMessage, temp)}
                            ${timeFormat}
                        </div>
                    </div>
                `;
        }

        if (!temp && document.querySelector(`[data-chat-id="${chat.id}"]`)) {
        document.querySelector(`[data-chat-id="${chat.id}"]`).remove();
        }

        messageDiv.innerHTML = messageHTML;
        const dotsDiv = chatBody.querySelector('.dots');

        if (dotsDiv) {
            chatBody.insertBefore(messageDiv, dotsDiv);
        } else {
            chatBody.appendChild(messageDiv);
        }
        
        chatBody.scrollTop = chatBody.scrollHeight;

        linkifyMessage(messageDiv);
    }

    @if(isset($selectedTicket))
        document.getElementById('chat-form').addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent page reload

            const messageInput = document.getElementById('messageTextarea');

            const endpoint = "{{ route('SupportClientChat.store') }}";
            const message = messageInput.value.trim();
            if (!message && selectedAtts.length === 0 && selectedImages.length === 0) return;

            messageInput.value = '';
            submitBtn.classList.toggle('disabled', true);
            submitBtn.disabled = true;

            const tempId = 'temp-' + Date.now();

            const attsToSend = [...selectedAtts];
            
            const imagesToSend = [...selectedImages];

            const tempImages = imagesToSend.map(file => ({
                name: file.name,
                extension: file.name.split('.').pop().toLowerCase(),
                preview: URL.createObjectURL(file),
                url: URL.createObjectURL(file)
            }));

            const tempAtts = attsToSend.map(file => ({
                name: file.name,
                extension: file.name.split('.').pop().toLowerCase(),
                size: file.size,
                preview: URL.createObjectURL(file),
                url: URL.createObjectURL(file)
            }));

            const tempAttachments = [...tempAtts, ...tempImages];

            const tempChat = {
                id: tempId,
                type: 'support',
                response: message,
                attachments: tempAttachments,
                created_at: new Date().toISOString(),
            };

            renderChat(tempChat, true);

            const formData = new FormData();
            formData.append('ticket', "{{ $selectedTicket->id }}");
            formData.append('message', message);

            attsToSend.forEach(file => {
                formData.append('attachment[]', file);
            });

            imagesToSend.forEach(file => {
                formData.append('image[]', file);
            });

            selectedImages = [];
            selectedAtts = [];
            updatePreviewContainer();
            updateFilePreviewContainer();

            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
            submitBtn.classList.add('disabled');
            submitBtn.classList.add('waiting');
            submitBtn.disabled = true;

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();

                submitBtn.innerHTML = originalBtnText;

                if (messageInput.value.trim().length <= 0) {
                    submitBtn.classList.add('disabled');
                    submitBtn.disabled = true;
                } else {
                    submitBtn.classList.remove('disabled');
                    submitBtn.disabled = false;
                }

                if (response.ok && data.success) {
                    messageInput.style.height = 'auto';
                    tempAttachments.forEach(att => URL.revokeObjectURL(att.preview));
                    //showNotification('Message sent successfully!', 'success');

                    if (window.firebaseDatabase && window.firebaseFns) {
                        const db = window.firebaseDatabase;
                        const { ref, remove } = window.firebaseFns;

                        const ticketID = "{{ $selectedTicket->id }}";

                        const typingRef = ref(db, `tickets/typing/sp-${ticketID}`);
                        remove(typingRef);
                    }

                    //console.log(messageInput.value.trim().length <= 0);
                    //console.log('Button state restored');

                    // Replace temp chat id with real db id
                    /*const tempEl = document.querySelector(`[data-chat-id="${tempId}"]`);
                    if (tempEl) {
                        const sender = tempEl.querySelector('.send-indicator');
                        sender.classList.remove('d-none');
                        sender.classList.add('d-inline-block');
                        sender.textContent = 'Sent';
                        tempEl.setAttribute('data-chat-id', data.chat.id);
                    }*/
                } else {
                    showNotification(data.error || 'Pesan gagal dikirim', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Error dengan pesan', 'error');
            }
        });
    @endif
</script>