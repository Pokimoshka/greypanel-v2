{% extends 'base.tpl' %}

{% block title %}Настройки — {{ site_name }}{% endblock %}

{% block content %}
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="widget-card p-4">
                <h4 class="mb-4"><i class="fas fa-user-cog me-2" style="color: var(--accent);"></i>Настройки аккаунта</h4>
                
                {% if error %}
                    <div class="alert alert-danger">{{ error }}</div>
                {% endif %}
                {% if success %}
                    <div class="alert alert-success">{{ success }}</div>
                {% endif %}

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ user.email }}">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="password" class="form-control" placeholder="Оставьте пустым, чтобы не менять">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Повторите пароль</label>
                            <input type="password" name="password_confirm" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Аватар</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <div class="form-text text-secondary">JPEG, PNG, GIF до 2 МБ</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Сохранить
                    </button>
                </form>
            </div>
        </div>
    </div>
{% endblock %}