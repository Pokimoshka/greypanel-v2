import EasyMDE from 'easymde';

export function toolbarConfig(editorInstance) {
    return [
        {
            name: 'bold',
            action: EasyMDE.toggleBold,
            className: 'fa fa-bold',
            title: 'Жирный (Ctrl+B)',
        },
        {
            name: 'italic',
            action: EasyMDE.toggleItalic,
            className: 'fa fa-italic',
            title: 'Курсив (Ctrl+I)',
        },
        {
            name: 'strikethrough',
            action: EasyMDE.toggleStrikethrough,
            className: 'fa fa-strikethrough',
            title: 'Зачёркнутый',
        },
        '|',
        {
            name: 'heading',
            action: EasyMDE.toggleHeadingSmaller,
            className: 'fa fa-header',
            title: 'Заголовок',
        },
        {
            name: 'quote',
            action: EasyMDE.toggleBlockquote,
            className: 'fa fa-quote-left',
            title: 'Цитата',
        },
        {
            name: 'unordered-list',
            action: EasyMDE.toggleUnorderedList,
            className: 'fa fa-list-ul',
            title: 'Маркированный список',
        },
        {
            name: 'ordered-list',
            action: EasyMDE.toggleOrderedList,
            className: 'fa fa-list-ol',
            title: 'Нумерованный список',
        },
        '|',
        {
            name: 'link',
            action: () => editorInstance.insertLink(),
            className: 'fa fa-link',
            title: 'Вставить ссылку',
        },
        {
            name: 'image',
            action: () => editorInstance.insertImageByUrl(),
            className: 'fa fa-picture-o',
            title: 'Вставить изображение',
        },
        {
            name: 'upload-image',
            action: () => editorInstance.uploadImageFile(),
            className: 'fa fa-upload',
            title: 'Загрузить изображение',
        },
        '|',
        {
            name: 'code',
            action: EasyMDE.toggleCodeBlock,
            className: 'fa fa-code',
            title: 'Блок кода',
        },
        {
            name: 'spoiler',
            action: (editor) => {
                const cm = editor.codemirror;
                cm.replaceSelection('<spoiler>\n' + cm.getSelection() + '\n</spoiler>');
            },
            className: 'fa fa-eye-slash',
            title: 'Спойлер',
        },
        {
            name: 'emoji',
            action: (editor) => {
                const textarea = editor.element;
                const md = textarea._markdownEditor;
                if (md) {
                    const btn = document.querySelector('.fa-smile-o');
                    md.showEmojiPicker(btn);
                }
            },
            className: 'fa fa-smile-o',
            title: 'Эмодзи',
        },
        '|',
        {
            name: 'preview',
            action: EasyMDE.togglePreview,
            className: 'fa fa-eye no-disable',
            title: 'Предпросмотр',
        },
        {
            name: 'side-by-side',
            action: EasyMDE.toggleSideBySide,
            className: 'fa fa-columns no-disable no-mobile',
            title: 'Два окна',
        },
        {
            name: 'fullscreen',
            action: EasyMDE.toggleFullScreen,
            className: 'fa fa-arrows-alt no-disable no-mobile',
            title: 'На весь экран',
        },
        '|',
        {
            name: 'guide',
            action: 'https://www.markdownguide.org/basic-syntax/',
            className: 'fa fa-question-circle',
            title: 'Справка по Markdown',
        },
    ];
}
