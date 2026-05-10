const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

async function request(url, options = {}) {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
        ...(options.headers || {}),
    };
    let body = options.body;
    if (!(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(body);
    }
    const resp = await fetch(url, { ...options, headers, body, credentials: 'same-origin' });

    let data;
    const contentType = resp.headers.get('Content-Type');
    if (contentType && contentType.includes('application/json')) {
        data = await resp.json();
    } else {
        data = await resp.text();
    }

    if (!resp.ok) {
        const message =
            data && data.error ? data.error : typeof data === 'string' ? data : resp.statusText;
        const error = new Error(message);
        error.status = resp.status;
        error.data = data;
        throw error;
    }

    return data;
}

export const api = {
    get: (url, params) => request(url + (params ? '?' + new URLSearchParams(params) : '')),
    post: (url, data) => request(url, { method: 'POST', body: data }),
    put: (url, data) => request(url, { method: 'PUT', body: data }),
    delete: (url, data) => request(url, { method: 'DELETE', body: data }),
};
