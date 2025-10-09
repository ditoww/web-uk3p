// Real-time Chat functionality using Firebase Firestore
class RealtimeChat {
    constructor(ticketId, userId, userName, userAvatar) {
        this.ticketId = ticketId;
        this.userId = userId;
        this.userName = userName;
        this.userAvatar = userAvatar;
        this.config = window.chatConfig || {};
        this.db = window.firebaseDb;
        this.messagesCollection = `tickets/${ticketId}/messages`;
        this.devReaderCollection = `tickets/reader/dev`;
        this.sptReaderCollection = `tickets/reader/spt`;
        this.devTypingCollection = `tickets/typing/dev`;
        this.sptTypingCollection = `tickets/typing/spt`;
        this.statusCollection = `status`;
        this.ticketTakeCollection = `taker`;
        this.tempChatLookoutDevID = null;
        this.tempChatLookoutSptID = null;
        this.upcomingDevChat = null;
        this.upcomingSptChat = null;
        this.tempImageURLs = [];
        this.unsubscribe = null;
        this.isInitialized = false;
        // Tambah cache untuk mencegah ACK read ganda
        this.readAcked = new Set(); // CHANGED
        //Quick note: the status changes are in docChanges: added, modified, and removed. You can guess which one. You can get the data the moment it gets removed.
        //For statusCollection, there are two variables: role and status.
        this.init();
    }

    async init() {
        try {
            console.log('Initializing RealtimeChat...');
            console.log('Firebase DB:', this.db);

            const { signInAnonymously } = await import("https://www.gstatic.com/firebasejs/12.1.0/firebase-auth.js");

            const auth = window.firebaseAuth;
            if (!auth.currentUser) {
                await signInAnonymously(auth);
            }
            
            // Import Firebase functions
            const { collection, addDoc, doc, deleteDoc, updateDoc, setDoc, onSnapshot, query, orderBy, serverTimestamp } = await import("https://www.gstatic.com/firebasejs/12.1.0/firebase-firestore.js");
            
            this.firebaseFunctions = {
                collection,
                addDoc,
                setDoc,
                doc,
                deleteDoc,
                updateDoc,
                onSnapshot,
                query,
                orderBy,
                serverTimestamp
            };

            console.log('Firebase functions loaded:', Object.keys(this.firebaseFunctions));
            if (!this.isInitialized)
            await this.sendReads(location.pathname.includes('/Support/chat'), window.initRead);
            this.initializeUnsubscribe();
            this.isInitialized = true;
            console.log('RealtimeChat initialized for ticket:', this.ticketId);
        } catch (error) {
            console.error('Failed to initialize RealtimeChat:', error);
        }
    }

    /*setupRealtimeListener() {
        if (!this.firebaseFunctions) {
            console.error('Firebase functions not loaded');
            return;
        }

        const { collection, onSnapshot, query, orderBy } = this.firebaseFunctions;
        
        console.log('Setting up real-time listener for collection:', this.messagesCollection);
        const messagesRef = collection(this.db, this.messagesCollection);
        const q = query(messagesRef, orderBy('timestamp', 'asc'));
        
        this.unsubscribe = onSnapshot(q, (snapshot) => {
            console.log('Received snapshot with', snapshot.docChanges().length, 'changes');
            snapshot.docChanges().forEach((change) => {
                if (change.type === 'added') {
                    console.log('New message received:', change.doc.data());
                    this.displayMessage(change.doc.data());
                }
            });
        }, (error) => {
            console.error('Error listening to messages:', error);
        });
        
        console.log('Real-time listener set up successfully');
    }*/

    initializeUnsubscribe() {
        if (!this.firebaseFunctions) {
            console.error("Firebase functions not loaded");
            return;
        }

        const { collection, onSnapshot, deleteDoc, query, orderBy } = this.firebaseFunctions;

        const collectionsToListen = [
            { key: "messages", path: this.messagesCollection, ordered: true },
            { key: "devReader", path: this.devReaderCollection },
            { key: "sptReader", path: this.sptReaderCollection },
            { key: "devTyping", path: this.devTypingCollection },
            { key: "sptTyping", path: this.sptTypingCollection },
            { key: "statusUpdates", path: this.statusCollection},
            { key: "ticketTake", path: this.ticketTakeCollection}
        ];

        const unsubscribers = [];

        collectionsToListen.forEach(({ key, path, ordered }) => {
            try {
                console.log(`Setting up listener for ${key}: ${path}`);
                const ref = collection(this.db, path);
                const q = ordered ? query(ref, orderBy("timestamp", "asc")) : ref;

                const unsubscribeFn = onSnapshot(
                    q,
                    (snapshot) => {
                        console.log(`[${key}] Snapshot received (${snapshot.docChanges().length} changes)`);

                        snapshot.docChanges().forEach(async (change) => {
                            const data = change.doc.data();
                            if (data) {
                                if (key === "messages") {
                                    if (change.type === "added") {
                                    // Tampilkan pesan dari lawan (own message akan disaring di displayMessage)
                                    this.displayMessage(data);

                                    // Jika sudah ada chat_id, kirim read-receipt segera (tanpa menunggu removed)
                                    if (data.chat_id) { // CHANGED
                                        await this.maybeSendReadAck(data);
                                    }
                                    }
                                    if (change.type === "modified") {
                                    // Simpan upcoming untuk fallback lama (tetap dipertahankan)
                                    if (data.sender === 'support') {
                                        this.upcomingSptChat = data;
                                    } else if (data.sender === 'dev') {
                                        this.upcomingDevChat = data;
                                    }
                                    // Map id sementara -> id final supaya tidak duplikat saat removed
                                    if (data.messageId && data.chat_id) {
                                        const tempEl = document.querySelector(`[data-message-id="${data.messageId}"]`);
                                        if (tempEl) {
                                            tempEl.setAttribute('data-message-id', String(data.chat_id));
                                            const tempSender = tempEl.querySelector('.send-indicator');
                                            tempSender.classList.remove('d-none');
                                            tempSender.classList.add('d-inline-block');
                                        }
                                    }

                                    // Kirim read-receipt segera setelah chat_id tersedia (REAL-TIME) // CHANGED
                                    if (data.chat_id) {
                                        await this.maybeSendReadAck(data);
                                    }
                                    }
                                    if (change.type === "removed") {
                                    // Render hanya jika belum ada elemen dengan chat_id
                                    if (location.pathname.includes('/Support/chat') && this.upcomingDevChat !== null) {
                                        const exists = document.querySelector(`[data-message-id="${this.upcomingDevChat.chat_id}"]`);
                                        if (!exists) {
                                        this.displayMessage(this.upcomingDevChat, true);
                                        }
                                        // Fallback: kirim read jika belum terkirim (anti-duplikat dengan cache) // CHANGED
                                        await this.maybeSendReadAck(this.upcomingDevChat);
                                        this.upcomingDevChat = null;
                                    }
                                    if (location.pathname.includes('/dev/chatdev') && this.upcomingSptChat !== null) {
                                        const exists = document.querySelector(`[data-message-id="${this.upcomingSptChat.chat_id}"]`);
                                        if (!exists) {
                                        this.displayMessage(this.upcomingSptChat, true);
                                        }
                                        // Fallback: kirim read jika belum terkirim // CHANGED
                                        await this.maybeSendReadAck(this.upcomingSptChat);
                                        this.upcomingSptChat = null;
                                    }
                                    }
                                }

                                if (key === "devReader" && (change.type === "added" || change.type === "modified") && String(data.ticketId) === String(this.ticketId)) {
                                    this.handleReaderUpdate(data);
                                    await deleteDoc(change.doc.ref);
                                }
                                if (key === "sptReader" && (change.type === "added" || change.type === "modified") && String(data.ticketId) === String(this.ticketId)) {
                                    this.handleReaderUpdate(data);
                                    await deleteDoc(change.doc.ref);
                                }
                                if (((key === "sptTyping" && location.pathname.includes('/dev/chatdev')) || (key === "devTyping" && location.pathname.includes('/Support/chat'))) && String(data.ticketId) === String(this.ticketId)) {
                                    if (change.type === "added") document.querySelector('.dots').classList.toggle('used', true);
                                    else if (change.type === "removed") document.querySelector('.dots').classList.toggle('used', false);
                                }

                                if (key === "statusUpdates" && (change.type === "added" || change.type === "modified")) {
                                    this.handleStatusUpdate(data);
                                    await deleteDoc(change.doc.ref);
                                }

                                if (key === "ticketTake" && change.type === "added") {
                                    this.handleTakeTicketUpdate(data);
                                    await deleteDoc(change.doc.ref);
                                }
                            }
                        });
                    },
                    (error) => {
                        console.error(`Error listening to ${key}:`, error);
                    }
                );

                unsubscribers.push(unsubscribeFn);
            } catch (err) {
                console.error(`Failed to setup listener for ${key}:`, err);
            }
        });

        this.unsubscribe = () => {
            unsubscribers.forEach((fn) => fn());
            console.log("All listeners unsubscribed.");
        };
    }

    async maybeSendReadAck(data) {

    }

    handleReaderUpdate(readData) {
        const as = String(readData.arrayReading).split(',');
        for (let a of as) {
            a = a.trim();
            if (!a) continue;
            const msgEl = document.querySelector(`[data-message-id="${a}"]`);
            if (!msgEl) continue;

            // Tampilkan "read" dan sembunyikan "sent"
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

    async sendReads(isSupportChat, s) {
        const { doc, setDoc, deleteDoc } = this.firebaseFunctions;
        const c = isSupportChat ? this.sptReaderCollection : this.devReaderCollection;

        const readRef = doc(this.db, c, this.ticketId);
        await setDoc(readRef, { arrayReading: s, ticketId: this.ticketId }, { merge: true });
        await this.readMessageToDatabase(s);
    }

    async sendTicketStatus(isSupportChat, s) {
        const { doc, setDoc, deleteDoc } = this.firebaseFunctions;
        const r = isSupportChat ? 'spt' : 'dev';
        const c = this.statusCollection;

        const statusRef = doc(this.db, c, this.ticketId);
        await setDoc(statusRef, { role: r, status: s, ticketId: this.ticketId}, { merge: true });
    }

    handleTakeTicketUpdate(ticketTakeData) {
        if (location.pathname.includes('/dev/chatdev')) return;
        const un = ticketTakeData.userName;
        const ue = ticketTakeData.userEmail;
        const ua = ticketTakeData.userAvatar;
        this.displaySystemMessage(un);
        const t = String(ticketTakeData.ticketId);
        const card = document.getElementById('tc-' + ticketTakeData.ticketId);
        const small = card.querySelector('small');
        small.textContent = small.textContent.replace('(Belum Diambil)', ticketTakeData.userName);
        if (t !== String(this.ticketId)) return;
        const submitterPFP = document.querySelector('.submitter img');
        const submitterName = document.querySelector('.submitter-name');
        const submitterEmail = document.querySelector('.submitter-email');
        submitterPFP.src = ua;
        submitterName.textContent = un;
        if (ue !== "<USER TIDAK PUNYA EMAIL>") submitterEmail.textContent = ue;
    }

    handleStatusUpdate(statusData) {
        const s = statusData.status.toLowerCase();
        const r = statusData.role.toLowerCase(); 
        // When ticket gets claimed change Open to In Progress for Support Devs
        if (r === "dev" && s === "in progress") {
            const card = document.getElementById('tc-' + statusData.ticketId);
            let statusSpanner = card.querySelector('span');
            statusSpanner.classList.toggle('bg-primary', false);
            statusSpanner.classList.toggle('bg-warning', true);
            statusSpanner.classList.toggle('text-dark', true);
            statusSpanner.classList.toggle('bg-success', false);
            statusSpanner.classList.toggle('bg-danger', false);
            statusSpanner.textContent = "In Progress";
            if (String(statusData.ticketId) !== String(this.ticketId)) return;
            let badge = document.querySelector('.status-badge');
            badge.classList.toggle('chuu', true);
            badge.classList.toggle('shuu', false);
            badge.classList.toggle('tei', false);
            badge.innerHTML = `
            <i class="bi bi-clock"></i>
            In Progress
            `;
        }

        // When Support closes ticket, change to Closed for Devs
        if (r === "spt" && location.pathname.includes('/dev/chatdev') && s === "closed") {
            const card = document.getElementById('tc-' + statusData.ticketId);
            let statusSpanner = card.querySelector('span');
            statusSpanner.classList.toggle('bg-primary', false);
            statusSpanner.classList.toggle('bg-warning', false);
            statusSpanner.classList.toggle('bg-success', false);
            statusSpanner.classList.toggle('text-dark', false);
            statusSpanner.classList.add('bg-danger');
            statusSpanner.textContent = "Closed";
            if (String(statusData.ticketId) !== String(this.ticketId)) return;
            let badge = document.querySelector('.status-badge');
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

        // When Developer resolves ticket, change to Resolved for Support.
        if (r === "dev" && location.pathname.includes('/Support/chat') && s === "resolved") {
            const card = document.getElementById('tc-' + statusData.ticketId);
            let statusSpanner = card.querySelector('span');
            statusSpanner.classList.toggle('bg-primary', false);
            statusSpanner.classList.toggle('bg-warning', false);
            statusSpanner.classList.toggle('text-dark', false);
            statusSpanner.classList.toggle('bg-success', true);
            statusSpanner.classList.toggle('bg-danger', false);
            statusSpanner.textContent = "Resolved";
            if (String(statusData.ticketId) !== String(this.ticketId)) return;
            let badge = document.querySelector('.status-badge');
            badge.classList.toggle('chuu', false);
            badge.classList.toggle('shuu', true);
            badge.classList.toggle('tei', false);
            badge.innerHTML = `
            <i class="bi bi-check2-circle"></i>
            Resolved
            `;
        }
    }

    async sendMessage(message, attachments = []) {
        if (!this.firebaseFunctions) {
            console.error('Firebase functions not loaded');
            return { success: false, error: 'Firebase functions not loaded' };
        }

        try {
            const { collection, addDoc, deleteDoc, serverTimestamp } = this.firebaseFunctions;
            
            // Process attachments for Firebase storage
            const processedAttachments = await this.processAttachmentsForFirebase(attachments);
            
            // Determine sender type based on current page
            let senderType = 'dev'; // default
            if (location.pathname.includes('/Support/chat')) {
                senderType = 'support';
            }

            const messageData = {
                sender: senderType,
                senderId: this.userId,
                senderName: this.userName,
                message: message,
                attachments: processedAttachments,
                timestamp: serverTimestamp(),
                createdAt: new Date().toISOString(),
                messageId: 'temp_' + Date.now().toString() + '_' + Math.random().toString(36).substr(2, 9)
            };

            // Optionally include avatar in Firestore based on config
            if (this.config.sendAvatar !== false) {
                messageData.senderAvatar = this.userAvatar;
            }
            
            if (senderType === 'dev') this.tempChatLookoutDevID = messageData.messageId;
            if (senderType === 'support') this.tempChatLookoutSptID = messageData.messageId;

            const messagesRef = collection(this.db, this.messagesCollection);
            const docRef = await addDoc(messagesRef, messageData);
            //console.log('Message sent successfully with attachments:', processedAttachments.length);

            return { success: true, docRef };
        } catch (error) {
            console.error('Error sending/deleted message:', error);
            return { success: false, error };
        }
    }

    async sendTypingStatus(isSupportChat, status) {
        const { doc, setDoc, deleteDoc } = this.firebaseFunctions;
        const c = isSupportChat ? this.sptTypingCollection : this.devTypingCollection;

        const typingDocRef = doc(this.db, c, this.ticketId);

        if (status) {
            await setDoc(typingDocRef, { typing: true, ticketId: this.ticketId }, { merge: true });
        } else {
            await deleteDoc(typingDocRef);
        }
    }

    async processAttachmentsForFirebase(attachments) {
        const processedAttachments = [];
        
        for (const attachment of attachments) {
            try {
                if (attachment.type === 'file') {
                    // For files, we'll create a blob URL for download
                    processedAttachments.push({
                        type: 'file',
                        name: attachment.name,
                        url: attachment.url,
                        size: attachment.size || 0,
                        mimeType: attachment.mimeType || 'application/octet-stream'
                    });
                }
                else if (attachment.type === 'image') {
                    // For images, we'll use base64 for immediate display
                    processedAttachments.push({
                        type: 'image',
                        name: attachment.name,
                        url: attachment.url,
                        size: attachment.size || 0,
                        mimeType: attachment.mimeType || 'image/jpeg'
                    });
                }
            } catch (error) {
                console.error('Error processing attachment:', attachment.name, error);
            }
        }
        
        return processedAttachments;
    }

    async readMessageToDatabase(readIds) {
        if (!readIds) return;
        const formData = new FormData();
        formData.append('ids', readIds);
        
        const endpoint = location.pathname.includes('/Support/chat') ? '/Support/chat/read/' : '/dev/chatdev/read/';

        try {
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const csrf = tokenEl ? tokenEl.getAttribute('content') : (window.csrfToken || '');

            const response = await fetch (endpoint+this.ticketId, {
                method: 'POST',
                headers: csrf ? {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                } : { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            if (response.ok) {
                this.handleReaderUpdate({arrayReading: readIds, ticketId: this.ticketId});
            }
        } catch (err) {
            console.error(err);
        }
    }

    async storeInDatabase(message, attachments = [], images = []) {
        try {
            const formData = new FormData();
            formData.append('ticket', this.ticketId);
            formData.append('message', message);
            
            // Add attachments to form data
            attachments.forEach(file => {
                formData.append('attachment[]', file);
            });

            images.forEach(file => {
                formData.append('image[]', file);
            });

            // Determine the correct endpoint based on current page
            let endpoint = '/dev/chatdev/store-realtime'; // default
            if (location.pathname.includes('/Support/chat')) {
                endpoint = '/Support/chat/store-realtime';
            }

            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const csrf = tokenEl ? tokenEl.getAttribute('content') : (window.csrfToken || '');

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: csrf ? {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                } : { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            if (!response.ok) {
                throw new Error('Failed to store message in database');
            }

            const result = await response.json();
            console.log('Message stored in database:', result);
            return result;
        } catch (error) {
            console.error('Error storing message in database:', error);
            return null;
        }
    }

    destroyTempMessage() {
        if (this.tempChatLookoutDevID) {
            const devtempId = this.tempChatLookoutDevID;
            const devchatBody = document.getElementById('chatBody');
            const devexistingMessage = devtempId ? devchatBody.querySelector(`[data-message-id="${devtempId}"]`) : null;
            if (devexistingMessage) {
                devexistingMessage.remove();
                this.tempChatLookoutDevID = null;
            }
        }
        if (this.tempChatLookoutSptID) {
            const tempId = this.tempChatLookoutSptID;
            const chatBody = document.getElementById('chatBody');
            const existingMessage = tempId ? chatBody.querySelector(`[data-message-id="${tempId}"]`) : null;
            if (existingMessage) {
                existingMessage.remove()
                this.tempChatLookoutSptID = null;
            };
        }
    }

    displaySystemMessage(devName) {
        const chatBody = document.getElementById('chatBody');
        const dotsDiv = chatBody.querySelector('.dots');

        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="chat-divider text-center">
                <div class="d-flex justify-content-center">
                    <div class="message system">
                        ${devName} telah mengambil tiket tersebut.
                    </div>
                </div>
            </div>
        `;

        const messageNode = wrapper.firstElementChild;

        if (dotsDiv) {
            chatBody.insertBefore(messageNode, dotsDiv);
        } else {
            chatBody.appendChild(messageNode);
        }
    }

    displayMessage(messageData, real = false) {
      const chatBody = document.getElementById('chatBody');
      if (!chatBody) return;
      const rawId = messageData.chat_id ?? (messageData.messageId || messageData.id);
      const uniqueId = rawId != null ? String(rawId) : null;
      const isTemp = uniqueId?.includes('temp_');
      const existingMessage = uniqueId ? chatBody.querySelector(`[data-message-id="${uniqueId}"]`) : null;
      if (existingMessage) return;

      if (!real) {
        const isDevPage = location.pathname.includes('/dev/chatdev');
        const isSupportPage = location.pathname.includes('/Support/chat');
        const isOwnSender =
          (isDevPage && messageData.sender === 'dev') ||
          (isSupportPage && messageData.sender === 'support');
        if (!isOwnSender) return;
      }

      const messageDiv = document.createElement('div');
      messageDiv.className = 'chat-divider';
      messageDiv.setAttribute('data-message-id', uniqueId || Date.now());

      const isOwnMessage = String(messageData.senderId) === String(this.userId);
      let messageClass = isOwnMessage ? 'dev' : 'user';
      if (isOwnMessage) messageDiv.className = 'chat-divider text-end';

      const createdTs = messageData.createdAt || messageData.timestamp || Date.now();
      const timeText = this.formatTime(createdTs);

      // Pastikan hanya satu status tampil pada pesan kita
      const isRead = (messageData.status === 'read');
      const sentClass = isRead || isTemp ? 'd-none' : 'd-inline-block';
      const readClass = isRead ? 'd-inline-block' : 'd-none';

      let messageHTML = '';
      const showAvatar = (window.chatConfig?.showAvatar !== false);

      if (isOwnMessage) {
          const timeFormat = `
              <div class="text-small text-muted mt-1 text-end">
                  <span class="send-indicator me-1 ${sentClass}">Sent</span>
                  <span class="read-indicator me-1 ${readClass}">Read</span>
                  ${timeText}
              </div>
          `;
          const avatarHTML = showAvatar ? `<img src="${userProf}" class="rounded-circle ms-2 mt-1 chat-avatar" width="32" height="32" style="object-fit: cover;"/>` : '';
          messageHTML = `
              <div class="d-flex justify-content-end align-items-start">
                  <div>
                      ${messageData.message ? `<div class="message ${messageClass}" style="min-width: 180px; max-width: 300px;">${this.escapeHTML(messageData.message)}</div>` : ''}
                      ${this.renderAttachments(messageData.attachments, isOwnMessage, real)}
                      ${timeFormat}
                  </div>
                  ${avatarHTML}
              </div>
          `;
      } else {
          const timeFormat = `<div class="text-small text-muted mt-1">${timeText}</div>`;
          const avatarHTML = showAvatar ? `<img src="${otherProf}" class="rounded-circle me-2 mt-1 chat-avatar" width="32" height="32" style="object-fit: cover;"/>` : '';
          messageHTML = `
              <div class="d-flex">
                  ${avatarHTML}
                  <div>
                      ${messageData.message ? `<div class="message ${messageClass} mb-2" style="min-width: 180px; max-width: 300px;">${this.escapeHTML(messageData.message)}</div>` : ''}
                      ${this.renderAttachments(messageData.attachments, isOwnMessage, real)}
                      ${timeFormat}
                  </div>
              </div>
          `;
      }

      messageDiv.innerHTML = messageHTML;
      const dotsDiv = chatBody.querySelector('.dots');
      if (dotsDiv) chatBody.insertBefore(messageDiv, dotsDiv);
      else chatBody.appendChild(messageDiv);

      chatBody.scrollTop = chatBody.scrollHeight;
      this.linkifyMessage(messageDiv);
    }

    renderAttachments(attachments, isOwnMessage, real = false) {
        if (!attachments || attachments.length === 0) return '';

        const endpoint = !isOwnMessage ? routes.client : routes.support;
        const imgpoint = !isOwnMessage ? imgRoutes.client : imgRoutes.support;

        const getEnd = (f, i) => {
            return i ? `${imgpoint}/${f}` : `${endpoint}/${f}`;
        } 

        const getSrc = (att) => {
            if (!real && att.url) {
                return att.url;
            }

            const isImg = ['png', 'jpg', 'jpeg', 'webp'].includes(att.extension);

            return att.name ?
            getEnd(att.name, isImg) : 
            '';
        };

        let html = '';

        const supChat = location.pathname.includes('/Support/chat');

        attachments.forEach(attachment => {
            if (attachment.type === 'image') {
                html += `
                    <div class="mt-${isOwnMessage ? '2' : '3'} ${isOwnMessage ? 'text-end' : ''}">
                        <img src="${getSrc(attachment)}" class="img-thumbnail rounded shadow-sm chat-image-clickable" alt="${this.formatName(attachment.name, (supChat && isOwnMessage) || (!supChat && !isOwnMessage))}" style="max-width: 250px; max-height: 200px; object-fit: cover;">
                    </div>
                `;
            } else {
                const fileSize = attachment.size ? this.formatFileSize(attachment.size) : '';
                const fileIcon = this.getFileIcon(attachment.mimeType || attachment.name);
                
                html += `
                    <div class="file-box mt-2 p-2 rounded bg-light border d-inline-block" style="margin-top:8px; max-width: 300px;">
                        <i class="${fileIcon} me-2"></i>
                        <div class="d-flex flex-column">
                            <a href="${getSrc(attachment)}" download="${this.formatName(attachment.name, (supChat && isOwnMessage) || (!supChat && !isOwnMessage))}" class="fw-semibold text-primary text-decoration-none">${this.formatName(attachment.name, (supChat && isOwnMessage) || (!supChat && !isOwnMessage))}</a>
                            ${fileSize ? `<small class="text-muted">${fileSize}</small>` : ''}
                        </div>
                    </div>
                `;
            }
        });
        return html;
    }

    getFileIcon(mimeType) {
        if (mimeType.startsWith('image/')) return 'bi bi-file-earmark-image';
        if (mimeType.includes('pdf')) return 'bi bi-file-earmark-pdf pdf-icon';
        if (mimeType.includes('word') || mimeType.includes('document')) return 'bi bi-file-earmark-word doc-icon';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'bi bi-file-earmark-excel';
        if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'bi bi-file-earmark-ppt';
        if (mimeType.includes('zip') || mimeType.includes('rar')) return 'bi bi-file-earmark-zip';
        if (mimeType.includes('text/')) return 'bi bi-file-earmark-text';
        return 'bi bi-file-earmark-arrow-down';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    formatName(name, support) {
        if (support) return name.replace(/^[A-Za-z]{2}-\d{8}-\d+_\d+_/, '');
        else return name.replace(/^[A-Za-z]{2}-\d{8}-\d+_\d+_dev_\d+_/, '');
    }

    escapeHTML(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    linkifyMessage(messageDiv) {
        const messageElements = messageDiv.querySelectorAll('.message');
        messageElements.forEach(element => {
            const rawText = element.textContent || '';
            const escapedText = this.escapeHTML(rawText);
            const trimmedText = escapedText.replace(/^\n+/, '').replace(/\n+$/, '');
            const withBreaks = trimmedText.replace(/\n/g, '<br>');
            const linkifiedText = this.linkify(withBreaks);
            element.innerHTML = linkifiedText;
        });
    }

    linkify(text) {
        const urlRegex = /((https?:\/\/)[^\s]+)/g;
        return text.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener noreferrer" onmouseover="text-decoration: underline;">$1</a>');
    }

    formatTime(ts) {
        if (!ts) return '';
        let date;
        try {
            if (ts instanceof Date) {
                date = ts;
            } else if (ts && typeof ts.toDate === 'function') {
                date = ts.toDate(); // Firestore Timestamp
            } else if (ts && typeof ts === 'object' && 'seconds' in ts) {
                date = new Date(ts.seconds * 1000); // Firestore Timestamp (plain)
            } else if (typeof ts === 'string' || typeof ts === 'number') {
                date = new Date(ts);
            } else {
                return '';
            }
        } catch {
            return '';
        }

        if (isNaN(date.getTime())) return '';

        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);

        if (diffInHours < 24) {
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        } else {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
        }
    }

    destroy() {
        if (this.unsubscribe) {
            this.unsubscribe();
        }
        this.isInitialized = false;
        console.log('RealtimeChat destroyed');
    }
}

// Initialize real-time chat when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const isDevChat = location.pathname.includes('/dev/chatdev');
    const isSupportChat = location.pathname.includes('/Support/chat');
    const ticketStatus = window.ticketStatus || 'Unknown';

    // Hanya inisialisasi jika status tiket "In Progress"
    if ((!isDevChat && !isSupportChat)) {
        return;
    }

    // Wait for Firebase DB to be available (module script may load asynchronously)
    function waitForFirebase(maxWaitMs = 5000) {
        return new Promise((resolve, reject) => {
            const start = Date.now();
            const check = () => {
                if (window.firebaseDb && window.userData && window.ticketId) return resolve();
                if (Date.now() - start > maxWaitMs) return reject(new Error('Firebase DB not available'));
                setTimeout(check, 50);
            };
            check();
        });
    }

    // Get user data from the page - these will be set by the Blade template
    const userData = window.userData || { id: null, name: null, avatar: null };
    const ticketId = window.ticketId || null;

    waitForFirebase().then(async () => {
        // Initialize real-time chat
        window.realtimeChat = new RealtimeChat(
            ticketId,
            userData.id,
            userData.name,
            userData.avatar
        );
    }).catch((e) => {
        console.warn('[realtime-chat] Skipping realtime init:', e.message);
    });

    // Global sending guard to prevent duplicate submissions
    let isSending = false;

    // Override the form submission to use real-time chat
    const chatForm = document.getElementById('chat-form');
    const resolverForm = document.getElementById('ticket-resolver');
    /*const resolverButton = document.getElementById('resolverButton');

    if (resolverButton) {
        resolverButton.addEventListener('click', async function (e) {
            if (location.pathname.includes('/Support/chat')) {
                await window.realtimeChat.sendTicketStatus(true, 'Closed');
            }
            else if (location.pathname.includes('/dev/chatdev')) {
                await window.realtimeChat.sendTicketStatus(false, 'Resolved');
            }
            resolverForm.submit();
        });
    }*/

    resolverForm.addEventListener('submit', async function (e) {
        if (location.pathname.includes('/Support/chat')) {
                await window.realtimeChat.sendTicketStatus(true, 'Closed');
        }
        if (location.pathname.includes('/dev/chatdev')) {
            await window.realtimeChat.sendTicketStatus(false, 'Resolved');
        }
    })



    inputEl = document.getElementById("messageTextarea");
    if (inputEl) {
        inputEl.addEventListener('input', async function() {
            if (inputEl.value.trim().length > 0) {
                await window.realtimeChat.sendTypingStatus(isSupportChat, true);
            } else {
                await window.realtimeChat.sendTypingStatus(isSupportChat, false);
            }
        });
    }

    async function closeTypingStatus() {
        await window.realtimeChat.setTypingStatus(isSupportChat, false);
    }

    window.addEventListener("beforeunload", closeTypingStatus);
    window.addEventListener("pagehide", closeTypingStatus);
        document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "hidden") {
            closeTypingStatus();
        }
    });
    if (chatForm) {
        const submitBtn = chatForm.querySelector('button[type="submit"]'); //Makes sure it is re-disabled.
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (isSending) {
                console.log('Send prevented: already sending');
                return;
            }

            const messageTextarea = document.getElementById('messageTextarea');
            const message = messageTextarea.value.trim();

            // Check if message is empty and no attachments
            if (!message && selectedImages.length === 0 && selectedAtts.length === 0) {
                return;
            }

            // Acquire send lock
            isSending = true;

            // Prepare attachments
            const attachments = [];

            //TODO: Make a function that replaces the attachments.
            
            // Process images with better error handling
            for (const file of selectedImages) {
                try {
                    const imageData = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            resolve({
                                type: 'image',
                                name: file.name,
                                url: URL.createObjectURL(file),
                                size: file.size,
                                mimeType: file.type
                            });
                        };
                        reader.onerror = function() {
                            reject(new Error('Failed to read image file'));
                        };
                        reader.readAsDataURL(file);
                    });
                    attachments.push(imageData);
                } catch (error) {
                    console.error('Error processing image:', file.name, error);
                    // Continue with other files
                }
            }

            // Process files with better error handling
            for (const file of selectedAtts) {
                try {
                    const fileData = {
                        type: 'file',
                        name: file.name,
                        url: URL.createObjectURL(file),
                        size: file.size,
                        mimeType: file.type
                    };
                    attachments.push(fileData);
                } catch (error) {
                    console.error('Error processing file:', file.name, error);
                    // Continue with other files
                }
            }

            // Show loading state
            const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
                submitBtn.classList.add('waiting');
            }

            try {
                console.log('Sending message via real-time chat...');
                messageTextarea.value = '';
                attsToSend = [...selectedAtts];
                imagesToSend = [...selectedImages];
                selectedImages = [];
                selectedAtts = [];
                updatePreviewContainer();
                updateFilePreviewContainer();
                
                // Send message via real-time chat
                const result = await window.realtimeChat.sendMessage(message, attachments);
                console.log('Real-time send result:', result.success);
                
                if (result.success) {
                    console.log('Storing message in database...');
                    // Also store in database
                    const r = await window.realtimeChat.storeInDatabase(message, attsToSend, imagesToSend);

                    // Jika gagal simpan DB, fallback submit normal agar konsisten seperti sisi dev
                    if (!r || typeof r !== 'object') {
                        console.warn('DB store failed or invalid payload; falling back to normal form submit.');
                        try {
                            const { deleteDoc } = window.realtimeChat.firebaseFunctions;
                            if (result && result.docRef) await deleteDoc(result.docRef);
                        } catch (e) {
                            console.warn('Failed to cleanup temp doc:', e);
                        }
                        chatForm.submit(); // native submit tidak memicu listener lagi
                        return;
                    }

                    // Clear form
                    const messageTextarea = document.getElementById('messageTextarea');
                    messageTextarea.style.height = 'auto';
                    
                    //showNotification('Message sent successfully!', 'success');
                    //console.log('Message sent and stored successfully');

                    // Sinkronkan nama/url attachment nyata (jika ada)
                    if (r.attachments && Array.isArray(r.attachments) && r.attachments.length > 0) {
                        if (result.attachments && Array.isArray(result.attachments)) {
                            r.attachments.forEach((realAtt, i) => {
                                if (result.attachments[i]) {
                                    result.attachments[i].name = realAtt.name;
                                    result.attachments[i].url  = realAtt.url;
                                }
                            });
                        }
                    }

                    const { updateDoc } = window.realtimeChat.firebaseFunctions;
                    try {
                        if (result.docRef) {
                            await updateDoc(result.docRef, { chat_id: r.chat_id, attachments: r.attachments || [] });
                        }
                    } catch (err) {
                        console.warn("Failed to update chat_id on temp Firestore doc:", err);
                    }
                    
                    await window.realtimeChat.sendTypingStatus(isSupportChat, false);
                    if (r.sender === 'support') window.realtimeChat.upcomingSptChat = r;
                    else if (r.sender === 'dev') window.realtimeChat.upcomingDevChat = r;

                    window.realtimeChat.destroyTempMessage();
                    window.realtimeChat.displayMessage(r, true);
                    
                    try {
                        const { deleteDoc } = window.realtimeChat.firebaseFunctions;
                        if (result.docRef) await deleteDoc(result.docRef);
                    } catch (_) {}
                } else {
                    console.log('Real-time send failed, falling back to traditional submission');
                    // Fallback to traditional form submission
                    chatForm.submit();
                }
            } catch (error) {
                console.error('Error sending message:', error);
                showNotification('Failed to send message. Please try again.', 'error');
            } finally {
                // Restore button state with multiple fallbacks
                const restoreButton = () => {
                    if (!submitBtn) return;
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.classList.remove('waiting');
                    validateInputs();
                    console.log('Button state restored');
                };
                
                // Try immediate restore
                restoreButton();
                
                // Also try with timeout as backup
                setTimeout(restoreButton, 100);
                setTimeout(restoreButton, 500);
                setTimeout(restoreButton, 1000);

                // Release send lock
                isSending = false;
            }
        });
    }

    // Enter-to-send on all chat pages (Shift+Enter = newline)
    const messageTextarea = document.getElementById('messageTextarea');
    if (messageTextarea && chatForm) {
        messageTextarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!isSending) {
                    const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                    chatForm.dispatchEvent(submitEvent);
                }
            }
        });
    }

    // Notification function
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

    // Hide existing chat avatar images rendered by server if configured
    try {
        if (window.chatConfig?.showAvatar === false) {
            document.querySelectorAll('.chat-avatar').forEach(el => {
                el.style.display = 'none';
            });
        }
    } catch (e) { /* no-op */ }
});
