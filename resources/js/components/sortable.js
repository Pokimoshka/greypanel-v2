import Sortable from 'sortablejs';

export default (url, csrf) => ({
    init() {
        const el = this.$refs.sortableList;
        new Sortable(el, {
            animation: 150,
            onEnd: async (evt) => {
                const items = [...el.children].map((item, index) => ({
                    id: item.dataset.id,
                    order: index
                }));
                await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order: items, csrf_token: csrf })
                });
            }
        });
    }
});