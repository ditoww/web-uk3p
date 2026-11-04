window.addEventListener("load", async function () {
    //console.log('[chatdev-min.js] running...');

    // Auto-resize textarea (Enter handling moved to realtime-chat-min.js)
    const textarea = document.getElementById("messageTextarea");
    if (textarea) {
        textarea.addEventListener("input", function () {
            this.style.height = "auto";
            this.style.height = Math.min(this.scrollHeight, 120) + "px";

            // Scroll chat body to bottom when textarea expands
            const chatBody = document.getElementById("chatBody");
            if (chatBody) {
                setTimeout(() => {
                    chatBody.scrollTop = chatBody.scrollHeight;
                }, 10);
            }
        });
    }

    const images = document.querySelectorAll(".chat-image-clickable");
    const modalImage = document.getElementById("modalImage");
    const modalEl = document.getElementById("imageModal");

    if (images.length && modalImage && modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        images.forEach((img) => {
            img.addEventListener("click", () => {
                modalImage.src = img.src;
                modal.show();
            });
        });
    }
});

// Enter key handler removed from chatdev-min.js to avoid duplicates

function updatePreviewContainer() {
    const container = document.getElementById("selectedImageContainer");
    if (selectedImages.length > 0) {
        container.style.display = "block";
    } else {
        container.style.display = "none";
        container.innerHTML = "";
        return;
    }

    container.innerHTML = "";

    selectedImages.forEach((file, index) => {
        const imageWrapper = document.createElement("div");
        imageWrapper.className = "position-relative d-inline-block";

        const img = document.createElement("img");
        // Neutral placeholder to prevent "image not found"
        img.src = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
        img.alt = file.name;
        img.className = "img-thumbnail img-prev";
        img.style.maxWidth = "150px";

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "btn btn-sm btn-danger mx-2";
        removeBtn.innerText = "x";
        removeBtn.onclick = function () {
            selectedImages.splice(index, 1);
            updatePreviewContainer();
            validateInputs();
        };

        imageWrapper.appendChild(img);
        imageWrapper.appendChild(removeBtn);
        container.appendChild(imageWrapper);

        // Fill in image once loaded
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function updateFilePreviewContainer() {
    const container = document.getElementById("selectedFileContainer");
    container.innerHTML = ""; // Clear previous previews
    if (selectedAtts.length > 0) {
        container.style.display = "flex";
    } else {
        container.style.display = "none";
        container.innerHTML = "";
        return;
    }

    selectedAtts.forEach((file, index) => {
        const fileBox = document.createElement("div");
        fileBox.className = "file-box me-2";

        const icon = document.createElement("i");
        icon.className = "bi bi-file-earmark-arrow-down me-2";

        const fileName = document.createElement("span");
        fileName.textContent = file.name;

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "btn btn-sm btn-danger ms-2";
        removeBtn.textContent = "x";
        removeBtn.onclick = function () {
            selectedAtts.splice(index, 1);
            updateFilePreviewContainer();
            validateInputs();
        };

        fileBox.appendChild(icon);
        fileBox.appendChild(fileName);
        fileBox.appendChild(removeBtn);

        container.appendChild(fileBox);
    });
}

const imgInput = document.getElementById("image");
if (imgInput) {
    imgInput.disabled = false;
    imgInput.addEventListener("change", async function (event) {
        let added = false;

        for (const file of event.target.files) {
            const totalSelected = selectedImages.length + selectedAtts.length;
            if (totalSelected >= maxFiles) break;

            if (await allowedIMG(file)) {
                selectedImages.push(file);
                //console.log(selectedImages);
                added = true;
            }
        }
        if (added) {
            validateInputs();
            updatePreviewContainer();
        }
        event.target.value = "";
    });
}

const attInput = document.getElementById("attachment");
if (attInput) {
    attInput.disabled = false;
    attInput.addEventListener("change", async function (event) {
        let added = false;
        for (const file of event.target.files) {
            const totalSelected = selectedImages.length + selectedAtts.length;
            if (totalSelected >= maxFiles) break;

            if (await allowedATT(file)) {
                selectedAtts.push(file);
                added = true;
            }
        }
        if (added) {
            validateInputs();
            updateFilePreviewContainer();
        }
        event.target.value = "";
    });
}

const chatBody = document.getElementById("chatBody");
if (chatBody) {
    setTimeout(() => {
        chatBody.scrollTop = chatBody.scrollHeight;
    }, 100);
    ["dragenter", "dragover"].forEach((event) => {
        chatBody.addEventListener(event, (e) => {
            e.preventDefault();
            chatBody.classList.add("dragover");
        });
    });

    ["dragleave", "drop"].forEach((event) => {
        chatBody.addEventListener(event, (e) => {
            e.preventDefault();
            chatBody.classList.remove("dragover");
        });
    });
    chatBody.addEventListener("drop", async (e) => {
        const files = Array.from(e.dataTransfer.files);
        let newImg = false;
        let newAtt = false;
        for (const file of files) {
            const totalSelected = selectedImages.length + selectedAtts.length;
            if (totalSelected >= maxFiles) return;

            if (await allowedIMG(file)) {
                selectedImages.push(file);
                newImg = true;
            } else if (await allowedATT(file)) {
                selectedAtts.push(file);
                newAtt = true;
            }
        }
        if (newImg) updatePreviewContainer();
        if (newAtt) updateFilePreviewContainer();

        if (newImg || newAtt) validateInputs();
    });
}

document
    .getElementById("chat-form")
    .addEventListener("submit", function (event) {
        const img = document.getElementById("image");
        const att = document.getElementById("attachment");

        const dtImg = new DataTransfer();
        selectedImages.forEach((file) => dtImg.items.add(file));
        img.files = dtImg.files;

        const dtAtt = new DataTransfer();
        selectedAtts.forEach((file) => dtAtt.items.add(file));
        att.files = dtAtt.files;

        //console.log('Sending images:', selectedImages.length);

        // Clear raw input values to avoid false duplicates on next change
        // Turns out this only works if realtime or something. You will need a custom request for that. Uncomment after that is created.
        img.value = "";
        att.value = "";
    });

/*document.addEventListener('DOMContentLoaded', function () {
    validateInputs();
    const chatContents = document.getElementsByClassName("message");

    function escapeHTML(str) { // XSS PROTECTION
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }

    function linkify(text) {
        const urlRegex = /((https?:\/\/)[^\s]+)/g;
        return text.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
    }

    for (let i = 0; i < chatContents.length; i++) {
        const div = chatContents[i];

        let rawText = div.textContent || "";

        // 1️⃣ Escape HTML first
        let safe = escapeHTML(rawText);

        // 2️⃣ Remove ALL leading + trailing newlines
        safe = safe.replace(/^\n+|\n+$/g, "");

        // 3️⃣ Only convert newlines that are in the middle of text
        safe = safe.replace(/\n/g, "<br>");

        // 4️⃣ Convert URLs AFTER <br> insertion, but NEVER add <br> after a link
        safe = safe.replace(/(https?:\/\/[^\s<]+)/g, (match) => {
            return `<a href="${match}" target="_blank" rel="noopener noreferrer">${match}</a>`;
        });

        div.innerHTML = safe;
    }
})*/

document.querySelectorAll("button").forEach((btn) => {
    btn.addEventListener("click", function () {
        this.blur(); //Disables an annoying bug where it sticks the button every single time it is pressed.
    });
});

const messageTextArea = document.getElementById("messageTextarea");

function validateInputs() {
    const messageTextArea = document.getElementById("messageTextarea");
    const submitButton = document.getElementById("submitButton");
    const hasText =
        messageTextArea.value.trim().length > 0 &&
        messageTextArea.value.trim().length <= 10000;
    const nothing =
        (!hasText && selectedAtts.length <= 0 && selectedImages.length <= 0) ||
        submitButton.classList.contains("waiting");
    submitButton.disabled = nothing;
    submitButton.classList.toggle("disabled", nothing);
}

messageTextArea.addEventListener("input", validateInputs);

function escapeHTML(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function linkify(text) {
    const urlRegex = /((https?:\/\/)[^\s]+)/g;
    return text.replace(
        urlRegex,
        '<a href="$1" target="_blank" rel="noopener noreferrer" onmouseover="text-decoration: underline;">$1</a>',
    );
}

function linkifyMessage(messageDiv) {
    const messageElements = messageDiv.querySelectorAll(".message");
    messageElements.forEach((element) => {
        /*const rawText = element.textContent || '';
    const escapedText = escapeHTML(rawText);
    const trimmedText = escapedText.replace(/^\n+/, '').replace(/\n+$/, '');
    const withBreaks = trimmedText.replace(/\n/g, '<br>');
    const linkifiedText = linkify(withBreaks);
    element.innerHTML = linkifiedText;*/

        let text = element.textContent || "";

        text = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/^\n+|\n+$/g, "")
            .replace(/\n/g, "<br>")
            .replace(
                /(https?:\/\/[^\s<]+)/g,
                (m) => `<a href="${m}" target="_blank" rel="noopener">${m}</a>`,
            );

        element.innerHTML = text;
    });
}

document.addEventListener("DOMContentLoaded", () => {
    document
        .querySelectorAll(".file-box i[data-filename]")
        .forEach((iconEl) => {
            const filename = iconEl.getAttribute("data-filename");
            iconEl.className = getFileIcon(filename);
        });
});

function getFileIcon(filename) {
    if (!filename) return "bi bi-file-earmark-arrow-down me-2";
    const ext = filename.split(".").pop().toLowerCase();
    if (ext === "pdf") return "bi bi-file-earmark-pdf pdf-icon me-2";
    if (ext === "docx") return "bi bi-file-earmark-word doc-icon me-2";
    if (ext === "xlsx") return "bi bi-file-earmark-excel excel-icon me-2";
    return "bi bi-file-earmark-arrow-down me-2";
}
// Scroll chat body to bottom after render
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        var chatBody = document.getElementById("chatBody");
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }, 100);
});

// Event delegation untuk gambar yang bisa di klik
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("chat-image-clickable")) {
        var modalImg = document.getElementById("modalImage");
        modalImg.src = e.target.src;
        var imageModal = new bootstrap.Modal(
            document.getElementById("imageModal"),
        );
        imageModal.show();
    }
});

document
    .getElementById("toggleChatHistory")
    .addEventListener("click", function () {
        const panel = document.querySelector(".chat-history");
        panel.classList.toggle("show");
    });

document.addEventListener("click", function (e) {
    const panel = document.querySelector(".chat-history");
    const toggleBtn = document.getElementById("toggleChatHistory");
    if (window.innerWidth <= 1300 && panel.classList.contains("show")) {
        if (
            !panel.contains(e.target) &&
            e.target !== toggleBtn &&
            !toggleBtn.contains(e.target)
        ) {
            panel.classList.remove("show");
        }
    }
});

window.addEventListener("resize", function () {
    const panel = document.querySelector(".chat-history");
    if (window.innerWidth <= 1300) {
        panel.classList.add("transition-disabler");
        panel.offsetHeight; //do not erase, function needed to stop transition.
        panel.classList.remove("transition-disabler");
    }
    if (window.innerWidth > 1300 && panel.classList.contains("show"))
        panel.classList.remove("show");
});
