{% extends "base.tpl" %}

{% block title %}{{ server.id ? trans('admin.edit_server') : trans('admin.add_server') }}{% endblock %}

{% block content %}
<h1>{{ server.id ? trans('admin.edit_server') : trans('admin.add_server') }}</h1>

<script>
  window.__serverData = {{ server|json_encode|raw }} || {};
  window.serverForm = function(initialData) {
    return {
      type: initialData?.type || 'halflife',
      ip: initialData?.ip || '',
      c_port: initialData?.c_port || '',
      q_port: initialData?.q_port || '',
      s_port: initialData?.s_port || '',
      disabled: initialData?.disabled == 1,
      privilege_storage: initialData?.privilege_storage ?? 0,
      stats_engine: initialData?.stats_engine ?? 0,
      banlist_db_host: initialData?.banlist_db_host || '',
      banlist_db_user: initialData?.banlist_db_user || '',
      banlist_db_name: initialData?.banlist_db_name || '',
      banlist_db_prefix: initialData?.banlist_db_prefix || 'amx_',
      csstats_db_host: initialData?.csstats_db_host || '',
      csstats_db_user: initialData?.csstats_db_user || '',
      csstats_db_name: initialData?.csstats_db_name || '',
      csstats_table: initialData?.csstats_table || '',
      aes_stats_db_host: initialData?.aes_stats_db_host || '',
      aes_stats_db_user: initialData?.aes_stats_db_user || '',
      aes_stats_db_name: initialData?.aes_stats_db_name || '',
      ftp_host: initialData?.ftp_host || '',
      ftp_user: initialData?.ftp_user || '',
      ftp_path: initialData?.ftp_path || '',
    };
  };
</script>

<form method="post" x-data="serverForm(window.__serverData)">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">

    <div class="card mb-4">
        <div class="card-header">{{ trans('admin.main_params') }}</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ trans('admin.type') }}</label>
                    <select name="type" class="form-select" x-model="type">
                        <option value="halflife">CS 1.6 (GoldSource)</option>
                        <option value="source">CS:GO / CS2 (Source)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ trans('admin.ip') }}</label>
                    <input type="text" name="ip" class="form-control" x-model="ip" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">{{ trans('admin.port') }}</label>
                    <input type="number" name="c_port" class="form-control" x-model="c_port" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">{{ trans('admin.query_port') }}</label>
                    <input type="number" name="q_port" class="form-control" x-model="q_port">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">{{ trans('admin.rcon_port') }}</label>
                    <input type="number" name="s_port" class="form-control" x-model="s_port">
                </div>
            </div>
            <div class="form-check">
                <input type="checkbox" name="disabled" class="form-check-input" id="disabled" value="1" x-model="disabled">
                <label class="form-check-label" for="disabled">{{ trans('admin.disable') }}</label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">{{ trans('admin.privilege_integration') }}</div>
                <div class="card-body">
                    <select name="privilege_storage" class="form-select" x-model="privilege_storage">
                        <option value="0" x-bind:selected="privilege_storage == 0">{{ trans('admin.none') }}</option>
                        <option value="1">users.ini</option>
                        <option value="2">banlist / CsBans</option>
                        <option value="3">AmxBans/CsBans + users.ini</option>
                        <option value="4" x-bind:selected="privilege_storage == 4">SourceBans</option>
                    </select>

                    <div x-show="privilege_storage == 2 || privilege_storage == 3" class="mt-3">
                        <h6>{{ trans('admin.banlist_connection') }}</h6>
                        <div class="mb-2"><input type="text" name="banlist_db_host" class="form-control" placeholder="{{ trans('admin.db_host') }}" x-model="banlist_db_host"></div>
                        <div class="mb-2"><input type="text" name="banlist_db_user" class="form-control" placeholder="{{ trans('admin.db_user') }}" x-model="banlist_db_user"></div>
                        <div class="mb-2"><input type="password" name="banlist_db_pass" class="form-control" placeholder="••••••••"></div>
                        <div class="mb-2"><input type="text" name="banlist_db_name" class="form-control" placeholder="{{ trans('admin.db_name') }}" x-model="banlist_db_name"></div>
                        <div class="mb-2"><input type="text" name="banlist_db_prefix" class="form-control" placeholder="{{ trans('admin.db_prefix') }}" x-model="banlist_db_prefix"></div>
                        <small class="text-muted">{{ trans('admin.ban_list_auto') }}</small>
                    </div>
                    <div x-show="privilege_storage == 1 || privilege_storage == 3" class="mt-3">
                        <h6>{{ trans('admin.ftp_connection') }}</h6>
                        <div class="mb-2"><input type="text" name="ftp_host" class="form-control" placeholder="{{ trans('admin.ftp_host') }}" x-model="ftp_host"></div>
                        <div class="mb-2"><input type="text" name="ftp_user" class="form-control" placeholder="{{ trans('admin.ftp_user') }}" x-model="ftp_user"></div>
                        <div class="mb-2"><input type="password" name="ftp_pass" class="form-control" placeholder="••••••••"></div>
                        <div class="mb-2"><input type="text" name="ftp_path" class="form-control" placeholder="{{ trans('admin.ftp_path') }}" x-model="ftp_path"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">{{ trans('admin.stats_integration') }}</div>
                <div class="card-body">
                    <select name="stats_engine" class="form-select" x-model="stats_engine">
                        <option value="0">{{ trans('admin.none') }}</option>
                        <option value="1">CSStats SQL</option>
                        <option value="2">AES Stats</option>
                        <option value="3">CSStats SQL + AES Stats</option>
                        <option value="4">RankMe</option>
                        <option value="5">Levels Ranks</option>
                    </select>

                    <div x-show="stats_engine != 0" class="mt-3">
                        <h6>{{ trans('admin.stats_db_connection') }}</h6>
                        <div class="mb-2"><input type="text" name="csstats_db_host" class="form-control" placeholder="{{ trans('admin.db_host') }}" x-model="csstats_db_host"></div>
                        <div class="mb-2"><input type="text" name="csstats_db_user" class="form-control" placeholder="{{ trans('admin.db_user') }}" x-model="csstats_db_user"></div>
                        <div class="mb-2"><input type="password" name="csstats_db_pass" class="form-control" placeholder="••••••••""></div>
                        <div class="mb-2"><input type="text" name="csstats_db_name" class="form-control" placeholder="{{ trans('admin.db_name') }}" x-model="csstats_db_name"></div>
                        <div class="mb-2"><input type="text" name="csstats_table" class="form-control" placeholder="{{ trans('admin.table_name') }}" x-model="csstats_table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ trans('admin.save') }}</button>
        <a href="{{ url('admin/server-settings') }}" class="btn btn-secondary">{{ trans('admin.cancel') }}</a>
    </div>
</form>
{% endblock %}

{% block scripts %}
{{ parent() }}
{% endblock %}