{% extends 'base.tpl' %}

{% block title %}{{ forum.title }} — {{ site_name }}{% endblock %}

{% block content %}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ url('/forum') }}" class="link-accent small mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-1"></i>К разделам
            </a>
            <h2 class="mb-0" style="color: var(--accent-bright);">
                <i class="{{ forum.icon }} me-2"></i>{{ forum.title }}
            </h2>
            <p class="text-secondary mb-0">{{ forum.description }}</p>
        </div>
        <a href="{{ url('/forum/forum/' ~ forum.id ~ '/create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Создать тему
        </a>
    </div>

    <div class="widget-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="small text-secondary">
                    <tr>
                        <th>Тема</th>
                        <th style="width: 100px;">Ответов</th>
                        <th style="width: 100px;">Просмотров</th>
                        <th style="width: 30%;">Последнее</th>
                    </tr>
                </thead>
                <tbody>
                    {% for thread in threads %}
                        <tr>
                            <td>
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-comment-alt me-3 mt-1" style="color: var(--accent);"></i>
                                    <div>
                                        <a href="{{ url('/forum/thread/' ~ thread.id) }}" class="fw-bold d-block" style="color: var(--text-primary);">
                                            {{ thread.title }}
                                        </a>
                                        <small class="text-secondary">
                                            Автор: 
                                            <a href="{{ url('/profile/' ~ thread.author.id) }}" class="text-secondary">{{ thread.author.username }}</a>
                                            · {{ thread.created_at|date('d.m.Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle text-center">{{ thread.replies }}</td>
                            <td class="align-middle text-center">{{ thread.views }}</td>
                            <td class="align-middle small">
                                {% if thread.last_post_user %}
                                    <a href="{{ url('/forum/thread/' ~ thread.id) }}#post-{{ thread.last_post_id }}" class="d-block text-truncate" style="color: var(--text-primary);">
                                        {{ thread.last_post_title }}
                                    </a>
                                    <span class="text-secondary">
                                        {{ thread.last_post_at|date('d.m.Y H:i') }} · 
                                        <a href="{{ url('/profile/' ~ thread.last_post_user.id) }}" class="text-secondary">{{ thread.last_post_user.username }}</a>
                                    </span>
                                {% else %}
                                    <span class="text-muted">Нет ответов</span>
                                {% endif %}
                            </td>
                        </tr>
                    {% else %}
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-comment-slash fa-2x mb-2" style="color: var(--accent);"></i>
                                <p>В этом разделе ещё нет тем. Будьте первым!</p>
                            </td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>

    {% include 'partials/pagination.tpl' with {
        'current': page,
        'total': total,
        'per_page': per_page,
        'url': '/forum/forum/' ~ forum.id
    } %}
{% endblock %}