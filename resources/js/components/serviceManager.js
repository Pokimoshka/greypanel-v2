import { api } from '../utils/api.js';
import { Toast } from '../utils/toast-global.js';

export default () => ({
    services: window._services || [],
    selectedServiceId: null,
    selectedTariffs: [],
    editingServiceId: null,
    editingTariffId: null,
    editingService: {
        name: '',
        description: '',
        rights: '',
        isActive: true,
        sortOrder: 0,
        groupId: null,
        servers: [],
    },
    editingTariff: { durationDays: 30, price: 100, isActive: true, sortOrder: 0 },

    init(initialServiceId) {
        if (initialServiceId) this.selectedServiceId = initialServiceId;
        this.$watch('selectedServiceId', () => this.refreshSelectedTariffs());
        this.refreshSelectedTariffs();
    },

    refreshSelectedTariffs() {
        if (!this.selectedServiceId) {
            this.selectedTariffs = [];
            return;
        }
        const service = this.services.find((s) => s.service.id == this.selectedServiceId);
        this.selectedTariffs = service ? [...service.tariffs] : [];
    },

    startEditService(serviceId) {
        const item = this.services.find((s) => s.service.id == serviceId);
        if (!item) return;
        this.editingServiceId = serviceId;
        this.editingService = { ...item.service };
    },

    startEditTariff(tariffId) {
        const tariff = this.selectedTariffs.find((t) => t.id == tariffId);
        if (!tariff) return;
        this.editingTariffId = tariffId;
        this.editingTariff = { ...tariff };
    },

    async saveEditing() {
        if (this.editingServiceId) {
            const id = this.editingServiceId;
            try {
                const json = await api.put(`/api/services/${id}`, { ...this.editingService });
                if (json.success) {
                    const idx = this.services.findIndex((s) => s.service.id == id);
                    if (idx !== -1) this.services[idx].service = { ...this.editingService };
                    this.cancelEditing();
                    Toast.success(window.__['admin.service_updated'] || 'Услуга обновлена');
                } else {
                    Toast.error(json.error || window.__['admin.error'] || 'Ошибка');
                }
            } catch (e) {
                Toast.error('Ошибка сети');
            }
        } else if (this.editingTariffId) {
            const id = this.editingTariffId;
            try {
                const json = await api.put(
                    `/api/services/${this.selectedServiceId}/tariffs/${id}`,
                    { ...this.editingTariff }
                );
                if (json.success) {
                    const sIdx = this.services.findIndex(
                        (s) => s.service.id == this.selectedServiceId
                    );
                    if (sIdx !== -1) {
                        const tIdx = this.services[sIdx].tariffs.findIndex((t) => t.id == id);
                        if (tIdx !== -1)
                            this.services[sIdx].tariffs[tIdx] = {
                                ...this.editingTariff,
                                id,
                                serviceId: this.selectedServiceId,
                            };
                    }
                    this.refreshSelectedTariffs();
                    this.cancelEditing();
                    Toast.success('Тариф обновлён');
                } else {
                    Toast.error(json.error || window.__['admin.error'] || 'Ошибка');
                }
            } catch (e) {
                Toast.error('Ошибка сети');
            }
        }
    },

    async deleteService(serviceId) {
        if (!confirm('Удалить услугу?')) return;
        try {
            const res = await fetch(`/admin/services/delete/${serviceId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)}`,
            });
            if (res.ok) {
                this.services = this.services.filter((s) => s.service.id != serviceId);
                if (this.selectedServiceId == serviceId) this.selectedServiceId = null;
                Toast.success('Услуга удалена');
            } else {
                Toast.error('Ошибка');
            }
        } catch (e) {
            Toast.error('Ошибка сети');
        }
    },

    async deleteTariff(serviceId, tariffId) {
        if (!confirm('Удалить тариф?')) return;
        try {
            const res = await fetch(`/admin/services/${serviceId}/tariffs/delete/${tariffId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)}`,
            });
            if (res.ok) {
                const sIdx = this.services.findIndex((s) => s.service.id == serviceId);
                if (sIdx !== -1) {
                    this.services[sIdx].tariffs = this.services[sIdx].tariffs.filter(
                        (t) => t.id != tariffId
                    );
                    this.services[sIdx].tariffs_count = this.services[sIdx].tariffs.length;
                }
                this.refreshSelectedTariffs();
                Toast.success('Тариф удалён');
            } else {
                Toast.error('Ошибка');
            }
        } catch (e) {
            Toast.error('Ошибка сети');
        }
    },

    async addTariff(serviceId, event) {
        const form = event.target.closest('form');
        if (!form) return;
        const formData = new FormData(form);
        formData.append('service_id', serviceId);

        try {
            const json = await api.post(`/admin/services/${serviceId}/tariffs/add`, formData);
            if (json.success) {
                this.refreshSelectedTariffs();
                Toast.success('Тариф добавлен');
            } else {
                Toast.error(json.error || window.__['admin.error'] || 'Ошибка');
            }
        } catch (e) {
            Toast.error('Ошибка сети');
        }
    },

    cancelEditing() {
        this.editingServiceId = null;
        this.editingTariffId = null;
        this.editingService = {
            name: '',
            description: '',
            rights: '',
            isActive: true,
            sortOrder: 0,
            groupId: null,
            servers: [],
        };
        this.editingTariff = { durationDays: 30, price: 100, isActive: true, sortOrder: 0 };
    },

    normalizeFlags(event) {
        let value = event.target.value.replace(/[^a-z]/g, '');
        value = [...new Set(value)].sort().join('');
        event.target.value = value;
        if (this.editingService) this.editingService.rights = value;
    },
});
