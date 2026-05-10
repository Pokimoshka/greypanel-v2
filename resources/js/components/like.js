import { api } from '../utils/api.js';

export default (type, targetId, initialCount = 0, initialLiked = false) => ({
    count: initialCount,
    liked: initialLiked,

    async toggle() {
        const data = await api.post('/forum/like', { type, target_id: targetId });
        if (data.success) {
            this.liked = !this.liked;
            this.count += this.liked ? 1 : -1;
        }
    },
});
