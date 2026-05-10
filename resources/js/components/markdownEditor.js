import MarkdownEditor from '../editor/MarkdownEditor';

export default () => ({
    editorInstance: null,
    init() {
        if (this.$el._markdownEditor) {
            return;
        }
        try {
            this.editorInstance = new MarkdownEditor(this.$el);
            console.log('MarkdownEditor инициализирован');
        } catch (e) {
            console.error('Ошибка инициализации MarkdownEditor:', e);
        }
    },
    getContent() {
        return this.editorInstance ? this.editorInstance.getContent() : this.$el.value;
    },
    setContent(markdown) {
        if (this.editorInstance) {
            this.editorInstance.setContent(markdown);
        } else {
            this.$el.value = markdown;
        }
    },
});