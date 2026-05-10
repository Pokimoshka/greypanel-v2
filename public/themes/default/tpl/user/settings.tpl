{% extends 'base.tpl' %}

{% block title %}{{ trans('settings.title') }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="widget-card p-4">
                <h4 class="mb-4"><i class="fas fa-user-cog me-2" style="color: var(--accent);"></i>{{ trans('settings.title') }}</h4>
                
                {% if error %}
                    <div class="alert alert-danger">{{ error }}</div>
                {% endif %}
                {% if success %}
                    <div class="alert alert-success">{{ success }}</div>
                {% endif %}

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    
                    <div class="mb-3">
                        <label class="form-label">{{ trans('settings.email') }}</label>
                        <input type="email" name="email" class="form-control" value="{{ user.email }}">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('settings.new_password') }}</label>
                            <input type="password" name="password" class="form-control" placeholder="{{ trans('settings.password_placeholder') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('settings.password_confirm') }}</label>
                            <input type="password" name="password_confirm" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">{{ trans('settings.avatar') }}</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <div class="form-text text-secondary">{{ trans('settings.avatar_hint') }}</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>{{ trans('settings.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
{% endblock %}