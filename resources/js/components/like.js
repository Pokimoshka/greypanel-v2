export default (type, targetId, initialCount = 0, initialLiked = false) => ({
    count: initialCount,
    liked: initialLiked,
    csrf: document.querySelector('meta[name="csrf-token"]').content,

    async toggle() {
        const formData = new FormData();
        formData.append('type', type);
        formData.append('target_id', targetId);
        formData.append('csrf_token', this.csrf);
        const res = await fetch('/forum/like', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            this.liked = !this.liked;
            this.count += this.liked ? 1 : -1;
        }
    }
});