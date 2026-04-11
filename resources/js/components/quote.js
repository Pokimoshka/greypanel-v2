export default () => ({
    insertQuote(author, content) {
        const textarea = document.getElementById('reply-content');
        if (textarea) {
            textarea.value += `[quote=${author}]${content}[/quote]\n\n`;
            textarea.focus();
        }
    }
});