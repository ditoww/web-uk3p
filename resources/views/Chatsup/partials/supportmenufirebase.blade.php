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

    onAuthStateChanged(auth, (fbuser) => {
        const ticketRef = ref(db, 'tickets/status/progress/');
        const closedRef = ref(db, 'tickets/status/closed');

        onChildAdded(ticketRef, async (snapshot) => {
            const data = snapshot.val();
             if (!data) return;

            const id = data.user_id;
            const tick = data.id;
            if (String(user.id) === String(id)) {
                getTicket(tick);
            } else {
                eraseTicket(tick);
            }
            //await sendTicket(snapshot.val().id);
        });

        onChildAdded(closedRef, (snapshot) => {
            const data = snapshot.val();
            if (!data) return;

            if (data.status === "DITUTUP") {
                const ticket = document.getElementById('tc-' + data.id);
                if (ticket) ticket.remove();
            }
        })
    });

    async function sendTicket(t) {
        const formData = new FormData();
        try {
            const response = await fetch(`/support/ticket/getTicket/${t}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (String(user.id) === String(data.user_id)) {
                    getTicket(t);
                } else {
                    eraseTicket(t);
                }

            }
        } catch(err) {
            console.error(err);
        }
    }

    function eraseTicket(t) {
        const ticketCard = document.getElementById(`tc-${t}`);
        ticketCard.remove();
    }

    function getTicket(t) {
        const ticket = document.querySelector('#tc-' + t);
        const hyp = "/support/clientchat?ticket=" + t;
        const menu = `
        <a href="${hyp}" 
            class="btn btn-sm btn-outline-primary">
            <i class="bi bi-chat-dots me-1"></i>Chat
        </a>
        `;

        const badge = ticket.querySelector('.status-badge');
        if (badge) {
            badge.classList.remove('status-open');
            badge.classList.add('status-progress');
            badge.textContent = "In Progress";
        }

        ticket.querySelector('#button-menu').innerHTML = menu;
    }

    document.querySelectorAll('form[id^="ticket-taker-"]').forEach(function (frm) {
        const ticketId = frm.id.replace("ticket-taker-", "");

        frm.addEventListener('submit', async function (e) {
            e.preventDefault();
            await sendTicket(ticketId);
        });
    });
</script>