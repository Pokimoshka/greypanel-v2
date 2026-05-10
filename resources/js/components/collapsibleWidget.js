export default (widgetId, defaultOpen = true) => ({
    open:
        localStorage.getItem(`widget_${widgetId}`) !== null
            ? localStorage.getItem(`widget_${widgetId}`) === 'true'
            : defaultOpen,

    toggle() {
        this.open = !this.open;
        localStorage.setItem(`widget_${widgetId}`, this.open);
    },
});
