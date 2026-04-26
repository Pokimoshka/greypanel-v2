{% extends "base.tpl" %}

{% block title %}Услуги{% endblock %}
{% block page_title %}Услуги и тарифы{% endblock %}

{% block content %}
<script>
window._services = {{ services|json_encode|raw }};
</script>

<div x-data="serviceManager">
    <div class="row g-4">
        <!-- Левая колонка: создание услуги -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-plus-circle me-1"></i>Добавить услугу</div>
                <div class="card-body">
                    <form method="post" action="{{ url('admin/services/add') }}">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                        <div class="mb-3">
                            <label class="form-label">Название</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Флаги</label>
                                <input type="text" name="rights" class="form-control" placeholder="abcdef" @input="normalizeFlags($event)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Группа при покупке</label>
                                <select name="group_id" class="form-select">
                                    <option value="">Не менять</option>
                                    {% for g in all_groups %}
                                        <option value="{{ g.id }}">{{ g.name }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Серверы</label>
                            <div class="row">
                                {% for srv in all_servers %}
                                <div class="col-6 mb-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="servers[]" value="{{ srv.id }}" id="srv_{{ srv.id }}">
                                        <label class="form-check-label small" for="srv_{{ srv.id }}">{{ srv.ip }}:{{ srv.c_port }}</label>
                                    </div>
                                </div>
                                {% endfor %}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Сортировка</label>
                                <input type="number" name="sort_order" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Активна</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Создать услугу</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Правая колонка: создание тарифа -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-tags me-1"></i>Добавить тариф</div>
                <div class="card-body">
                    <label class="form-label">Услуга</label>
                    <select x-model="selectedServiceId" class="form-select mb-3">
                        <option value="">-- Выберите услугу --</option>
                        <template x-for="item in services" :key="item.service.id">
                            <option :value="item.service.id" x-text="item.service.name"></option>
                        </template>
                    </select>

                    <template x-if="selectedServiceId">
                        <form method="post" :action="'/admin/services/' + selectedServiceId + '/tariffs/add'">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Длительность (дней)</label>
                                    <input type="number" name="duration_days" class="form-control" value="30" min="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Цена (₽)</label>
                                    <input type="number" name="price" class="form-control" value="100" min="1" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Сортировка</label>
                                    <input type="number" name="sort_order" class="form-control" value="0">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                                        <label class="form-check-label">Активен</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Добавить тариф</button>
                        </form>
                    </template>
                    <div x-show="!selectedServiceId" class="text-muted small">Сначала выберите услугу</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Вторая строка: список услуг и тарифов -->
<div class="row g-4 mt-2">
    <!-- Левая колонка: список услуг -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-list me-1"></i>Список услуг</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Название</th><th>Флаги</th><th>Тарифов</th><th></th></tr>
                        </thead>
                        <tbody>
                            <template x-for="item in services" :key="item.service.id">
                                <tr>
                                    <td>
                                        <template x-if="editingServiceId !== item.service.id">
                                            <a href="#" @click.prevent="selectedServiceId = item.service.id" class="fw-bold text-decoration-none" x-text="item.service.name"></a>
                                        </template>
                                        <template x-if="editingServiceId === item.service.id">
                                            <input type="text" x-model="editingService.name" class="form-control form-control-sm">
                                        </template>
                                    </td>
                                    <td>
                                        <template x-if="editingServiceId !== item.service.id">
                                            <code x-text="item.service.rights"></code>
                                        </template>
                                        <template x-if="editingServiceId === item.service.id">
                                            <input type="text" x-model="editingService.rights" class="form-control form-control-sm" style="max-width: 100px;" @input="normalizeFlags($event)">
                                        </template>
                                    </td>
                                    <td x-text="item.tariffs_count"></td>
                                    <td>
                                        <template x-if="editingServiceId !== item.service.id">
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary" @click="startEditService(item.service.id)">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" @click="deleteService(item.service.id)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="editingServiceId === item.service.id">
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-success" @click="saveEditing">Сохранить</button>
                                                <button class="btn btn-sm btn-secondary" @click="cancelEditing">Отмена</button>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="services.length === 0">
                                <td colspan="4" class="text-center text-muted">Нет услуг</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Правая колонка: тарифы выбранной услуги -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-list me-1"></i>Тарифы услуги</div>
            <div class="card-body p-0">
                <template x-if="selectedServiceId">
                    <div>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Дней</th><th>Цена</th><th>Активен</th><th></th></tr></thead>
                            <tbody>
                                <template x-for="t in selectedTariffs" :key="t.id">
                                    <tr>
                                        <td>
                                            <template x-if="editingTariffId !== t.id">
                                                <span x-text="t.durationDays + ' дн.'"></span>
                                            </template>
                                            <template x-if="editingTariffId === t.id">
                                                <input type="number" x-model="editingTariff.durationDays" class="form-control form-control-sm" min="1">
                                            </template>
                                        </td>
                                        <td>
                                            <template x-if="editingTariffId !== t.id">
                                                <span x-text="t.price + ' ₽'"></span>
                                            </template>
                                            <template x-if="editingTariffId === t.id">
                                                <input type="number" x-model="editingTariff.price" class="form-control form-control-sm" min="1">
                                            </template>
                                        </td>
                                        <td>
                                            <template x-if="editingTariffId !== t.id">
                                                <span x-text="t.isActive ? 'Да' : 'Нет'"></span>
                                            </template>
                                            <template x-if="editingTariffId === t.id">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" x-model="editingTariff.isActive">
                                                </div>
                                            </template>
                                        </td>
                                        <td>
                                            <template x-if="editingTariffId !== t.id">
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary" @click="startEditTariff(t.id)">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" @click="deleteTariff(selectedServiceId, t.id)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="editingTariffId === t.id">
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-success" @click="saveEditing">Сохранить</button>
                                                    <button class="btn btn-sm btn-secondary" @click="cancelEditing">Отмена</button>
                                                    <button class="btn btn-sm btn-outline-danger" @click="deleteTariff(selectedServiceId, t.id)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="selectedTariffs.length === 0">
                                    <td colspan="4" class="text-center text-muted">Нет тарифов</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
                <div x-show="!selectedServiceId" class="p-3 text-muted text-center">Выберите услугу слева</div>
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('serviceManager', () => ({
        services: window._services || [],
        selectedServiceId: null,
        selectedTariffs: [],
        editingServiceId: null,
        editingTariffId: null,
        editingService: { name: '', description: '', rights: '', isActive: true, sortOrder: 0, groupId: null, servers: [] },
        editingTariff: { durationDays: 30, price: 100, isActive: true, sortOrder: 0 },

        init() {
            this.$watch('selectedServiceId', id => this.refreshSelectedTariffs());
            // начальная инициализация, если selectedServiceId уже задан (может быть null)
            this.refreshSelectedTariffs();
        },

        refreshSelectedTariffs() {
            if (!this.selectedServiceId) {
                this.selectedTariffs = [];
                return;
            }
            const service = this.services.find(s => s.service.id == this.selectedServiceId);
            this.selectedTariffs = service ? [...service.tariffs] : [];
        },

        startEditService(serviceId) {
            const item = this.services.find(s => s.service.id == serviceId);
            if (!item) return;
            this.editingServiceId = serviceId;
            this.editingService = { ...item.service };
        },

        startEditTariff(tariffId) {
            const tariffs = this.selectedTariffs;
            if (!tariffs) return;
            const t = tariffs.find(t => t.id == tariffId);
            if (!t) return;
            this.editingTariffId = tariffId;
            this.editingTariff = { ...t };
        },

        async saveEditing() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            };
            if (this.editingServiceId) {
                const id = this.editingServiceId;
                const data = { ...this.editingService };
                try {
                    const res = await fetch(`/api/services/${id}`, {
                        method: 'PUT',
                        headers,
                        body: JSON.stringify(data)
                    });
                    const json = await res.json();
                    if (json.success) {
                        // Обновляем локальные данные
                        const idx = this.services.findIndex(s => s.service.id == id);
                        if (idx !== -1) {
                            this.services[idx].service = { ...this.editingService };
                        }
                        this.cancelEditing();
                        window.dispatchEvent(new CustomEvent('toast:success', { detail: 'Услуга обновлена' }));
                    } else {
                        window.dispatchEvent(new CustomEvent('toast:error', { detail: json.error || 'Ошибка' }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка соединения' }));
                }
            } else if (this.editingTariffId) {
                const id = this.editingTariffId;
                const data = { ...this.editingTariff };
                try {
                    const res = await fetch(`/api/services/${this.selectedServiceId}/tariffs/${id}`, {
                        method: 'PUT',
                        headers,
                        body: JSON.stringify(data)
                    });
                    const json = await res.json();
                    if (json.success) {
                        // Обновляем данные в services и пересчитываем selectedTariffs
                        const sIdx = this.services.findIndex(s => s.service.id == this.selectedServiceId);
                        if (sIdx !== -1) {
                            const tIdx = this.services[sIdx].tariffs.findIndex(t => t.id == id);
                            if (tIdx !== -1) {
                                this.services[sIdx].tariffs[tIdx] = { ...this.editingTariff, id: id, serviceId: this.selectedServiceId };
                            }
                        }
                        this.refreshSelectedTariffs();
                        this.cancelEditing();
                        window.dispatchEvent(new CustomEvent('toast:success', { detail: 'Тариф обновлён' }));
                    } else {
                        window.dispatchEvent(new CustomEvent('toast:error', { detail: json.error || 'Ошибка' }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка соединения' }));
                }
            }
        },

        async deleteService(serviceId) {
            if (!confirm('Удалить услугу и все связанные тарифы?')) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const res = await fetch(`/admin/services/delete/${serviceId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `csrf_token=${encodeURIComponent(csrf)}`
                });
                if (res.ok) {
                    // Удаляем услугу из локального массива
                    this.services = this.services.filter(s => s.service.id != serviceId);
                    if (this.selectedServiceId == serviceId) {
                        this.selectedServiceId = null; // скроет тарифы
                    }
                    window.dispatchEvent(new CustomEvent('toast:success', { detail: 'Услуга удалена' }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка удаления' }));
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка соединения' }));
            }
        },

        async deleteTariff(serviceId, tariffId) {
            if (!confirm('Удалить тариф?')) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const res = await fetch(`/admin/services/${serviceId}/tariffs/delete/${tariffId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `csrf_token=${encodeURIComponent(csrf)}`
                });
                if (res.ok) {
                    // Удаляем тариф из services и пересчитываем selectedTariffs
                    const sIdx = this.services.findIndex(s => s.service.id == serviceId);
                    if (sIdx !== -1) {
                        this.services[sIdx].tariffs = this.services[sIdx].tariffs.filter(t => t.id != tariffId);
                        this.services[sIdx].tariffs_count = this.services[sIdx].tariffs.length;
                    }
                    this.refreshSelectedTariffs();
                    window.dispatchEvent(new CustomEvent('toast:success', { detail: 'Тариф удалён' }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка удаления' }));
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast:error', { detail: 'Ошибка соединения' }));
            }
        },

        cancelEditing() {
            this.editingServiceId = null;
            this.editingTariffId = null;
            this.editingService = { name: '', description: '', rights: '', isActive: true, sortOrder: 0, groupId: null, servers: [] };
            this.editingTariff = { durationDays: 30, price: 100, isActive: true, sortOrder: 0 };
        },

        normalizeFlags(event) {
            let value = event.target.value.replace(/[^a-z]/g, '');
            value = [...new Set(value)].sort().join('');
            event.target.value = value;
            // если это поле в модели – обновим x-model
            if (this.editingService) {
                this.editingService.rights = value;
            }
        }
    }));
});
</script>
{% endblock %}