{% extends "base.tpl" %}

{% block title %}{{ server.id ? 'Редактирование сервера' : 'Добавление сервера' }}{% endblock %}

{% block content %}
<h1>{{ server.id ? 'Редактирование' : 'Добавление' }} сервера</h1>

<form method="post" x-data="serverForm({{ server|json_encode|raw }})">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">

    <!-- Основные настройки -->
    <div class="card mb-4">
        <div class="card-header">Основные параметры</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Тип сервера</label>
                    <select name="type" class="form-select" x-model="type">
                        <option value="halflife">CS 1.6 (GoldSource)</option>
                        <option value="source">CS:GO / CS2 (Source)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">IP адрес</label>
                    <input type="text" name="ip" class="form-control" x-model="ip" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Порт (игровой)</label>
                    <input type="number" name="c_port" class="form-control" x-model="c_port" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Query порт</label>
                    <input type="number" name="q_port" class="form-control" x-model="q_port">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">RCON порт</label>
                    <input type="number" name="s_port" class="form-control" x-model="s_port">
                </div>
            </div>
            <div class="form-check">
                <input type="checkbox" name="disabled" class="form-check-input" id="disabled" value="1" x-model="disabled">
                <label class="form-check-label" for="disabled">Отключить (не показывать в мониторинге)</label>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Колонка 1: Интеграция привилегий -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Куда записывать купленные услуги</div>
                <div class="card-body">
                    <select name="privilege_storage" class="form-select" x-model="privilege_storage">
                        <option value="1">users.ini</option>
                        <option value="2">AmxBans / CsBans</option>
                        <option value="3">AmxBans/CsBans + users.ini</option>
                    </select>

                    <div x-show="privilege_storage == 2 || privilege_storage == 3" class="mt-3">
                        <h6>Подключение к AmxBans</h6>
                        <div class="mb-2">
                            <input type="text" name="amxbans_db_host" class="form-control" placeholder="Хост БД" x-model="amxbans_db_host">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="amxbans_db_user" class="form-control" placeholder="Пользователь" x-model="amxbans_db_user">
                        </div>
                        <div class="mb-2">
                            <input type="password" name="amxbans_db_pass" class="form-control" placeholder="Пароль">
                            {% if server.amxbans_db_pass %}
                                <small class="text-muted">Оставьте пустым, чтобы не менять</small>
                            {% endif %}
                        </div>
                        <div class="mb-2">
                            <input type="text" name="amxbans_db_name" class="form-control" placeholder="Имя БД" x-model="amxbans_db_name">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="amxbans_db_prefix" class="form-control" placeholder="Префикс таблиц (например, amx_)" x-model="amxbans_db_prefix">
                        </div>
                        <small class="text-muted">Бан-лист будет автоматически подключаться с этого сервера.</small>
                    </div>
                    <div x-show="privilege_storage == 1 || privilege_storage == 3" class="mt-3">
                        <h6>FTP подключение (users.ini)</h6>
                        <div class="mb-2">
                            <input type="text" name="ftp_host" class="form-control" placeholder="FTP хост" x-model="ftp_host">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="ftp_user" class="form-control" placeholder="Пользователь FTP" x-model="ftp_user">
                        </div>
                        <div class="mb-2">
                            <input type="password" name="ftp_pass" class="form-control" placeholder="Пароль FTP">
                            {% if server.ftp_pass %}
                                <small class="text-muted">Оставьте пустым, чтобы не менять</small>
                            {% endif %}
                        </div>
                        <div class="mb-2">
                            <input type="text" name="ftp_path" class="form-control" placeholder="Путь к файлу (например, /cstrike/addons/amxmodx/configs/users.ini)" x-model="ftp_path">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Колонка 2: Статистика -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Статистика</div>
                <div class="card-body">
                    <select name="stats_engine" class="form-select" x-model="stats_engine">
                        <option value="1">CSStats SQL</option>
                        <option value="2">AES Stats</option>
                        <option value="3">CSStats SQL + AES Stats</option>
                    </select>

                    <div x-show="stats_engine == 1 || stats_engine == 3" class="mt-3">
                        <h6>CSStats SQL</h6>
                        <div class="mb-2">
                            <input type="text" name="csstats_db_host" class="form-control" placeholder="Хост БД" x-model="csstats_db_host">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="csstats_db_user" class="form-control" placeholder="Пользователь" x-model="csstats_db_user">
                        </div>
                        <div class="mb-2">
                            <input type="password" name="csstats_db_pass" class="form-control" placeholder="Пароль">
                            {% if server.csstats_db_pass %}
                                <small class="text-muted">Оставьте пустым, чтобы не менять</small>
                            {% endif %}
                        </div>
                        <div class="mb-2">
                            <input type="text" name="csstats_db_name" class="form-control" placeholder="Имя БД" x-model="csstats_db_name">
                        </div>
                    </div>

                    <div x-show="stats_engine == 2 || stats_engine == 3" class="mt-3">
                        <h6>AES Stats</h6>
                        <div class="mb-2">
                            <input type="text" name="aes_stats_db_host" class="form-control" placeholder="Хост БД" x-model="aes_stats_db_host">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="aes_stats_db_user" class="form-control" placeholder="Пользователь" x-model="aes_stats_db_user">
                        </div>
                        <div class="mb-2">
                            <input type="password" name="aes_stats_db_pass" class="form-control" placeholder="Пароль">
                            {% if server.aes_stats_db_pass %}
                                <small class="text-muted">Оставьте пустым, чтобы не менять</small>
                            {% endif %}
                        </div>
                        <div class="mb-2">
                            <input type="text" name="aes_stats_db_name" class="form-control" placeholder="Имя БД" x-model="aes_stats_db_name">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Сохранить
        </button>
        <a href="{{ url('admin/server-settings') }}" class="btn btn-secondary">Отмена</a>
    </div>
</form>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script>
function serverForm(initialData) {
    return {
        type: initialData?.type || 'halflife',
        ip: initialData?.ip || '',
        c_port: initialData?.c_port || '',
        q_port: initialData?.q_port || '',
        s_port: initialData?.s_port || '',
        disabled: initialData?.disabled == 1,
        privilege_storage: initialData?.privilege_storage || 1,
        stats_engine: initialData?.stats_engine || 1,
        amxbans_db_host: initialData?.amxbans_db_host || '',
        amxbans_db_user: initialData?.amxbans_db_user || '',
        amxbans_db_name: initialData?.amxbans_db_name || '',
        amxbans_db_prefix: initialData?.amxbans_db_prefix || 'amx_',
        csstats_db_host: initialData?.csstats_db_host || '',
        csstats_db_user: initialData?.csstats_db_user || '',
        csstats_db_name: initialData?.csstats_db_name || '',
        aes_stats_db_host: initialData?.aes_stats_db_host || '',
        aes_stats_db_user: initialData?.aes_stats_db_user || '',
        aes_stats_db_name: initialData?.aes_stats_db_name || '',
        ftp_host: initialData?.ftp_host || '',
        ftp_user: initialData?.ftp_user || '',
        ftp_path: initialData?.ftp_path || '',
    };
}
</script>
{% endblock %}