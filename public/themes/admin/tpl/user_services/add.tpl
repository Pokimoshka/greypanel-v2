{% extends "base.tpl" %}
{% block title %}{{ trans('admin.issue_service') }} {{ user.username }}{% endblock %}
{% block page_title %}{{ trans('admin.issue_service') }}: {{ user.username }}{% endblock %}
{% block content %}
<a href="{{ url('admin/users/' ~ user.id ~ '/services') }}" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left"></i> {{ trans('admin.back_to_services') }}</a>

<form method="post" action="{{ url('admin/users/' ~ user.id ~ '/services/add') }}">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.service') }}</label>
        <select name="service_id" id="service_select" class="form-select" required>
            <option value="">-- {{ trans('admin.select_service') }} --</option>
            {% for srv in all_services %}
                <option value="{{ srv.getId() }}">{{ srv.getName() }} ({{ srv.getRights() }})</option>
            {% endfor %}
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.tariff') }}</label>
        <select name="tariff_id" id="tariff_select" class="form-select" required>
            <option value="">-- {{ trans('admin.select_service_first') }} --</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.bind_type') }}</label>
        <select name="bind_type" id="bind_type" class="form-select" onchange="showBindFields()">
            <option value="1">{{ trans('admin.nick_password') }}</option>
            <option value="2">STEAM ID</option>
            <option value="3">STEAM ID + {{ trans('admin.password') }}</option>
        </select>
    </div>
    <div id="nick_pass_fields">
        <div class="mb-3">
            <label class="form-label">{{ trans('admin.nick') }}</label>
            <input type="text" name="nick" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">{{ trans('admin.password') }}</label>
            <input type="text" name="password" class="form-control">
        </div>
    </div>
    <div id="steam_fields" class="mb-3" style="display:none;">
        <label class="form-label">STEAM ID</label>
        <input type="text" name="steam_id" class="form-control" placeholder="STEAM_0:1:12345">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('admin.activate_on_servers') }}</label>
        {% for server in all_servers %}
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="servers[]" value="{{ server.id }}" id="srv_{{ server.id }}">
                <label class="form-check-label" for="srv_{{ server.id }}">{{ server.ip }}:{{ server.c_port }}</label>
            </div>
        {% endfor %}
    </div>
    <button type="submit" class="btn btn-primary">{{ trans('admin.issue') }}</button>
</form>
{% endblock %}
{% block scripts %}
{{ parent() }}
<script>
const serviceSelect = document.getElementById('service_select');
const tariffSelect = document.getElementById('tariff_select');
serviceSelect.addEventListener('change', async () => {
    const serviceId = serviceSelect.value;
    tariffSelect.innerHTML = '<option value="">{{ trans("admin.loading") }}...</option>';
    if (!serviceId) return;
    try {
        const resp = await fetch(`/api/services/${serviceId}/tariffs`);
        const data = await resp.json();
        tariffSelect.innerHTML = '';
        if (data.length === 0) {
            tariffSelect.innerHTML = '<option value="">{{ trans("admin.no_tariffs") }}</option>';
            return;
        }
        data.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = `${t.durationDays} {{ trans("admin.days") }} – ${t.price} ₽`;
            tariffSelect.appendChild(opt);
        });
    } catch(e) {
        tariffSelect.innerHTML = '<option value="">{{ trans("admin.load_error") }}</option>';
    }
});

function showBindFields() {
    const type = document.getElementById('bind_type').value;
    document.getElementById('nick_pass_fields').style.display = (type == '1' || type == '3') ? '' : 'none';
    document.getElementById('steam_fields').style.display = (type == '2' || type == '3') ? '' : 'none';
}
showBindFields();
</script>
{% endblock %}