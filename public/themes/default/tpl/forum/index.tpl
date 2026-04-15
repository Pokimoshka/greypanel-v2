{% extends 'base.tpl' %}

{% block title %}Форум — {{ site_name }}{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0" style="color: var(--accent-bright);">
            <i class="fas fa-comments me-2"></i>Форум
        </h2>
        <a href="{{ url('/forum/search') }}" class="link-accent">
            <i class="fas fa-search me-1"></i>Поиск
        </a>
    </div>

    {% for category in categories %}
        <div class="widget-card p-0 mb-4 overflow-hidden">
            <div class="p-3 border-bottom" style="border-color: var(--border-color) !important; background: linear-gradient(145deg, var(--bg-surface), var(--card-bg));">
                <h4 class="mb-0">{{ category.title }}</h4>
                {% if category.description %}
                    <p class="text-secondary small mb-0 mt-1">{{ category.description }}</p>
                {% endif %}
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="small text-secondary">
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Раздел</th>
                            <th style="width: 100px;">Темы</th>
                            <th style="width: 35%;">Последнее сообщение</th>
                        </tr>
                    </thead>
                    <tbody>
                        {% for forum in category.forums %}
                            <tr>
                                <td class="text-center align-middle">
                                    <i class="{{ forum.icon }} fa-lg" style="color: var(--accent);"></i>
                                </td>
                                <td>
                                    <a href="{{ url('/forum/forum/' ~ forum.id) }}" class="fw-bold d-block" style="color: var(--text-primary);">
                                        {{ forum.title }}
                                    </a>
                                    <small class="text-secondary">{{ forum.description }}</small>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge" style="background: var(--accent);">{{ forum.threads_count }}</span>
                                </td>
                                <td class="align-middle small">
                                    {% if forum.last_thread %}
                                        <a href="{{ url('/forum/thread/' ~ forum.last_thread.id) }}" class="d-block text-truncate" style="color: var(--text-primary);">
                                            {{ forum.last_thread.title }}
                                        </a>
                                        <span class="text-secondary">
                                            {{ forum.last_thread.created_at|date('d.m.Y H:i') }}
                                            {% if forum.last_thread.author_name %}
                                                · {{ forum.last_thread.author_name }}
                                            {% endif %}
                                        </span>
                                    {% else %}
                                        <span class="text-muted">Нет сообщений</span>
                                    {% endif %}
                                </td>
                            </tr>
                        {% else %}
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">В этой категории нет разделов</td>
                            </tr>
                        {% endfor %}
                    </tbody>
                </table>
            </div>
        </div>
    {% else %}
        <div class="widget-card p-4 text-center text-muted">
            <i class="fas fa-comments fa-3x mb-3" style="color: var(--accent);"></i>
            <h5>Форум пока пуст</h5>
            <p>Загляните позже или станьте первым автором!</p>
        </div>
    {% endfor %}
{% endblock %}