class RealtimeMenu {
    constructor(userId, userName, userEmail, userAvatar) {
        this.userId = userId;
        this.userName = userName;
        this.userEmail = userEmail;
        this.userAvatar = userAvatar;
        this.db = window.firebaseDb;
        this.ticketTakeCollection = `taker`;
        this.statusCollection = `status`;
        this.unsubscribe = null;
        this.firebaseFunctions = null;
        this.isInitialized = false;
        this.init();
    }

    async init() {
        try {
            const { signInAnonymously } = await import("https://www.gstatic.com/firebasejs/12.1.0/firebase-auth.js");
            const auth = window.firebaseAuth;
            if (!auth.currentUser) {
                await signInAnonymously(auth);
            }
            const { collection, doc, deleteDoc, setDoc, onSnapshot, query, orderBy } = await import("https://www.gstatic.com/firebasejs/12.1.0/firebase-firestore.js");
            this.firebaseFunctions = { collection, doc, deleteDoc, setDoc, onSnapshot, query, orderBy };
            this.initializeUnsubscribe();
            this.isInitialized = true;
        } catch (error) {
            console.error('Failed to initialize RealtimeChat:', error);
        }
    }

    initializeUnsubscribe() {
        const { collection, onSnapshot } = this.firebaseFunctions;
        const takeTicketRef = collection(this.db, this.ticketTakeCollection);
        const statusRef = collection(this.db, this.statusCollection);
        const unsubscribers = [];
        const takeTicketUnsubscribe = onSnapshot(takeTicketRef, (snapshot) => {
            snapshot.docChanges().forEach(async (change) => {
                if (change.doc.data()) {
                    this.takeTicket(change.doc.data());
                    await deleteDoc(change.doc.ref);
                }
            });
        });

        // Used if the ticket got closed
        const statusUnsubscribe = onSnapshot(statusRef, (snapshot) => {
            snapshot.docChanges().forEach(async (change) => {
                const data = change.doc.data();
                if (data) {
                    if (data.status.toLowerCase() === 'closed') {
                        this.takeTicket(change.doc.data());
                        await deleteDoc(change.doc.ref);
                    }
                }
            });
        })

        unsubscribers.push(takeTicketUnsubscribe);
        unsubscribers.push(statusUnsubscribe);

        this.unsubscribe = () => {
            unsubscribers.forEach((fn) => fn());
        };
    }

    takeTicket(data) {
        const ticket = document.getElementById(`ticket-number-${data.ticketId}`);
        if (ticket) ticket.remove(); //This is for dev panel only
        const buttonMaker = document.querySelector('.btn-take');
        if (buttonMaker) buttonMaker.disabled = true;
    }

    destroy() {
        if (this.unsubscribe) {
            this.unsubscribe();
        }
        this.isInitialized = false;
    }

    async sendTicketTakeRequest(ticketId) {
        const { doc, setDoc, deleteDoc } = this.firebaseFunctions;
        const takeTicketRef = doc(this.db, this.ticketTakeCollection, ticketId);
        await setDoc(takeTicketRef, { ticketId: ticketId, userId: this.userId, userName: this.userName, userEmail: this.userEmail, userAvatar: this.userAvatar} );
        
    }

    async sendTicketStatus(ticketId) {
        const { doc, setDoc, deleteDoc } = this.firebaseFunctions;
        const statusRef = doc(this.db, this.statusCollection, ticketId);
        await setDoc(statusRef, { ticketId: ticketId, role: 'dev', status: 'in progress' });
    }
}

document.addEventListener('DOMContentLoaded', function(){
    function waitForFirebase(maxWaitMs = 5000) {
        return new Promise((resolve, reject) => {
            const start = Date.now();
            const check = () => {
                if (window.firebaseDb && window.userData) return resolve();
                if (Date.now() - start > maxWaitMs) return reject(new Error('Firebase DB not available'));
                setTimeout(check, 50);
            };
            check();
        });
    }

    const userData = window.userData || { id: null, name: null, email: null, avatar: null };

    waitForFirebase().then(() => {
        // Initialize real-time chat
        window.realtimeMenu = new RealtimeMenu(
            userData.id,
            userData.name,
            userData.email,
            userData.avatar
        );
    }).catch((e) => {
        console.warn('[realtime-chat] Skipping realtime init:', e.message);
    });

    document.querySelectorAll('form[id^="ticket-claim-"]').forEach(function (frm) {
        const ticketId = frm.id.replace("ticket-claim-", "");

        frm.addEventListener('submit', async function (e) {
            await window.realtimeMenu.sendTicketTakeRequest(ticketId);
            await window.realtimeMenu.sendTicketStatus(ticketId);
        });
    });
});