{% extends "base.tpl" %}

{% block title %}{{ trans('admin.services') }}{% endblock %}
{% block page_title %}{{ trans('admin.services') }}{% endblock %}

{% block content %}
<div x-data="serviceManager" x-init="init({{ selected_service_id|default('null') }})">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-plus-circle me-1"></i>{{ trans('admin.add_service') }}</div>
                <div class="card-body">
                    <form method="post" action="{{ url('admin/services/add') }}">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                        <div class="mb-3">
                            <label class="form-label">{{ trans('admin.service_name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('admin.service_description') }}</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('admin.flags') }}</label>
                                <input type="text" name="rights" class="form-control" placeholder="abcdef" @input="normalizeFlags($event)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('admin.group_on_purchase') }}</label>
                                <select name="group_id" class="form-select">
                                    <option value="">{{ trans('admin.no_change') }}</option>
                                    {% for g in all_groups %}
                                        <option value="{{ g.id }}">{{ g.name }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('admin.servers') }}</label>
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
                                <label class="form-label">{{ trans('admin.sort_order') }}</label>
                                <input type="number" name="sort_order" class="form-control" value="0">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">{{ trans('admin.active') }}</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">{{ trans('admin.create_service') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-tags me-1"></i>{{ trans('admin.add_tariff') }}</div>
                <div class="card-body">
                    <label class="form-label">{{ trans('admin.service') }}</label>
                    <select x-model="selectedServiceId" class="form-select mb-3">
                        <option value="">-- {{ trans('admin.select_service') }} --</option>
                        <template x-for="item in services" :key="item.service.id">
                            <option :value="item.service.id" x-text="item.service.name"></option>
                        </template>
                    </select>

                    <template x-if="selectedServiceId">
                        <form method="post" :action="'/admin/services/' + selectedServiceId + '/tariffs/add'" @submit.prevent="addTariff(selectedServiceId, $event)">
                            <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ trans('admin.tariff_duration') }}</label>
                                    <input type="number" name="duration_days" class="form-control" value="30" min="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ trans('admin.tariff_price') }}</label>
                                    <input type="number" name="price" class="form-control" value="100" min="1" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ trans('admin.sort_order') }}</label>
                                    <input type="number" name="sort_order" class="form-control" value="0">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                                        <label class="form-check-label">{{ trans('admin.tariff_active') }}</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">{{ trans('admin.add_tariff') }}</button>
                        </form>
                    </template>
                    <div x-show="!selectedServiceId" class="text-muted small">{{ trans('admin.select_service') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><i class="fas fa-list me-1"></i>{{ trans('admin.services_list') }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr><th>{{ trans('admin.name') }}</th><th>{{ trans('admin.flags') }}</th><th>{{ trans('admin.tariffs_of_service') }}</th><th>{{ trans('admin.actions') }}</th></tr>
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
                                                    <button class="btn btn-sm btn-outline-primary" @click="startEditService(item.service.id)"><i class="fas fa-pen"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" @click="deleteService(item.service.id)"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </template>
                                            <template x-if="editingServiceId === item.service.id">
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-success" @click="saveEditing">{{ trans('admin.save') }}</button>
                                                    <button class="btn btn-sm btn-secondary" @click="cancelEditing">{{ trans('admin.cancel') }}</button>
                                                </div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="services.length === 0">
                                    <td colspan="4" class="text-center text-muted">{{ trans('admin.no_services') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><i class="fas fa-list me-1"></i>{{ trans('admin.tariffs_of_service') }}</div>
                <div class="card-body p-0">
                    <template x-if="selectedServiceId">
                        <div>
                            <table class="table table-sm mb-0">
                                <thead><tr><th>{{ trans('admin.days') }}</th><th>{{ trans('admin.tariff_price') }}</th><th>{{ trans('admin.tariff_active') }}</th><th>{{ trans('admin.actions') }}</th></tr></thead>
                                <tbody>
                                    <template x-for="t in selectedTariffs" :key="t.id">
                                        <tr>
                                            <td>
                                                <template x-if="editingTariffId !== t.id">
                                                    <span x-text="t.durationDays + ' {{ trans('admin.days') }}'"></span>
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
                                                    <span x-text="t.isActive ? '{{ trans('common.yes') }}' : '{{ trans('common.no') }}'"></span>
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
                                                        <button class="btn btn-sm btn-outline-primary" @click="startEditTariff(t.id)"><i class="fas fa-pen"></i></button>
                                                        <button class="btn btn-sm btn-outline-danger" @click="deleteTariff(selectedServiceId, t.id)"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </template>
                                                <template x-if="editingTariffId === t.id">
                                                    <div class="d-flex gap-1">
                                                        <button class="btn btn-sm btn-success" @click="saveEditing">{{ trans('admin.save') }}</button>
                                                        <button class="btn btn-sm btn-secondary" @click="cancelEditing">{{ trans('admin.cancel') }}</button>
                                                        <button class="btn btn-sm btn-outline-danger" @click="deleteTariff(selectedServiceId, t.id)"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="selectedTariffs.length === 0">
                                        <td colspan="4" class="text-center text-muted">{{ trans('admin.no_tariffs') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <div x-show="!selectedServiceId" class="p-3 text-muted text-center">{{ trans('admin.select_service') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script>
    window._services = {{ services|json_encode|raw }};
</script>
{% endblock %}