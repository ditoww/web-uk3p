<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-app.js";
  import { getDatabase, ref, child, set, remove, onValue, get, onChildAdded } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-database.js";
  import { getAuth, signInAnonymously, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/12.1.0/firebase-auth.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyCmtErS8-zwtWlbSoIk9-staRHpNQ9eo9M",
    authDomain: "ticketdesker.firebaseapp.com",
    databaseURL: "https://ticketdesker-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "ticketdesker",
    storageBucket: "ticketdesker.firebasestorage.app",
    messagingSenderId: "121036371692",
    appId: "1:121036371692:web:b70d3adb1161b247efd7c9",
    measurementId: "G-EC13ZK69QG"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const db = getDatabase(app);
  const auth = getAuth(app);
  signInAnonymously(auth);
  const user = @json(Auth::user());

  window.firebaseApp = app;
  window.firebaseDatabase = db;
  window.firebaseAuth = auth;
  window.firebaseFns = { ref, child, set, remove, onValue, get, onChildAdded, onAuthStateChanged };

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

    function formatName(name, support) {
        if (!support) return name.replace(/^\d+_/, '');
        else return name.replace(/^[A-Za-z]{2}-\d{8}-\d+_\d+_[1-4]_/, '');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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

    let messageHTML = '';

    const pfp = user.profile_picture ? user.profile_picture : "{{ asset('assetsadmin/img/avatars/default.png') }}";
    const userPfp = "{{ 'https://ui-avatars.com/api/?name=' . urlencode($selectedTicket->client_name) }}";

    const timeText = formatTime(date);

    const isRead = (chat.status === 'read');
    const sentClass = isRead ? 'd-none' : 'd-inline-block';
    const readClass = isRead ? 'd-inline-block' : 'd-none';

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
            <img src="${pfp}" class="rounded-circle ms-2 mt-1" width="32" height="32" style="object-fit: cover;"/>
        </div>`;
    } else {
        const timeFormat = !temp ?  `<div class="text-small text-muted mt-1">${formatTime(date)}</div>` : '';
        messageHTML = `
                <div class="d-flex">
                    <img src="${userPfp}" class="rounded-circle me-2 mt-1" width="32" height="32" style="object-fit: cover;"/>
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

  function statusChange(t) {
    const card = document.getElementById(`tc-${t}`);
    let badge = document.querySelector('.status-badge');
    let statusSpanner = card.querySelector('span');

    statusSpanner.classList.toggle('bg-primary', false);
    statusSpanner.classList.toggle('bg-warning', false);
    statusSpanner.classList.toggle('bg-success', false);
    statusSpanner.classList.toggle('text-dark', false);
    statusSpanner.classList.add('bg-danger');
    statusSpanner.textContent = "Closed";
    badge.classList.toggle('chuu', false);
    badge.classList.toggle('shuu', false);
    badge.classList.add('tei');
    badge.innerHTML = `
    <i class="bi bi-x-lg"></i>
    Closed
    `;

    document.getElementById('resolverButton').disabled = true;
    document.getElementById('attachment').onclick = null;
    document.getElementById('image').onclick = null;
    document.querySelector('textarea').disabled = true;
    document.querySelector('textarea').value = '';
    document.querySelector('textarea').placeholder = "Anda tidak dapat chat pada tiket yang sudah ditutup.";
  }

    function enableDots() {
        document.querySelector('.dots').classList.toggle('used', true);
    }

    function disableDots() {
        document.querySelector('.dots').classList.toggle('used', false);
    }

  @if(isset($selectedTicket))
    onAuthStateChanged(auth, (user) => {
        const ticketID = "{{ $selectedTicket->id }}";
        const chatRef = ref(db, 'tickets/' + ticketID + '/');
        const ticketRef = ref(db, 'tickets/status/closed/' + ticketID);
        let initialLoad = true;

        get(chatRef).then(() => {
            initialLoad = false;
        });

        onChildAdded(chatRef, async (snapshot) => {
            if (initialLoad) return;
            const chat = snapshot.val();
            //TODO: immediately after renderChat, restore send button
            if (String(chat.ticket_id) === ticketID) {
                const tempEl = document.querySelector(`[data-chat-id^="temp-"]`);
                if (tempEl) tempEl.remove();
                setTypingStatus(false);
                submitBtn.classList.remove('waiting');
                submitBtn.innerHTML = origText;
                validateInputs();
            }
            renderChat(chat, false);
            if (chat.type === "client") {
                const t = document.getElementById("currentTicketID").value;
                await readMessage(String(chat.id));
                const readingRef = ref(db, `tickets/status/reader/sp-${t}`);
                set(readingRef, { arrayReading: String(chat.id) });
                remove(readingRef);
            }
        });

        onChildAdded(ticketRef, (snapshot) => {
            statusChange(ticketID);
        });

        const typingRef = ref(db, 'tickets/typing/sp-'+ticketID);
        const usTypingRef = ref(db, 'tickets/typing/us-'+ticketID);
        const readerRef = ref(db, 'tickets/status/reader/us-'+ticketID);
        const inputEl = document.getElementById("messageTextarea");

        function setTypingStatus(isTyping) {
            if (isTyping) {
                set(typingRef, { typing: true });
            } else {
                remove(typingRef);
            }
        }

        function closeTypingStatus() {
            setTypingStatus(false);
        }
        
        inputEl.addEventListener("input", () => {
            if (inputEl.value.trim().length > 0) {
                setTypingStatus(true);
            } else {
                setTypingStatus(false);
            }
        });

        window.addEventListener("beforeunload", closeTypingStatus);
        window.addEventListener("pagehide", closeTypingStatus);
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "hidden") {
            closeTypingStatus();
            }
        });

        

        const connectedRef = ref(db, ".info/connected");
        onValue(connectedRef, (snap) => {
            if (!snap.val()) {
                setTypingStatus(false);
            }
        });

        onValue(usTypingRef, (snapshot) => {
            const typingUsers = snapshot.val() || {};

            if (typingUsers && typingUsers.typing) {
                enableDots();
            } else {
                disableDots();
            }
        });

        onChildAdded(readerRef, async (snapshot) => {
            await readMessage(snapshot.val());
        });

        async function readMessage(readIds) {
            const formData = new FormData();
            formData.append('ids', readIds);
            try {
                const response = await fetch('/support/clientchat/read/'+"{{$selectedTicket->id}}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                if (response.ok) {
                    if (!readIds) return;
                    const chatIds = readIds.split(',');
                    for (const readId of chatIds) {
                        const msgEl = document.querySelector(`[data-chat-id="${readId}"]`);
                        const readEl = msgEl.querySelector('.read-indicator');
                        if (readEl) {
                            readEl.classList.remove('d-none');
                            readEl.classList.add('d-inline-block');
                            readEl.textContent = 'Read';
                        }
                        const sentEl = msgEl.querySelector('.send-indicator');
                        if (sentEl) {
                            sentEl.classList.add('d-none');
                        }
                    }
                }
            } catch(err) {
                console.error(err);
            }
        }
    });
  @endif
</script>