// Mengurutkan ulang daftar tiket di sidebar chat secara real-time.
// Urutan harus sama dengan query di SupportChatController::index dan
// ChatDevController::index: status, priority (jika ada), lalu chat terbaru.
import {
    collectionGroup,
    onSnapshot,
} from "https://www.gstatic.com/firebasejs/12.1.0/firebase-firestore.js";

const list = document.querySelector(".chat-history[data-status-order]");

if (list) {
    const statusOrder = list.dataset.statusOrder.split(",");

    const items = () =>
        [...list.querySelectorAll(".ticket-card")].map((card) => ({
            card,
            el: card.closest(".chat-history > *"),
        }));

    const statusRank = (card) => {
        const rank = statusOrder.indexOf(
            card.querySelector(".badge-status").textContent.trim(),
        );
        return rank === -1 ? statusOrder.length : rank;
    };

    const priority = (card) =>
        card.dataset.priority === ""
            ? Infinity
            : Number(card.dataset.priority ?? Infinity);

    const sortList = () => {
        const sorted = items().sort(
            (a, b) =>
                statusRank(a.card) - statusRank(b.card) ||
                priority(a.card) - priority(b.card) ||
                Number(b.card.dataset.latest) - Number(a.card.dataset.latest),
        );
        const heading = list.querySelector("h6");
        sorted.forEach(({ el }) => list.appendChild(el));
        if (heading) list.prepend(heading);
    };

    const pad = (n) => String(n).padStart(2, "0");
    const formatTime = (d) =>
        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ` +
        `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;

    const bumpTicket = (ticketId, time) => {
        const card = document.getElementById("tc-" + ticketId);
        if (!card || time <= Number(card.dataset.latest)) return;

        card.dataset.latest = time;
        const timeEl = card.querySelector(".time");
        if (timeEl) timeEl.textContent = formatTime(new Date(time));
        sortList();
    };

    // Status tiket diubah oleh realtime-chat.js lewat teks badge.
    const statusObserver = new MutationObserver(sortList);
    list.querySelectorAll(".badge-status").forEach((badge) =>
        statusObserver.observe(badge, {
            childList: true,
            characterData: true,
            subtree: true,
        }),
    );

    const waitForFirebase = () =>
        new Promise((resolve) => {
            const check = () =>
                window.firebaseDb && window.firebaseAuth
                    ? resolve()
                    : setTimeout(check, 50);
            check();
        });

    waitForFirebase()
        .then(() => window.firebaseAuth.authStateReady())
        .then(() => {
            onSnapshot(
                collectionGroup(window.firebaseDb, "messages"),
                (snapshot) => {
                    snapshot.docChanges().forEach((change) => {
                        if (change.type !== "added") return;

                        const ticketId = change.doc.ref.parent.parent?.id;
                        const createdAt = Date.parse(
                            change.doc.data().createdAt,
                        );
                        if (ticketId && !Number.isNaN(createdAt)) {
                            bumpTicket(ticketId, createdAt);
                        }
                    });
                },
                (error) => console.warn("ticket-list-sort:", error),
            );
        });
}
