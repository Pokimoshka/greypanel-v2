export default () => ({
    insertQuote(author, content, postId) {
        const textarea = document.getElementById('reply-editor');
        if (!textarea) return;

        const editor = textarea._markdownEditor;
        if (editor) {
            const safeContent = content.replace(/<[^>]*>/g, '').trim();
            const quoteLine = `> **${author}** (#${postId})\n> ${safeContent}\n\n`;
            const cm = editor.easyMDE.codemirror;
            cm.replaceSelection(quoteLine);
            cm.focus();
        }
    },
});
