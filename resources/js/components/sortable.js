import Sortable from 'sortablejs';
import { api } from '../utils/api.js';

export default (url) => ({
    init() {
        const el = this.$refs.sortableList;
        if (!el) return;
        new Sortable(el, {
            animation: 150,
            onEnd: async () => {
                const items = [...el.children].map((item, index) => ({
                    id: item.dataset.id,
                    order: index,
                }));

                const order = Object.fromEntries(items.map((i) => [i.id, i.order]));

                const formData = new FormData();
                formData.append('order', JSON.stringify(order));

                await api.post(url, formData);
            },
        });
    },
});
