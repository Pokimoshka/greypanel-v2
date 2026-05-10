import { api } from '../utils/api.js';

export async function uploadImage(file, endpoint, csrfToken) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', csrfToken);
    const data = await api.post(endpoint, formData);
    if (data.url) {
        return data.url;
    }
    throw new Error(data.error || 'Ошибка загрузки');
}
