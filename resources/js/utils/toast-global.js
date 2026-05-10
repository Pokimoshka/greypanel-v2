export const Toast = {
    success(msg) {
        window.dispatchEvent(new CustomEvent('toast:success', { detail: msg }));
    },
    error(msg) {
        window.dispatchEvent(new CustomEvent('toast:error', { detail: msg }));
    },
    info(msg) {
        window.dispatchEvent(new CustomEvent('toast:info', { detail: msg }));
    },
    warning(msg) {
        window.dispatchEvent(new CustomEvent('toast:warning', { detail: msg }));
    },
};
