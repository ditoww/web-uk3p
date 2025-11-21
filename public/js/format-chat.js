processMessages();

function processMessages() {
    const chatContents = document.getElementsByClassName("message");

    for (let div of chatContents) {
        let text = div.textContent || "";

        text = text.replace(/&/g, "&amp;")
                   .replace(/</g, "&lt;")
                   .replace(/>/g, "&gt;")
                   .replace(/^\n+|\n+$/g, "")
                   .replace(/\n/g, "<br>")
                   .replace(/(https?:\/\/[^\s<]+)/g, m => `<a href="${m}" target="_blank" rel="noopener">${m}</a>`);

        div.innerHTML = text;
    }
}